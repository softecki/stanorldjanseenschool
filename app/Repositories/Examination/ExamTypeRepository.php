<?php

namespace App\Repositories\Examination;

use App\Interfaces\Examination\ExamTypeInterface;;

use App\Models\Examination\ExamAssign;
use App\Models\Examination\ExamAssignChildren;
use App\Models\Examination\ExamType;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamTypeRepository implements ExamTypeInterface
{
    use ReturnFormatTrait;

    private $model;

    public function __construct(ExamType $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->get();
    }

    public function getPaginateAll()
    {
        return $this->model::latest()->paginate(10);
    }

    public function store($request)
    {
        $selectedClasses = $request->input('classes', []);
    
        try {
            // Create the primary exam type record
            $row = new $this->model; // Assumes $this->model is set (e.g., ExamType::class)
            $row->name = $request->input('name');
            $row->status = $request->input('status');
            $row->save();
    
            $exam_type_id = $row->id;
    
            foreach ($selectedClasses as $class) {
                // Fetch sections for the class using Eloquent or optimized query
                $sections = DB::table('class_setup_childrens')
                    ->join('class_setups', 'class_setups.id', '=', 'class_setup_childrens.class_setup_id')
                    ->where('class_setups.classes_id', $class)
                    ->pluck('section_id');
    
                foreach ($sections as $section_id) {
                    // Fetch subjects for class and section
                    $subjects = DB::table('subjects')
                        ->join('subject_assign_childrens', 'subjects.id', '=', 'subject_assign_childrens.subject_id')
                        ->join('subject_assigns', 'subject_assigns.id', '=', 'subject_assign_childrens.subject_assign_id')
                        ->where('subject_assigns.classes_id', $class)
                        ->where('subject_assigns.section_id', $section_id)
                        ->select('subjects.id')
                        ->get();
    
                    foreach ($subjects as $subject) {
                        // Create ExamAssign record
                        $examAssignRow = new ExamAssign();
                        $examAssignRow->session_id = setting('session'); // Assumes setting() helper exists
                        $examAssignRow->classes_id = $class;
                        $examAssignRow->section_id = $section_id;
                        $examAssignRow->exam_type_id = $exam_type_id;
                        $examAssignRow->subject_id = $subject->id;
                        $examAssignRow->total_mark = 100;
                        $examAssignRow->save();
    
                        // Create ExamAssignChildren record
                        $examAssignChild = new ExamAssignChildren();
                        $examAssignChild->exam_assign_id = $examAssignRow->id;
                        $examAssignChild->title = "written";
                        $examAssignChild->mark = 100;
                        $examAssignChild->save();
                    }
                }
            }
    
            return $this->responseWithSuccess(__('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            Log::error("Error creating exam assignment: " . $th->getMessage());
            return $this->responseWithError(__('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function show($id)
    {
        return $this->model->find($id);
    }

    public function update($request, $id)
    {
        try {
            $row                = $this->model->findOrfail($id);
            $row->name          = $request->name;
            $row->status        = $request->status;
            $row->save();
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function destroy($id)
    {
        try {
            $row = $this->model->find($id);
            $row->delete();
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }
}
