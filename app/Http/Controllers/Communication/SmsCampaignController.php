<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\FailedSms;
use App\Models\StudentInfo\Student;

class SmsCampaignController extends Controller
{
    /**
     * Display SMS campaign page
     */
    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = 'SMS Campaign - Fee Reminders';

        $data['failed_sms_count'] = FailedSms::where('is_sent', 0)->count();

        $data['last_campaign'] = DB::table('sms_campaign_logs')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('communication/smsmail/campaign'));
    }

    /**
     * Get current quarter based on date
     * Quarter 1: Jan-Apr (ends April)
     * Quarter 2: May-Jun (ends June)
     * Quarter 3: Jul-Sep (ends September)
     * Quarter 4: Oct-Dec (ends December)
     */
    private function getCurrentQuarter()
    {
        $month = (int) date('n'); // 1-12
        
        if ($month >= 1 && $month <= 4) {
            return 1; // Quarter 1
        } elseif ($month >= 5 && $month <= 6) {
            return 2; // Quarter 2
        } elseif ($month >= 7 && $month <= 9) {
            return 3; // Quarter 3
        } else {
            return 4; // Quarter 4
        }
    }

    /**
     * Get quarter column name
     */
    private function getQuarterColumn($quarter)
    {
        $columns = [
            1 => 'quater_one',
            2 => 'quater_two',
            3 => 'quater_three',
            4 => 'quater_four',
        ];
        
        return $columns[$quarter] ?? 'quater_one';
    }

    /**
     * Send fee reminder SMS campaign
     */
    public function sendCampaign(Request $request)
    {
        try {
            // Check if it's Friday (5 = Friday, 0 = Sunday)
            $dayOfWeek = (int) date('w');
            if ($dayOfWeek !== 5) {
                $nextFriday = date('l, F j, Y', strtotime('next friday'));
                return response()->json([
                    'status' => false,
                    'message' => "Campaign can only be sent on Fridays. Next available date: {$nextFriday}"
                ], 400);
            }
            
            DB::beginTransaction();
            
            $currentQuarter = $this->getCurrentQuarter();
            $quarterColumn = $this->getQuarterColumn($currentQuarter);
            
            Log::info("SMS Campaign: Starting fee reminder campaign", [
                'current_quarter' => $currentQuarter,
                'quarter_column' => $quarterColumn
            ]);
            
            // Find students with unpaid quarter amounts for current quarter
            // Group by student_id to send only one SMS per student
            $studentsWithUnpaid = DB::select("
                SELECT DISTINCT
                    fac.student_id,
                    students.first_name,
                    students.last_name,
                    parent_guardians.guardian_mobile,
                    SUM(CASE 
                        WHEN fac.{$quarterColumn} > 0 THEN fac.{$quarterColumn}
                        ELSE 0
                    END) as total_unpaid_quarter,
                    MAX(classes.name) as class_name,
                    MAX(sections.name) as section_name
                FROM fees_assign_childrens fac
                INNER JOIN students ON students.id = fac.student_id
                LEFT JOIN parent_guardians ON students.parent_guardian_id = parent_guardians.id
                LEFT JOIN session_class_students scs ON scs.student_id = students.id 
                    AND scs.session_id = 9
                LEFT JOIN classes ON classes.id = scs.classes_id
                LEFT JOIN sections ON sections.id = scs.section_id
                WHERE YEAR(fac.created_at) = 2026
                  AND fac.{$quarterColumn} > 0
                  AND parent_guardians.guardian_mobile IS NOT NULL
                  AND parent_guardians.guardian_mobile != ''
                GROUP BY fac.student_id, students.first_name, students.last_name, parent_guardians.guardian_mobile
                HAVING total_unpaid_quarter > 0
            ");
            
            $sentCount = 0;
            $failedCount = 0;
            
            foreach ($studentsWithUnpaid as $student) {
                try {
                    // Format phone number
                    $phoneNumber = preg_replace('/[\s\-\(\)]/', '', $student->guardian_mobile);
                    $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
                    
                    if (substr($phoneNumber, 0, 3) !== '255') {
                        if (substr($phoneNumber, 0, 1) === '0') {
                            $phoneNumber = '255' . substr($phoneNumber, 1);
                        } else {
                            $phoneNumber = '255' . $phoneNumber;
                        }
                    }
                    
                    if (strlen($phoneNumber) !== 12) {
                        Log::warning("SMS Campaign: Invalid phone number", [
                            'student_id' => $student->student_id,
                            'phone' => $phoneNumber
                        ]);
                        $failedCount++;
                        continue;
                    }
                    
                    // Get class and section
                    $className = $student->class_name ?? '';
                    $sectionName = $student->section_name ?? '';
                    
                    preg_match('/\d+/', $className, $classMatches);
                    $classNum = !empty($classMatches) ? $classMatches[0] : '';
                    if (empty($classNum)) {
                        $classNameClean = preg_replace('/^(CLASS|FORM|STANDARD)\s*/i', '', $className);
                        $classNum = !empty($classNameClean) ? trim($classNameClean) : $className;
                    }
                    
                    preg_match('/[A-Za-z]+/', $sectionName, $sectionMatches);
                    $sectionLetter = !empty($sectionMatches) ? strtoupper($sectionMatches[0]) : '';
                    if (empty($sectionLetter)) {
                        $sectionLetter = !empty($sectionName) ? strtoupper(trim($sectionName)) : '';
                    }
                    
                    $classSection = trim($classNum . ' ' . $sectionLetter);
                    if (empty($classSection)) {
                        $classSection = 'N/A';
                    }
                    
                    // Format amount
                    $formattedAmount = number_format($student->total_unpaid_quarter, 0, '.', ',');
                    
                    // Create reminder message
                    $message = "Ndugu mzazi/mlezi, kumbuka malipo ya robo ya {$currentQuarter}.\n";
                    $message .= "Jina la mtoto: " . strtoupper(trim($student->first_name . ' ' . $student->last_name)) . "\n";
                    $message .= "Darasa: {$classSection}\n";
                    $message .= "Kiasi: {$formattedAmount} TZS\n";
                    $message .= "Tafadhali lipa mapema.\n";
                    $message .= "Asante - Nalopa School";
                    
                    // Check message length
                    if (strlen($message) > 160) {
                        $message = "Malipo ya robo {$currentQuarter} bado haijalipwa.\n";
                        $message .= "Mtoto: " . strtoupper(trim($student->first_name . ' ' . $student->last_name)) . "\n";
                        $message .= "Kiasi: {$formattedAmount} TZS\n";
                        $message .= "Tafadhali lipa mapema.\n";
                        $message .= "Asante - Nalopa School";
                    }
                    
                    // Send SMS
                    $reference = 'CAMP' . time() . rand(1000, 9999) . '_' . substr($phoneNumber, -4);
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Basic Tmpvb2xheTpFQDAxMDl0eg==',
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->post('https://messaging-service.co.tz/api/sms/v1/text/single', [
                        'from' => 'NALOPA SCH',
                        'to' => $phoneNumber,
                        'text' => $message,
                        'reference' => $reference,
                    ]);
                    
                    if ($response->successful()) {
                        $sentCount++;
                        Log::info("SMS Campaign: SMS sent successfully", [
                            'student_id' => $student->student_id,
                            'phone' => $phoneNumber,
                            'quarter' => $currentQuarter
                        ]);
                    } else {
                        // Save failed SMS
                        FailedSms::create([
                            'student_id' => $student->student_id,
                            'phone_number' => $phoneNumber,
                            'message' => $message,
                            'reference' => $reference,
                            'status_code' => $response->status(),
                            'error_response' => $response->body(),
                            'retry_count' => 0,
                            'is_sent' => 0,
                        ]);
                        
                        $failedCount++;
                        Log::error("SMS Campaign: SMS failed", [
                            'student_id' => $student->student_id,
                            'phone' => $phoneNumber,
                            'status' => $response->status()
                        ]);
                    }
                } catch (\Throwable $th) {
                    $failedCount++;
                    Log::error("SMS Campaign: Error sending SMS", [
                        'student_id' => $student->student_id ?? null,
                        'error' => $th->getMessage()
                    ]);
                }
            }
            
            // Log campaign run
            DB::table('sms_campaign_logs')->insert([
                'quarter' => $currentQuarter,
                'total_students' => count($studentsWithUnpaid),
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'status' => true,
                'message' => "Campaign completed. Sent: {$sentCount}, Failed: {$failedCount}",
                'data' => [
                    'total_students' => count($studentsWithUnpaid),
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                    'quarter' => $currentQuarter
                ]
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("SMS Campaign: Campaign failed", [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'Campaign failed: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Retry failed SMS
     */
    public function retryFailedSms(Request $request)
    {
        try {
            $failedSms = FailedSms::where('is_sent', 0)
                ->where('retry_count', '<', 3)
                ->limit(100)
                ->get();
            
            $retriedCount = 0;
            $successCount = 0;
            
            foreach ($failedSms as $sms) {
                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Basic Tmpvb2xheTpFQDAxMDl0eg==',
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->post('https://messaging-service.co.tz/api/sms/v1/text/single', [
                        'from' => 'NALOPA SCH',
                        'to' => $sms->phone_number,
                        'text' => $sms->message,
                        'reference' => $sms->reference ?? 'RETRY' . time() . rand(1000, 9999),
                    ]);
                    
                    if ($response->successful()) {
                        $sms->is_sent = 1;
                        $sms->last_retry_at = now();
                        $sms->save();
                        $successCount++;
                    } else {
                        $sms->retry_count = $sms->retry_count + 1;
                        $sms->last_retry_at = now();
                        $sms->status_code = $response->status();
                        $sms->error_response = $response->body();
                        $sms->save();
                    }
                    
                    $retriedCount++;
                } catch (\Throwable $th) {
                    $sms->retry_count = $sms->retry_count + 1;
                    $sms->last_retry_at = now();
                    $sms->save();
                    Log::error("SMS Retry: Error", [
                        'sms_id' => $sms->id,
                        'error' => $th->getMessage()
                    ]);
                }
            }
            
            return response()->json([
                'status' => true,
                'message' => "Retry completed. Retried: {$retriedCount}, Success: {$successCount}",
                'data' => [
                    'retried_count' => $retriedCount,
                    'success_count' => $successCount
                ]
            ]);
            
        } catch (\Throwable $th) {
            Log::error("SMS Retry: Failed", [
                'error' => $th->getMessage()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'Retry failed: ' . $th->getMessage()
            ], 500);
        }
    }
}
