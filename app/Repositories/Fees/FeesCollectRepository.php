<?php

namespace App\Repositories\Fees;

use App\Models\BankAccounts;
use App\Models\Amendment;
use Illuminate\Support\Facades\Log;
use Stripe\Charge;
use Stripe\Stripe;
use App\Models\Accounts\Income;
use App\Models\Fees\FeesCollect;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\Fees\FeesAssignChildren;
use App\Interfaces\Fees\FeesMasterInterface;
use App\Interfaces\Fees\FeesCollectInterface;
use App\Models\StudentInfo\SessionClassStudent;
use Illuminate\Support\Facades\Validator;
use App\Models\FloatBalance;
use Illuminate\Support\Facades\Http;
use App\Models\FailedSms;
use App\Services\Accounts\BankAccountBalanceService;

class FeesCollectRepository implements FeesCollectInterface
{
    use ReturnFormatTrait;

    private $model;
    private $feesMasterRepo;

    public function __construct(FeesCollect $model, FeesMasterInterface $feesMasterRepo)
    {
        $this->model          = $model;
        $this->feesMasterRepo = $feesMasterRepo;
    }

    public function all()
    {
        return $this->model->active()->get();
    }

    public function getPaginateAll()
    {
        return $this->model::latest()->paginate(10);
    }

    public function generateUniqueTrackingNumber() {
        do {
            // Generate a random 4-digit number
            $trackingNumber = random_int(100000, 999999);

            // Check if it exists in the tracking_number table
            $exists = DB::table('tracking_number')
                ->where('tracking_number', $trackingNumber)
                ->exists();
        } while ($exists); // Keep generating until the number is unique

        // Insert the unique tracking number into the table
        DB::table('tracking_number')->insert([
            'tracking_number' => $trackingNumber,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $trackingNumber; // Return the generated tracking number
    }

   public function getStudentName($id) {
    $result = DB::select("SELECT CONCAT(first_name, ' ', last_name) as name FROM students WHERE id = ?", [$id]);
    return $result[0]->name ?? null; // returns null if not found
    }

    /**
     * Send SMS notification for payment
     *
     * @param int $studentId
     * @param float $amount
     * @param string $transactionId
     * @param string $date
     * @param int $accountId
     * @return void
     */
    private function sendPaymentSMS($studentId, $amount, $transactionId, $date, $accountId)
    {
        Log::info("SMS: Starting payment SMS process", [
            'student_id' => $studentId,
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'date' => $date,
            'account_id' => $accountId
        ]);

        try {
            // Get student information including class, section, mobile, and bank name
            // First try to get from session 9 (2026), if not found, get from any session
            $student = DB::select("
                SELECT 
                    students.mobile as student_mobile,
                    parent_guardians.guardian_mobile,
                    students.first_name,
                    students.last_name,
                    COALESCE(classes.name, '') as class_name,
                    COALESCE(sections.name, '') as section_name,
                    bank_accounts.bank_name,
                    session_class_students.session_id
                FROM students
                LEFT JOIN parent_guardians ON students.parent_guardian_id = parent_guardians.id
                LEFT JOIN session_class_students ON session_class_students.student_id = students.id 
                    AND session_class_students.session_id = 9
                LEFT JOIN classes ON classes.id = session_class_students.classes_id
                LEFT JOIN sections ON sections.id = session_class_students.section_id
                LEFT JOIN bank_accounts ON bank_accounts.id = ?
                WHERE students.id = ?
            ", [$accountId, $studentId]);
            
            // If class/section not found for session 9, try to get from any session
            if (empty($student) || (empty($student[0]->class_name) && empty($student[0]->section_name))) {
                $studentFallback = DB::select("
                    SELECT 
                        students.mobile as student_mobile,
                        parent_guardians.guardian_mobile,
                        students.first_name,
                        students.last_name,
                        COALESCE(classes.name, '') as class_name,
                        COALESCE(sections.name, '') as section_name,
                        bank_accounts.bank_name,
                        session_class_students.session_id
                    FROM students
                    LEFT JOIN parent_guardians ON students.parent_guardian_id = parent_guardians.id
                    LEFT JOIN session_class_students ON session_class_students.student_id = students.id
                    LEFT JOIN classes ON classes.id = session_class_students.classes_id
                    LEFT JOIN sections ON sections.id = session_class_students.section_id
                    LEFT JOIN bank_accounts ON bank_accounts.id = ?
                    WHERE students.id = ?
                    ORDER BY session_class_students.created_at DESC
                    LIMIT 1
                ", [$accountId, $studentId]);
                
                if (!empty($studentFallback)) {
                    $student = $studentFallback;
                    Log::info("SMS: Using fallback class/section data", [
                        'student_id' => $studentId,
                        'class_name' => $studentFallback[0]->class_name ?? 'N/A',
                        'section_name' => $studentFallback[0]->section_name ?? 'N/A',
                        'session_id' => $studentFallback[0]->session_id ?? 'N/A'
                    ]);
                }
            }

            if (empty($student)) {
                Log::warning("SMS: Student not found for ID: {$studentId}");
                return;
            }

            $studentData = $student[0];
            
            Log::info("SMS: Student data retrieved", [
                'student_id' => $studentId,
                'student_name' => ($studentData->first_name ?? '') . ' ' . ($studentData->last_name ?? ''),
                'guardian_mobile' => $studentData->guardian_mobile ?? 'N/A',
                'class_name' => $studentData->class_name ?? 'N/A',
                'section_name' => $studentData->section_name ?? 'N/A',
                'bank_name' => $studentData->bank_name ?? 'N/A'
            ]);
            
            // Get phone number from parent guardian (guardian_mobile) - this is the primary number for SMS
            $phoneNumber = $studentData->guardian_mobile ?? null;

            if (empty($phoneNumber)) {
                Log::warning("SMS: No guardian mobile number found for student ID: {$studentId}");
                return;
            }

            // Format phone number: remove all spaces and non-numeric characters
            $originalPhone = $phoneNumber;
            $phoneNumber = preg_replace('/[\s\-\(\)]/', '', $phoneNumber); // Remove spaces, dashes, parentheses
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber); // Remove any remaining non-numeric characters
            
            Log::info("SMS: Phone number formatting", [
                'student_id' => $studentId,
                'original_phone' => $originalPhone,
                'cleaned_phone' => $phoneNumber
            ]);
            
            // Ensure phone number is in format 255716718040 (Tanzania format)
            // Check if phone number starts with 255 (Tanzania country code)
            if (substr($phoneNumber, 0, 3) !== '255') {
                // If it starts with 0, replace with 255
                if (substr($phoneNumber, 0, 1) === '0') {
                    $phoneNumber = '255' . substr($phoneNumber, 1);
                    Log::info("SMS: Phone number formatted (0 replaced with 255)", [
                        'student_id' => $studentId,
                        'formatted_phone' => $phoneNumber
                    ]);
                } else {
                    // If it doesn't start with country code, add it
                    $phoneNumber = '255' . $phoneNumber;
                    Log::info("SMS: Phone number formatted (255 prefix added)", [
                        'student_id' => $studentId,
                        'formatted_phone' => $phoneNumber
                    ]);
                }
            }
            
            // Final validation: ensure it's exactly 12 digits (255 + 9 digits)
            if (strlen($phoneNumber) !== 12) {
                Log::warning("SMS: Invalid phone number format for student ID: {$studentId}", [
                    'phone' => $phoneNumber,
                    'length' => strlen($phoneNumber),
                    'expected_length' => 12
                ]);
                return;
            }
            
            Log::info("SMS: Phone number validated successfully", [
                'student_id' => $studentId,
                'final_phone' => $phoneNumber
            ]);

            // Format amount with thousand separators (commas) and without decimals (e.g., 255,000 TZS)
            $formattedAmount = number_format($amount, 0, '.', ',');
            
            // Format date as date only (2026-01-22)
            $formattedDate = date('Y-m-d', strtotime($date));
            
            // Get student name in uppercase
            $studentName = strtoupper(trim(($studentData->first_name ?? '') . ' ' . ($studentData->last_name ?? '')));
            
            // Get class and section (e.g., "1A" - extract numbers from class and letters from section)
            $className = trim($studentData->class_name ?? '');
            $sectionName = trim($studentData->section_name ?? '');
            
            Log::info("SMS: Class and section extraction", [
                'student_id' => $studentId,
                'raw_class_name' => $className,
                'raw_section_name' => $sectionName
            ]);
            
            // Extract numeric part from class name (e.g., "CLASS 1" -> "1", "1" -> "1", "CLASS I" -> "I")
            preg_match('/\d+/', $className, $classMatches);
            $classNum = !empty($classMatches) ? $classMatches[0] : '';
            
            // If no number found, try to extract Roman numerals or just use the class name as is
            if (empty($classNum)) {
                // Try to extract any meaningful part (remove "CLASS", "FORM", etc.)
                $classNameClean = preg_replace('/^(CLASS|FORM|STANDARD)\s*/i', '', $className);
                $classNum = !empty($classNameClean) ? trim($classNameClean) : $className;
            }
            
            // Extract letter part from section name (e.g., "SECTION A" -> "A", "A" -> "A")
            preg_match('/[A-Za-z]+/', $sectionName, $sectionMatches);
            $sectionLetter = !empty($sectionMatches) ? strtoupper($sectionMatches[0]) : '';
            
            // If no letter found, use section name as is
            if (empty($sectionLetter)) {
                $sectionLetter = !empty($sectionName) ? strtoupper(trim($sectionName)) : '';
            }
            
            // Combine class and section with space (e.g., "1 A", "VI A", "Baby class A")
            $classSection = trim($classNum . ' ' . $sectionLetter);
            
            // If still empty, try alternative combinations
            if (empty($classSection)) {
                $classSection = trim($className . ' ' . $sectionName);
            }
            
            // Final fallback - if still empty, use "N/A"
            if (empty($classSection)) {
                $classSection = 'N/A';
                Log::warning("SMS: Class and section not available for student", [
                    'student_id' => $studentId,
                    'class_name' => $className,
                    'section_name' => $sectionName
                ]);
            }
            
            Log::info("SMS: Final class section", [
                'student_id' => $studentId,
                'class_section' => $classSection,
                'class_num' => $classNum,
                'section_letter' => $sectionLetter
            ]);
            
            // Get bank name
            $bankName = $studentData->bank_name ?? '';

            // Create SMS message in Kiswahili (format as specified, keep within 160 characters)
            $message = "Ndugu mzazi/mlezi malipo yako yamepokelewa.\n";
            $message .= "Jina la mtoto: {$studentName}\n";
            $message .= "Darasa: {$classSection}\n";
            $message .= "Kiasi: {$formattedAmount} TZS\n";
            $message .= "Risiti: {$transactionId}\n";
            $message .= "Tarehe: {$formattedDate}\n";
            $message .= "Kupitia: {$bankName}\n";
            $message .= "Asante Kwa Malipo";
            
            // Ensure message is within 160 characters (SMS limit)
            // Count actual characters (newlines count as 1 character each)
            $messageLength = strlen($message);
            if ($messageLength > 160) {
                // If exceeded 160, remove "Ndugu mzazi/mlezi " and start with "Malipo yako yamepokelewa." (capital M)
                $message = "Malipo yako yamepokelewa.\n";
                $message .= "Jina la mtoto: {$studentName}\n";
                $message .= "Darasa: {$classSection}\n";
                $message .= "Kiasi: {$formattedAmount} TZS\n";
                $message .= "Risiti: {$transactionId}\n";
                $message .= "Tarehe: {$formattedDate}\n";
                $message .= "Kupitia: {$bankName}\n";
                $message .= "Asante Kwa Malipo";
                
                // Final check - if still too long after removing greeting, truncate student name
                $messageLength = strlen($message);
                if ($messageLength > 160) {
                    $maxNameLength = 25;
                    if (strlen($studentName) > $maxNameLength) {
                        $studentName = substr($studentName, 0, $maxNameLength);
                        $message = "Malipo yako yamepokelewa.\n";
                        $message .= "Jina la mtoto: {$studentName}\n";
                        $message .= "Darasa: {$classSection}\n";
                        $message .= "Kiasi: {$formattedAmount} TZS\n";
                        $message .= "Risiti: {$transactionId}\n";
                        $message .= "Tarehe: {$formattedDate}\n";
                        $message .= "Kupitia: {$bankName}\n";
                        $message .= "Asante Kwa Malipo";
                    }
                    
                    // Last resort - truncate the entire message
                    $messageLength = strlen($message);
                    if ($messageLength > 160) {
                        $message = substr($message, 0, 157) . '...';
                    }
                }
            }
            
            Log::info("SMS: Message created", [
                'student_id' => $studentId,
                'message_length' => strlen($message),
                'class_section' => $classSection,
                'formatted_date' => $formattedDate
            ]);

            // Format phone number helper function
            $formatPhoneNumber = function($phone) {
                // Remove all spaces and non-numeric characters
                $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
                $phone = preg_replace('/[^0-9]/', '', $phone);
                
                // Ensure Tanzania format (255...)
                if (substr($phone, 0, 3) !== '255') {
                    if (substr($phone, 0, 1) === '0') {
                        $phone = '255' . substr($phone, 1);
                    } else {
                        $phone = '255' . $phone;
                    }
                }
                
                return $phone;
            };

            // List of recipients: parent/guardian + two default numbers
            $recipients = [
                $phoneNumber, // Parent/guardian mobile
                // '0765438924', // Default receiver 1
                // '0715268400'  // Default receiver 2
            ];

            // Format all phone numbers
            $formattedRecipients = array_map($formatPhoneNumber, $recipients);

            // Send SMS to all recipients
            foreach ($formattedRecipients as $recipientPhone) {
                // Generate unique reference for each SMS
                $reference = 'NAL' . time() . rand(1000, 9999) . '_' . substr($recipientPhone, -4);

                // Send SMS via API
                $response = Http::withHeaders([
                    'Authorization' => 'Basic Tmpvb2xheTpFQDAxMDl0eg==',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post('https://messaging-service.co.tz/api/sms/v1/text/single', [
                    'from' => 'NALOPA SCH',
                    'to' => $recipientPhone,
                    'text' => $message,
                    'reference' => $reference,
                ]);

                if ($response->successful()) {
                    Log::info("SMS sent successfully", [
                        'student_id' => $studentId,
                        'phone' => $recipientPhone,
                        'transaction_id' => $transactionId,
                        'reference' => $reference,
                        'response' => $response->json()
                    ]);
                } else {
                    // Save failed SMS to database for retry later
                    FailedSms::create([
                        'student_id' => $studentId,
                        'phone_number' => $recipientPhone,
                        'message' => $message,
                        'reference' => $reference,
                        'transaction_id' => $transactionId,
                        'amount' => $amount,
                        'payment_date' => date('Y-m-d', strtotime($date)),
                        'status_code' => $response->status(),
                        'error_response' => $response->body(),
                        'retry_count' => 0,
                        'is_sent' => 0,
                    ]);
                    
                    Log::error("SMS sending failed - saved to database", [
                        'student_id' => $studentId,
                        'phone' => $recipientPhone,
                        'transaction_id' => $transactionId,
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                }
            }
        } catch (\Throwable $th) {
            // Don't fail the transaction if SMS fails
            Log::error("SMS sending error", [
                'student_id' => $studentId,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
        }
    }

    public function store($request)
    {
        //  $validator = Validator::make($request->all(), [
        //         'comment' => 'required',
        //     ], [
        //         'comment.required' => 'Missing receipt number.',
        //     ]);

            // if ($validator->fails()) {
            //     return $this->responseWithError('Fail Missing Receipt Number', []);
            // }
        DB::beginTransaction();
        try {
            

               if (empty($request->fees_assign_childrens)) {
                $request->status = "2";
            $feesAssignId = DB::table('fees_assign_childrens')
                ->where('student_id', $request->student_id)
                 ->where('fees_master_id', '!=', 11)
                ->value('id'); // fetch a single ID

            if ($feesAssignId) {
                $request->merge([
                    'fees_assign_childrens' => [$feesAssignId] // make it an array
                ]);
            } else {
                return $this->responseWithError('No fee assignment found for this student.', []);
            }
        }
            
      foreach ((array) $request->fees_assign_childrens as $key => $item) {
                $isDuplicate = DB::table('fees_collects')->where([
        ['date', '=', $request->date],
        ['student_id', '=', $request->student_id],
        ['fees_assign_children_id', '=', $item],
        ['amount', '=', $request->amounts],
        ['comments', '=', $request->comment ?? NULL],
    ])->exists();

    if ($isDuplicate) {
        DB::rollBack(); // Important: rollback early
        return $this->responseWithError("Duplicate Receipt Detected", []);
    }
                $receiptNumber = DB::Select("SELECT * from fees_collects WHERE date = ? and amount =? and student_id = ? ",
                [$request->date,$request->amounts,$request->student_id]);

                // $receiptNumber = DB::Select("SELECT * from fees_collects WHERE comments = ?",[$request->comment]);
                if(empty($receiptNumber)){
                $row                   = new $this->model;
                $row->date             = $request->date;
                $row->payment_method   = $request->payment_method;
                $row->fees_assign_children_id   = $item;
                $row->amount           = $request->amounts ?? 0;
                $row->fine_amount = !empty($request->fine_amounts[$key]) 
                    ? $request->fine_amounts[$key] 
                    : 0;
                $row->fees_collect_by  = Auth::user()->id;
                $row->transaction_id = $this->generateUniqueTrackingNumber();
                $row->student_id       = $request->student_id;
                $row->account_id       = $request->account_id;
                $row->comments       = $request->comment??NULL;
                $row->session_id       = setting('session');
                $row->save();

                // Reflect fee collection on bank account balance (money in)
                $accountId = is_numeric($request->account_id) ? (int) $request->account_id : null;
                if ($accountId && $row->amount > 0) {
                    BankAccountBalanceService::credit($accountId, (float) $row->amount);
                }

                $incomeStore                   = new Income();
                $incomeStore->fees_collect_id  = $row->id;
                $incomeStore->name             = $this->getStudentName( $request->student_id);
                $incomeStore->session_id       = setting('session'); 
                $incomeStore->income_head      = 1; // Because, Fees id 1.
                $incomeStore->date             = date('Y-m-d');
                $incomeStore->amount           = $row->amount;
                $incomeStore->invoice_number           = $request->comment??NULL;
                $incomeStore->account_number    = $row->account_id;
                if (Schema::hasColumn('incomes', 'bank_account_id')) {
                    $incomeStore->bank_account_id = $accountId;
                }
                $incomeStore->save();

            //      $existingBalance = FloatBalance::where('account', $request->account_number)->first();
            // if ($existingBalance) {
            //     // Account exists – update the balance
            //     $existingBalance->balance_amount += $request->amount;
            //     $existingBalance->save();
            // } else {
            //     // Account doesn't exist – create a new record
            //     $FloatBalance = new FloatBalance();
            //     $FloatBalance->balance_amount = $request->amount;
            //     $FloatBalance->account = $request->account_number;
            //     $FloatBalance->save();
            // }

                //This is changes for new Life to accomadate payment for transport and school fees separate
                // $transportId  = DB::select("SELECT fees_assigns.fees_group_id as fees_group_id
                //                 FROM `fees_assign_childrens` 
                //                 INNER JOIN fees_asseigns on fees_assigns.id = fees_assign_childrens.fees_assign_id 
                //                 WHERE fees_assign_childrens.id=?",[$item])[0]->fees_group_id;
                //                 if($transportId != "3"){
                                    $this->updateFeesAssigned($item,$row->amount,$request->status,$row->id);
                                    
                                    // Send SMS notification
                                    $this->sendPaymentSMS($request->student_id, $row->amount, $row->transaction_id, $row->date, $row->account_id);
                                // }else{
                                //     $this->processTransportFees($item,$row->amount);
                                // }
                }else{
                    return $this->responseWithError("Duplicate Receipt", []);
                }

            }
            DB::commit();
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            // dd($th);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);

        }
    }


    public function storeOnline($request)
    {
       
        Log::info($request->all());
        Log::info("request after settlement");
        DB::beginTransaction();
        try {
            
       
              $request->date = date('Y-m-d H:i:s', strtotime($request->date));
                // Ensure a valid status so updateFeesAssigned() can apply payments (default to 1 for successful settlement)
                if (!isset($request->status) || $request->status === null || $request->status === '') {
                    $request->status = 1;
                }
                $row                   = new $this->model;
                $row->date             = $request->date;
                $row->payment_method   = $request->payment_method;
                $row->fees_assign_children_id   = $request->fees_assign_children_id;
                $row->amount           = $request->amounts ?? 0;
                $row->fees_collect_by  = "1";
                $row->transaction_id = $this->generateUniqueTrackingNumber();
                $row->student_id       = $request->student_id;
                $row->account_id       = "1";
                $row->comments       = $request->comment??NULL;
                $row->session_id       = setting('session');
                $row->save();

                $onlineAccountId = 1;
                if ($onlineAccountId && $row->amount > 0) {
                    BankAccountBalanceService::credit($onlineAccountId, (float) $row->amount);
                }

                $incomeStore                   = new Income();
                $incomeStore->fees_collect_id  = $row->id;
                $incomeStore->name             = $this->getStudentName( $request->student_id);
                $incomeStore->session_id       = setting('session'); 
                $incomeStore->income_head      = 1; // Because, Fees id 1.
                $incomeStore->date             =  $request->date ;
                $incomeStore->amount           = $row->amount;
                $incomeStore->invoice_number           = $request->comment??NULL;
                $incomeStore->account_number    = $row->account_id;
                if (Schema::hasColumn('incomes', 'bank_account_id')) {
                    $incomeStore->bank_account_id = $onlineAccountId;
                }
                $incomeStore->save();

            //      $existingBalance = FloatBalance::where('account', $request->account_number)->first();
            // if ($existingBalance) {
            //     // Account exists – update the balance
            //     $existingBalance->balance_amount += $request->amount;
            //     $existingBalance->save();
            // } else {
            //     // Account doesn't exist – create a new record
            //     $FloatBalance = new FloatBalance();
            //     $FloatBalance->balance_amount = $request->amount;
            //     $FloatBalance->account = $request->account_number;
            //     $FloatBalance->save();
            // }

                //This is changes for new Life to accomadate payment for transport and school fees separate
                // $transportId  = DB::select("SELECT fees_assigns.fees_group_id as fees_group_id
                //                 FROM `fees_assign_childrens` 
                //                 INNER JOIN fees_asseigns on fees_assigns.id = fees_assign_childrens.fees_assign_id 
                //                 WHERE fees_assign_childrens.id=?",[$item])[0]->fees_group_id;
                //                 if($transportId != "3"){
                $request->status = $request->status;
                                    $this->updateFeesAssigned($request->fees_assign_children_id,$row->amount,$request->status,$row->id);
                                // }else{
                                //     $this->processTransportFees($item,$row->amount);
                                // }
              

            
            DB::commit();
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info($th);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);

        }
    }

     public function updateFeesAssigned($id, $amount,$status,$fees_collect_id)
{
    // Get the student ID based on the given fee assignment child ID
    $studentId = DB::table('fees_assign_childrens')->where('id', $id)->value('student_id');

    // Try to retrieve the Outstanding Fee ID (only from 2026)
    $outstandingId = DB::table('fees_assign_childrens')
        ->join('fees_masters', 'fees_assign_childrens.fees_master_id', '=', 'fees_masters.id')
        ->where('fees_masters.fees_group_id', 1)
        ->where('fees_assign_childrens.remained_amount', '!=', 0)
        ->where('fees_assign_childrens.student_id', $studentId)
        ->whereYear('fees_assign_childrens.created_at', 2026)
        ->value('fees_assign_childrens.id');

         // Try to retrieve the Outstanding Fee ID for Transport (only from 2026)
    $outstandingIdTransport = DB::table('fees_assign_childrens')
        ->join('fees_masters', 'fees_assign_childrens.fees_master_id', '=', 'fees_masters.id')
        ->where('fees_masters.fees_group_id', 9)
        ->where('fees_assign_childrens.remained_amount', '!=', 0)
        ->where('fees_assign_childrens.student_id', $studentId)
        ->whereYear('fees_assign_childrens.created_at', 2026)
        ->value('fees_assign_childrens.id');

        if($status==1){
        if ($outstandingId) {
            // Handle Outstanding Fee Payment.
            $remainedAmount = DB::table('fees_assign_childrens')->where('id', $outstandingId)->sum('remained_amount');
            Log::info("remainedAmount ".$remainedAmount);
            if ($remainedAmount >= $amount) {
                DB::update(
                    'update fees_assign_childrens set paid_amount = paid_amount + ?, remained_amount = remained_amount - ? where id = ?',
                    [$amount, $amount, $outstandingId]
                );
                DB::insert('INSERT INTO fees_tracking (fees_collect_id, amount, fees_assign_children_id,statusId) 
                VALUES (?, ?, ?, ?)', [
                    $fees_collect_id,
                    $amount,
                    $outstandingId,
                    '1'
                ]);
                return; // Exit as the amount is fully covered by the outstanding fee.
            } else {
                DB::update(
                    'update fees_assign_childrens set paid_amount = paid_amount + ?, remained_amount = remained_amount - ? where id = ?',
                    [$remainedAmount, $remainedAmount, $outstandingId]
                );
                 DB::insert('INSERT INTO fees_tracking (fees_collect_id, amount, fees_assign_children_id,statusId) 
                VALUES (?, ?, ?, ?)', [
                    $fees_collect_id,
                    $remainedAmount,
                    $outstandingId,
                    '1'
                ]);
                // Adjust remaining amount to be collected.
                $amount -= $remainedAmount;

                if($outstandingIdTransport){
                $remainedAmountTransport = DB::table('fees_assign_childrens')->where('id', $outstandingIdTransport)->sum('remained_amount');
                 if ($remainedAmountTransport >= $amount) {
                DB::update(
                    'update fees_assign_childrens set paid_amount = paid_amount + ?, remained_amount = remained_amount - ? where id = ?',
                    [$amount, $amount, $outstandingIdTransport]
                );
                 DB::insert('INSERT INTO fees_tracking (fees_collect_id, amount, fees_assign_children_id,statusId) 
                VALUES (?, ?, ?, ?)', [
                    $fees_collect_id,
                    $amount,
                    $outstandingIdTransport,
                    '1'
                ]);
                return; // Exit as the amount is fully covered by the outstanding fee.
                } else{
                    DB::update(
                    'update fees_assign_childrens set paid_amount = paid_amount + ?, remained_amount = remained_amount - ? where id = ?',
                    [$remainedAmountTransport, $remainedAmountTransport, $outstandingIdTransport]
                );
                 DB::insert('INSERT INTO fees_tracking (fees_collect_id, amount, fees_assign_children_id,statusId) 
                VALUES (?, ?, ?, ?)', [
                    $fees_collect_id,
                    $remainedAmountTransport,
                    $outstandingIdTransport,
                    '1'
                ]);
                // Adjust remaining amount to be collected.
                $amount -= $remainedAmountTransport;
                }
                }
            }
        }
    }
    //check if status is 1 then check if admission fees is available and it is not full paid update it to finish the paymentwhen the amount remained proceed with the remined amount
    $this->checkAdmissionFees($studentId, $amount,$status,$id,$fees_collect_id);

}


private function checkAdmissionFees($studentId, $amount, $status, $id, $fees_collect_id)
{
    // Only process admission/outstanding fees when status is 1 (bulk payment)
    if ($status == 1) {
        // Get admission/outstanding fees (fees_group_id = 1) with remaining amount
        // Join with fees_masters to check fees_group_id, and filter by current session
        $admissionFees = DB::table('fees_assign_childrens')
            ->join('fees_masters', 'fees_assign_childrens.fees_master_id', '=', 'fees_masters.id')
            ->join('fees_assigns', 'fees_assign_childrens.fees_assign_id', '=', 'fees_assigns.id')
            ->where('fees_assign_childrens.student_id', $studentId)
            ->where('fees_masters.fees_group_id', 5) // Outstanding/Admission fees group
            ->where('fees_assign_childrens.remained_amount', '!=', 0)
            ->where('fees_assigns.session_id', setting('session')) // Filter by current session
            ->select('fees_assign_childrens.id', 'fees_assign_childrens.remained_amount')
            ->first();
            
        if ($admissionFees) {
            $admissionFeesAmount = $admissionFees->remained_amount;
            
            if ($admissionFeesAmount >= $amount) {
                // Full amount goes to admission fees
                DB::update('UPDATE fees_assign_childrens SET paid_amount = paid_amount + ?, remained_amount = remained_amount - ? WHERE id = ?', 
                    [$amount, $amount, $admissionFees->id]);
                DB::insert('INSERT INTO fees_tracking (fees_collect_id, amount, fees_assign_children_id, statusId) VALUES (?, ?, ?, ?)', 
                    [$fees_collect_id, $amount, $admissionFees->id, 1]);
                return; // All amount used, no need to process other fees
            } else {
                // Partial payment to admission fees, remainder goes to other fees
                DB::update('UPDATE fees_assign_childrens SET paid_amount = paid_amount + ?, remained_amount = remained_amount - ? WHERE id = ?', 
                    [$admissionFeesAmount, $admissionFeesAmount, $admissionFees->id]);
                DB::insert('INSERT INTO fees_tracking (fees_collect_id, amount, fees_assign_children_id, statusId) VALUES (?, ?, ?, ?)', 
                    [$fees_collect_id, $admissionFeesAmount, $admissionFees->id, 1]);
                $amount -= $admissionFeesAmount; // Update remaining amount
            }
        }
    }

    // If no admission fees or remaining amount after admission fees payment, process other fees
    // Only process if there's remaining amount
    if ($amount > 0) {
        $this->processOtherFees($studentId, $amount, $status, $id, $fees_collect_id);
    }
}

/**
 * Function to process other fees by quarter
 */
// private function processOtherFees($studentId, $amount,$status,$id,$fees_collect_id)
// {
//     if($status==1){
//         $feeIds = DB::table('fees_assign_childrens')
//         ->join('fees_masters', 'fees_masters.id', '=', 'fees_assign_childrens.fees_master_id')
//         ->whereNotIn('fees_masters.fees_group_id', [ 5,]) // More concise condition
//         ->where('fees_assign_childrens.student_id', $studentId)
//         ->orderBy('fees_assign_childrens.fees_master_id') // Ensures ordering is correct
//         ->pluck('fees_assign_childrens.id');
//     }else{
//         $feeIds = DB::table('fees_assign_childrens')
//         ->where('student_id', $studentId)
//         ->where('id',$id)
//         ->orderBy('fees_master_id') // Assuming fees_master_id determines the order of fees
//         ->pluck('id'); 
//     }

//     foreach (['quater_one', 'quater_two', 'quater_three', 'quater_four'] as $quarter) {
//         foreach ($feeIds as $feeId) {
//             $quarterAmount = DB::table('fees_assign_childrens')->where('id', $feeId)->value($quarter);

//             if ($quarterAmount > 0) {
//                 if ($amount >= $quarterAmount) {
//                     DB::update(
//                         "update fees_assign_childrens set paid_amount = paid_amount + ?, remained_amount = remained_amount - ?, $quarter = $quarter - ? where id = ?",
//                         [$quarterAmount, $quarterAmount, $quarterAmount, $feeId]
//                     );
//                      DB::insert('INSERT INTO fees_tracking (fees_collect_id, amount, fees_assign_children_id,statusId) 
//                 VALUES (?, ?, ?, ?)', [
//                     $fees_collect_id,
//                     $quarterAmount,
//                     $feeId,
//                     '1'
//                 ]);
//                     $amount -= $quarterAmount;
//                 } else {
//                     DB::update(
//                         "update fees_assign_childrens set paid_amount = paid_amount + ?, remained_amount = remained_amount - ?, $quarter = $quarter - ? where id = ?",
//                         [$amount, $amount, $amount, $feeId]
//                     );
//                     DB::insert('INSERT INTO fees_tracking (fees_collect_id, amount, fees_assign_children_id,statusId) 
//                 VALUES (?, ?, ?, ?)', [
//                     $fees_collect_id,
//                     $amount,
//                     $feeId,
//                     '1'
//                 ]);
//                     return; // Exit as the amount is fully utilized
//                 }
//             }
//         }
//     }
// }

private function processOtherFees($studentId, $amount, $status, $id, $fees_collect_id)
{
    try {
        if ($status == 1) {
            $feeIds = DB::table('fees_assign_childrens')
                ->join('fees_masters', 'fees_masters.id', '=', 'fees_assign_childrens.fees_master_id')
                ->whereNotIn('fees_masters.fees_group_id', [5])
                ->where('fees_assign_childrens.student_id', $studentId)
                ->where('fees_assign_childrens.remained_amount', '!=', 0)
                ->whereYear('fees_assign_childrens.created_at', 2026)
                ->orderBy('fees_assign_childrens.fees_master_id')
                ->pluck('fees_assign_childrens.id');
        } else {
            $feeIds = DB::table('fees_assign_childrens')
                ->where('student_id', $studentId)
                ->where('id', $id)
                ->whereYear('created_at', 2026)
                ->orderBy('fees_master_id')
                ->pluck('id');
        }
		$lastFeeId = $feeIds->last();
        foreach (['quater_one', 'quater_two', 'quater_three', 'quater_four'] as $quarter) {
            foreach ($feeIds as $feeId) {
                $quarterAmount = DB::table('fees_assign_childrens')->where('id', $feeId)->value($quarter);
                if ($quarterAmount > 0) {
                    if ($amount >= $quarterAmount) {
                        DB::update(
                            "UPDATE fees_assign_childrens 
                             SET paid_amount = paid_amount + ?, 
                                 remained_amount = remained_amount - ?, 
                                 $quarter = $quarter - ? 
                             WHERE id = ?",
                            [$quarterAmount, $quarterAmount, $quarterAmount, $feeId]
                        );

                        DB::insert(
                            'INSERT INTO fees_tracking (fees_collect_id, amount, fees_assign_children_id, statusId) VALUES (?, ?, ?, ?)',
                            [$fees_collect_id, $quarterAmount, $feeId, 1]
                        );

                        $amount -= $quarterAmount;
                    } else {
                        DB::update(
                            "UPDATE fees_assign_childrens 
                             SET paid_amount = paid_amount + ?, 
                                 remained_amount = remained_amount - ?, 
                                 $quarter = $quarter - ? 
                             WHERE id = ?",
                            [$amount, $amount, $amount, $feeId]
                        );

                        DB::insert(
                            'INSERT INTO fees_tracking (fees_collect_id, amount, fees_assign_children_id, statusId) VALUES (?, ?, ?, ?)',
                            [$fees_collect_id, $amount, $feeId, 1]
                        );

                        return; // Exit as the amount is fully utilized
                    }
                }
				 elseif ($quarter === 'quater_four' && $amount > 0 && (string)$feeId === (string)$lastFeeId) {
                    // All quarters are already cleared for this fee. Apply any remaining amount as negative on quater_four and remained_amount.
                    DB::update(
                        "UPDATE fees_assign_childrens
                         SET paid_amount = paid_amount + ?
                         WHERE id = ?",
                        [$amount, $feeId]
                    );

                    DB::insert(
                        'INSERT INTO fees_tracking (fees_collect_id, amount, fees_assign_children_id, statusId) VALUES (?, ?, ?, ?)',
                        [$fees_collect_id, $amount, $feeId, 1]
                    );

                    return; // Applied the remaining as negative balance; exit.
                }
            }
        }
    } catch (\Exception $e) {
        Log::error('Error processing other fees: ' . $e->getMessage());
        return response()->json(['error' => 'Fee processing failed'], 500);
    }
}


private function processTransportFees($studentFeeId, $amount)
{
    $feeIds = DB::table('fees_assign_childrens')
        ->where('id', $studentFeeId)
        ->orderBy('fees_master_id') // Assuming fees_master_id determines the order of fees
        ->pluck('id');

    foreach (['quater_one', 'quater_two', 'quater_three', 'quater_four'] as $quarter) {
        foreach ($feeIds as $feeId) {
            $quarterAmount = DB::table('fees_assign_childrens')->where('id', $feeId)->value($quarter);

            if ($quarterAmount > 0) {
                if ($amount >= $quarterAmount) {
                    DB::update(
                        "update fees_assign_childrens set paid_amount = paid_amount + ?, remained_amount = remained_amount - ?, $quarter = $quarter - ? where id = ?",
                        [$quarterAmount, $quarterAmount, $quarterAmount, $feeId]
                    );
                    $amount -= $quarterAmount;
                } else {
                    DB::update(
                        "update fees_assign_childrens set paid_amount = paid_amount + ?, remained_amount = remained_amount - ?, $quarter = $quarter - ? where id = ?",
                        [$amount, $amount, $amount, $feeId]
                    );
                    return; // Exit as the amount is fully utilized
                }
            }
        }
    }
}


    public function updateQuarters($id, $amount)
    {
        // Retrieve the record for the given ID
        $feesAssign = DB::table('fees_assign_childrens')->where('id', $id)->first();

        if ($feesAssign) {
            // List of quarters in sequential order
            $quarters = ['quater_one', 'quater_two', 'quater_three', 'quater_four'];

            foreach ($quarters as $quarter) {
                // Check if there's enough amount to cover the current quarter
                if ($amount >= $feesAssign->$quarter) {

                    // Deduct the entire quarter amount
                    if ($quarter != 'quater_four') {
                        DB::table('fees_assign_childrens')
                            ->where('id', $id)
                            ->update([$quarter => 0]);
                    }else{
                        DB::table('fees_assign_childrens')
                            ->where('id', $id)
                            ->update([$quarter => $feesAssign->$quarter - $amount]);
                    }
                    $amount -= $feesAssign->$quarter;
                    // Set quarter balance to 0
                } else {
                    // Partially reduce the remaining amount from the current quarter
                    DB::table('fees_assign_childrens')
                        ->where('id', $id)
                        ->update([$quarter => $feesAssign->$quarter - $amount]);
                    return; // Exit the loop since the amount is fully utilized
                }
            }
        }
        return true;


    }

    public function show($id)
    {
        return $this->model->find($id);
    }

    public function showFeesAssignPerChildren($id)
    {
        return FeesAssignChildren::find($id);
    }

    public function feesAssigned($id) // student id
    {
        $groups = FeesAssignChildren::withCount('feesCollect')->with('feesCollect')->where('student_id', $id);

        // Filter to only show fees created in 2026
        $groups = $groups->whereYear('created_at', 2026);

        // Only show fees for session_id = 9
        $groups = $groups->whereHas('feesAssign', function($q) {
            $q->where('session_id', '9');
        });

        return $groups->get();
    }

    public function feesAssignedDetailsSearch($request)
    {
        $currentSession = setting('session');
        $groups = FeesAssignChildren::withCount('feesCollect')
            ->with('feesCollect')
            ->join('students', 'fees_assign_childrens.student_id', '=', 'students.id')
            ->join('fees_assigns', 'fees_assign_childrens.fees_assign_id', '=', 'fees_assigns.id')
            ->join('fees_collects', 'fees_assign_childrens.id', '=', 'fees_collects.fees_assign_children_id')
            ->leftJoin('bank_accounts', 'bank_accounts.id', '=', 'fees_collects.account_id')
            ->where('students.status', '!=', 0)
            ->where('fees_assigns.session_id', '9')
            ->whereYear('fees_assign_childrens.created_at', 2026)
            ->select(
                'fees_assign_childrens.*',
                'students.first_name',
                'students.last_name',
                'fees_collects.amount as transaction_amount',
                'fees_collects.created_at as transaction_date',
                'bank_accounts.*',
                'fees_collects.id as fees_collect_id'
            );

        if ($request->filled('name')) {
            $groups->where(function ($query) use ($request) {
                $query->where('students.first_name', 'like', '%' . $request->name . '%')
                    ->orWhere('students.last_name', 'like', '%' . $request->name . '%');
            });
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $groups->whereBetween('fees_collects.created_at', [$request->start_date, $request->end_date]);
        }

        return $groups->get();
    }
    public function feesAssignedDetails()
    {
        $currentSession = setting('session');
        
        // Optimized query: Remove unnecessary eager loading, add pagination, add session filter
        $groups = DB::table('fees_collects')
            ->join('fees_assign_childrens', 'fees_collects.fees_assign_children_id', '=', 'fees_assign_childrens.id')
            ->join('fees_assigns', 'fees_assign_childrens.fees_assign_id', '=', 'fees_assigns.id')
            ->join('students', 'fees_assign_childrens.student_id', '=', 'students.id')
            ->leftJoin('bank_accounts', 'fees_collects.account_id', '=', 'bank_accounts.id')
            ->leftJoin('fees_masters', 'fees_assign_childrens.fees_master_id', '=', 'fees_masters.id')
            ->leftJoin('fees_types', 'fees_masters.fees_type_id', '=', 'fees_types.id')
            ->where('students.status', '!=', 0)
            ->where('fees_assigns.session_id', '9')
            ->whereYear('fees_assign_childrens.created_at', 2026)
            ->where(function($query) {
                // Exclude completed transactions
                $query->whereNull('fees_assign_childrens.comment')
                      ->orWhere('fees_assign_childrens.comment', '!=', 'completed');
            })
            ->select(
                'fees_assign_childrens.id as fees_assign_children_id',
                'fees_assign_childrens.student_id',
                'fees_assign_childrens.fees_master_id',
                'fees_assign_childrens.fees_amount',
                'fees_assign_childrens.paid_amount',
                'fees_assign_childrens.remained_amount',
                'fees_assign_childrens.outstandingbalance',
                'fees_assign_childrens.comment',
                'students.first_name',
                'students.last_name',
                'fees_collects.id as fees_collect_id',
                'fees_collects.amount as transaction_amount',
                'fees_collects.created_at as transaction_date',
                'fees_collects.printed',
                'fees_collects.comments',
                'bank_accounts.bank_name',
                'bank_accounts.account_number',
                'fees_types.name as fees_type_name'
            )
            ->orderBy('fees_collects.created_at', 'DESC');

        // Return paginated results (50 per page for better performance)
        return $groups->paginate(50);
    }

      public function feesAssignedDetailsForPushTransactions()
    {
        // Avoid eager-loading feesCollect here: nested toArray() JSON responses can recurse and blow the stack.
        $groups = FeesAssignChildren::query()
            ->join('students', 'fees_assign_childrens.student_id', '=', 'students.id')
            ->join('fees_assigns', 'fees_assign_childrens.fees_assign_id', '=', 'fees_assigns.id')
            ->join('push_transactions', 'fees_assign_childrens.id', '=', 'push_transactions.fees_assign_children_id')
            ->leftJoin('bank_accounts', 'bank_accounts.id', '=', 'push_transactions.account_id')
            ->where('fees_assigns.session_id', '9')
            ->whereYear('fees_assign_childrens.created_at', 2026)
            ->where('students.status', '!=', 0)
            ->where(function ($q) {
                $q->whereNull('push_transactions.payment_status')
                    ->orWhere('push_transactions.payment_status', '!=', 'cancelled');
            })
            ->select(
                'fees_assign_childrens.id',
                'fees_assign_childrens.student_id',
                'fees_assign_childrens.fees_assign_id',
                'fees_assign_childrens.fees_master_id',
                'students.first_name',
                'students.last_name',
                'push_transactions.amount as transaction_amount',
                'push_transactions.payment_date as transaction_date',
                'bank_accounts.bank_name',
                'bank_accounts.account_number',
                'push_transactions.id as fees_collect_id',
                'push_transactions.payment_receipt',
                'push_transactions.settlement_receipt'

            )->orderBy('push_transactions.created_at', 'DESC');

        return $groups->get();
    }

    public function feesAssignedUnpaidDetails()
    {
        $groups = Amendment::join('fees_assign_childrens', 'fees_assign_childrens.id', '=', 'amendments.fees_assign_id')
            ->join('students', 'fees_assign_childrens.student_id', '=', 'students.id')
            ->join('fees_assigns', 'fees_assign_childrens.fees_assign_id', '=', 'fees_assigns.id')
//            ->join('fees_collects', 'fees_assign_childrens.id', '=', 'fees_collects.fees_assign_children_id')
//            ->leftJoin('bank_accounts', 'bank_accounts.id', '=', 'fees_collects.account_id')
            ->where('fees_assigns.session_id', '9')
            ->whereYear('fees_assign_childrens.created_at', 2026)
            ->where('fees_assign_childrens.remained_amount', '>', 0)
            ->where('students.status', '!=', 0)
            ->select(
                'fees_assign_childrens.*',
                'students.first_name',
                'students.last_name',
                'fees_assign_childrens.remained_amount as transaction_amount',
                'fees_assign_childrens.created_at as transaction_date',
//                'bank_accounts.*',
                'fees_assign_childrens.id as fees_collect_id',
                'students.id as student_id',
                'amendments.description',
                'amendments.date',
                'amendments.parent_name'
            );

        return $groups->get();
    }

    public function  feesAssignedUnpaidDetailsSearch($request){
        $groups = FeesAssignChildren::withCount('feesCollect')
            ->with('feesCollect')
            ->join('students', 'fees_assign_childrens.student_id', '=', 'students.id')
            ->join('fees_assigns', 'fees_assign_childrens.fees_assign_id', '=', 'fees_assigns.id')
//            ->join('fees_collects', 'fees_assign_childrens.id', '=', 'fees_collects.fees_assign_children_id')
//            ->leftJoin('bank_accounts', 'bank_accounts.id', '=', 'fees_collects.account_id')
            ->where('fees_assigns.session_id', '9')
            ->whereYear('fees_assign_childrens.created_at', 2026)
            ->where('fees_assign_childrens.remained_amount', '>', 0)
            ->where('students.status', '!=', 0)
            ->select(
                'fees_assign_childrens.*',
                'students.first_name',
                'students.last_name',
                'fees_assign_childrens.remained_amount as transaction_amount',
                'fees_assign_childrens.created_at as transaction_date',
//                'bank_accounts.*',
                'fees_assign_childrens.id as fees_collect_id',
                'students.id as student_id'

            );

        if ($request->filled('name')) {
            $groups->where(function ($query) use ($request) {
                $query->where('students.first_name', 'like', '%' . $request->name . '%')
                    ->orWhere('students.last_name', 'like', '%' . $request->name . '%');
            });
        }

        return $groups->get();
    }


    public function update($request, $id)
    {
        try {
            $row                = $this->model->findOrfail($id);
            $row->name          = $request->name;
            $row->code          = $request->code;
            $row->description   = $request->description;
            $row->status        = $request->status;
            $row->save();
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function updateFeesAssignChildren($request, $id)
    {
        try {
            $feesAmount = FeesAssignChildren::where('id', $id)->value('fees_amount');
            $paid_amount = FeesAssignChildren::where('id', $id)->value('paid_amount');
            $remained_amount = FeesAssignChildren::where('id', $id)->value('remained_amount');
            $comment = FeesAssignChildren::where('id', $id)->value('comment');
            $quater_one = FeesAssignChildren::where('id', $id)->value('quater_one');
            $quater_two = FeesAssignChildren::where('id', $id)->value('quater_two');
            $quater_three = FeesAssignChildren::where('id', $id)->value('quater_three');
            $quater_four = FeesAssignChildren::where('id', $id)->value('quater_four');
            // $quater_amount = FeesAssignChildren::where('id', $id)->value('quater_amount');
            $paidDifferenceAmount = $paid_amount - $request->paid_amount;
            if ($feesAmount !== null) {
                $difference = $feesAmount - $request->fees_amount;
                $remainedAmount = $request->fees_amount - $request->paid_amount;
                $row = FeesAssignChildren::findOrFail($id);
                $row->fees_amount = $request->fees_amount;
                $row->paid_amount = $request->paid_amount;
                $row->remained_amount =  $remainedAmount; // Prevent negative values.
                $row->comment = $request->description;
                $row->quater_one = $request->quater_one;
                $row->quater_two = $request->quater_two;
                $row->quater_three = $request->quater_three;
                $row->quater_four = $request->quater_four;
                $row->save();

                   // DB::statement("CALL UpdateFeesAmount()");
                    //DB::statement("CALL UpdateQuarters()");
                DB::insert("INSERT INTO fees_assign_children_history (fees_id,user_id, fees_amount, paid_amount, remained_amount, comment, quater_one, quater_two, quater_three, quater_four) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [$id, Auth::User()->id, $feesAmount, $paid_amount, $remained_amount, $comment, $quater_one, $quater_two, $quater_three, $quater_four]);
             
              
            } else {
                return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
            }
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            dd($th);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function updateAmendment($request, $id)
    {
        try {
           
            
             $amount = $request->new_amount ?? 0;

                if ($request->amendment_type == "1") {
                    DB::update('UPDATE fees_assign_childrens 
                        SET fees_amount = fees_amount - ?, 
                            remained_amount = remained_amount - ? 
                        WHERE id = ?', [$amount, $amount, $id]);
                } else {
                    DB::update('UPDATE fees_assign_childrens 
                        SET fees_amount = fees_amount + ?, 
                            remained_amount = remained_amount + ? 
                        WHERE id = ?', [$amount, $amount, $id]);
                }
                DB::statement("CALL UpdateQuartersById(?)", [$id]);
                $row = new Amendment();
                $row->parent_name = $request->parent_name;
                $row->phonenumber = $request->phonenumber;
                $row->date =  $request->date; 
                $row->description = $request->description;
                $row->fees_assign_id = $id;
                $row->user_id = Auth::User()->id;
                $row->save();

         
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            dd($th);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function destroy($id)
    {
        try {
            $studentId = DB::table('fees_collects')->where('id', $id)->value('student_id');
            $amount = DB::table('fees_collects')->where('id', $id)->value('amount');

            $row = $this->model->find($id);
            $row->delete();

            DB::delete('DELETE FROM incomes WHERE fees_collect_id = ?', [$id]);

           $fees_tracking = DB::select("SELECT * FROM fees_tracking WHERE fees_collect_id = ?", [$id]);

            foreach ($fees_tracking as $fee_tracking) {
                DB::update(
                    'UPDATE fees_assign_childrens 
                    SET paid_amount = paid_amount - ?, 
                        remained_amount = remained_amount + ? 
                    WHERE id = ?',
                    [
                        $fee_tracking->amount,
                        $fee_tracking->amount,
                        $fee_tracking->fees_assign_children_id
                    ]
                );
            }

            // Call stored procedure
            //DB::statement("CALL UpdateFeesAmount()");
            //DB::statement("CALL UpdateQuarters()");

            // $this->updateFeesAssignedDeletion($studentId,$amount);
            
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    /**
     * Cancel a push transaction and reverse all effects (fees_collects, fees_assign_childrens, incomes, bank balance, fees_tracking).
     * Marks push_transactions as cancelled. Used from collect-transactions when user deletes from details modal.
     */
    public function cancelPushTransactionAndReverse(int $pushTransactionId)
    {
        DB::beginTransaction();
        try {
            $push = DB::table('push_transactions')->where('id', $pushTransactionId)->first();
            if (!$push) {
                return $this->responseWithError('Push transaction not found.', []);
            }

            $receipt = $push->payment_receipt ?? $push->settlement_receipt ?? null;
            $feesAssignChildrenId = $push->fees_assign_children_id;

            $feesCollect = null;
            if ($receipt && $feesAssignChildrenId) {
                $feesCollect = DB::table('fees_collects')
                    ->where('fees_assign_children_id', $feesAssignChildrenId)
                    ->where(function ($q) use ($receipt) {
                        $q->where('comments', $receipt);
                    })
                    ->first();
            }
            if ($feesCollect) {
                $feesCollectId = $feesCollect->id;
                $amount = (float) $feesCollect->amount;
                $accountId = $feesCollect->account_id ? (int) $feesCollect->account_id : null;

                $feesTrackingRows = DB::table('fees_tracking')->where('fees_collect_id', $feesCollectId)->get();
                foreach ($feesTrackingRows as $row) {
                    DB::update(
                        'UPDATE fees_assign_childrens SET paid_amount = paid_amount - ?, remained_amount = remained_amount + ? WHERE id = ?',
                        [$row->amount, $row->amount, $row->fees_assign_children_id]
                    );
                }

                if ($accountId && $amount > 0) {
                    BankAccountBalanceService::debit($accountId, $amount);
                }

                DB::table('incomes')->where('fees_collect_id', $feesCollectId)->delete();
                DB::table('fees_collects')->where('id', $feesCollectId)->delete();
            }

            DB::table('push_transactions')->where('id', $pushTransactionId)->update([
                'payment_status' => 'cancelled',
                'settlement_status' => 'cancelled',
                'is_processed' => 0,
                'updated_at' => now(),
            ]);

            DB::commit();
            return $this->responseWithSuccess(__('Transaction cancelled and all related data reversed successfully.'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('cancelPushTransactionAndReverse: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function getFeesAssignStudents($request)
    {
        $students = SessionClassStudent::query();
        $students = $students->where('session_class_students.session_id', setting('session'));
        if($request->class != "") {

            $students = $students->where('classes_id', $request->class);
        }

        if($request->section != "") {

            $students = $students->where('section_id', $request->section);
        }

        if ($request->name != "") {
            // Split the name into parts (e.g., "John Doe" -> ['John', 'Doe'])
            $nameParts = explode(' ', $request->name);
        
            $students = $students->whereHas('student', function ($query) use ($nameParts) {
                // If there are two parts, compare both first and last names
                if (count($nameParts) === 2) {
                    $query->where(function ($subQuery) use ($nameParts) {
                        $subQuery->where('first_name', 'LIKE', '%' . $nameParts[0] . '%')
                                 ->where('last_name', 'LIKE', '%' . $nameParts[1] . '%');
                    })->orWhere(function ($subQuery) use ($nameParts) {
                        $subQuery->where('first_name', 'LIKE', '%' . $nameParts[1] . '%')
                                 ->where('last_name', 'LIKE', '%' . $nameParts[0] . '%');
                    });
                } else {
                    // If only one part is provided, check both first_name and last_name
                    $query->where('first_name', 'LIKE', '%' . $nameParts[0] . '%')
                          ->orWhere('last_name', 'LIKE', '%' . $nameParts[0] . '%');
                }
            });
        }

        if($request->student != "") {
            $students = $students->where('session_class_students.student_id', $request->student);
        }

        $students = $students
            ->join('students', 'session_class_students.student_id', '=', 'students.id')
            ->join('fees_assign_childrens', 'students.id', '=', 'fees_assign_childrens.student_id')
            ->join('fees_masters', 'fees_masters.id', '=', 'fees_assign_childrens.fees_master_id')
            ->join('fees_types', 'fees_types.id', '=', 'fees_masters.fees_type_id')
            ->leftJoin('classes', 'session_class_students.classes_id', '=', 'classes.id')
            ->where('students.status', '!=', 0)
            ->where(function ($q) {
                $q->where('fees_assign_childrens.collect_status', 1)
                  ->orWhereNull('fees_assign_childrens.collect_status');
            })
            ->where('fees_masters.session_id', '9')
            ->whereYear('fees_assign_childrens.created_at', 2026)
            ->select(
                'fees_types.name as fees_name',
                'session_class_students.*',
                'students.*',
                'fees_assign_childrens.*',
                'fees_masters.*',
                'fees_assign_childrens.id as assignId',
                'classes.name as class_name'
            )
            ->orderByRaw("COALESCE(students.first_name, '') ASC")
            ->paginate(10);

        return $students;
    }

    // public function getFeesAssignStudentsAll()
    // {
    //     $students = SessionClassStudent::query();
    //     $students = $students->where('session_id', setting('session'));

    //     $students = SessionClassStudent::query();
    //         $students = $students->where('session_class_students.session_id', setting('session'))
    //         ->join('students', 'session_class_students.student_id', '=', 'students.id')
    //         ->join('fees_assign_childrens', 'students.id', '=', 'fees_assign_childrens.student_id')
    //         ->join('fees_masters', 'fees_masters.id', '=', 'fees_assign_childrens.fees_master_id')
    //             ->join('fees_types', 'fees_types.id', '=', 'fees_masters.fees_type_id')
    //         ->select('session_class_students.*', 'students.*', 'fees_assign_childrens.*','fees_assign_childrens.id as assignId',
    //         'fees_masters.*','fees_types.name as fees_name')
    //         ->orderByRaw("COALESCE(students.first_name, '') ASC")
    //         ->paginate(10);
    //     return $students;
    // }

    public function getFeesAssignStudentsAll()
        {
            return SessionClassStudent::join('students', 'session_class_students.student_id', '=', 'students.id')
                ->join('fees_assign_childrens', 'students.id', '=', 'fees_assign_childrens.student_id')
                ->join('fees_masters', 'fees_masters.id', '=', 'fees_assign_childrens.fees_master_id')
                ->join('fees_types', 'fees_types.id', '=', 'fees_masters.fees_type_id')
                ->leftJoin('classes', 'session_class_students.classes_id', '=', 'classes.id')
                ->where('session_class_students.session_id', setting('session'))
                ->where('students.status', '!=', 0)
                ->where(function ($q) {
                    $q->where('fees_assign_childrens.collect_status', 1)
                      ->orWhereNull('fees_assign_childrens.collect_status');
                })
                ->where('fees_masters.session_id', '9')
                ->whereYear('fees_assign_childrens.created_at', 2026)
                ->select(
                    'students.*',
                    'fees_assign_childrens.*',
                    'fees_assign_childrens.id as assignId',
                    'fees_masters.*',
                    'fees_types.name as fees_name',
                    'classes.name as class_name'
                )
                ->orderByRaw("COALESCE(students.first_name, '') ASC")
                ->paginate(10);
        }

    /**
     * Cancel (soft) a fees assign child - set collect_status = 0. No row is deleted.
     */
    public function cancelFeesAssign($id)
    {
        $updated = DB::table('fees_assign_childrens')
            ->where('id', $id)
            ->update([
                'collect_status' => 0,
                'updated_at'      => now(),
            ]);
        return $updated > 0;
    }

    /**
     * Get cancelled fee assignments for "Cancelled Collect" page.
     */
    public function getCancelledCollects($perPage = 20)
    {
        return SessionClassStudent::join('students', 'session_class_students.student_id', '=', 'students.id')
            ->join('fees_assign_childrens', 'students.id', '=', 'fees_assign_childrens.student_id')
            ->join('fees_masters', 'fees_masters.id', '=', 'fees_assign_childrens.fees_master_id')
            ->join('fees_types', 'fees_types.id', '=', 'fees_masters.fees_type_id')
            ->leftJoin('classes', 'session_class_students.classes_id', '=', 'classes.id')
            ->where('session_class_students.session_id', setting('session'))
            ->where('students.status', '!=', 0)
            ->where('fees_assign_childrens.collect_status', 0)
            ->where('fees_masters.session_id', '9')
            ->whereYear('fees_assign_childrens.created_at', 2026)
            ->select(
                'students.first_name',
                'students.last_name',
                'fees_assign_childrens.id as assignId',
                'fees_assign_childrens.fees_amount',
                'fees_assign_childrens.paid_amount',
                'fees_assign_childrens.remained_amount',
                'fees_assign_childrens.updated_at as cancelled_at',
                'fees_types.name as fees_name',
                'classes.name as class_name'
            )
            ->orderBy('fees_assign_childrens.updated_at', 'desc')
            ->paginate($perPage);
    }

    public function feesShow($request)
    {
        $data['fees_assign_children'] = $this->feesAssigned($request->student_id)->whereIn('id', $request->fees_assign_childrens);

        $data['account_number']           = BankAccounts::all();
        $data['student_id']           = $request->student_id;

        return $data;
    }


    


    public function payWithStripeStore($request)
    {
        DB::transaction(function () use ($request) {
            Stripe::setApiKey(Setting('stripe_secret'));

            $feesAssignChildren = optional(FeesAssignChildren::with('feesMaster')->where('id', $request->fees_assign_children_id)->first());
            $description = 'Pay ' . ($request->amount + $request->fine_amount) . ' for ' . $feesAssignChildren->feesMaster?->type?->name;
            
            $charge = Charge::create ([
                "amount"        => ($request->amount + $request->fine_amount) * 100,
                "currency"      => "usd",
                "source"        => $request->stripeToken,
                "description"   => $description 
            ]);

            $this->feeCollectStoreByStripe($request, @$charge->balance_transaction);
        });
    }





    protected function feeCollectStoreByStripe($request, $transaction_id)
    {
        $feesCollect = FeesCollect::create([
            'date'                      => $request->date,
            'payment_method'            => 2,
            'payment_gateway'           => 'Stripe',
            'transaction_id'            => $transaction_id,
            'fees_assign_children_id'   => $request->fees_assign_children_id,
            'amount'                    => $request->amount + $request->fine_amount ?? 0,
            'fine_amount'               => $request->fine_amount,
            'fees_collect_by'           => 1, // Because student/parent can not be collect so that's why we use first admin user id.
            'student_id'                => $request->student_id,
            'session_id'                => setting('session')
        ]);

        Income::create([
            'fees_collect_id'           => $feesCollect->id,
            'name'                      => $request->fees_assign_children_id,
            'session_id'                => setting('session'),
            'income_head'               => 1, // Because, Fees id 1.
            'date'                      => $request->date,
            'amount'                    => $feesCollect->amount
        ]);
    }




    public function paypalOrderData($invoice_no, $success_route, $cancel_route)
    {
        $feesAssignChildren = optional(FeesAssignChildren::with('feesMaster')->where('id', session()->get('FeesAssignChildrenID'))->first());

        $total = $feesAssignChildren->feesMaster?->amount;
        if (date('Y-m-d') > $feesAssignChildren->feesMaster?->due_date && $feesAssignChildren->fees_collect_count == 0) {
            $total += $feesAssignChildren->feesMaster?->fine_amount;
        }

        $description = 'Pay ' . $total . ' for ' . $feesAssignChildren->feesMaster?->type?->name;

        $data                           = [];
        $data['items']                  = [];
        $data['invoice_id']             = $invoice_no;
        $data['invoice_description']    = $description;
        $data['return_url']             = $success_route;
        $data['cancel_url']             = $cancel_route;
        $data['total']                  = $total;

        return $data;
    }





    public function feeCollectStoreByPaypal($response, $feesAssignChildren)
    {
        DB::transaction(function () use ($response, $feesAssignChildren) {

            $amount = $feesAssignChildren->feesMaster?->amount;
            $fine_amount = 0;

            if (date('Y-m-d') > $feesAssignChildren->feesMaster?->due_date && $feesAssignChildren->fees_collect_count == 0) {
                $fine_amount = $feesAssignChildren->feesMaster?->fine_amount;
                $amount += $fine_amount;
            }

            $date = date('Y-m-d', strtotime($response['PAYMENTINFO_0_ORDERTIME']));

            $feesCollect = FeesCollect::create([
                'date'                      => $date,
                'payment_method'            => 2,
                'payment_gateway'           => 'PayPal',
                'transaction_id'            => $response['PAYMENTINFO_0_TRANSACTIONID'],
                'fees_assign_children_id'   => $feesAssignChildren->id,
                'amount'                    => $amount,
                'fine_amount'               => $fine_amount,
                'fees_collect_by'           => 1, // Because student/parent can not be collect so that's why we use first admin user id.
                'student_id'                => $feesAssignChildren->student_id,
                'session_id'                => setting('session')
            ]);

            Income::create([
                'fees_collect_id'           => $feesCollect->id,
                'name'                      => $feesAssignChildren->id,
                'session_id'                => setting('session'),
                'income_head'               => 1, // Because, Fees id 1.
                'date'                      => $date,
                'amount'                    => $amount
            ]);
        });
    }
}
