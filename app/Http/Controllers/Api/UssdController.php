<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\StudentInfo\Student;
use App\Models\Fees\FeesAssignChildren;

class UssdController extends Controller
{
    /**
     * Handle USSD session for Nalopa School
     */
    public function handle(Request $request)
    {
        try {
            $sessionId = $request->input('sessionId');
            $phoneNumber = $request->input('phoneNumber');
            $userInput = trim($request->input('userInput', ''));
            $institutionId = $request->input('institution_id');

            Log::info('Nalopa School USSD Request', [
                'sessionId' => $sessionId,
                'phoneNumber' => $phoneNumber,
                'userInput' => $userInput
            ]);

            // Normalize phone number
            $normalizedPhone = $this->normalizePhone($phoneNumber);

            // Get or create session
            $session = $this->getOrCreateSession($sessionId, $phoneNumber, $institutionId);
            
            // Check if session is null (timed out or duplicate)
            if ($session === null) {
                return $this->formatUssdResponse("END " . $this->trans('timeout', 'sw'));
            }
            
            $sessionData = is_array($session->session_data) ? $session->session_data : (json_decode($session->session_data ?? '{}', true) ?? []);
            // Get language from last session for this phone number, default to Swahili
            if (!isset($sessionData['language'])) {
                $sessionData['language'] = $this->getLanguageFromLastSession($phoneNumber);
            }
            $currentMenu = $session->current_menu ?? 'main';
            
            // Check if this is a brand new session (created within the last 2 seconds)
            $isNewSession = false;
            if (isset($session->created_at)) {
                $createdAt = is_string($session->created_at) 
                    ? \Carbon\Carbon::parse($session->created_at) 
                    : $session->created_at;
                $isNewSession = $createdAt->diffInSeconds(now()) <= 2;
            }
            
            Log::info('USSD Session State', [
                'sessionId' => $sessionId,
                'currentMenu' => $currentMenu,
                'userInput' => $userInput,
                'sessionData' => $sessionData,
                'isNewSession' => $isNewSession
            ]);

            // For new sessions, always show main menu first, ignore any userInput
            if ($isNewSession && !empty($userInput)) {
                Log::info('New session detected with userInput, ignoring input and showing main menu', [
                    'sessionId' => $sessionId,
                    'userInput' => $userInput
                ]);
                $this->updateSession($sessionId, 'main', $sessionData);
                return $this->showMainMenu($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
            }

            // Handle user input based on current menu
            if (empty($userInput)) {
                // If no input and we're at main menu, show main menu
                if ($currentMenu === 'main') {
                    return $this->showMainMenu($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
                }
                // If no input but not at main menu, reset to main menu
                // This handles cases where session state might be corrupted
                Log::warning('Empty userInput but not at main menu, resetting', [
                    'sessionId' => $sessionId,
                    'currentMenu' => $currentMenu
                ]);
                $this->updateSession($sessionId, 'main', $sessionData);
                return $this->showMainMenu($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
            }

            // Handle "99" to return to main menu (check timeout first)
            if ($userInput === '99') {
                if ($this->isSessionTimedOut($session)) {
                    $this->terminateSession($sessionId);
                    $lang = $this->getLanguage($sessionData);
                    return $this->formatUssdResponse("END " . $this->trans('session_timeout', $lang));
                }
                $this->updateSession($sessionId, 'main', $sessionData);
                return $this->showMainMenu($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
            }

            // Process user selection based on current menu state
            switch ($currentMenu) {
                case 'language_selection':
                    return $this->handleLanguageSelection($sessionId, $userInput, $phoneNumber, $normalizedPhone, $sessionData);
                
                case 'main':
                    return $this->handleMainMenu($sessionId, $userInput, $phoneNumber, $normalizedPhone, $sessionData);
                
                case 'student_selection':
                    // Check timeout before processing
                    if ($this->isSessionTimedOut($session)) {
                        $this->terminateSession($sessionId);
                        return $this->formatUssdResponse("END Session has timed out. Please start again.");
                    }
                    return $this->handleStudentSelection($sessionId, $userInput, $phoneNumber, $normalizedPhone, $sessionData);
                
                case 'student_selection_payment':
                    // Check timeout before processing
                    if ($this->isSessionTimedOut($session)) {
                        $this->terminateSession($sessionId);
                        return $this->formatUssdResponse("END Session has timed out. Please start again.");
                    }
                    return $this->handleStudentSelectionForPayment($sessionId, $userInput, $phoneNumber, $normalizedPhone, $sessionData);
                
                case 'student_info':
                    // Check timeout before processing
                    if ($this->isSessionTimedOut($session)) {
                        $this->terminateSession($sessionId);
                        return $this->formatUssdResponse("END Session has timed out. Please start again.");
                    }
                    return $this->showStudentInfo($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
                
                case 'payment_amount':
                    // Check timeout before processing
                    if ($this->isSessionTimedOut($session)) {
                        $this->terminateSession($sessionId);
                        return $this->formatUssdResponse("END Session has timed out. Please start again.");
                    }
                    return $this->handlePaymentAmount($sessionId, $userInput, $phoneNumber, $normalizedPhone, $sessionData);
                
                case 'payment_confirm':
                    // Check timeout before processing
                    if ($this->isSessionTimedOut($session)) {
                        $this->terminateSession($sessionId);
                        return $this->formatUssdResponse("END Session has timed out. Please start again.");
                    }
                    return $this->handlePaymentConfirm($sessionId, $userInput, $phoneNumber, $normalizedPhone, $sessionData);
                
                default:
                    // Check timeout before processing
                    if ($this->isSessionTimedOut($session)) {
                        $this->terminateSession($sessionId);
                        return $this->formatUssdResponse("END Session has timed out. Please start again.");
                    }
                    return $this->formatUssdResponse("CON Invalid selection. Please try again.\n\n1. Student Info & Balance\n2. Pay Fees\n0. Exit");
            }

        } catch (\Exception $e) {
            Log::error('Nalopa USSD Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->formatUssdResponse("END An error occurred. Please try again later.");
        }
    }

    /**
     * Show main menu
     */
    private function showMainMenu($sessionId, $phoneNumber, $normalizedPhone, $sessionData = [])
    {
        $lang = $this->getLanguage($sessionData);
        $this->updateSession($sessionId, 'main', $sessionData);

        $message = $this->trans('welcome', $lang) . "\n";
        $message .= "1. " . $this->trans('student_info', $lang) . "\n";
        $message .= "2. " . $this->trans('pay_fees', $lang) . "\n";
        $message .= "3. " . $this->trans('change_language', $lang) . "\n";
        $message .= "0. " . $this->trans('exit', $lang);

        return $this->formatUssdResponse("CON " . $message);
    }

    /**
     * Handle language selection
     */
    private function handleLanguageSelection($sessionId, $userInput, $phoneNumber, $normalizedPhone, $sessionData)
    {
        $currentLang = $this->getLanguage($sessionData);
        $lang = 'sw'; // Default
        
        switch ($userInput) {
            case '1':
                $lang = 'en';
                break;
            case '2':
                $lang = 'sw';
                break;
            case '0':
                // Go back to main menu
                $this->updateSession($sessionId, 'main', $sessionData);
                return $this->showMainMenu($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
            default:
                // Check timeout before repeating menu
                $session = $this->getOrCreateSession($sessionId, $phoneNumber, null);
                if ($this->isSessionTimedOut($session)) {
                    $this->terminateSession($sessionId);
                    return $this->formatUssdResponse("END " . $this->trans('session_timeout', $currentLang));
                }
                // Repeat language selection
                $this->updateSession($sessionId, 'language_selection', $sessionData);
                $message = $this->trans('select_language', $currentLang) . "\n";
                $message .= "1. " . $this->trans('english', $currentLang) . "\n";
                $message .= "2. " . $this->trans('kiswahili', $currentLang) . "\n";
                $message .= "0. " . $this->trans('back', $currentLang);
                return $this->formatUssdResponse("CON " . $message);
        }
        
        // Store language preference in session
        $sessionData['language'] = $lang;
        $this->updateSession($sessionId, 'main', $sessionData);
        
        // Show main menu with new language
        return $this->showMainMenu($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
    }

    /**
     * Handle main menu selection
     */
    private function handleMainMenu($sessionId, $userInput, $phoneNumber, $normalizedPhone, $sessionData)
    {
        $lang = $this->getLanguage($sessionData);
        
        switch ($userInput) {
            case '1':
                $this->updateSession($sessionId, 'student_info', $sessionData);
                return $this->showStudentInfo($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
            
            case '2':
                // Check if we need student selection for payment
                $students = $this->findStudentsByPhone($normalizedPhone);
                
                if ($students->isEmpty()) {
                    $this->terminateSession($sessionId);
                    return $this->formatUssdResponse("END " . $this->trans('phone_not_found', $lang));
                }
                
                if ($students->count() === 1) {
                    // Single student - proceed directly to amount entry
                    $sessionData['selected_student_id'] = $students->first()->id;
                    $this->updateSession($sessionId, 'payment_amount', $sessionData);
                    return $this->formatUssdResponse("CON " . $this->trans('enter_amount', $lang));
                } else {
                    // Multiple students - show selection menu for payment
                    return $this->showStudentSelectionMenuForPayment($sessionId, $phoneNumber, $normalizedPhone, $students, $sessionData);
                }
            
            case '3':
                // Show language selection menu
                $this->updateSession($sessionId, 'language_selection', $sessionData);
                $message = $this->trans('select_language', $lang) . "\n";
                $message .= "1. " . $this->trans('english', $lang) . "\n";
                $message .= "2. " . $this->trans('kiswahili', $lang) . "\n";
                $message .= "0. " . $this->trans('back', $lang);
                return $this->formatUssdResponse("CON " . $message);
            
            case '0':
                $this->terminateSession($sessionId);
                return $this->formatUssdResponse("END " . $this->trans('thank_you', $lang));
            
            default:
                // Check timeout before repeating menu
                $session = $this->getOrCreateSession($sessionId, $phoneNumber, null);
                if ($this->isSessionTimedOut($session)) {
                    $this->terminateSession($sessionId);
                    return $this->formatUssdResponse("END " . $this->trans('session_timeout', $lang));
                }
                // Repeat the same menu for invalid selection
                return $this->showMainMenu($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
        }
    }

    /**
     * Show student info and balance
     */
    private function showStudentInfo($sessionId, $phoneNumber, $normalizedPhone, $sessionData = [])
    {
        try {
            // If student_id is in session data (from selection), use it
            if (isset($sessionData['selected_student_id'])) {
                $student = Student::where('students.id', $sessionData['selected_student_id'])
                    ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
                    ->join('classes', 'session_class_students.classes_id', '=', 'classes.id')
                    ->select('students.*', 'classes.name as class_name')
                    ->first();
            } else {
                // Find students by phone
                $students = $this->findStudentsByPhone($normalizedPhone);
                
            $lang = $this->getLanguage($sessionData);
            
            if ($students->isEmpty()) {
                $this->terminateSession($sessionId);
                return $this->formatUssdResponse("END " . $this->trans('phone_not_found', $lang));
            }
            
            // If only one student, use it directly
            if ($students->count() === 1) {
                $student = $students->first();
            } else {
                // Multiple students - show selection menu
                return $this->showStudentSelectionMenu($sessionId, $phoneNumber, $normalizedPhone, $students, $sessionData);
            }
        }

        $lang = $this->getLanguage($sessionData);

        if (!$student) {
            $this->terminateSession($sessionId);
            return $this->formatUssdResponse("END " . $this->trans('phone_not_found', $lang));
        }

        // Get all fee groups with their remaining balances
        // Group by fee_group to show each fee group separately
        $feeDetails = DB::table('fees_assign_childrens')
            ->join('fees_masters', 'fees_assign_childrens.fees_master_id', '=', 'fees_masters.id')
            ->join('fees_assigns', 'fees_assign_childrens.fees_assign_id', '=', 'fees_assigns.id')
            ->leftJoin('fees_groups', 'fees_assigns.fees_group_id', '=', 'fees_groups.id')
            ->where('fees_assign_childrens.student_id', $student->id)
            ->select(
                'fees_groups.name as fee_group_name',
                'fees_groups.id as fee_group_id',
                DB::raw('SUM(COALESCE(fees_assign_childrens.fees_amount, 0)) as total_fees'),
                DB::raw('SUM(COALESCE(fees_assign_childrens.paid_amount, 0)) as paid_amount'),
                DB::raw('SUM(COALESCE(fees_assign_childrens.remained_amount, 0)) as remained_amount')
            )
            ->groupBy('fees_groups.id', 'fees_groups.name')
            ->get();

        // Build message with all fee types
        $message = $this->trans('student', $lang) . ": " . trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) . "\n";
        $message .= $this->trans('class', $lang) . ": " . ($student->class_name ?? 'N/A') . "\n\n";
        
        if ($feeDetails->isEmpty()) {
            $message .= $this->trans('no_fees', $lang);
        } else {
            $message .= $this->trans('fee_details', $lang) . "\n";
            foreach ($feeDetails as $index => $fee) {
                $feeGroupName = $fee->fee_group_name ?? 'Fee Group ' . ($index + 1);
                $remainedAmount = (float) ($fee->remained_amount ?? 0);
                $totalFees = (float) ($fee->total_fees ?? 0);
                $paidAmount = (float) ($fee->paid_amount ?? 0);
                
                $message .= ($index + 1) . ". " . $feeGroupName . "\n";
                $message .= "   " . $this->trans('total', $lang) . ": TZS " . number_format($totalFees, 0) . "\n";
                $message .= "   " . $this->trans('paid', $lang) . ": TZS " . number_format($paidAmount, 0) . "\n";
                $message .= "   " . $this->trans('balance', $lang) . ": TZS " . number_format($remainedAmount, 0) . "\n";
            }
            
            // Calculate totals
            $totalRemained = $feeDetails->sum('remained_amount');
            $message .= "\n" . $this->trans('total_balance', $lang) . ": TZS " . number_format($totalRemained, 0);
        }

            $this->terminateSession($sessionId);
            return $this->formatUssdResponse("END " . $message);

        } catch (\Exception $e) {
            Log::error('Student Info Error', ['error' => $e->getMessage()]);
            $this->terminateSession($sessionId);
            return $this->formatUssdResponse("END Error retrieving student information. Please try again later.");
        }
    }

    /**
     * Find students by phone number (user or parent/guardian)
     */
    private function findStudentsByPhone($normalizedPhone)
    {
        $students = collect();
        
        // First, try to find user by phone
        $user = User::where(function($query) use ($normalizedPhone) {
            $query->where('phone', 'like', '%' . $normalizedPhone . '%')
                  ->orWhere('phone', 'like', '%' . substr($normalizedPhone, -9) . '%');
        })->first();

        if ($user) {
            // Get all students for this user
            $userStudents = Student::where('user_id', $user->id)
                ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
                ->join('classes', 'session_class_students.classes_id', '=', 'classes.id')
                ->select('students.*', 'classes.name as class_name')
                ->get();
            
            $students = $students->merge($userStudents);
        }

        // Also try to find by parent/guardian phone
        $parent = DB::table('parent_guardians')
            ->where(function($query) use ($normalizedPhone) {
                // Check guardian_mobile first
                if (DB::getSchemaBuilder()->hasColumn('parent_guardians', 'guardian_mobile')) {
                    $query->where('guardian_mobile', 'like', '%' . $normalizedPhone . '%')
                          ->orWhere('guardian_mobile', 'like', '%' . substr($normalizedPhone, -9) . '%');
                }
                // Also check father_mobile and mother_mobile as fallback
                if (DB::getSchemaBuilder()->hasColumn('parent_guardians', 'father_mobile')) {
                    $query->orWhere('father_mobile', 'like', '%' . $normalizedPhone . '%')
                          ->orWhere('father_mobile', 'like', '%' . substr($normalizedPhone, -9) . '%');
                }
                if (DB::getSchemaBuilder()->hasColumn('parent_guardians', 'mother_mobile')) {
                    $query->orWhere('mother_mobile', 'like', '%' . $normalizedPhone . '%')
                          ->orWhere('mother_mobile', 'like', '%' . substr($normalizedPhone, -9) . '%');
                }
            })
            ->first();

        if ($parent) {
            // Get all students by parent_id
            $studentQuery = Student::query();
            
            if (DB::getSchemaBuilder()->hasColumn('students', 'parent_id')) {
                $studentQuery->where('parent_id', $parent->id);
            } else {
                // Fallback: if parent_id doesn't exist, try user_id relationship
                if (isset($parent->user_id)) {
                    $studentQuery->where('user_id', $parent->user_id);
                }
            }
            
            $parentStudents = $studentQuery
                ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
                ->join('classes', 'session_class_students.classes_id', '=', 'classes.id')
                ->select('students.*', 'classes.name as class_name')
                ->get();
            
            // Merge and remove duplicates
            $students = $students->merge($parentStudents)->unique('id');
        }

        return $students;
    }

    /**
     * Show student selection menu when parent has multiple students
     */
    private function showStudentSelectionMenu($sessionId, $phoneNumber, $normalizedPhone, $students, $sessionData = [])
    {
        $lang = $this->getLanguage($sessionData);
        
        $message = $this->trans('select_student', $lang) . "\n";
        $studentsArray = [];
        $index = 1;
        
        foreach ($students as $student) {
            $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
            $className = $student->class_name ?? 'N/A';
            $message .= $index . ". " . $studentName . " (" . $className . ")\n";
            
            $studentsArray[] = [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'class_name' => $className
            ];
            $index++;
        }
        $message .= "0. " . $this->trans('back', $lang) . "\n";
        $message .= "99. " . $this->trans('main_menu', $lang);

        $this->updateSession($sessionId, 'student_selection', array_merge($sessionData, [
            'students' => $studentsArray
        ]));

        return $this->formatUssdResponse("CON " . $message);
    }

    /**
     * Handle student selection
     */
    private function handleStudentSelection($sessionId, $userInput, $phoneNumber, $normalizedPhone, $sessionData)
    {
        // Check timeout first
        $session = $this->getOrCreateSession($sessionId, $phoneNumber, null);
        if ($this->isSessionTimedOut($session)) {
            $this->terminateSession($sessionId);
            return $this->formatUssdResponse("END Session has timed out. Please start again.");
        }

        // Check for return to main menu
        if ($userInput === '99') {
            $this->updateSession($sessionId, 'main', $sessionData);
            return $this->showMainMenu($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
        }

        if ($userInput === '0') {
            $this->updateSession($sessionId, 'main', $sessionData);
            return $this->showMainMenu($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
        }

        $students = $sessionData['students'] ?? [];
        $selectedIndex = (int) $userInput - 1;

        if (!isset($students[$selectedIndex])) {
            // Repeat the same menu for invalid selection
            return $this->showStudentSelectionMenu($sessionId, $phoneNumber, $normalizedPhone, collect($students), $sessionData);
        }

        $selectedStudent = $students[$selectedIndex];
        
        // Update session with selected student
        $sessionData['selected_student_id'] = $selectedStudent['id'];
        $this->updateSession($sessionId, 'student_info', $sessionData);
        
        // Show student info for selected student
        return $this->showStudentInfo($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
    }

    /**
     * Show student selection menu for payment
     */
    private function showStudentSelectionMenuForPayment($sessionId, $phoneNumber, $normalizedPhone, $students, $sessionData = [])
    {
        $lang = $this->getLanguage($sessionData);
        
        $message = $this->trans('select_student_payment', $lang) . "\n";
        $studentsArray = [];
        $index = 1;
        
        foreach ($students as $student) {
            $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
            $className = $student->class_name ?? 'N/A';
            $message .= $index . ". " . $studentName . " (" . $className . ")\n";
            
            $studentsArray[] = [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'class_name' => $className
            ];
            $index++;
        }
        $message .= "0. " . $this->trans('back', $lang) . "\n";
        $message .= "99. " . $this->trans('main_menu', $lang);

        $this->updateSession($sessionId, 'student_selection_payment', [
            'students' => $studentsArray
        ]);

        return $this->formatUssdResponse("CON " . $message);
    }

    /**
     * Handle student selection for payment
     */
    private function handleStudentSelectionForPayment($sessionId, $userInput, $phoneNumber, $normalizedPhone, $sessionData)
    {
        // Check timeout first
        $session = $this->getOrCreateSession($sessionId, $phoneNumber, null);
        if ($this->isSessionTimedOut($session)) {
            $this->terminateSession($sessionId);
            return $this->formatUssdResponse("END Session has timed out. Please start again.");
        }

        // Check for return to main menu
        if ($userInput === '99') {
            $this->updateSession($sessionId, 'main', $sessionData);
            return $this->showMainMenu($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
        }

        if ($userInput === '0') {
            $this->updateSession($sessionId, 'main', $sessionData);
            return $this->showMainMenu($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
        }

        $students = $sessionData['students'] ?? [];
        $selectedIndex = (int) $userInput - 1;

        if (!isset($students[$selectedIndex])) {
            // Repeat the same menu for invalid selection
            $studentsCollection = collect($students)->map(function($s) {
                return (object) $s;
            });
            return $this->showStudentSelectionMenuForPayment($sessionId, $phoneNumber, $normalizedPhone, $studentsCollection, $sessionData);
        }

        $selectedStudent = $students[$selectedIndex];
        
        $lang = $this->getLanguage($sessionData);
        
        // Update session with selected student and proceed to amount entry
        $sessionData['selected_student_id'] = $selectedStudent['id'];
        $this->updateSession($sessionId, 'payment_amount', $sessionData);
        
        return $this->formatUssdResponse("CON " . $this->trans('enter_amount', $lang));
    }

    /**
     * Handle payment amount input
     */
    private function handlePaymentAmount($sessionId, $userInput, $phoneNumber, $normalizedPhone, $sessionData)
    {
        // Check timeout first
        $session = $this->getOrCreateSession($sessionId, $phoneNumber, null);
        if ($this->isSessionTimedOut($session)) {
            $this->terminateSession($sessionId);
            return $this->formatUssdResponse("END Session has timed out. Please start again.");
        }

        // Check for return to main menu
        if ($userInput === '99') {
            $this->updateSession($sessionId, 'main', []);
            return $this->showMainMenu($sessionId, $phoneNumber, $normalizedPhone);
        }

        $lang = $this->getLanguage($sessionData);

        // Validate that userInput is a valid number
        if (!is_numeric($userInput) || trim($userInput) === '') {
            // Repeat the same prompt for invalid input
            return $this->formatUssdResponse("CON " . $this->trans('enter_amount', $lang));
        }
        
        $amount = (float) $userInput;

        // Check minimum amount (1000)
        if ($amount < 1000) {
            return $this->formatUssdResponse("CON " . $this->trans('min_amount', $lang));
        }

        if ($amount <= 0) {
            // Repeat the same prompt for invalid amount
            return $this->formatUssdResponse("CON " . $this->trans('enter_amount', $lang));
        }

        // Store amount in session
        $sessionData['amount'] = $amount;
        $this->updateSession($sessionId, 'payment_confirm', $sessionData);

        // Get student name for confirmation
        $studentName = "Student";
        if (isset($sessionData['selected_student_id'])) {
            $student = Student::find($sessionData['selected_student_id']);
            if ($student) {
                $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
            }
        }

        $lang = $this->getLanguage($sessionData);

        // Show confirmation
        $message = $this->trans('confirm_payment', $lang) . "\n";
        $message .= $this->trans('student', $lang) . " " . $studentName . "\n";
        $message .= $this->trans('amount', $lang) . " TZS " . number_format($amount, 0) . "\n";
        $message .= "1. " . $this->trans('confirm', $lang) . "\n";
        $message .= "2. " . $this->trans('cancel', $lang) . "\n";
        $message .= "99. " . $this->trans('main_menu', $lang);

        return $this->formatUssdResponse("CON " . $message);
    }

    /**
     * Handle payment confirmation
     */
    private function handlePaymentConfirm($sessionId, $userInput, $phoneNumber, $normalizedPhone, $sessionData)
    {
        // Check timeout first
        $session = $this->getOrCreateSession($sessionId, $phoneNumber, null);
        if ($this->isSessionTimedOut($session)) {
            $this->terminateSession($sessionId);
            return $this->formatUssdResponse("END Session has timed out. Please start again.");
        }

        // Check for return to main menu
        if ($userInput === '99') {
            $this->updateSession($sessionId, 'main', $sessionData);
            return $this->showMainMenu($sessionId, $phoneNumber, $normalizedPhone, $sessionData);
        }

        $lang = $this->getLanguage($sessionData);

        if ($userInput === '2') {
            $this->terminateSession($sessionId);
            return $this->formatUssdResponse("END " . $this->trans('payment_cancelled', $lang));
        }

        if ($userInput !== '1') {
            // Repeat the same confirmation menu for invalid input
            $amount = $sessionData['amount'] ?? 0;
            
            // Get student name for confirmation
            $studentName = $this->trans('student', $lang);
            if (isset($sessionData['selected_student_id'])) {
                $student = Student::find($sessionData['selected_student_id']);
                if ($student) {
                    $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
                }
            }
            
            $message = $this->trans('confirm_payment', $lang) . "\n";
            $message .= $this->trans('student', $lang) . " " . $studentName . "\n";
            $message .= $this->trans('amount', $lang) . " TZS " . number_format($amount, 0) . "\n";
            $message .= "1. " . $this->trans('confirm', $lang) . "\n";
            $message .= "2. " . $this->trans('cancel', $lang) . "\n";
            $message .= "99. " . $this->trans('main_menu', $lang);
            return $this->formatUssdResponse("CON " . $message);
        }

        $amount = $sessionData['amount'] ?? 0;

        if ($amount <= 0) {
            $this->terminateSession($sessionId);
            return $this->formatUssdResponse("END Invalid payment amount.");
        }

        // Initiate payment by calling the payment endpoint
        try {
            // Get selected student from session
            $selectedStudentId = $sessionData['selected_student_id'] ?? null;
            
            if (!$selectedStudentId) {
                $this->terminateSession($sessionId);
                return $this->formatUssdResponse("END Student not selected. Please try again.");
            }

            // Get student by selected ID
            $student = Student::find($selectedStudentId);

            if (!$student) {
                $this->terminateSession($sessionId);
                return $this->formatUssdResponse("END Student record not found.");
            }

            // Get fees_group_id from student's fee assignments
            // Get the first fee group with remaining balance
            $feeGroup = DB::table('fees_assign_childrens')
                ->join('fees_masters', 'fees_assign_childrens.fees_master_id', '=', 'fees_masters.id')
                ->join('fees_assigns', 'fees_assign_childrens.fees_assign_id', '=', 'fees_assigns.id')
                ->leftJoin('fees_groups', 'fees_assigns.fees_group_id', '=', 'fees_groups.id')
                ->where('fees_assign_childrens.student_id', $student->id)
                ->whereRaw('COALESCE(fees_assign_childrens.remained_amount, 0) > 0')
                ->select('fees_groups.id as fee_group_id', 'fees_assign_childrens.id as fees_assign_children_id')
                ->orderBy('fees_groups.id', 'asc')
                ->first();

            $lang = $this->getLanguage($sessionData);
            
            if (!$feeGroup) {
                $this->terminateSession($sessionId);
                return $this->formatUssdResponse("END " . $this->trans('no_outstanding', $lang));
            }

            // Generate reference and control_number
            $baseUrl = rtrim(request()->getSchemeAndHttpHost(), '/');
            $reference = 'USSD' . time() . rand(1000, 9999);
            $controlNumber = 'CTRL' . time() . rand(100, 999);

            // Format phone number for the payment API (should start with 0)
            $formattedPhone = $phoneNumber;
            if (strpos($formattedPhone, '+255') === 0) {
                $formattedPhone = '0' . substr($formattedPhone, 4);
            } elseif (strpos($formattedPhone, '255') === 0) {
                $formattedPhone = '0' . substr($formattedPhone, 3);
            } elseif (strpos($formattedPhone, '0') !== 0) {
                $formattedPhone = '0' . $formattedPhone;
            }

            // Call payment API with all required fields
            try {
                $response = Http::timeout(30)
                    ->connectTimeout(10)
                    ->post($baseUrl . '/api/payment', [
                        'phone' => $formattedPhone,
                        'sender_account' => $formattedPhone,
                        'amount' => $amount,
                        'reference' => $reference,
                        'service' => 'M-Pesa',
                        'control_number' => $controlNumber,
                        'fees_group_id' => $feeGroup->fees_assign_children_id, // Using fees_assign_children_id as per payment method
                    ]);

                $this->terminateSession($sessionId);

                if ($response->successful()) {
                    $responseData = $response->json();
                    
                    // Check if the response indicates success
                    if (isset($responseData['data']) && isset($responseData['data']['message'])) {
                        return $this->formatUssdResponse("END Payment of TZS " . number_format($amount, 0) . " initiated successfully. You will receive a confirmation SMS shortly. Ref: " . $reference);
                    } else {
                        $errorMsg = $responseData['data']['message'] ?? ($responseData['message'] ?? 'Unknown error');
                        return $this->formatUssdResponse("END Payment failed: " . $errorMsg);
                    }
                } else {
                    $errorData = $response->json();
                    $errorMsg = $errorData['data']['message'] ?? ($errorData['message'] ?? 'Payment request failed');
                    return $this->formatUssdResponse("END " . $errorMsg);
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('Payment API Connection Error', [
                    'error' => $e->getMessage(),
                    'sessionId' => $sessionId,
                    'reference' => $reference
                ]);
                $this->terminateSession($sessionId);
                return $this->formatUssdResponse("END Payment request failed due to connection error. Please try again later.");
            } catch (\Exception $e) {
                Log::error('Payment API Error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'sessionId' => $sessionId,
                    'reference' => $reference
                ]);
                $this->terminateSession($sessionId);
                return $this->formatUssdResponse("END Payment request failed. Please try again later.");
            }

        } catch (\Exception $e) {
            Log::error('Payment Initiation Error', ['error' => $e->getMessage()]);
            $this->terminateSession($sessionId);
            return $this->formatUssdResponse("END Payment initiation failed. Please try again later.");
        }
    }

    /**
     * Normalize phone number
     */
    private function normalizePhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) >= 9) {
            return substr($phone, -9);
        }
        return $phone;
    }

    /**
     * Get or create session using database
     */
    private function getOrCreateSession($sessionId, $phoneNumber, $institutionId)
    {
        // Try to get existing session from database (check all statuses to prevent duplicates)
        $existingSession = DB::table('ussd_sessions')
            ->where('session_id', $sessionId)
            ->first();
        
        // If session exists but is not active, check if it's a duplicate or old
        if ($existingSession && $existingSession->status !== 'active') {
            // Check if it's a duplicate (different phone number)
            if ($existingSession->phone_number !== $phoneNumber) {
                Log::info('Duplicate Session Detected - Different Phone', [
                    'sessionId' => $sessionId,
                    'existingPhone' => $existingSession->phone_number,
                    'newPhone' => $phoneNumber
                ]);
                return null;
            }
        }
        
        // Try to get active session
        $session = DB::table('ussd_sessions')
            ->where('session_id', $sessionId)
            ->where('status', 'active')
            ->first();
        
        if (!$session) {
            // Check if we're trying to create a duplicate (sessionId already exists)
            if ($existingSession) {
                Log::info('Duplicate Session Detected - Session ID Already Exists', [
                    'sessionId' => $sessionId,
                    'existingStatus' => $existingSession->status
                ]);
                return null;
            }
            
            // Create new session
            $sessionIdInsert = DB::table('ussd_sessions')->insertGetId([
                'session_id' => $sessionId,
                'phone_number' => $phoneNumber,
                'current_menu' => 'main',
                'session_data' => json_encode([]),
                'status' => 'active',
                'last_activity' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Retrieve the created session
            $session = DB::table('ussd_sessions')->find($sessionIdInsert);
            
            Log::info('New USSD Session Created', [
                'sessionId' => $sessionId,
                'phoneNumber' => $phoneNumber
            ]);
        } else {
            // Check if session is older than 2 minutes
            $lastActivity = is_string($session->last_activity) 
                ? \Carbon\Carbon::parse($session->last_activity) 
                : $session->last_activity;
            $createdAt = is_string($session->created_at) 
                ? \Carbon\Carbon::parse($session->created_at) 
                : $session->created_at;
            
            $twoMinutesAgo = now()->subMinutes(2);
            $isOlderThanTwoMinutes = ($lastActivity < $twoMinutesAgo) || ($createdAt < $twoMinutesAgo);
            
            // Check if it's a duplicate (different phone number)
            $isDuplicate = ($session->phone_number !== $phoneNumber);
            
            // If session is older than 2 minutes or is a duplicate, terminate it
            if ($isOlderThanTwoMinutes || $isDuplicate) {
                DB::table('ussd_sessions')
                    ->where('session_id', $sessionId)
                    ->update([
                        'status' => 'completed',
                        'updated_at' => now()
                    ]);
                
                Log::info('Session Terminated - Timeout or Duplicate', [
                    'sessionId' => $sessionId,
                    'isOlderThanTwoMinutes' => $isOlderThanTwoMinutes,
                    'isDuplicate' => $isDuplicate,
                    'existingPhone' => $session->phone_number,
                    'newPhone' => $phoneNumber
                ]);
                
                return null;
            }
            
            // Update last activity
            DB::table('ussd_sessions')
                ->where('session_id', $sessionId)
                ->update([
                    'last_activity' => now(),
                    'updated_at' => now()
                ]);
            
            // Refresh session data
            $session = DB::table('ussd_sessions')
                ->where('session_id', $sessionId)
                ->first();
        }
        
        // Convert session_data from JSON to array if it's a string
        if (is_string($session->session_data)) {
            $sessionData = json_decode($session->session_data, true) ?? [];
        } else {
            $sessionData = $session->session_data ?? [];
        }
        
        // Create a new object with decoded session_data
        $sessionObj = (object) [
            'id' => $session->id,
            'session_id' => $session->session_id,
            'phone_number' => $session->phone_number,
            'current_menu' => $session->current_menu,
            'session_data' => $sessionData,
            'status' => $session->status,
            'last_activity' => $session->last_activity,
            'created_at' => $session->created_at,
            'updated_at' => $session->updated_at
        ];
        
        return $sessionObj;
    }

    /**
     * Update session state and data
     */
    private function updateSession($sessionId, $menu, $data)
    {
        // Update session in database
        $updated = DB::table('ussd_sessions')
            ->where('session_id', $sessionId)
            ->where('status', 'active')
            ->update([
                'current_menu' => $menu,
                'session_data' => json_encode($data),
                'last_activity' => now(),
                'updated_at' => now()
            ]);
        
        if ($updated) {
            Log::info('Session Updated', [
                'sessionId' => $sessionId,
                'menu' => $menu,
                'data' => $data
            ]);
        } else {
            Log::warning('Attempted to update non-existent session', [
                'sessionId' => $sessionId,
                'menu' => $menu
            ]);
        }
    }

    /**
     * Terminate session
     */
    private function terminateSession($sessionId)
    {
        DB::table('ussd_sessions')
            ->where('session_id', $sessionId)
            ->update([
                'status' => 'completed',
                'updated_at' => now()
            ]);
        
        Log::info('Session Terminated', ['sessionId' => $sessionId]);
    }

    /**
     * Check if session has timed out (5 minutes of inactivity)
     */
    private function isSessionTimedOut($session)
    {
        if (!$session || !isset($session->last_activity)) {
            return true;
        }

        $lastActivity = is_string($session->last_activity) 
            ? \Carbon\Carbon::parse($session->last_activity) 
            : $session->last_activity;
        
        $timeoutMinutes = 5;
        $timeoutThreshold = now()->subMinutes($timeoutMinutes);
        
        return $lastActivity < $timeoutThreshold;
    }

    /**
     * Get language from session (default: sw for Kiswahili)
     */
    private function getLanguage($sessionData)
    {
        return $sessionData['language'] ?? 'sw';
    }

    /**
     * Get language from last session for this phone number
     */
    private function getLanguageFromLastSession($phoneNumber)
    {
        try {
            // Get the most recent completed or active session for this phone number
            $lastSession = DB::table('ussd_sessions')
                ->where('phone_number', $phoneNumber)
                ->whereIn('status', ['completed', 'active'])
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastSession && $lastSession->session_data) {
                $lastSessionData = is_string($lastSession->session_data) 
                    ? json_decode($lastSession->session_data, true) 
                    : $lastSession->session_data;
                
                if (isset($lastSessionData['language'])) {
                    return $lastSessionData['language'];
                }
            }
        } catch (\Exception $e) {
            Log::error('Error getting language from last session', [
                'error' => $e->getMessage(),
                'phoneNumber' => $phoneNumber
            ]);
        }

        return 'sw'; // Default to Swahili
    }

    /**
     * Translation helper
     */
    private function trans($key, $lang = 'sw')
    {
        $translations = [
            'sw' => [
                'welcome' => 'Karibu Shule ya Nalopa',
                'student_info' => 'Taarifa za Mwanafunzi na Salio',
                'pay_fees' => 'Lipa Ada',
                'change_language' => 'Badilisha Lugha',
                'exit' => 'Toka',
                'select_language' => 'Chagua Lugha',
                'english' => 'Kiingereza',
                'kiswahili' => 'Kiswahili',
                'enter_amount' => 'Ingiza kiasi cha kulipa (TZS):',
                'min_amount' => 'Kiwango cha chini ni TZS 1,000. Ingiza kiasi:',
                'confirm_payment' => 'Thibitisha malipo:',
                'student' => 'Mwanafunzi:',
                'amount' => 'Kiasi:',
                'confirm' => 'Thibitisha',
                'cancel' => 'Ghairi',
                'main_menu' => 'Menyu Kuu',
                'select_student' => 'Chagua Mwanafunzi:',
                'select_student_payment' => 'Chagua Mwanafunzi wa Kulipia:',
                'back' => 'Rudi',
                'thank_you' => 'Asante kwa kutumia huduma za Shule ya Nalopa.',
                'timeout' => 'Muda umeisha. Jaribu tena.',
                'session_timeout' => 'Muda wa kikao umeisha. Tafadhali anza tena.',
                'payment_cancelled' => 'Malipo yameghairiwa. Asante.',
                'payment_initiated' => 'Malipo ya TZS {amount} yameanzishwa kwa mafanikio. Utapokea SMS ya uthibitisho hivi karibuni. Ref: {ref}',
                'payment_failed' => 'Malipo yameshindwa: {error}',
                'connection_error' => 'Malipo yameshindwa kutokana na hitilafu ya muunganisho. Tafadhali jaribu tena baadaye.',
                'phone_not_found' => 'Nambari ya simu haijapatikana. Tafadhali wasiliana na shule.',
                'no_outstanding' => 'Hakuna ada zisizolipwa za mwanafunzi huyu.',
                'class' => 'Darasa:',
                'fee_details' => 'Maelezo ya Ada:',
                'total' => 'Jumla:',
                'paid' => 'Imelipwa:',
                'balance' => 'Salio:',
                'total_balance' => 'Jumla ya Salio:',
                'no_fees' => 'Hakuna ada zilizoainishwa.',
            ],
            'en' => [
                'welcome' => 'Welcome to Nalopa School',
                'student_info' => 'Student Info & Balance',
                'pay_fees' => 'Pay Fees',
                'change_language' => 'Change Language',
                'exit' => 'Exit',
                'select_language' => 'Select Language',
                'english' => 'English',
                'kiswahili' => 'Kiswahili',
                'enter_amount' => 'Enter amount to pay (TZS):',
                'min_amount' => 'Minimum amount is TZS 1,000. Enter amount:',
                'confirm_payment' => 'Confirm payment:',
                'student' => 'Student:',
                'amount' => 'Amount:',
                'confirm' => 'Confirm',
                'cancel' => 'Cancel',
                'main_menu' => 'Main Menu',
                'select_student' => 'Select Student:',
                'select_student_payment' => 'Select Student for Payment:',
                'back' => 'Back',
                'thank_you' => 'Thank you for using Nalopa School services.',
                'timeout' => 'Time out. Try again.',
                'session_timeout' => 'Session has timed out. Please start again.',
                'payment_cancelled' => 'Payment cancelled. Thank you.',
                'payment_initiated' => 'Payment of TZS {amount} initiated successfully. You will receive a confirmation SMS shortly. Ref: {ref}',
                'payment_failed' => 'Payment failed: {error}',
                'connection_error' => 'Payment request failed due to connection error. Please try again later.',
                'phone_not_found' => 'Phone number not found. Please contact the school.',
                'no_outstanding' => 'No outstanding fees found for this student.',
                'class' => 'Class:',
                'fee_details' => 'Fee Details:',
                'total' => 'Total:',
                'paid' => 'Paid:',
                'balance' => 'Balance:',
                'total_balance' => 'Total Balance:',
                'no_fees' => 'No fees assigned.',
            ],
        ];

        $text = $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
        
        // Replace placeholders
        if (func_num_args() > 2) {
            $args = array_slice(func_get_args(), 2);
            foreach ($args as $arg) {
                if (is_array($arg)) {
                    foreach ($arg as $placeholder => $value) {
                        $text = str_replace('{' . $placeholder . '}', $value, $text);
                    }
                }
            }
        }
        
        return $text;
    }

    /**
     * Format USSD response
     */
    private function formatUssdResponse($message)
    {
        return response($message, 200)
            ->header('Content-Type', 'text/plain');
    }
}
