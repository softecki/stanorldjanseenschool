<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * USSD API endpoints for external gateway (e.g. hikvision_portal).
 * Returns student name and fees_assign_children_id for control number lookup.
 */
class UssdApiController extends Controller
{
    /**
     * Get student name and a fees_assign_children_id by control number.
     * Used by USSD flow to show "Weka kiasi kumlipia ada {name} kwenda {school}".
     */
    public function getStudentByControlNumber(Request $request)
    {
        try {
            Log::info('USSD API getStudentByControlNumber request', [
                'control_number_length' => strlen(trim($request->control_number ?? '')),
            ]);

            $validator = Validator::make($request->all(), [
                'control_number' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::warning('USSD API getStudentByControlNumber validation failed', [
                    'errors' => $validator->errors()->toArray(),
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid request data',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $controlNumber = trim($request->control_number);

            // Students table: control_number column (used for USSD/QR lookup)
            $student = DB::table('students')
                ->where('control_number', $controlNumber)
                ->where('status', '!=', 0)
                ->first();

            if (!$student) {
                Log::info('USSD API getStudentByControlNumber student not found', [
                    'control_number' => $controlNumber,
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nambari ya rejea haijasajiliwa.',
                ], 404);
            }

            $studentId = $student->id;
            $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
            if ($studentName === '') {
                $studentName = 'Mwanafunzi';
            }

            // Get one fees_assign_children for this student (current session if available)
            $sessionId = function_exists('setting') ? setting('session') : null;
            $feesChildQuery = DB::table('fees_assign_childrens')
                ->where('fees_assign_childrens.student_id', $studentId);

            if ($sessionId) {
                $feesChildQuery->join('fees_assigns', 'fees_assign_childrens.fees_assign_id', '=', 'fees_assigns.id')
                    ->where('fees_assigns.session_id', $sessionId)
                    ->select('fees_assign_childrens.id');
            } else {
                $feesChildQuery->select('fees_assign_childrens.id');
            }

            $feesChild = $feesChildQuery->first();

            if (!$feesChild) {
                Log::info('USSD API getStudentByControlNumber no fees_assign_children for student', [
                    'student_id' => $studentId,
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Taarifa za ada hazikupatikana kwa mwanafunzi huyu.',
                ], 404);
            }

            $feesAssignChildrenId = $feesChild->id;

            Log::info('USSD API getStudentByControlNumber success', [
                'student_id' => $studentId,
                'student_name' => $studentName,
                'fees_assign_children_id' => $feesAssignChildrenId,
            ]);

            return response()->json([
                'status' => 'success',
                'student_name' => $studentName,
                'fees_assign_children_id' => $feesAssignChildrenId,
                'school_name' => 'Nalopa School',
                'data' => [
                    'student_name' => $studentName,
                    'fees_assign_children_id' => $feesAssignChildrenId,
                    'school_name' => 'Nalopa School',
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('USSD API getStudentByControlNumber exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Shida ya kiufundi. Jaribu tena.',
            ], 500);
        }
    }
}
