<?php

namespace App\Repositories\SmsMailLog;

use App\Models\User;
use App\Enums\UserType;
use App\Models\Session;
use Twilio\Rest\Client;
use App\Models\SmsMailLog;
use App\Enums\TemplateType;
use App\Traits\CommonHelperTrait;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;
use App\Models\StudentInfo\Student;
use App\Models\StudentInfo\SessionClassStudent;
use App\Repositories\SmsMailLog\SmsMailLogInterface;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;


class SmsMailLogRepository implements SmsMailLogInterface
{
    use ReturnFormatTrait, CommonHelperTrait;

    private $model;

    public function __construct(SmsMailLog $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->orderByDesc('id')->get();
    }

    public function getPaginateAll()
    {
        // return $this->model::latest()->orderByDesc('id')->paginate(10);
                   $perPage = 10;
    $currentPage = Request::get('page', 1);

    // Calculate the OFFSET for the SQL query
    $offset = ($currentPage - 1) * $perPage;

    // Count total records in the table
    $total = DB::table('sms_tracking')->count();

    // Fetch records with LIMIT and OFFSET for pagination
    $data = DB::select('SELECT * FROM sms_tracking ORDER BY id DESC LIMIT ? OFFSET ?', [$perPage, $offset]);

    // Create a paginator instance
    return new LengthAwarePaginator($data, $total, $perPage, $currentPage, [
        'path' => Request::url(),  // Keep the current URL
        'query' => Request::query() // Keep query parameters
    ]);
    }

    public function search($request)
    {
        $rows = $this->model::query();
        if($request->class != "") {
            $rows = $rows->where('classes_id', $request->class);
        }
        if($request->section != "") {
            $rows = $rows->where('section_id', $request->section);
        }
        if($request->subject != "") {
            $rows = $rows->where('subject_id', $request->subject);
        }
        return $rows->paginate(10);
    }

    function insertSmsTracking($messages)
    {
        // Check if the input is a JSON string and decode it
        if (is_string($messages)) {
            $messages = json_decode($messages, true);
        }
    
        // Ensure the decoding was successful and the messages key exists
        if (isset($messages['messages']) && is_array($messages['messages'])) {
            foreach ($messages['messages'] as $message) {
                // Check if `sendReference` is set in the message, if not set to null
                $sendReference = isset($message['sendReference']) ? $message['sendReference'] : null;
                $messageId = isset($message['messageId']) ? $message['messageId'] : null;
                $text = isset($message['message']) ? $message['message'] : null;


                // Insert data into the sms_tracking table
                DB::insert('
                    INSERT INTO sms_tracking 
                    (`to`, status_groupId, status_groupName, status_id, status_name, status_description, sendReference,messageId, smsCount,sms) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?)',
                    [
                        $message['to'],
                        $message['status']['groupId'],
                        $message['status']['groupName'],
                        $message['status']['id'],
                        $message['status']['name'],
                        $message['status']['description'],
                        $sendReference,
                        $messageId,
                        $message['smsCount'],
                        $text
                    ]
                );
            }
        } else {
            // Handle the error case where decoding failed or messages key is missing
            // throw new Exception("Invalid data format. Please pass a valid JSON string or array.");
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $mobiles = [];

            // Check if Excel file is uploaded for SMS sending
            $studentData = null;
            if ($request->hasFile('excel_file') && $request->type == TemplateType::SMS) {
                // Process Excel file and get student data (name, phone, balance)
                $studentData = $this->processExcelForSms($request);
                
                if (empty($studentData)) {
                    DB::rollBack();
                    return $this->responseWithError('No valid students found in Excel file. Please check student names.', []);
                }
            } else {
                // Original logic for role/individual/class selection
                $role_ids    = explode(',', $request->role_ids ?? '');
                $users       = explode(',', $request->users ?? '');
                $section_ids = explode(',', $request->section_ids ?? '');
                Log::info($request);

                if($request->user_type == UserType::ROLE) { 
                if($role_ids == '25' || $role_ids == '26'){
                    $mobiles = DB::table('students')
                    ->join('parent_guardians', 'students.parent_guardian_id', '=', 'parent_guardians.id')
                    ->join('fees_assign_childrens', 'fees_assign_childrens.student_id', '=', 'students.id')
                    ->whereRaw('fees_assign_childrens.fees_amount != fees_assign_childrens.paid_amount')
                    ->select('parent_guardians.guardian_mobile')
                    ->distinct()
                    ->pluck('guardian_mobile')
                    ->toArray();
                }else{
                    $mobiles = User::whereIn('role_id', $role_ids)->pluck('phone')->toArray();
                }
            } elseif ($request->user_type == UserType::INDIVIDUAL) {
                $mobiles = User::whereIn('id', $users)->pluck('phone')->toArray();
            } elseif ($request->user_type == UserType::CLASSSECTION) {
                $students = SessionClassStudent::where('classes_id', $request->class_id)->whereIn('section_id', $section_ids)->where('session_id', setting('session'))->pluck('student_id');
                $mobiles = DB::table('students')
                            ->join('parent_guardians', 'students.parent_guardian_id', '=', 'parent_guardians.id')
                            ->whereIn('students.id', $students)
                            ->pluck('parent_guardians.guardian_mobile')
                            ->toArray();
                }
            }

            // Handle Excel-based SMS sending with personalized messages
            if ($request->hasFile('excel_file') && $studentData) {
                $api_url = 'https://smsapi.ubx.co.tz/api/v1/text/singltrssdae';
                $bearer_token = '14|bu3uGtBxy7z4Hq6fWvFUnSJ2XVan7OJbG4U1XwGg01b96e32';
                $senderId = 'M-Mtandao';
                $successCount = 0;
                $failCount = 0;

                foreach ($studentData as $student) {
                    // Replace placeholders in message
                    $message = $request->sms_description;
                    $message = str_replace('{name}', $student['name'], $message);
                    $message = str_replace('{balance}', number_format($student['balance'], 0), $message);
                    
                    // Prepare payload
                    $reference = 'sms-'.now()->format('YmdHis').'-'.$student['student_id'];
                    $payload = [
                        'from' => $senderId,
                        'to' => $student['phone'],
                        'body' => $message,
                        'reference' => $reference
                    ];
                    
                    // Send SMS
                    $result = \Illuminate\Support\Facades\Http::withHeaders([
                        'Authorization' => 'Bearer ' . $bearer_token,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ])->timeout(30)->post($api_url, $payload);
                    
                    Log::info('SMS Send Result', [
                        'student' => $student['name'],
                        'mobile' => $student['phone'],
                        'balance' => $student['balance'],
                        'message' => $message,
                        'result' => $result->json()
                    ]);
                    
                    if ($result->successful()) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                }
            } else {
                // Original logic for non-Excel sending
                // Remove duplicates and empty values
                $mobiles = array_filter(array_unique($mobiles));
                
                if (empty($mobiles)) {
                    DB::rollBack();
                    return $this->responseWithError('No valid phone numbers found.', []);
                }

                $api_url = 'https://smsapi.ubx.co.tz/api/v1/text/singltrssdae';
                $bearer_token = '14|bu3uGtBxy7z4Hq6fWvFUnSJ2XVan7OJbG4U1XwGg01b96e32';
                $senderId = 'M-Mtandao';
                $successCount = 0;
                $failCount = 0;

                foreach ($mobiles as $mobile) {
                     // Prepare payload
                     $reference = 'sms-'.now()->format('YmdHis');
            $payload = [
                'from' => $senderId,
                'to' => $mobile,
                'body' => $request->sms_description,
                'reference' => $reference
            ];
                    
                    // Send SMS
            $result = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearer_token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->timeout(30)->post($api_url, $payload);
                    
                    Log::info('SMS Send Result', ['mobile' => $mobile, 'result' => $result->json()]);
                    
                    if ($result->successful()) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                }
                }

                // $ch = curl_init();
                // curl_setopt($ch, CURLOPT_URL, 'https://messaging-service.co.tz/api/sms/v1/text/multi');
                // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                // curl_setopt($ch, CURLOPT_POST, 1);

        // Updated data payload for multiple messages
                // $data = [
                //     'messages' => $messages,
                //     'reference' => $mobile
                // ];

                // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Encode the data as JSON
                // $headers = [
                //     'Authorization: Basic ZmlsYmVydG46RXVzYWJpdXMxNzEwLg==', // Replace with your actual auth header
                //     'Content-Type: application/json',
                //     'Accept: application/json'
                // ];
                // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                // $result = curl_exec($ch);
                // if (curl_errno($ch)) {
                //     echo 'Error:' . curl_error($ch);
                // }
                // curl_close($ch);
                // $this->insertSmsTracking($result);



            $row                    = new $this->model;
            $row->title             = $request->title;
            $row->type              = $request->type;

            if($request->type == TemplateType::SMS) {

                $row->sms_description      = $request->sms_description;

            } else {

                $row->mail_description     = $request->mail_description;
                $row->attachment           = $this->UploadImageCreate($request->attachment, 'backend/uploads/communication');
            }

            $row->user_type             = $request->user_type ?? ($request->hasFile('excel_file') ? 'excel' : UserType::ROLE);

            if(!$request->hasFile('excel_file')) {
            if($request->user_type == UserType::ROLE) { 
                $row->role_ids             = $role_ids;
            } elseif ($request->user_type == UserType::INDIVIDUAL) {
                $row->individual_user_ids             = $users;
            } elseif ($request->user_type == UserType::CLASSSECTION) {
                $row->class_id             = $request->class_id;
                $row->section_ids          = $section_ids;
                }
            }
            $row->save();
            DB::commit();
            
            $message = ___('alert.created_successfully');
            if ($request->hasFile('excel_file') && $studentData) {
                $message = "SMS sent successfully. Sent: {$successCount}, Failed: {$failCount}";
            }
            
            $total = ($request->hasFile('excel_file') && $studentData) ? count($studentData) : count($mobiles ?? []);
            
            return $this->responseWithSuccess($message, [
                'sent' => $successCount,
                'failed' => $failCount,
                'total' => $total
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS Store Error: ' . $e->getMessage());
            return $this->responseWithError('Error sending SMS: ' . $e->getMessage(), []);
        }
    }

    /**
     * Process Excel file and extract phone numbers for SMS sending
     */
    public function processExcelForSms($request)
    {
        try {
            $data = Excel::toArray([], $request->file('excel_file'));
            $studentData = [];

            if (empty($data) || empty($data[0])) {
                return [];
            }

            // Find the header row and determine the name column index
            $nameColumnIndex = null;
            $headerRowIndex = 0;
            
            // Check if first row is header
            $firstRow = $data[0][0] ?? [];
            foreach ($firstRow as $colIndex => $value) {
                $valueStr = strtoupper(trim($value ?? ''));
                if (in_array($valueStr, ['NAME', 'NAME', 'NAMES', 'STUDENT NAME', 'STUDENTNAME'])) {
                    $nameColumnIndex = $colIndex;
                    $headerRowIndex = 0;
                    break;
                }
            }

            // If header not found in first row, assume first column (index 0)
            if ($nameColumnIndex === null) {
                $nameColumnIndex = 0;
                $headerRowIndex = -1; // No header row
            }

            foreach ($data[0] as $index => $row) {
                // Skip header row
                if ($index === $headerRowIndex) {
                    continue;
                }

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Get name from the determined column index or try associative array
                $nameValue = '';
                if (isset($row[$nameColumnIndex])) {
                    $nameValue = $row[$nameColumnIndex];
                } elseif (isset($row['Name'])) {
                    $nameValue = $row['Name'];
                } elseif (isset($row['name'])) {
                    $nameValue = $row['name'];
                } elseif (isset($row['NAME'])) {
                    $nameValue = $row['NAME'];
                } elseif (isset($row[0])) {
                    $nameValue = $row[0];
                }
                
                $nameValue = trim($nameValue ?? '');

                // Skip if empty or contains certain keywords
                if (empty($nameValue) || 
                    stripos($nameValue, 'Total') !== false || 
                    stripos($nameValue, 'TRANSPORT') !== false ||
                    stripos($nameValue, 'Name') !== false) {
                    continue;
                }

                $nameStr = trim($nameValue);
                $studentName = '';
                $className = '';
                $sectionLetter = '';

                // Parse "CLASS X Y: STUDENT NAME" format (e.g., "CLASS 1 B:SHURAIMU MRISHO")
                if (preg_match('/CLASS\s+(\d+)\s+([A-Z])\s*:\s*(.+)/i', $nameStr, $matches)) {
                    $className = $matches[1];
                    $sectionLetter = strtoupper(trim($matches[2]));
                    $studentName = trim($matches[3]);
                } else {
                    // Try alternative format without "CLASS" prefix (e.g., "1 B: NAME")
                    if (preg_match('/(\d+)\s+([A-Z])\s*:\s*(.+)/i', $nameStr, $matches)) {
                        $className = $matches[1];
                        $sectionLetter = strtoupper(trim($matches[2]));
                        $studentName = trim($matches[3]);
                    } else {
                        // Just use the name as is (no prefix)
                        $studentName = $nameStr;
                    }
                }

                // Skip if student name is empty after parsing
                if (empty($studentName)) {
                    continue;
                }

                // Find student phone number and opening balance
                $studentInfo = $this->findStudentInfoByName($studentName, $className, $sectionLetter);
                
                // Include student even if no phone found (for preview purposes)
                $studentData[] = [
                    'name' => $studentName,
                    'phone' => $studentInfo['phone'] ?? null,
                    'balance' => $studentInfo['balance'] ?? 0,
                    'student_id' => $studentInfo['student_id'] ?? null,
                    'found' => !empty($studentInfo),
                    'full_name' => $studentInfo['full_name'] ?? $studentName
                ];
            }

            return $studentData;
        } catch (\Exception $e) {
            Log::error('Excel processing error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Find student phone number by name
     */
    private function findStudentPhoneByName($studentName, $className = '', $sectionLetter = '')
    {
        $studentInfo = $this->findStudentInfoByName($studentName, $className, $sectionLetter);
        return $studentInfo ? $studentInfo['phone'] : null;
    }

    /**
     * Find student info (phone, balance, student_id) by name
     */
    public function findStudentInfoByName($studentName, $className = '', $sectionLetter = '')
    {
        try {
            // First, try to find the student by name (more flexible matching)
            $studentQuery = DB::table('students')
                ->where('students.status', '!=', 0);

            // Build flexible name matching using LIKE on both sides
            $nameParts = explode(' ', trim($studentName));
            $studentNameUpper = strtoupper(trim($studentName));
            $studentQuery->where(function($q) use ($studentName, $studentNameUpper, $nameParts) {
                // Try match on concatenated full name (LIKE on both sides)
                $q->where(DB::raw("UPPER(TRIM(CONCAT(students.first_name, ' ', students.last_name)))"), 'LIKE', '%' . $studentNameUpper . '%')
                  // Try reverse match (last name first) with LIKE on both sides
                  ->orWhere(DB::raw("UPPER(TRIM(CONCAT(students.last_name, ' ', students.first_name)))"), 'LIKE', '%' . $studentNameUpper . '%')
                  // Try match on first name + last name separately (case insensitive)
                  ->orWhere(function($subQ) use ($nameParts, $studentNameUpper) {
                      if (count($nameParts) >= 2) {
                          $firstName = strtoupper($nameParts[0]);
                          $lastName = strtoupper(implode(' ', array_slice($nameParts, 1)));
                          $subQ->where(DB::raw("UPPER(students.first_name)"), 'LIKE', '%' . $firstName . '%')
                               ->where(DB::raw("UPPER(students.last_name)"), 'LIKE', '%' . $lastName . '%');
                      }
                  })
                  // Try matching each name part individually
                  ->orWhere(function($subQ) use ($nameParts) {
                      foreach ($nameParts as $part) {
                          $partUpper = strtoupper(trim($part));
                          if (!empty($partUpper)) {
                              $subQ->orWhere(DB::raw("UPPER(CONCAT(students.first_name, ' ', students.last_name))"), 'LIKE', '%' . $partUpper . '%');
                          }
                      }
                  });
            });

            // If class and section provided, filter by them
            if (!empty($className) && !empty($sectionLetter)) {
                $studentQuery->join('session_class_students', 'students.id', '=', 'session_class_students.student_id')
                      ->join('classes', 'session_class_students.classes_id', '=', 'classes.id')
                      ->leftJoin('sections', 'session_class_students.section_id', '=', 'sections.id')
                      ->where('session_class_students.session_id', setting('session'))
                      ->where(function($q) use ($className, $sectionLetter) {
                          $q->where('classes.name', 'LIKE', '%' . $className . '%')
                            ->where('sections.name', 'LIKE', '%' . $sectionLetter . '%');
                      });
            }

            $studentResult = $studentQuery->select(
                'students.id as student_id',
                'students.first_name',
                'students.last_name'
            )->first();

            if (!$studentResult) {
                Log::warning('Student not found', ['name' => $studentName, 'class' => $className, 'section' => $sectionLetter]);
                return null;
            }

            // Now try to get phone number from parent/guardian
            $phoneResult = DB::table('students')
                ->join('parent_guardians', 'students.parent_guardian_id', '=', 'parent_guardians.id')
                ->where('students.id', $studentResult->student_id)
                ->whereNotNull('parent_guardians.guardian_mobile')
                ->where('parent_guardians.guardian_mobile', '!=', '')
                ->select('parent_guardians.guardian_mobile as phone')
                ->first();

            $phone = null;
            if ($phoneResult && !empty($phoneResult->phone)) {
                // Format phone number (remove spaces, ensure 255 prefix)
                $phone = preg_replace('/[^0-9]/', '', $phoneResult->phone);
                if (strlen($phone) == 9 && !str_starts_with($phone, '0')) {
                    $phone = '255' . $phone;
                } elseif (strlen($phone) == 10 && str_starts_with($phone, '0')) {
                    $phone = '255' . substr($phone, 1);
                }
            }

            // Get opening balance (outstanding balance)
            $openingBalance = $this->getStudentOpeningBalance($studentResult->student_id);

            return [
                'phone' => $phone,
                'balance' => $openingBalance,
                'student_id' => $studentResult->student_id,
                'full_name' => trim($studentResult->first_name . ' ' . $studentResult->last_name)
            ];
        } catch (\Exception $e) {
            Log::error('Error finding student info: ' . $e->getMessage(), [
                'student_name' => $studentName,
                'class' => $className,
                'section' => $sectionLetter,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get student opening balance (outstanding balance)
     */
    private function getStudentOpeningBalance($studentId)
    {
        try {
            $balance = DB::select('
                SELECT 
                    COALESCE(SUM(
                        CASE 
                            WHEN fees_groups.name = "Outstanding Balance" AND fees_assign_childrens.remained_amount != 0 
                            THEN fees_assign_childrens.remained_amount
                            WHEN fees_groups.name != "Outstanding Balance"
                            THEN fees_assign_childrens.remained_amount
                            ELSE 0 
                        END
                    ), 0) as total_balance
                FROM fees_assign_childrens 
                INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
                WHERE fees_assign_childrens.student_id = ? 
                  AND fees_assigns.session_id = ?
                  AND (
                      (fees_groups.name = "Outstanding Balance" AND fees_assign_childrens.remained_amount != 0)
                      OR (fees_groups.name != "Outstanding Balance" AND fees_assign_childrens.remained_amount > 0)
                  )
            ', [$studentId, setting('session')]);

            return $balance[0]->total_balance ?? 0;
        } catch (\Exception $e) {
            Log::error('Error getting student opening balance: ' . $e->getMessage());
            return 0;
        }
    }

    public function show($id)
    {
        return $this->model->find($id);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $row                    = $this->model->find($id);
            $row->title             = $request->title;
            $row->type              = $request->type;

            if($request->type == TemplateType::SMS) {

                $row->sms_description          = $request->sms_description;

            } else {

                $row->mail_description     = $request->mail_description;
                $row->attachment           = $this->UploadImageUpdate($request->attachment, 'backend/uploads/communication', $row->attachment);
            }
            $row->save();
            
            DB::commit();
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $row = $this->model->find($id);
            $this->UploadImageDelete($row->attachment);
            $row->delete();

            DB::commit();
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

}
