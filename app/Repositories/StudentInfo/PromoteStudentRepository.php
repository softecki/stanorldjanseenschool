<?php

namespace App\Repositories\StudentInfo;

use App\Enums\ApiStatus;
use App\Models\Fees\FeesAssign;
use App\Models\Fees\FeesAssignChildren;
use App\Models\Fees\FeesGroup;
use App\Models\Fees\FeesMaster;
use App\Models\Fees\FeesMasterChildren;
use App\Models\Fees\FeesType;
use App\Traits\ReturnFormatTrait;
use App\Models\StudentInfo\PromoteStudent;
use App\Interfaces\StudentInfo\PromoteStudentInterface;
use App\Models\Academic\ClassSetup;
use App\Models\Academic\ClassSetupChildren;
use App\Models\ExaminationResult;
use App\Models\StudentInfo\SessionClassStudent;
use Illuminate\Support\Facades\DB;

class PromoteStudentRepository implements PromoteStudentInterface
{
    use ReturnFormatTrait;

    private $model;

    public function __construct(PromoteStudent $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->active()->get();
    }

    public function getPaginateAll()
    {
        return $this->model::latest()->paginate(10);
    }

    public function search($request)
    {
        try {
            // Get student IDs that already exist in the target promotion session/class/section
            // to avoid promoting students who are already in the target session
            $ids = SessionClassStudent::where('session_id', $request->promote_session)
            ->where('classes_id', $request->promote_class)
            ->where('section_id', $request->promote_section)
            ->pluck('student_id');

            
            // Get students from current session who should be promoted
            // This includes newly onboarded students who are in the current session
            // The search should find all students in the current session with the specified class/section
            // who are not already in the target promotion session/class/section
            $students = SessionClassStudent::where('session_id', setting('session'))
            ->where('classes_id', $request->class)
            ->where('section_id', $request->section)
            ->whereNotIn('student_id', $ids)
            ->get();
            
            // If no students found, check if there's an issue with session matching
            // This handles edge cases where students might be in a different session
            if ($students->isEmpty()) {
                // Log for debugging - students might be onboarded but not in the expected session
                \Log::info('Promotion search returned no students', [
                    'current_session' => setting('session'),
                    'request_class' => $request->class,
                    'request_section' => $request->section,
                    'promote_session' => $request->promote_session,
                    'promote_class' => $request->promote_class,
                    'promote_section' => $request->promote_section
                ]);
            }

            
            $examResults = ExaminationResult::where('session_id', setting('session'))
            ->where('classes_id', $request->class)
            ->where('section_id', $request->section)
            ->whereNotIn('student_id', $ids)
            ->select('student_id','result')
            ->get();
            
            
            $results = [];
            foreach ($students as $student) {
                $items = [];
                foreach ($examResults as $result) {
                    if ($student->student_id == $result->student_id) {
                        $items[] = $result->result == 'Failed' ? 'Fail' : 'Pass';
                    }
                }
                foreach ($items as $item) {
                    if($item == 'Fail'){
                        $results[$student->student_id] = 'Fail';
                        break;
                    }else
                        $results[$student->student_id] = 'Pass';
                }
            }

            $data['students'] = $students;
            $data['results']  = $results;
            

            return $this->responseWithSuccess(___('alert.get_successfully'), $data);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }


    private function checkIfOutstandingBalanceExist()
    {

        $class_setup = DB::select('SELECT id from fees_groups where name  = ?',["Outstanding Balance"]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }

    private function checkAdmissionFeeExist()
    {

        $class_setup = DB::select('SELECT id from fees_groups where name  = ?',["School Fees"]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }

    private function checkAdmissionFeeTypeExist($class)
    {
        $class_setup = DB::select('SELECT id from fees_types where class_id  = ?',[$class]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }

    private function checkAdmissionFeeGroupExist()
    {

        $class_setup = DB::select('SELECT id from fees_groups where name  = ?',["Admission Fee Details"]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }


    private function checkFeeType()
    {
        $class_setup = DB::select('SELECT id from fees_types where name  = ?',["Outstanding Balance Fee"]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }

    private function checkFeeMaster( $fees_group_id,  $fee_type_id,$session_id)
    {
        $class_setup = DB::select('SELECT id from fees_masters where fees_group_id  = ? and fees_type_id = ? and session_id =?'
            ,[$fees_group_id,$fee_type_id,$session_id]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }

    private function getClassSchoolFee( $fees_group_id,  $fee_type_id,$session_id)
    {
        $class_setup = DB::select('SELECT amount from fees_masters where fees_group_id  = ? and fees_type_id = ? and session_id =?'
            ,[$fees_group_id,$fee_type_id,$session_id]);
        if(!empty($class_setup)){
            return $class_setup[0]->amount;
        }else{
            return "";
        }
    }

    private function checkFeesMasterChildren($fee_type_id, $fees_master_id)
    {
        $class_setup = DB::select('SELECT id from fees_master_childrens where fees_master_id = ? and fees_type_id = ?'
            , [$fees_master_id, $fee_type_id]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }

    public function store($request)
    {
        try {
            if($request->students == null){
                return $this->responseWithError(___('alert.Please select students'), []);
            }

            // Use database transaction to ensure data consistency
            DB::beginTransaction();

            // if (empty($this->checkIfOutstandingBalanceExist())) {
            //     $rowFeesGroup = new FeesGroup();
            //     $rowFeesGroup->name = "Outstanding Balance";
            //     $rowFeesGroup->description = "Outstanding Balance";
            //     $rowFeesGroup->status = "1";
            //     $rowFeesGroup->online_admission_fees = 0;
            //     $rowFeesGroup->save();
            //     $fees_group_id = $rowFeesGroup->id;
            // }else{
            //     $fees_group_id = $this->checkIfOutstandingBalanceExist();
            // }


            // if (empty($this->checkFeeType())) {
            //     $rowFeesType = new FeesType();
            //     $rowFeesType->name = "Outstanding Balance Fee";
            //     $rowFeesType->code = "001";
            //     $rowFeesType->description = "Outstanding Balance Fee";
            //     $rowFeesType->status = "1";
            //     $rowFeesType->save();
            //     $fee_type_id = $rowFeesType->id;
            // }else{
            //     $fee_type_id = $this->checkFeeType();
            // }


            // if (empty($this->checkAdmissionFeeExist())) {
            //     $rowFeesGroup = new FeesGroup();
            //     $rowFeesGroup->name = "School Fees";
            //     $rowFeesGroup->description = "School Fees";
            //     $rowFeesGroup->status = "1";
            //     $rowFeesGroup->online_admission_fees = 0;
            //     $rowFeesGroup->save();
            //     $admissionfee = $rowFeesGroup->id;
            // }else{
            //     $admissionfee = $this->checkAdmissionFeeExist();
            // }
            // Get all fees masters for the promote session and class
            $feesMasters = DB::select("
                SELECT fees_group_id, fees_type_id, amount, fees_masters.id as fees_master_id
            FROM fees_masters
            INNER JOIN fees_types
                ON fees_types.id = fees_masters.fees_type_id
                WHERE fees_masters.session_id = ?
                  AND fees_types.class_id = ?
            ", [$request->promote_session, $request->promote_class]);

            // Commented out old fee assignment logic
            /*
            if (empty($this->checkAdmissionFeeTypeExist($request->promote_class))) {
                $rowFeesType = new FeesType();
                $rowFeesType->name = "School Fees " . $request->promote_class;
                $rowFeesType->code = "00".$request->promote_class;
                $rowFeesType->description = "School Fees " . $request->promote_class;
                $rowFeesType->status = "1";
                $rowFeesType->class_id =  $request->promote_class;
                $rowFeesType->save();
                $admissionfeetype = $rowFeesType->id;
            }else{
                $admissionfeetype = $this->checkAdmissionFeeTypeExist($request->promote_class);
            }


            $session_id = $request->promote_session;
            if (empty($this->checkFeeMaster($fees_group_id,$fee_type_id,$session_id))) {
                $rowFeesMaster = new FeesMaster();
                $rowFeesMaster->session_id = $session_id;
                $rowFeesMaster->fees_group_id = $fees_group_id;
                $rowFeesMaster->fees_type_id = $fee_type_id;
                $rowFeesMaster->due_date = Date('Y-12-31');
                $rowFeesMaster->amount = "0";
                $rowFeesMaster->fine_type = "0";
                $rowFeesMaster->percentage = "0";
                $rowFeesMaster->fine_amount = "0";
                $rowFeesMaster->status = "1";
                $rowFeesMaster->save();
                $fees_master_id = $rowFeesMaster->id;
            }else{
                $fees_master_id = $this->checkFeeMaster($fees_group_id,$fee_type_id,$session_id);
            }

            if (empty($this->checkFeeMaster($admissionfee,$admissionfeetype,$session_id))) {
                $rowFeesMaster = new FeesMaster();
                $rowFeesMaster->session_id = $session_id;
                $rowFeesMaster->fees_group_id = $admissionfee;
                $rowFeesMaster->fees_type_id = $admissionfeetype;
                $rowFeesMaster->due_date = Date('Y-12-31');
                // Try to get fee from new session first, fallback to current session
                $schoolFeeAmount = $this->getClassSchoolFee($admissionfee, $admissionfeetype, $session_id);
                if (empty($schoolFeeAmount)) {
                    $schoolFeeAmount = $this->getClassSchoolFee($admissionfee, $admissionfeetype, setting('session'));
                }
                $rowFeesMaster->amount = $schoolFeeAmount ?: 0;
                $rowFeesMaster->fine_type = "0";
                $rowFeesMaster->percentage = "0";
                $rowFeesMaster->fine_amount = "0";
                $rowFeesMaster->status = "1";
                $rowFeesMaster->save();
                $admissionfees_master_id = $rowFeesMaster->id;
            }else{
                $admissionfees_master_id = $this->checkFeeMaster($admissionfee,$admissionfeetype,$session_id);
            }

            if(empty($this->checkFeesMasterChildren($fee_type_id, $fees_master_id))){
                $feesChield                 = new FeesMasterChildren();
                $feesChield->fees_master_id =  $fees_master_id;
                $feesChield->fees_type_id   = $fee_type_id;
                $feesChield->save();
                $feesChield_id =  $feesChield->id;
            }else{
                $feesChield_id = $this->checkFeesMasterChildren($fee_type_id, $fees_master_id);
            }

            if(empty($this->checkFeesMasterChildren($admissionfeetype, $admissionfees_master_id))){
                $feesChield                 = new FeesMasterChildren();
                $feesChield->fees_master_id =  $admissionfees_master_id;
                $feesChield->fees_type_id   = $admissionfeetype;
                $feesChield->save();
                $feesChieldMaster_id =  $feesChield->id;
            }else{
                $feesChieldMaster_id = $this->checkFeesMasterChildren($admissionfeetype, $admissionfees_master_id);
            }
            */



            foreach ($request->students as $key=>$value) {
                $student_id = $value[0];
                
                // Check if student already exists in the new session
                $existingSessionStudent = SessionClassStudent::where('session_id', $request->promote_session)
                    ->where('student_id', $student_id)
                    ->first();
                
                if ($existingSessionStudent) {
                    // Update existing record
                    $existingSessionStudent->classes_id = $request->promote_class;
                    $existingSessionStudent->section_id = $request->promote_section;
                    $existingSessionStudent->result = $request->result[$key][0];
                    $existingSessionStudent->roll = $request->roll[$key][0];
                    $existingSessionStudent->save();
                    $row = $existingSessionStudent;
                } else {
                    // Create new record
                    $row                     = new SessionClassStudent;
                    $row->session_id         = $request->promote_session;
                    $row->classes_id         = $request->promote_class;
                    $row->section_id         = $request->promote_section;
                    $row->student_id         = $student_id;
                    $row->result             = $request->result[$key][0];
                    $row->roll               = $request->roll[$key][0];
                    $row->save();
                }

                // Loop through each fees_master from the query and create FeesAssign and FeesAssignChildren
                foreach ($feesMasters as $feesMaster) {
                    // Check or create FeesAssign for this fees_group_id
                    $feesAssignId = $this->checkFeesAssign(
                        $request->promote_class,
                        $feesMaster->fees_group_id,
                        $request->promote_section,
                        $request->promote_session
                    );

                    if (empty($feesAssignId)) {
                        $rowFeesAssign = new FeesAssign();
                        $rowFeesAssign->session_id = $request->promote_session;
                        $rowFeesAssign->classes_id = $request->promote_class;
                        $rowFeesAssign->section_id = $request->promote_section;
                        $rowFeesAssign->fees_group_id = $feesMaster->fees_group_id;
                        $rowFeesAssign->save();
                        $feesAssignId = $rowFeesAssign->id;
                    }

                    // Create FeesAssignChildren for this student and fees_master
                    if (empty($this->checkFeesAssignChildren($feesAssignId, $feesMaster->fees_master_id, $student_id))) {
                        $feesChield = new FeesAssignChildren();
                        $feesChield->fees_assign_id = $feesAssignId;
                        $feesChield->fees_master_id = $feesMaster->fees_master_id;
                        $feesChield->student_id = $student_id;
                        $feesChield->fees_amount = $feesMaster->amount;
                        $feesChield->remained_amount = $feesMaster->amount;
                        $feesChield->paid_amount = 0;
                        $feesChield->control_number = $this->getStudentControlNumber($student_id);
                        $feesChield->save();
                    }
                }

                // Handle Transport Fees (fees_group_id = 3)
                // First, get the fees_type_id from previous session for transport fees
                $previousTransportFeeType = DB::select("
                    SELECT fees_masters.fees_type_id 
                    FROM fees_assign_childrens 
                    INNER JOIN fees_masters ON fees_masters.id = fees_assign_childrens.fees_master_id 
                    INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                    WHERE fees_assign_childrens.student_id = ? 
                      AND fees_assigns.fees_group_id = 3
                      AND fees_assigns.session_id = ?
                    LIMIT 1
                ", [$student_id, '8']);

                if (!empty($previousTransportFeeType)) {
                    $transportFeeTypeId = $previousTransportFeeType[0]->fees_type_id;
                    
                    // Fetch fees_master for transport in the new session
                    $transportFeesMaster = DB::select("
                        SELECT fees_group_id, fees_type_id, amount, fees_masters.id as fees_master_id 
                        FROM fees_masters 
                        INNER JOIN fees_types ON fees_types.id = fees_masters.fees_type_id 
                        WHERE fees_masters.session_id = ? 
                          AND fees_masters.fees_type_id = ?
                        LIMIT 1
                    ", [$request->promote_session, $transportFeeTypeId]);

                    if (!empty($transportFeesMaster)) {
                        $transportMaster = $transportFeesMaster[0];
                        
                        // Check or create FeesAssign for transport (fees_group_id = 3)
                        $transportFeesAssignId = $this->checkFeesAssign(
                            $request->promote_class,
                            3, // fees_group_id = 3 for transport
                            $request->promote_section,
                            $request->promote_session
                        );

                        if (empty($transportFeesAssignId)) {
                            $rowFeesAssign = new FeesAssign();
                            $rowFeesAssign->session_id = $request->promote_session;
                            $rowFeesAssign->classes_id = $request->promote_class;
                            $rowFeesAssign->section_id = $request->promote_section;
                            $rowFeesAssign->fees_group_id = 3; // Transport fees
                            $rowFeesAssign->save();
                            $transportFeesAssignId = $rowFeesAssign->id;
                        }

                        // Create FeesAssignChildren for transport
                        if (empty($this->checkFeesAssignChildren($transportFeesAssignId, $transportMaster->fees_master_id, $student_id))) {
                            $feesChield = new FeesAssignChildren();
                            $feesChield->fees_assign_id = $transportFeesAssignId;
                            $feesChield->fees_master_id = $transportMaster->fees_master_id;
                            $feesChield->student_id = $student_id;
                            $feesChield->fees_amount = $transportMaster->amount;
                            $feesChield->remained_amount = $transportMaster->amount;
                            $feesChield->paid_amount = 0;
                            $feesChield->control_number = $this->getStudentControlNumber($student_id);
                            $feesChield->save();
                        }
                    }
                }

                // Commented out old fee assignment logic
                /*
                if (empty($this->checkFeesAssign($request->promote_class,$fees_group_id,$request->promote_section,$request->promote_session))) {
                    $rowFeesAssign = new FeesAssign();
                    $rowFeesAssign->session_id = $request->promote_session;
                    $rowFeesAssign->classes_id = $request->promote_class;
                    $rowFeesAssign->section_id =  $request->promote_section;
                    $rowFeesAssign->fees_group_id = $fees_group_id;
                    $rowFeesAssign->save();
                    $feesAssignId = $rowFeesAssign->id;
                }else{
                    $feesAssignId = $this->checkFeesAssign($request->promote_class,$fees_group_id,$request->promote_section,$request->promote_session);
                }

                if (empty($this->checkFeesAssign($request->promote_class,$admissionfee,$request->promote_section,$request->promote_session))) {
                    $rowFeesAssign = new FeesAssign();
                    $rowFeesAssign->session_id = $request->promote_session;
                    $rowFeesAssign->classes_id = $request->promote_class;
                    $rowFeesAssign->section_id =  $request->promote_section;
                    $rowFeesAssign->fees_group_id = $admissionfee;
                    $rowFeesAssign->save();
                    $feesAssignIdAdmissionFee = $rowFeesAssign->id;
                }else{
                    $feesAssignIdAdmissionFee = $this->checkFeesAssign($request->promote_class,$admissionfee,$request->promote_section,$request->promote_session);
                }

                // Initialize exceededAmount for each student
                $exceededAmount = 0;
                $studentBalance = $this->getStudentBalance($student_id, setting('session'));
                
                // Handle outstanding balance from previous session
                if (!empty(trim($studentBalance)) && $studentBalance != 0) {
                    if (empty($this->checkFeesAssignChildren($feesAssignId, $fees_master_id, $student_id))) {
                        $feesChield = new FeesAssignChildren();
                        $feesChield->fees_assign_id = $feesAssignId;
                        $feesChield->fees_master_id = $fees_master_id;
                        $feesChield->student_id = $student_id;
                        $feesChield->fees_amount = $studentBalance;
                        
                        if ($studentBalance > 0) {
                            $feesChield->remained_amount = $studentBalance;
                        } else {
                            // Negative balance means overpayment
                            $exceededAmount = $studentBalance;
                            $feesChield->remained_amount = '0';
                        }
                        $feesChield->control_number = $this->getStudentControlNumber($student_id);
                        $feesChield->save();
                    }
                }

                // Assign school fees for the new session
                $schoolFeeAmount = $this->getClassSchoolFee($admissionfee, $admissionfeetype, $request->promote_session);
                if (empty($schoolFeeAmount)) {
                    // Fallback to current session if not found in new session
                    $schoolFeeAmount = $this->getClassSchoolFee($admissionfee, $admissionfeetype, setting('session'));
                }
                
                if (empty($this->checkFeesAssignChildren($feesAssignIdAdmissionFee, $admissionfees_master_id, $student_id))) {
                    $feesChield = new FeesAssignChildren();
                    $feesChield->fees_assign_id = $feesAssignIdAdmissionFee;
                    $feesChield->fees_master_id = $admissionfees_master_id;
                    $feesChield->student_id = $student_id;
                    $feesChield->fees_amount = $schoolFeeAmount;
                    
                    // If there was an overpayment, adjust the remaining amount
                    if ($exceededAmount < 0) {
                        $feesChield->remained_amount = $schoolFeeAmount + abs($exceededAmount);
                    } else {
                        $feesChield->remained_amount = $schoolFeeAmount;
                    }
                    $feesChield->control_number = $this->getStudentControlNumber($student_id);
                    $feesChield->save();
                }
                */
            }
            
            // Commit transaction if all operations succeed
            DB::commit();
            return $this->responseWithSuccess(___('alert.Promote successfully'), []);
        } catch (\Throwable $th) {
            // Rollback transaction on error
            DB::rollBack();
            \Log::error('Student Promotion Error: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString(),
                'request' => $request->all()
            ]);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again') . ': ' . $th->getMessage(), []);
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

    public function getStudentBalance($id, $session_id = null){
        // Get balance from fees_assign_childrens filtered by session through fees_assigns
        if ($session_id) {
            $result = DB::select('
                SELECT SUM(fac.remained_amount) as amount 
                FROM fees_assign_childrens fac
                INNER JOIN fees_assigns fa ON fa.id = fac.fees_assign_id
                WHERE fac.student_id = ? AND fa.session_id = ?
            ', [$id, $session_id]);
        } else {
            // If no session specified, get balance from all sessions (for backward compatibility)
            $result = DB::select('SELECT SUM(remained_amount) as amount FROM fees_assign_childrens where student_id = ?', [$id]);
        }
        
        if(!empty($result) && $result[0]->amount !== null){
            return $result[0]->amount;
        }else{
            return 0;
        }
    }

    public function getFeeAmount($id){
        $result = DB::select('SELECT remained_amount as amount FROM fees_assign_childrens where student_id = ?',[$id]);
        if(!empty($result)){
            return $result[0]->amount;
        }else{
            return "";
        }

    }
    private function checkFeesAssignChildren( $feesAssignId,  $fees_master_id,  $student_id)
    {
        $class_setup = DB::select('SELECT id from fees_assign_childrens where fees_assign_id  = ? and fees_master_id = ? and student_id = ?'
            ,[$feesAssignId,$fees_master_id,$student_id]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }
    private function checkFeesAssign( $classesStore_id,  $fees_group_id,$sectionStore_id,$session_id)
    {
        $class_setup = DB::select('SELECT id from fees_assigns where classes_id  = ? and fees_group_id = ? and section_id = ? and session_id = ?'
            ,[$classesStore_id,$fees_group_id,$sectionStore_id,$session_id]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }

    public function getClass($request)
    {
        return ClassSetup::where('session_id', $request->id)->with('class')->get();
    }

    public function getSections($request)
    {
        $result = ClassSetup::where('session_id', $request->session)->where('classes_id', $request->class)->with('classSetupChildrenAll')->first();
        return ClassSetupChildren::where('class_setup_id', $result->id)->with('section')->get();
    }
}
