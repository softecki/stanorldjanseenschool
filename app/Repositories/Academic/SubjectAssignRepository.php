<?php

namespace App\Repositories\Academic;

use App\Enums\ApiStatus;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Academic\SubjectAssign;
use App\Models\Examination\ExamAssign;
use App\Models\Examination\MarksRegister;
use App\Models\Academic\SubjectAssignChildren;
use App\Interfaces\Academic\SubjectAssignInterface;
use App\Models\Examination\ExamAssignChildren;

class SubjectAssignRepository implements SubjectAssignInterface
{
    use ReturnFormatTrait;

    private $model;
    private $classSetupRepo;

    public function __construct(SubjectAssign $model, ClassSetupRepository $classSetupRepo)
    {
        $this->model          = $model;
        $this->classSetupRepo = $classSetupRepo;
    }

    public function all()
    {
        return $this->model->active()->where('session_id', setting('session'))->get();
    }

    public function getPaginateAll()
    {
        return $this->model::latest()->where('session_id', setting('session'))->paginate(100);
    }

    public function store($request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {

            if($this->model::where('session_id', setting('session'))->where('classes_id', $request->class)->where('section_id', $request->section)->first()) {
                return $this->responseWithError(___('alert.there_is_already_a_class_for_this_session'), []);
            }

            $setup              = new $this->model;
            $setup->session_id  = setting('session');
            $setup->classes_id  = $request->class;
            $setup->section_id  = $request->section;
            $setup->status      = $request->status;
            $setup->save();

            foreach ($request->subjects ?? [] as $key => $item) {
                $row = new SubjectAssignChildren();
                $row->subject_assign_id   = $setup->id;
                $row->subject_id          = $item;
                $row->staff_id            = $request->teachers[$key];
                $row->save();
            }

            DB::commit();
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            dd($th);
            DB::rollback();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function getSubjects($request)
    {
        $result = $this->model->active()->where('session_id', setting('session'))->where('classes_id', $request->classes_id)->where('section_id', $request->section_id)->first();

        return SubjectAssignChildren::with('subject')
                                        ->where('subject_assign_id', @$result->id)
                                        ->when(Auth::user()->role_id == 5, function ($query) {
                                            return $query->where('staff_id', Auth::user()->staff->id);
                                        })
                                        ->select('subject_id')
                                        ->get();
    }

    public function show($id)
    {
        $row = $this->model->find($id);
        $subjects = [];
        $disabled = false;
        $redirect = true;
        foreach ($row->subjectTeacher as $key => $value) {
            $result = ExamAssign::where('session_id',$row->session_id)
                ->where('classes_id',$row->classes_id)
                ->where('section_id',$row->section_id)
                ->where('subject_id',$value->subject_id)
                ->first();
                
            $result ? $subjects[] = 1 : $subjects[] = 0;
            $result ? $disabled = false : false;
            if($redirect)
                $result ? $redirect = true : $redirect = false;
        }
        $data['row']            = $row;
        $data['assignSubjects'] = $subjects;
        $data['disabled']       = $disabled;
        $data['redirect']       = $redirect;
        
        return $data;
    }

    public function update($request, $id)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {

            // if($this->model::where('session_id', setting('session'))->where('classes_id', $request->class)->where('section_id', $request->section)->where('id', '!=', $id)->first()) {
            //     return $this->responseWithError(___('alert.there_is_already_a_class_for_this_session'), []);
            // }

            $setup              = $this->model->find($id);
            $setup->session_id  = setting('session');
            $setup->classes_id  = $request->class;
            $setup->section_id  = $request->section;
            $setup->status      = $request->status;
            $setup->save();

            SubjectAssignChildren::where('subject_assign_id', $setup->id)->delete();

            foreach ($request->subjects ?? [] as $key => $item) {
                $row = new SubjectAssignChildren();
                $row->subject_assign_id   = $setup->id;
                $row->subject_id          = $item;
                $row->staff_id            = $request->teachers[$key];
                $row->save();
            }

            //Process to assign existing exams to the classess with the assigned subjects
           $selectedClasses = DB::select("
                    SELECT exam_type_id, classes_id 
                    FROM exam_assigns 
                    GROUP BY exam_type_id, classes_id
                ");

                foreach ($selectedClasses as $selectedClass) {
                    // Get all section IDs for the selected class
                    $sections = DB::table('class_setup_childrens')
                        ->join('class_setups', 'class_setups.id', '=', 'class_setup_childrens.class_setup_id')
                        ->where('class_setups.classes_id', $selectedClass->classes_id)
                        ->pluck('section_id');

                    foreach ($sections as $section_id) {
                        // Get all subject IDs for the class and section
                        $subjects = DB::table('subjects')
                            ->join('subject_assign_childrens', 'subjects.id', '=', 'subject_assign_childrens.subject_id')
                            ->join('subject_assigns', 'subject_assigns.id', '=', 'subject_assign_childrens.subject_assign_id')
                            ->where('subject_assigns.classes_id', $selectedClass->classes_id)
                            ->where('subject_assigns.section_id', $section_id)
                            ->select('subjects.id')
                            ->get();

                            // Step 1: Get current subject IDs from the database
                            $currentSubjectIds = DB::table('subjects')->pluck('id')->toArray();

                            // Step 2: Get all existing subject IDs in exam_assigns for this class and section
                            $assignedSubjectIds = DB::table('exam_assigns')
                                ->where('classes_id', $selectedClass->classes_id)
                                ->where('section_id', $section_id)
                                ->where('exam_type_id', $selectedClass->exam_type_id)
                                ->pluck('subject_id')
                                ->toArray();

                            // Step 3: Determine which subject_ids in exam_assigns are no longer in the current subject list
                            $subjectsToDelete = array_diff($assignedSubjectIds, $currentSubjectIds);

                            // Step 4: Delete outdated exam_assigns and their children
                            if (!empty($subjectsToDelete)) {
                                foreach ($subjectsToDelete as $subjectId) {
                                    // First delete children
                                    $assignIds = DB::table('exam_assigns')
                                        ->where('subject_id', $subjectId)
                                        ->where('classes_id', $selectedClass->classes_id)
                                        ->where('section_id', $section_id)
                                        ->where('exam_type_id', $selectedClass->exam_type_id)
                                        ->pluck('id');

                                    DB::table('exam_assign_childrens')
                                        ->whereIn('exam_assign_id', $assignIds)
                                        ->delete();

                                    // Then delete main exam_assign record(s)
                                    DB::table('exam_assigns')
                                        ->whereIn('id', $assignIds)
                                        ->delete();
                                }
                            }

                        foreach ($subjects as $subject) {
                            // Check if ExamAssign already exists
                            $examAssign = DB::table('exam_assigns')
                                ->where('subject_id', $subject->id)
                                ->where('classes_id', $selectedClass->classes_id)
                                ->where('section_id', $section_id)
                                ->where('exam_type_id', $selectedClass->exam_type_id)
                                ->first();

                            if ($examAssign) {
                                // ExamAssign exists - check if ExamAssignChildren exists
                                $childExists = DB::table('exam_assign_childrens')
                                    ->where('exam_assign_id', $examAssign->id)
                                    ->where('title', 'written') // Prevent duplicate "written"
                                    ->first();

                                if (!$childExists) {
                                    // Create new ExamAssignChildren
                                      $examAssignChild = new ExamAssignChildren();
                                        $examAssignChild->exam_assign_id = $examAssign->id;
                                        $examAssignChild->title = "written";
                                        $examAssignChild->mark = 100;
                                        $examAssignChild->save();
                                 
                                }

                            } else {
                                // Create new ExamAssign
                                $examAssignRow = new ExamAssign();
                                $examAssignRow->session_id = setting('session'); // Assumes setting() helper exists
                                $examAssignRow->classes_id = $selectedClass->classes_id;
                                $examAssignRow->section_id = $section_id;
                                $examAssignRow->exam_type_id = $selectedClass->exam_type_id;
                                $examAssignRow->subject_id = $subject->id;
                                $examAssignRow->total_mark = 100;
                                $examAssignRow->save();
                              

                                // Create corresponding ExamAssignChildren
                                 $examAssignChild = new ExamAssignChildren();
                                    $examAssignChild->exam_assign_id = $examAssignRow->id;
                                    $examAssignChild->title = "written";
                                    $examAssignChild->mark = 100;
                                    $examAssignChild->save();
                            }
                        }
                    }
                }

            DB::commit();
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            dd($th);
            DB::rollback();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $row = $this->model->find($id);

            foreach ($row->subjectTeacher as $key => $value) {
                ExamAssign::where('session_id',$row->session_id)
                    ->where('classes_id',$row->classes_id)
                    ->where('section_id',$row->section_id)
                    ->where('subject_id',$value->subject_id)
                    ->delete();

                MarksRegister::where('session_id',$row->session_id)
                    ->where('classes_id',$row->classes_id)
                    ->where('section_id',$row->section_id)
                    ->where('subject_id',$row->subject_id)
                    ->delete();
            }

            $row->delete();
            DB::commit();
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function checkSection($request)
    {
        if ($request->form_type == "update") {

            $result = $this->model->active()->where('id', '!=', $request->id)->where('session_id', setting('session'))->where('classes_id', $request->class)->where('section_id', $request->section)->first();
        } else {

            $result = $this->model->active()->where('session_id', setting('session'))->where('classes_id', $request->class)->where('section_id', $request->section)->first();
        }

        $data   = [];
        // if($result) {
        //     $data['message']   = ___('academic.already_assigned_for_this_section');
        //     $data['status']    = false;
        //     $data['sections']  = $this->classSetupRepo->getSections($request->class);
        // } else {
            $data['message']   = '';
            $data['status']    = true;
            $data['sections']  = true;
        // }

        return $data;
    }

    
    public function checkExamAssign($id){
        $row = $this->model->find($id);
        
        foreach ($row->subjectTeacher as $key => $value) {
            $result = ExamAssign::where('session_id',$row->session_id)
                ->where('classes_id',$row->classes_id)
                ->where('section_id',$row->section_id)
                ->where('subject_id',$value->subject_id)
                ->first();
                
            if($result)
                return true;
        }
        return false;
    }
}
