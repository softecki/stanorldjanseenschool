<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QrCodeScanController extends Controller
{
    /**
     * Scan QR code and check payment status
     */
    public function scan(Request $request)
    {
        try {
            $request->validate([
                'control_number' => 'required|string',
                'scan_date' => 'required|date',
            ]);

            $controlNumber = $request->control_number;
            $scanDate = Carbon::parse($request->scan_date);
            $currentMonth = $scanDate->month;
            $currentYear = $scanDate->year;

            // Determine scan period
            $isBeforeApril = $currentMonth < 4;
            $isAfterMarchBeforeJune = $currentMonth >= 4 && $currentMonth < 6; // April, May
            $isAfterJuneBeforeSeptember = $currentMonth >= 6 && $currentMonth < 9; // June, July, August
            $isAfterSeptemberUntilDecember = $currentMonth >= 9 && $currentMonth <= 12; // September, October, November, December

            // Find student by control number
            $student = DB::table('students')
                ->where('control_number', $controlNumber)
                ->where('status', '!=', 0)
                ->first();

            if (!$student) {
                return response()->json([
                    'status' => false,
                    'message' => 'Student not found with this control number',
                    'data' => null
                ], 404);
            }

            $studentId = $student->id;
            $studentName = trim($student->first_name . ' ' . $student->last_name);

            // Get current session
            $sessionId = setting('session');
        Log::info('Session ID: ' . $sessionId);
        Log::info('Student ID: ' . $studentId);
        Log::info('Student Name: ' . $studentName);
        Log::info('Control Number: ' . $controlNumber);
        Log::info('Scan Date: ' . $scanDate->format('Y-m-d H:i:s'));
        Log::info('Current Month: ' . $currentMonth);
        Log::info('Current Year: ' . $currentYear);
        Log::info('Is Before April: ' . $isBeforeApril);
        Log::info('Is After March Before June: ' . $isAfterMarchBeforeJune);
        Log::info('Is After June Before September: ' . $isAfterJuneBeforeSeptember);
        Log::info('Is After September Until December: ' . $isAfterSeptemberUntilDecember);

            // Calculate amounts based on scan period
            $quarterOneTotal = 0;
            $quarterTwoTotal = 0;
            $quarterThreeTotal = 0;
            $outstandingAmount = 0;
            $totalRemainedAmount = 0;

            // Base query for regular fees (excluding transport)
            $baseQuery = DB::table('fees_assign_childrens')
                ->join('fees_assigns', 'fees_assign_childrens.fees_assign_id', '=', 'fees_assigns.id')
                ->join('fees_masters', 'fees_assign_childrens.fees_master_id', '=', 'fees_masters.id')
                ->where('fees_assign_childrens.student_id', $studentId)
                ->where('fees_assigns.session_id', $sessionId)
                ->whereNotIn('fees_masters.fees_group_id', [5]) // Exclude transport (3)
                ->where('fees_assign_childrens.remained_amount', '!=', 0);

            // Base query for outstanding fees
            $outstandingBaseQuery = DB::table('fees_assign_childrens')
                ->join('fees_assigns', 'fees_assign_childrens.fees_assign_id', '=', 'fees_assigns.id')
                ->join('fees_masters', 'fees_assign_childrens.fees_master_id', '=', 'fees_masters.id')
                ->where('fees_assign_childrens.student_id', $studentId)
                ->where('fees_assigns.session_id', $sessionId)
                ->where('fees_masters.fees_group_id', 5) // Outstanding fees
                ->where('fees_assign_childrens.remained_amount', '!=', 0);

            if ($isAfterSeptemberUntilDecember) {
                // After September until December: Sum all remained_amount
                $totalRemainedAmount = (clone $baseQuery)->sum('fees_assign_childrens.remained_amount');
                $outstandingAmount = (clone $outstandingBaseQuery)->sum('fees_assign_childrens.remained_amount');
                $totalAmount = ($totalRemainedAmount ?? 0) + ($outstandingAmount ?? 0);
            } elseif ($isAfterJuneBeforeSeptember) {
                // After June and before September: Sum quarter_one + quarter_two + quarter_three + outstanding
                $quarterOneTotal = (clone $baseQuery)
                    ->where('fees_assign_childrens.quater_one', '>', 0)
                    ->sum('fees_assign_childrens.quater_one');
                
                $quarterTwoTotal = (clone $baseQuery)
                    ->where('fees_assign_childrens.quater_two', '>', 0)
                    ->sum('fees_assign_childrens.quater_two');
                
                $quarterThreeTotal = (clone $baseQuery)
                    ->where('fees_assign_childrens.quater_three', '>', 0)
                    ->sum('fees_assign_childrens.quater_three');
                
                // Outstanding: sum quarter_one + quarter_two + quarter_three
                $outstandingQuarterOne = (clone $outstandingBaseQuery)
                    ->where('fees_assign_childrens.quater_one', '>', 0)
                    ->sum('fees_assign_childrens.quater_one');
                
                $outstandingQuarterTwo = (clone $outstandingBaseQuery)
                    ->where('fees_assign_childrens.quater_two', '>', 0)
                    ->sum('fees_assign_childrens.quater_two');
                
                $outstandingQuarterThree = (clone $outstandingBaseQuery)
                    ->where('fees_assign_childrens.quater_three', '>', 0)
                    ->sum('fees_assign_childrens.quater_three');
                
                $outstandingAmount = ($outstandingQuarterOne ?? 0) + ($outstandingQuarterTwo ?? 0) + ($outstandingQuarterThree ?? 0);
                $totalAmount = ($quarterOneTotal ?? 0) + ($quarterTwoTotal ?? 0) + ($quarterThreeTotal ?? 0) + ($outstandingAmount ?? 0);
            } elseif ($isAfterMarchBeforeJune) {
                // After March and before June: Sum quarter_one + quarter_two + outstanding
                $quarterOneTotal = (clone $baseQuery)
                    ->where('fees_assign_childrens.quater_one', '>', 0)
                    ->sum('fees_assign_childrens.quater_one');
                
                $quarterTwoTotal = (clone $baseQuery)
                    ->where('fees_assign_childrens.quater_two', '>', 0)
                    ->sum('fees_assign_childrens.quater_two');
                
                // Outstanding: sum quarter_one + quarter_two
                $outstandingQuarterOne = (clone $outstandingBaseQuery)
                    ->where('fees_assign_childrens.quater_one', '>', 0)
                    ->sum('fees_assign_childrens.quater_one');
                
                $outstandingQuarterTwo = (clone $outstandingBaseQuery)
                    ->where('fees_assign_childrens.quater_two', '>', 0)
                    ->sum('fees_assign_childrens.quater_two');
                
                $outstandingAmount = ($outstandingQuarterOne ?? 0) + ($outstandingQuarterTwo ?? 0);
                $totalAmount = ($quarterOneTotal ?? 0) + ($quarterTwoTotal ?? 0) + ($outstandingAmount ?? 0);
            } else {
                // Before April: Only quarter_one
                $quarterOneTotal = (clone $baseQuery)
                    ->where('fees_assign_childrens.quater_one', '>', 0)
                    ->sum('fees_assign_childrens.quater_one');
                
                $outstandingQuarterOne = (clone $outstandingBaseQuery)
                    ->where('fees_assign_childrens.quater_one', '>', 0)
                    ->sum('fees_assign_childrens.quater_one');
                
                $outstandingAmount = $outstandingQuarterOne ?? 0;
                $totalAmount = ($quarterOneTotal ?? 0) + ($outstandingAmount ?? 0);
            }

            // Check if payment is complete (balance is 0)
            $isPaidComplete = $totalAmount == 0;

            // Determine scan period name
            $scanPeriod = 'Before April';
            if ($isAfterMarchBeforeJune) {
                $scanPeriod = 'April-May';
            } elseif ($isAfterJuneBeforeSeptember) {
                $scanPeriod = 'June-August';
            } elseif ($isAfterSeptemberUntilDecember) {
                $scanPeriod = 'September-December';
            }

            // Log the scan
            DB::table('qr_code_scans')->insert([
                'student_id' => $studentId,
                'control_number' => $controlNumber,
                'scan_date' => $scanDate->format('Y-m-d H:i:s'),
                'scan_month' => $currentMonth,
                'scan_year' => $currentYear,
                'is_before_april' => $isBeforeApril,
                'total_amount' => $totalAmount,
                'is_paid_complete' => $isPaidComplete,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Total Amount: ' . $totalAmount);
            Log::info('Is Paid Complete: ' . $isPaidComplete);
            Log::info('Scan Period: ' . $scanPeriod);
            Log::info('Is Before April: ' . $isBeforeApril);
            Log::info('Is After March Before June: ' . $isAfterMarchBeforeJune);
            Log::info('Is After June Before September: ' . $isAfterJuneBeforeSeptember);
            Log::info('Is After September Until December: ' . $isAfterSeptemberUntilDecember);
            Log::info('Quarter One Total: ' . $quarterOneTotal ?? 0);
            Log::info('Quarter Two Total: ' . $quarterTwoTotal ?? 0);
            Log::info('Quarter Three Total: ' . $quarterThreeTotal ?? 0);
            Log::info('Outstanding Amount: ' . $outstandingAmount ?? 0);
            Log::info('Total Remained Amount: ' . $totalRemainedAmount ?? 0);
            Log::info('Is Paid Complete: ' . $isPaidComplete);
            Log::info('Payment Status: ' . $isPaidComplete ? 'Paid Complete' : 'Paid Incomplete');
            Log::info('Payment Status Color: ' . $isPaidComplete ? 'green' : 'red');

            return response()->json([
                'status' => true,
                'message' => 'QR code scanned successfully',
                'data' => [
                    'student_id' => $studentId,
                    'student_name' => $studentName,
                    'control_number' => $controlNumber,
                    'scan_date' => $scanDate->format('Y-m-d H:i:s'),
                    'scan_month' => $currentMonth,
                    'scan_year' => $currentYear,
                    'scan_period' => $scanPeriod,
                    'is_before_april' => $isBeforeApril,
                    'is_after_march_before_june' => $isAfterMarchBeforeJune,
                    'is_after_june_before_september' => $isAfterJuneBeforeSeptember,
                    'is_after_september_until_december' => $isAfterSeptemberUntilDecember,
                    'quarter_one_total' => $quarterOneTotal ?? 0,
                    'quarter_two_total' => $quarterTwoTotal ?? 0,
                    'quarter_three_total' => $quarterThreeTotal ?? 0,
                    'outstanding_amount' => $outstandingAmount ?? 0,
                    'total_remained_amount' => $totalRemainedAmount ?? 0,
                    'total_amount' => $totalAmount,
                    'is_paid_complete' => $isPaidComplete,
                    'payment_status' => $isPaidComplete ? 'Paid Complete' : 'Paid Incomplete',
                    'payment_status_color' => $isPaidComplete ? 'green' : 'red',
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('QR Code Scan Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Error processing QR code scan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}

