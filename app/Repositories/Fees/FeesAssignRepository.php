<?php

namespace App\Repositories\Fees;

use App\Models\Fees\FeesAssign;
use App\Models\Fees\FeesMaster;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use App\Models\Fees\FeesAssignChildren;
use App\Models\Fees\FeesMasterQuarter;
use App\Interfaces\Fees\FeesAssignInterface;
use App\Models\StudentInfo\SessionClassStudent;
use App\Repositories\StudentInfo\StudentRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FeesAssignRepository implements FeesAssignInterface
{
    use ReturnFormatTrait;

    private FeesAssign $model;

    private StudentRepository $students;

    public function __construct(FeesAssign $model, StudentRepository $students)
    {
        $this->model = $model;
        $this->students = $students;
    }

    public function all()
    {
        return $this->model->active()->get();
    }

    public function getPaginateAll()
    {
        return $this->model::query()
            ->withCount('feesAssignChilds')
            ->with(['group:id,name', 'class:id,name', 'section:id,name', 'session:id,name', 'category:id,name'])
            ->latest()
            ->where('session_id', setting('session'))
            ->paginate(10);
    }

    public function show($id)
    {
        return $this->model
            ->withCount('feesAssignChilds')
            ->with([
                'group:id,name',
                'class:id,name',
                'section:id,name',
                'session:id,name',
                'category:id,name',
                'gender:id,name',
                'feesAssignChilds' => function ($q) {
                    $q->with(['student:id,first_name,last_name,admission_no,control_number', 'feesMaster' => function ($m) {
                        $m->select('id', 'fees_type_id', 'fees_group_id', 'amount', 'due_date')->with(['type:id,name']);
                    }])
                        /** Full list needed for SPA view/edit grouping by fee type (was capped at 80). */
                        ->orderBy('id');
                },
            ])
            ->find($id);
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
                $row->category_id   = $request->student_category == null || $request->student_category === '' ? null : $request->student_category;
                $row->gender_id     = $request->gender == null || $request->gender === '' ? null : $request->gender;
                $row->save();
                $feeAssignId = $row->id;
                Log::info('FeesAssignRepository::store - New fees assign created', ['fees_assign_id' => $feeAssignId]);
            } else {
                $feeAssignId = $feeAssignIdResult[0]->id;
                Log::info('FeesAssignRepository::store - Using existing fees assign', ['fees_assign_id' => $feeAssignId]);
                $this->model->whereKey($feeAssignId)->update([
                    'category_id' => $request->student_category == null || $request->student_category === '' ? null : $request->student_category,
                    'gender_id'   => $request->gender == null || $request->gender === '' ? null : $request->gender,
                ]);
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

                $masterPickList = array_values(array_unique(array_filter(array_map('intval', (array) ($request->fees_master_ids ?? [])))));
                $pickedUiMasterId = (isset($masterPickList[0]) && $masterPickList[0] > 0) ? $masterPickList[0] : null;

                if ((string) $request->fees_group !== '3' && ($pickedUiMasterId === null || $pickedUiMasterId <= 0)) {
                    return $this->responseWithError(__('Please select one fee master.'), []);
                }

                foreach ($request->student_ids as $student_id) {
                    $studentIdInt = (int) $student_id;
                    $target = $this->resolveBulkAssignmentTarget($request, $studentIdInt, (int) $sessionId, (int) $feeAssignId, $pickedUiMasterId);

                    if ($target === null) {
                        $totalSkipped++;

                        continue;
                    }

                    $insert = $this->insertFeesAssignChildOrSkip((int) $target['fees_assign_id'], (int) $target['fees_master_id'], $studentIdInt);
                    if ($insert === 'created') {
                        $totalProcessed++;
                    } elseif ($insert === 'skipped_duplicate') {
                        $totalSkipped++;
                    } else {
                        $totalErrors++;
                        Log::error('FeesAssignRepository::store - Student control number is empty', [
                            'student_id' => $student_id,
                        ]);

                        return $this->responseWithError(__('Students must have a control number before fees can be assigned.'), []);
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

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $sessionIdUpdate = (int) setting('session');
            $normalizedLines = $this->normalizeAssignmentLines($request->input('assignment_lines'));
            $useLineMode = $normalizedLines !== [];

            $studentIdsUnion = $useLineMode
                ? collect($normalizedLines)->flatMap(fn ($l) => $l['student_ids'])->unique()->values()->all()
                : array_values(array_unique(array_filter(array_map('intval', (array) ($request->student_ids ?? [])))));

            if ($studentIdsUnion === []) {
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
            $diff = array_diff($row->feesAssignChilds->pluck('student_id')->toArray(), $studentIdsUnion);
            if (! empty($diff)) {
                FeesAssignChildren::where('fees_assign_id', $row->id)
                    ->whereIn('student_id', $diff)
                    ->where('paid_amount', 0)
                    ->delete();
            }

            // 3. Add / sync lines
            if ($useLineMode) {
                if ($msg = $this->validateAssignmentLinesExclusive($normalizedLines)) {
                    DB::rollBack();

                    return $this->responseWithError($msg, []);
                }
                if ($msg = $this->validateAssignmentMastersBelongToGroup($normalizedLines, (string) $request->fees_group, $sessionIdUpdate)) {
                    DB::rollBack();

                    return $this->responseWithError($msg, []);
                }

                $unpaid = FeesAssignChildren::query()->where('fees_assign_id', $row->id);
                if (Schema::hasColumn('fees_assign_childrens', 'paid_amount')) {
                    $unpaid->where(function ($q) {
                        $q->whereNull('paid_amount')->orWhere('paid_amount', 0);
                    });
                }
                $unpaid->delete();

                foreach ($normalizedLines as $line) {
                    $pickedUiMasterId = (int) $line['fees_master_id'];
                    foreach ($line['student_ids'] as $student_id) {
                        $studentIdInt = (int) $student_id;
                        $target = $this->resolveBulkAssignmentTarget($request, $studentIdInt, $sessionIdUpdate, (int) $row->id, $pickedUiMasterId);

                        if ($target === null) {
                            continue;
                        }

                        $insert = $this->insertFeesAssignChildOrSkip((int) $target['fees_assign_id'], (int) $target['fees_master_id'], $studentIdInt);

                        if ($insert === 'skipped_no_control_number') {
                            DB::rollBack();

                            return $this->responseWithError(__('Students must have a control number before fees can be assigned.'), []);
                        }
                    }
                }
            } else {
                $masterPickList = array_values(array_unique(array_filter(array_map('intval', (array) ($request->fees_master_ids ?? [])))));
                $pickedCandidates = $masterPickList;
                if ((string) $request->fees_group === '3') {
                    $pickedCandidates = [(isset($masterPickList[0]) && $masterPickList[0] > 0) ? $masterPickList[0] : null];
                } elseif ($pickedCandidates === []) {
                    $pickedCandidates = [null];
                }

                foreach ($pickedCandidates as $pickedUiMasterId) {
                    foreach ($studentIdsUnion as $student_id) {
                        $studentIdInt = (int) $student_id;
                        $target = $this->resolveBulkAssignmentTarget($request, $studentIdInt, $sessionIdUpdate, (int) $row->id, $pickedUiMasterId);

                        if ($target === null) {
                            continue;
                        }

                        $insert = $this->insertFeesAssignChildOrSkip((int) $target['fees_assign_id'], (int) $target['fees_master_id'], $studentIdInt);

                        if ($insert === 'skipped_no_control_number') {
                            DB::rollBack();

                            return $this->responseWithError(__('Students must have a control number before fees can be assigned.'), []);
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
            if ($row === null) {
                DB::rollBack();

                return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
            }
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
        if ($request->gender != null && $request->gender !== '') {
            $students = $students->where('students.gender_id', $request->gender);
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

    private function getStudentCategoryId(int $studentId): ?int
    {
        $id = DB::table('students')->where('id', $studentId)->value('student_category_id');

        return $id !== null ? (int) $id : null;
    }

    /**
     * Bulk fees assign/update: enforce fees_type_student_category only for transport (fees_group 3).
     * School and other groups apply to every selected student (aligned with StudentRepository auto-assign).
     */
    private function masterEligibleForStudentBulkAssign($feesGroupId, int $feeTypeId, ?int $studentCategoryId): bool
    {
        if ((int) $feesGroupId !== 3) {
            return true;
        }

        return $this->feesTypeAllowsStudentCategory($feeTypeId, $studentCategoryId);
    }

    /**
     * If pivot has rows for the fee type, student must be in one of those categories.
     * No pivot rows means no restriction (backward compatible).
     */
    private function feesTypeAllowsStudentCategory(int $feeTypeId, ?int $studentCategoryId): bool
    {
        if (!Schema::hasTable('fees_type_student_category')) {
            return true;
        }

        $restricted = DB::table('fees_type_student_category')
            ->where('fees_type_id', $feeTypeId)
            ->exists();

        if (!$restricted) {
            return true;
        }

        if ($studentCategoryId === null) {
            return false;
        }

        return DB::table('fees_type_student_category')
            ->where('fees_type_id', $feeTypeId)
            ->where('student_category_id', $studentCategoryId)
            ->exists();
    }

    private function getStudentEnrollmentClassId(int $studentId, int $sessionId): ?int
    {
        $id = DB::table('session_class_students')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->value('classes_id');

        return $id !== null ? (int) $id : null;
    }

    /**
     * Transport (group 3): master from category; boarding assigns school lodging master elsewhere (no transport line).
     * Non-transport: prefer fees master for student class + selected group where types are class-linked; otherwise use picked UI master.
     *
     * @return array{fees_assign_id:int, fees_master_id:int}|null
     */
    private function resolveBulkAssignmentTarget($request, int $studentId, int $sessionId, int $defaultFeesAssignId, ?int $pickedUiMasterId): ?array
    {
        $feesGroupId = (string) $request->fees_group;
        $classIdForForm = (int) $request->class;
        $sectionId = 1;
        $catId = $this->getStudentCategoryId($studentId);

        if ($feesGroupId === '3') {
            if ($this->students->feesAssignIsBoardingStudentCategory($catId)) {
                $masterId = $this->students->feesAssignResolveBoardingSchoolFeesMasterId($catId);
                if (! $masterId) {
                    return null;
                }

                $masterGroup = DB::table('fees_masters')->where('id', $masterId)->value('fees_group_id');
                if ($masterGroup === null || $masterGroup === '') {
                    return null;
                }

                $assignId = $this->students->feesAssignEnsureFeesAssignContainer($classIdForForm, $masterGroup, $sectionId);

                return ['fees_assign_id' => $assignId, 'fees_master_id' => $masterId];
            }

            $masterId = $this->students->feesAssignResolveTransportFeesMasterId($catId);
            if (! $masterId) {
                return null;
            }

            $transportTypeId = (int) (DB::table('fees_masters')->where('id', $masterId)->value('fees_type_id') ?? 0);
            if (! $transportTypeId || ! $this->masterEligibleForStudentBulkAssign(3, $transportTypeId, $catId)) {
                return null;
            }

            return ['fees_assign_id' => $defaultFeesAssignId, 'fees_master_id' => $masterId];
        }

        $targetAssignId = $defaultFeesAssignId;

        if ($this->students->feesAssignIsBoardingStudentCategory($catId)) {
            $boardMid = $this->students->feesAssignResolveBoardingSchoolFeesMasterId($catId);
            if ($boardMid) {
                $g = DB::table('fees_masters')->where('id', $boardMid)->value('fees_group_id');
                if ($g !== null && $g !== '') {
                    if ((string) $g === $feesGroupId) {
                        return ['fees_assign_id' => $targetAssignId, 'fees_master_id' => (int) $boardMid];
                    }

                    $foreignAssignId = $this->students->feesAssignEnsureFeesAssignContainer($classIdForForm, $g, $sectionId);

                    return ['fees_assign_id' => $foreignAssignId, 'fees_master_id' => (int) $boardMid];
                }
            }
        }

        $enrollmentClassId = $this->getStudentEnrollmentClassId($studentId, $sessionId) ?: $classIdForForm;

        $feeTypeRaw = $this->students->getFeeTypeId($enrollmentClassId);
        if ($feeTypeRaw !== '' && $feeTypeRaw !== null && (int) $feeTypeRaw > 0) {
            $classMasterId = DB::table('fees_masters')
                ->where('session_id', $sessionId)
                ->where('fees_group_id', $feesGroupId)
                ->where('fees_type_id', (int) $feeTypeRaw)
                ->orderBy('id')
                ->value('id');
            if ($classMasterId) {
                return ['fees_assign_id' => $targetAssignId, 'fees_master_id' => (int) $classMasterId];
            }
        }

        if ($pickedUiMasterId === null || $pickedUiMasterId <= 0) {
            return null;
        }

        $pickedRow = DB::table('fees_masters')
            ->where('id', $pickedUiMasterId)
            ->where('session_id', $sessionId)
            ->where('fees_group_id', $feesGroupId)
            ->first();

        if (! $pickedRow || ! $pickedRow->fees_type_id) {
            return null;
        }

        if (! $this->masterEligibleForStudentBulkAssign($request->fees_group, (int) $pickedRow->fees_type_id, $catId)) {
            return null;
        }

        return ['fees_assign_id' => $targetAssignId, 'fees_master_id' => (int) $pickedUiMasterId];
    }

    /**
     * Inserts fees_assign_children if not duplicate for (assign × master × student).
     *
     * @return string 'created'|'skipped_no_control_number'|'skipped_duplicate'
     */
    private function insertFeesAssignChildOrSkip(int $feeAssignId, int $masterId, int $studentId): string
    {
        $already = DB::select(
            'SELECT id FROM fees_assign_childrens WHERE fees_assign_id = ? AND fees_master_id = ? AND student_id = ?',
            [$feeAssignId, $masterId, $studentId],
        );

        if (! empty($already)) {
            return 'skipped_duplicate';
        }

        $controlNumber = $this->getStudentControlNumber($studentId);
        if (empty($controlNumber)) {
            return 'skipped_no_control_number';
        }

        $feesAmount = $this->getFeesAmount($masterId);
        $dueDate = $this->getDueDate($masterId);

        $feesChild = new FeesAssignChildren();
        $feesChild->fees_assign_id = $feeAssignId;
        $feesChild->fees_master_id = $masterId;
        $feesChild->student_id = $studentId;
        $feesChild->fees_amount = $feesAmount;
        $feesChild->remained_amount = $feesAmount;
        $feesChild->control_number = $controlNumber;

        if ($dueDate > 8) {
            [$q1, $q2, $q3, $q4] = FeesMasterQuarter::resolvedQuarterAmounts($masterId, (float) $feesAmount);
            $feesChild->quater_one = $q1;
            $feesChild->quater_two = $q2;
            $feesChild->quater_three = $q3;
            $feesChild->quater_four = $q4;
        }

        $feesChild->save();

        return 'created';
    }

    /**
     * @return array<int, array{fees_master_id: int, student_ids: int[]}>
     */
    private function normalizeAssignmentLines(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $line) {
            if (! is_array($line)) {
                continue;
            }

            $mid = (int) ($line['fees_master_id'] ?? 0);
            $ids = [];
            foreach ((array) ($line['student_ids'] ?? []) as $sid) {
                $sid = (int) $sid;
                if ($sid > 0) {
                    $ids[$sid] = $sid;
                }
            }

            $ids = array_values($ids);

            if ($mid > 0 && $ids !== []) {
                $out[] = ['fees_master_id' => $mid, 'student_ids' => $ids];
            }
        }

        return $out;
    }

    private function validateAssignmentLinesExclusive(array $lines): ?string
    {
        $seenStudent = [];

        foreach ($lines as $line) {
            foreach ($line['student_ids'] ?? [] as $sid) {
                $sid = (int) $sid;
                if ($sid <= 0) {
                    continue;
                }

                if (isset($seenStudent[$sid])) {
                    return __('Each student can only be mapped to one fee type per assignment (same fees group).');
                }

                $seenStudent[$sid] = true;
            }
        }

        return null;
    }

    private function validateAssignmentMastersBelongToGroup(array $lines, string $feesGroupId, int $sessionId): ?string
    {
        foreach ($lines as $line) {
            $mid = (int) $line['fees_master_id'];
            $ok = DB::table('fees_masters')
                ->where('id', $mid)
                ->where('fees_group_id', $feesGroupId)
                ->where('session_id', $sessionId)
                ->exists();

            if (! $ok) {
                return __('One or more selected fee masters do not belong to this fees group / session.');
            }
        }

        return null;
    }

}
