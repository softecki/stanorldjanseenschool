<?php

namespace App\Repositories\Fees;

use App\Models\Fees\FeesAssign;
use App\Models\Fees\FeesMaster;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use App\Models\Fees\FeesAssignChildren;
use App\Interfaces\Fees\FeesAssignInterface;
use App\Models\StudentInfo\SessionClassStudent;
use Illuminate\Support\Facades\Log;

class FeesAssignRepository implements FeesAssignInterface
{
    use ReturnFormatTrait;

    private $model;

    public function __construct(FeesAssign $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->active()->get();
    }

    public function getPaginateAll()
    {
        return $this->model::latest()->where('session_id', setting('session'))->paginate(10);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            Log::info('FeesAssignRepository::store - Starting fees assignment', [
                'class_id' => $request->class,
                'fees_group_id' => $request->fees_group,
                'fees_master_ids' => $request->fees_master_ids,
                'student_count' => is_array($request->student_ids) ? count($request->student_ids) : 0,
            ]);

            if($request->student_ids == null) {
                Log::warning('FeesAssignRepository::store - No students selected');
                return $this->responseWithError(___('alert.Please select student.'), []);
            }

//             if($this->model->where('session_id', setting('session'))->where('classes_id', $request->class)->where('section_id', $request->section)->where('fees_group_id', $request->fees_group)->first())
//                 return $this->responseWithError(___('alert.there_is_already_assigned'), []);

            $sessionId = setting('session');
            Log::info('FeesAssignRepository::store - Checking for existing fees assign', [
                'class_id' => $request->class,
                'session_id' => $sessionId,
                'fees_group_id' => $request->fees_group,
            ]);
            
            $feeAssignIdResult = DB::select('SELECT id FROM fees_assigns WHERE classes_id = ? AND session_id = ? AND fees_group_id = ?', [$request->class, $sessionId, $request->fees_group]);
            if (empty($feeAssignIdResult) || !isset($feeAssignIdResult[0]->id)) {
                Log::info('FeesAssignRepository::store - Creating new fees assign');
                $row                = new $this->model;
                $row->session_id    = $sessionId;
                $row->classes_id      = $request->class;
                $row->section_id    = "1";
                $row->fees_group_id = $request->fees_group;
                $row->save();
                $feeAssignId = $row->id;
                Log::info('FeesAssignRepository::store - New fees assign created', ['fees_assign_id' => $feeAssignId]);
            } else {
                $feeAssignId = $feeAssignIdResult[0]->id;
                Log::info('FeesAssignRepository::store - Using existing fees assign', ['fees_assign_id' => $feeAssignId]);
            }


                // foreach ($request->fees_master_ids as $fees_master) {
                //     foreach ($request->student_ids as $item) {
                //         if($request->fees_group == "3"){
                //             $transportProfile = DB::select("select student_categories.name from student_categories
                //                                     inner join students on students.student_category_id = student_categories.id 
                //                                     where students.id = ? ",[$item])[0]->name;
                //             $transportProfileParts = explode('DAY', $transportProfile);
                //             if($transportProfileParts[1]==null || (empty($transportProfileParts[1]))){
                //                 $feeTypeId = 0;
                //             }else{
                //                 $feeTypeId = DB::SELECT("select id from fees_types where code = ? ",[trim($transportProfileParts[1])])[0]->id;
                //                 $fees_master = DB::SELECT("select id from fees_masters where fees_type_id = ?",[$feeTypeId]);
                //             }
                //         }
                //         if($feeTypeId != 0){
                //         $feeAssignChildrenId = DB::select('SELECT id FROM fees_assign_childrens WHERE fees_assign_id =? AND fees_master_id = ? AND student_id=?', [$feeAssignId,$fees_master,$item]);
                //         if (empty($feeAssignChildrenId[0]->id)) {
                //             if(!empty($this->getStudentControlNumber($item))) {
                //                 $feesChield = new FeesAssignChildren();
                //                 $feesChield->fees_assign_id = $feeAssignId;
                //                 $feesChield->fees_master_id = $fees_master;
                //                 $feesChield->student_id = $item;
                //                 $feesChield->fees_amount = $this->getFeesAmount($fees_master);
                //                 $feesChield->remained_amount = $this->getFeesAmount($fees_master);
                //                 $feesChield->control_number = $this->getStudentControlNumber($item);
                //                 if ($this->getDueDate($fees_master) > 8) {
                //                     $feesChield->quater_one = $this->getFeesAmount($fees_master) / 4;
                //                     $feesChield->quater_two = $this->getFeesAmount($fees_master) / 4;
                //                     $feesChield->quater_three = $this->getFeesAmount($fees_master) / 4;
                //                     $feesChield->quater_four = $this->getFeesAmount($fees_master) / 4;
                //                 }
                //                 $feesChield->save();
                //             }else{
                //                 return $this->responseWithError('Fees has already been assigned', []);
                //             }
                //         }
                //     }

                //     }
                // }

                $totalProcessed = 0;
                $totalSkipped = 0;
                $totalErrors = 0;
                
                foreach ($request->fees_master_ids as $fees_master) {
                    Log::info('FeesAssignRepository::store - Processing fees master', ['fees_master_id' => $fees_master]);
                    
                    foreach ($request->student_ids as $student_id) {
                        $feeTypeId = 0; // Default feeTypeId to 0
                
                        if ($request->fees_group == "3") {
                            Log::info('FeesAssignRepository::store - Processing transport fees for student', [
                                'student_id' => $student_id,
                                'fees_group' => '3 (Transport)',
                            ]);
                            
                            // Fetch transport profile for the student
                            $transportProfile = DB::select("
                                SELECT student_categories.name 
                                FROM student_categories
                                INNER JOIN students ON students.student_category_id = student_categories.id
                                WHERE students.id = ?
                            ", [$student_id]);
                            
                            if(!empty($transportProfile)){
                                $transportProfile =$transportProfile[0]->name;
                                Log::info('FeesAssignRepository::store - Transport profile found', [
                                    'student_id' => $student_id,
                                    'transport_profile' => $transportProfile,
                                ]);
                                
                            // Split transport profile based on 'DAY'
                            $transportProfileParts = explode('DAY', $transportProfile);
                
                            if (!empty($transportProfileParts[1])) {
                                // Get fee type ID based on transport profile part
                                $feeTypeResult = DB::select("
                                    SELECT id 
                                    FROM fees_types 
                                    WHERE code = ?
                                ", [trim($transportProfileParts[1])]);
                                
                                if (!empty($feeTypeResult)) {
                                    $feeTypeId = $feeTypeResult[0]->id;
                                    Log::info('FeesAssignRepository::store - Fee type ID found for transport', [
                                        'student_id' => $student_id,
                                        'fee_type_id' => $feeTypeId,
                                        'route_code' => trim($transportProfileParts[1]),
                                    ]);
                
                                    // Get fees master ID based on fee type ID
                                    $feesMasterResult = DB::select("
                                        SELECT id 
                                        FROM fees_masters 
                                        WHERE fees_type_id = ?
                                    ", [$feeTypeId]);
                                    
                                    if (!empty($feesMasterResult)) {
                                        $fees_master = $feesMasterResult[0]->id;
                                        Log::info('FeesAssignRepository::store - Fees master ID found for transport', [
                                            'student_id' => $student_id,
                                            'fees_master_id' => $fees_master,
                                        ]);
                                    } else {
                                        Log::warning('FeesAssignRepository::store - No fees master found for transport fee type', [
                                            'student_id' => $student_id,
                                            'fee_type_id' => $feeTypeId,
                                        ]);
                                    }
                                } else {
                                    Log::warning('FeesAssignRepository::store - No fee type found for transport route', [
                                        'student_id' => $student_id,
                                        'route_code' => trim($transportProfileParts[1]),
                                    ]);
                                }
                            } else {
                                Log::warning('FeesAssignRepository::store - Transport profile missing route code', [
                                    'student_id' => $student_id,
                                    'transport_profile' => $transportProfile,
                                ]);
                            }
                        } else {
                            Log::warning('FeesAssignRepository::store - No transport profile found for student', [
                                'student_id' => $student_id,
                            ]);
                        }
                        }else{
                            $feeTypeId = "1";
                            Log::info('FeesAssignRepository::store - Using default fee type for non-transport fees', [
                                'student_id' => $student_id,
                                'fee_type_id' => $feeTypeId,
                                'fees_master_id' => $fees_master,
                            ]);
                        }
                
                        // Proceed only if feeTypeId is not 0
                        if ($feeTypeId != 0) {
                            // Check if a fee assignment already exists
                            $feeAssignChildren = DB::select('
                                SELECT id 
                                FROM fees_assign_childrens 
                                WHERE fees_assign_id = ? AND fees_master_id = ? AND student_id = ?
                            ', [$feeAssignId, $fees_master, $student_id]);
                
                            if (empty($feeAssignChildren)) {
                                // Ensure student has a control number
                                $controlNumber = $this->getStudentControlNumber($student_id);
                                if (!empty($controlNumber)) {
                                    $feesAmount = $this->getFeesAmount($fees_master);
                                    $dueDate = $this->getDueDate($fees_master);
                                    
                                    Log::info('FeesAssignRepository::store - Creating fees assign child', [
                                        'student_id' => $student_id,
                                        'fees_assign_id' => $feeAssignId,
                                        'fees_master_id' => $fees_master,
                                        'fees_amount' => $feesAmount,
                                        'control_number' => $controlNumber,
                                        'due_date_month' => $dueDate,
                                    ]);
                                    
                                    $feesChild = new FeesAssignChildren();
                                    $feesChild->fees_assign_id = $feeAssignId;
                                    $feesChild->fees_master_id = $fees_master;
                                    $feesChild->student_id = $student_id;
                                    $feesChild->fees_amount = $feesAmount;
                                    $feesChild->remained_amount = $feesAmount;
                                    $feesChild->control_number = $controlNumber;
                
                                    // Divide fees into quarters if due date allows
                                    if ($dueDate > 8) {
                                        $quarterAmount = $feesAmount / 4;
                                        $feesChild->quater_one = $quarterAmount;
                                        $feesChild->quater_two = $quarterAmount;
                                        $feesChild->quater_three = $quarterAmount;
                                        $feesChild->quater_four = $quarterAmount;
                                        
                                        Log::info('FeesAssignRepository::store - Quarters assigned', [
                                            'student_id' => $student_id,
                                            'quarter_amount' => $quarterAmount,
                                        ]);
                                    } else {
                                        Log::info('FeesAssignRepository::store - Quarters not assigned (due date <= 8)', [
                                            'student_id' => $student_id,
                                            'due_date_month' => $dueDate,
                                        ]);
                                    }
                
                                    $feesChild->save();
                                    $totalProcessed++;
                                    
                                    Log::info('FeesAssignRepository::store - Fees assign child created successfully', [
                                        'student_id' => $student_id,
                                        'fees_assign_children_id' => $feesChild->id,
                                    ]);
                                } else {
                                    $totalErrors++;
                                    Log::error('FeesAssignRepository::store - Student control number is empty', [
                                        'student_id' => $student_id,
                                    ]);
                                    return $this->responseWithError('Fees has already been assigned', []);
                                }
                            } else {
                                $totalSkipped++;
                                Log::info('FeesAssignRepository::store - Fees assign child already exists, skipping', [
                                    'student_id' => $student_id,
                                    'fees_assign_id' => $feeAssignId,
                                    'fees_master_id' => $fees_master,
                                    'existing_id' => $feeAssignChildren[0]->id ?? null,
                                ]);
                            }
                        } else {
                            $totalSkipped++;
                            Log::warning('FeesAssignRepository::store - Skipping student due to invalid fee type', [
                                'student_id' => $student_id,
                                'fee_type_id' => $feeTypeId,
                            ]);
                        }
                    }
                }
                
                Log::info('FeesAssignRepository::store - Processing complete', [
                    'total_processed' => $totalProcessed,
                    'total_skipped' => $totalSkipped,
                    'total_errors' => $totalErrors,
                ]);
                

            DB::commit();
            Log::info('FeesAssignRepository::store - Transaction committed successfully');
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('FeesAssignRepository::store - Error creating fees assign', [
                'error_message' => $th->getMessage(),
                'error_trace' => $th->getTraceAsString(),
                'class_id' => $request->class ?? null,
                'fees_group_id' => $request->fees_group ?? null,
            ]);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);

        }
    }

    public function getStudentControlNumber($id){
        $result = DB::select('SELECT control_number FROM students where id = ?',[$id]);
        if(!empty($result)){
            return $result[0]->control_number;
        }else{
            return "";
        }
    }

    public function getDueDate($id)
    {
        // Fetch the month from the database
        $result = DB::table('fees_masters')
            ->where('id', $id)
            ->selectRaw('MONTH(due_date) AS month')
            ->first();  // Use 'first()' instead of 'select()' to get the first record directly

        // Check if the result is not null and return the month, else return "0"
        return $result ? $result->month : "0";
    }

    public function getFeesAmount($id){
        $result = DB::select('SELECT amount FROM fees_masters where id = ?',[$id])[0]->amount;
        return $result;
    }
    public function show($id)
    {
        return $this->model->find($id);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            if ($request->student_ids == null) {
                return $this->responseWithError(___('alert.Please select student.'), []);
            }

            $row = $this->model->with('feesAssignChilds')->findOrFail($id);

            // 1. Save previous data to history before any update
            $historyAt = now();
            $editHistoryId = DB::table('fees_assign_edit_history')->insertGetId([
                'original_fees_assign_id' => $row->id,
                'session_id'              => $row->session_id,
                'classes_id'              => $row->classes_id,
                'section_id'              => $row->section_id,
                'category_id'             => $row->category_id,
                'gender_id'               => $row->gender_id,
                'fees_group_id'           => $row->fees_group_id,
                'history_created_at'      => $historyAt,
                'created_at'              => $historyAt,
                'updated_at'              => $historyAt,
            ]);

            foreach ($row->feesAssignChilds as $child) {
                DB::table('fees_assign_children_edit_history')->insert([
                    'fees_assign_edit_history_id'       => $editHistoryId,
                    'original_fees_assign_children_id'  => $child->id,
                    'fees_assign_id'                    => $child->fees_assign_id,
                    'fees_master_id'                    => $child->fees_master_id,
                    'student_id'                        => $child->student_id,
                    'fees_amount'                       => $child->fees_amount ?? null,
                    'paid_amount'                       => $child->paid_amount ?? null,
                    'remained_amount'                   => $child->remained_amount ?? null,
                    'control_number'                    => $child->control_number ?? null,
                    'quater_one'                        => $child->quater_one ?? null,
                    'quater_two'                        => $child->quater_two ?? null,
                    'quater_three'                      => $child->quater_three ?? null,
                    'quater_four'                       => $child->quater_four ?? null,
                    'history_created_at'                => $historyAt,
                    'created_at'                        => $historyAt,
                    'updated_at'                        => $historyAt,
                ]);
            }

            // 2. Apply update to fees_assign row
            $row->session_id    = setting('session');
            $row->classes_id    = $request->class;
            $row->section_id   = $request->section ?? null;
            $row->fees_group_id = $request->fees_group;
            $row->category_id   = $request->student_category == "" ? null : $request->student_category;
            $row->gender_id     = $request->gender == "" ? null : $request->gender;
            $row->save();

            // Delete students that are no longer in the list, but only if paid_amount is 0
            $diff = array_diff($row->feesAssignChilds->pluck('student_id')->toArray(), $request->student_ids);
            if (!empty($diff)) {
                FeesAssignChildren::where('fees_assign_id', $row->id)
                    ->whereIn('student_id', $diff)
                    ->where('paid_amount', 0)
                    ->delete();
            }

            // 3. Process fees assignments: no duplicate for same (fees_assign, student, fees_master); no duplicate for same (fees_assign, student, fees_group)
            foreach ($request->fees_master_ids as $fees_master) {
                foreach ($request->student_ids as $student_id) {
                    $feeTypeId = 0;

                    if ($request->fees_group == "3") {
                        $transportProfile = DB::select("
                            SELECT student_categories.name 
                            FROM student_categories
                            INNER JOIN students ON students.student_category_id = student_categories.id
                            WHERE students.id = ?
                        ", [$student_id]);

                        if (!empty($transportProfile)) {
                            $transportProfile = $transportProfile[0]->name;
                            $transportProfileParts = explode('DAY', $transportProfile);

                            if (!empty($transportProfileParts[1])) {
                                $feeTypeId = DB::select("
                                    SELECT id FROM fees_types WHERE code = ?
                                ", [trim($transportProfileParts[1])])[0]->id;
                                $fees_master = DB::select("
                                    SELECT id FROM fees_masters WHERE fees_type_id = ?
                                ", [$feeTypeId])[0]->id;
                            }
                        }
                    } else {
                        $feeTypeId = "1";
                    }

                    if ($feeTypeId != 0) {
                        // Already exists for this (fees_assign_id, fees_master_id, student_id) -> skip
                        $existingSame = DB::select('
                            SELECT id FROM fees_assign_childrens 
                            WHERE fees_assign_id = ? AND fees_master_id = ? AND student_id = ?
                        ', [$row->id, $fees_master, $student_id]);

                        if (!empty($existingSame)) {
                            continue;
                        }

                        // Student already has an assignment in this fees_assign for the same fees_group -> do not duplicate
                        $sameGroupExists = DB::select('
                            SELECT fac.id 
                            FROM fees_assign_childrens fac
                            INNER JOIN fees_masters fm ON fm.id = fac.fees_master_id
                            WHERE fac.fees_assign_id = ? AND fac.student_id = ? AND fm.fees_group_id = ?
                        ', [$row->id, $student_id, $row->fees_group_id]);

                        if (!empty($sameGroupExists)) {
                            continue;
                        }

                        $controlNumber = $this->getStudentControlNumber($student_id);
                        if (!empty($controlNumber)) {
                            $feesChild = new FeesAssignChildren();
                            $feesChild->fees_assign_id = $row->id;
                            $feesChild->fees_master_id = $fees_master;
                            $feesChild->student_id = $student_id;
                            $feesChild->fees_amount = $this->getFeesAmount($fees_master);
                            $feesChild->remained_amount = $this->getFeesAmount($fees_master);
                            $feesChild->control_number = $controlNumber;

                            if ($this->getDueDate($fees_master) > 8) {
                                $quarterAmount = $this->getFeesAmount($fees_master) / 4;
                                $feesChild->quater_one = $quarterAmount;
                                $feesChild->quater_two = $quarterAmount;
                                $feesChild->quater_three = $quarterAmount;
                                $feesChild->quater_four = $quarterAmount;
                            }

                            $feesChild->save();
                        } else {
                            return $this->responseWithError('Fees has already been assigned', []);
                        }
                    }
                }
            }

            DB::commit();
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error updating fees assign: ' . $th->getMessage());
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $row = $this->model->find($id);
            $row->delete();
            //TO-DO: To clear all fees assign ID on fees_assign_children

            DB::commit();
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function getFeesAssignStudents($request)
    {
        $students = SessionClassStudent::query()
            ->where('session_id', setting('session'))
            ->where('classes_id', $request->class)
            ->join('students', 'students.id', '=', 'session_class_students.student_id')
            ->orderBy('students.first_name')
            ->orderBy('students.last_name')
            ->select('session_class_students.*');

        if ($request->category != "") {
            $students = $students->where('students.student_category_id', $request->category);
        }
        Log::info('getFeesAssignStudents - Query Results', [
            'session_id' => setting('session'),
            'class_id' => $request->class,
            'total_found' => $students->count()
        ]);
        return $students->with('student')->get();
    }

    public function groupTypes($request)
    {
        Log::info('groupTypes - Request', [
            'session_id' => setting('session'),
            'fees_group_id' => $request->id
        ]);
        //log a return value of the query
        Log::info('groupTypes - Return Value', [
            'return_value' => FeesMaster::active()
                ->where('fees_group_id', $request->id)
                ->where('session_id', setting('session'))
                ->get()
        ]);
        return FeesMaster::active()
            ->where('fees_group_id', $request->id)
            ->where('session_id', setting('session'))
            ->get();
    }


}
