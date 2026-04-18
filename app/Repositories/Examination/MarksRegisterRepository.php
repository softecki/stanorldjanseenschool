<?php

namespace App\Repositories\Examination;

use App\Imports\StudentsImport;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use App\Models\Examination\MarksRegister;
use App\Models\Examination\MarksRegisterChildren;
use App\Interfaces\Examination\MarksRegisterInterface;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

;

class MarksRegisterRepository implements MarksRegisterInterface
{
    use ReturnFormatTrait;

    private $model;

    public function __construct(MarksRegister $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->where('session_id', setting('session'))->get();

    }

    public function allForApp()
    {
        return MarksRegisterChildren::get();
    }

    public function getPaginateAll()
    {
        return $this->model::latest()->where('session_id', setting('session'))->whereIn('subject_id', teacherSubjects())->paginate(10);
    }

    public function searchMarkRegister($request)
    {
        $rows = $this->model::query();
        $rows = $rows->where('session_id', setting('session'));
        if($request->class != "") {
            $rows = $rows->where('classes_id', $request->class);
        }
        if($request->section != "") {
            $rows = $rows->where('section_id', $request->section);
        }
        if($request->exam_type != "") {
            $rows = $rows->where('exam_type_id', $request->exam_type);
        }
        if($request->subject != "") {
            $rows = $rows->where('subject_id', $request->subject);
        }
        return $rows->paginate(10);
    }

    public function computeGeneralResult(){

        DB::beginTransaction();
        try {
        $examAssigns = DB::table('exam_assigns')
        ->join('exam_types', 'exam_types.id', '=', 'exam_assigns.exam_type_id')
        ->where('exam_types.type', 1)
        ->select('exam_assigns.*', 'exam_types.type') // You can customize the selected columns
        ->get();
        foreach($examAssigns as $exam_assign){
            $sections = DB::select(
                'SELECT section_id FROM class_setup_childrens 
                INNER JOIN class_setups ON class_setups.id = class_setup_childrens.class_setup_id 
                WHERE class_setups.classes_id = ?', [$exam_assign->classes_id]
            );
            foreach($sections as $section) {
                
                $existingMarkRegister = $this->model::where('session_id', setting('session'))
                ->where('classes_id', $exam_assign->classes_id)
                ->where('section_id', $section->section_id) // Adjust if you're using a fixed value like "1"
                ->where('exam_type_id', $exam_assign->exam_type_id)
                ->where('subject_id', $exam_assign->subject_id)
                ->first();

            if ($existingMarkRegister) {
                // Record exists - use its ID
                $markregister_id = $existingMarkRegister->id;
            } else {
                // Create new record
                $markregister = new $this->model;
                $markregister->session_id = setting('session');
                $markregister->classes_id = $exam_assign->classes_id;
                $markregister->section_id = $section->section_id; // Or "1" for nalopa
                $markregister->exam_type_id = $exam_assign->exam_type_id;
                $markregister->subject_id = $exam_assign->subject_id;
                $markregister->save();

                $markregister_id = $markregister->id;
            }           

                 $students = DB::select(
                'SELECT students.id FROM students 
                INNER JOIN session_class_students ON session_class_students.student_id = students.id 
                WHERE session_class_students.classes_id = ? AND session_class_students.section_id = ?',
                 [$exam_assign->classes_id,$section->section_id]
            );
                    foreach($students as $student){
                        $rowChild = MarksRegisterChildren::where('marks_register_id', $markregister_id)
                            ->where('student_id', $student->id)
                            ->where('title', 'written')
                            ->first();

                        $mark = $this->getTerminalMarks($student->id, $exam_assign->subject_id);

                        if ($rowChild) {
                            // Update existing record
                            $rowChild->mark = $mark;
                            $rowChild->save();
                        } else {
                            // Create new record
                            $rowChild = new MarksRegisterChildren();
                            $rowChild->marks_register_id = $markregister_id;
                            $rowChild->student_id = $student->id;
                            $rowChild->title = "written";
                            $rowChild->mark = $mark;
                            $rowChild->save();
                        }
                    }
                
            }
        }
          DB::commit();
        return $this->responseWithSuccess(___('alert.created_successfully'), []);
         } catch (\Throwable $th) {
            dd($th);
            DB::rollBack();
            Log::error('An error occurred: ' . $th);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);

        }
    }

   public function getTerminalMarks($student_id, $subject_id)
    {
        $April = 1;
        $May = 2;

        $resultApril = DB::select(
            "SELECT mark FROM marks_register_childrens 
            INNER JOIN marks_registers ON marks_registers.id = marks_register_childrens.marks_register_id
            WHERE marks_register_childrens.student_id = ? 
            AND marks_registers.subject_id = ? 
            AND marks_registers.exam_type_id = ?",
            [$student_id, $subject_id, $April]
        );

        $resultMay = DB::select(
            "SELECT mark FROM marks_register_childrens 
            INNER JOIN marks_registers ON marks_registers.id = marks_register_childrens.marks_register_id
            WHERE marks_register_childrens.student_id = ? 
            AND marks_registers.subject_id = ? 
            AND marks_registers.exam_type_id = ?",
            [$student_id, $subject_id, $May]
        );

        $aprilMark = isset($resultApril[0]) ? $resultApril[0]->mark : 0;
        $mayMark = isset($resultMay[0]) ? $resultMay[0]->mark : 0;

        $terminalResults = ($aprilMark + $mayMark) / 2;

        return $terminalResults;
    }


    public function store($request)
    {


        DB::beginTransaction();
        try {

            $request->validate([
                'document_files' => 'required|mimes:xlsx,xls,csv',
            ]);

            $sections = DB::select(
                'SELECT section_id FROM class_setup_childrens 
                INNER JOIN class_setups ON class_setups.id = class_setup_childrens.class_setup_id 
                WHERE class_setups.classes_id = ?', [$request->class]
            );

            foreach($sections as $section) {
                $markregister = new $this->model;
                $markregister->session_id = setting('session');
                $markregister->classes_id = $request->class;
                //this for st anorld school
                $markregister->section_id = $section->section_id;
                //this for nalopa schools
                // $markregister->section_id = "1";
                $markregister->exam_type_id = $request->exam_type;
                $markregister->subject_id = $request->subject;
                $markregister->save();
                $markregister_id = $markregister->id;
            }
            DB::commit();

            DB::beginTransaction();
            $data = Excel::toArray(new StudentsImport, $request->file('document_files'));

            foreach ($data[0] as $row) {
                        $student_id = DB::select('select id from students where roll_no = ?', [$row['reg_number']])[0]->id;
                        $rowChild = new MarksRegisterChildren();
                        $rowChild->marks_register_id = $markregister_id;
                        $rowChild->student_id = $student_id;
                        $rowChild->title = "written";
                        $rowChild->mark = $row['marks'];
                        $rowChild->save();

            }
            DB::commit();

            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            dd($th);
            DB::rollBack();
            Log::error('An error occurred: ' . $th);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);

        }
    }

    public function storeForApp($request)
    {
       

        DB::beginTransaction();
        try {

           
            $sections = DB::select(
                'SELECT section_id FROM class_setup_childrens 
                INNER JOIN class_setups ON class_setups.id = class_setup_childrens.class_setup_id 
                WHERE class_setups.classes_id = ?', [$request->class]
            );
          
            foreach($sections as $section) {
               
                $marksRegisterId = DB::Select("select id from marks_registers 
                where classes_id = ? and section_id = ? and exam_type_id = ? 
                and subject_id = ?",[$request->class,$section->section_id,
                $request->exam_type,$request->subject]);

                if(empty($marksRegisterId)){
                    
                $markregister = new $this->model;
                $markregister->session_id = setting('session');
                $markregister->classes_id = $request->class;
                //this for st anorld school
                $markregister->section_id = $section->section_id;
                //this for nalopa schools
                // $markregister->section_id = "1";
                $markregister->exam_type_id = $request->exam_type;
                $markregister->subject_id = $request->subject;
              
                $markregister->save();
                }
               
              
            }
            
            DB::commit();

            DB::beginTransaction();
            $data = $request->submittedMarks;
           
            foreach ($data as $row) {
               
                $student_id = $row['id'];
            
                        $markRegisterId = $this->getMarksRegisterId($student_id, $request->exam_type,$request->subject);

                        $marksRegisterChildrenId = DB::Select("select id from marks_register_childrens 
                        where marks_register_id = ? and student_id = ? ",[$markRegisterId,$row['id']]);

                        if(empty($marksRegisterChildrenId)){
                        if(!empty($markRegisterId)){
                        $rowChild = new MarksRegisterChildren();
                        $rowChild->marks_register_id = $markRegisterId;
                        $rowChild->student_id =  $row['id'];
                        $rowChild->title = "written";
                        $rowChild->mark = $row['marks'];
                        $rowChild->save();
                   }}

            }

            DB::commit();

            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
           Log::error($th);
            Log::error('An error occurred: ' . $th);
            DB::rollBack();
            Log::error('An error occurred: ' . $th);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), [],201);

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
           
            if($this->model::where('session_id', setting('session'))->where('classes_id', $request->class)->where('section_id', $request->section)->where('exam_type_id', $request->exam_type)->where('subject_id', $request->subject)->where('id', '!=', $id)->first()) {
                return $this->responseWithError(___('alert.There is already a register for this session.'), []);
            }

            $row                   = $this->model->find($id);
            $row->session_id       = setting('session');
            $row->classes_id         = $request->class;
            $row->section_id       = $request->section;
            $row->exam_type_id     = $request->exam_type;
            $row->subject_id       = $request->subject;
            $row->save();

            MarksRegisterChildren::where('marks_register_id', $row->id)->delete();

            foreach ($request->student_ids as $id) {
                foreach ($request->marks[$id] as $key => $mark) {
                    $rowChild                     = new MarksRegisterChildren();
                    $rowChild->marks_register_id  = $row->id;
                    $rowChild->student_id         = $id;
                    $rowChild->title              = $key;
                    $rowChild->mark               = $mark;
                    $rowChild->save();
                }
            }
            
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
            MarksRegisterChildren::where('marks_register_id', $row->id)->delete();
            $row->delete();
            DB::commit();
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    private function getMarksRegisterId($student_id,$exam_type,$subject_id)
    {
        $class_id =  DB::select('SELECT classes_id from session_class_students where student_id = ? ',[$student_id])[0]->classes_id;
        $section_id =  DB::select('SELECT section_id from session_class_students where student_id = ? ',[$student_id])[0]->section_id;
        
        $guardian_id = DB::select('SELECT id from marks_registers where classes_id = ? AND section_id = ? AND exam_type_id = ? and subject_id = ?',
            [$class_id,$section_id,$exam_type,$subject_id]);
        if (!empty($guardian_id)) {
            return $guardian_id[0]->id;
        }else{
            return "";
        }
    }
}
