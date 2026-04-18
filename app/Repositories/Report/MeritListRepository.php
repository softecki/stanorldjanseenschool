<?php

namespace App\Repositories\Report;

use App\Models\ExaminationResult;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use App\Interfaces\Report\MeritListInterface;

class MeritListRepository implements MeritListInterface
{
    use ReturnFormatTrait;

    public function search($request)
    {
        $students = ExaminationResult::query();
        $students = $students->where('session_id', setting('session'));

        if($request->class != "") {
            $students = $students->where('classes_id', $request->class);
        }
        if($request->section != "") {
            $students = $students->where('section_id', $request->section);
        }        
        if($request->exam_type != "") {
            $students = $students->where('exam_type_id', $request->exam_type);
        }        

        
        $students  = $students->orderBy('position')->paginate(1000);
        return $students;
    }

    public function generateResultSheet($request)
        {

            try {
                // Step 1: Get ordered subject codes
                $subjectList = DB::table('subject_assigns')
                ->join('subject_assign_childrens', 'subject_assign_childrens.subject_assign_id', '=', 'subject_assigns.id')
                ->join('subjects', 'subjects.id', '=', 'subject_assign_childrens.subject_id')
                ->where('subject_assigns.classes_id', $request->class)
                ->where('subjects.status', 1)
                ->groupBy('subjects.id', 'subjects.name', 'subjects.code') // make sure to group properly
                ->orderBy('subjects.code') // or use 'subjects.name' or a custom order column
                ->pluck('subjects.code', 'subjects.name') // [ 'Numeracy' => 'NUM' ]
                ->toArray();

                // Step 2: Fetch student marks
                $examTypeId = $request->exam_type;
                $results = DB::table('examination_results as er')
                    ->join('students as s', 's.id', '=', 'er.student_id')
                    ->join('marks_registers as mr', function ($join) use ($examTypeId) {
                        $join->on('mr.exam_type_id', '=', DB::raw($examTypeId));
                    })
                    ->join('marks_register_childrens as mrc', function ($join) {
                        $join->on('mrc.marks_register_id', '=', 'mr.id')
                            ->on('mrc.student_id', '=', 'er.student_id');
                    })
                    ->join('subjects as sub', 'sub.id', '=', 'mr.subject_id')
                    ->where('er.classes_id', $request->class)
                    // ->where('er.section_id', $request->section)
                    ->where('er.exam_type_id', $examTypeId)
                    // ->where('er.session_id', $sessionId)
                    ->select(
                        's.id as student_id',
                        's.first_name',
                        's.last_name',
                        'sub.name as subject_name',
                        'sub.code as subject_code',
                        'mrc.mark',
                        'er.total_marks',
                        'er.grade_name',
                        'er.position'
                    )
                    ->get();

                // Step 3: Group and format
                $grouped = $results->groupBy('student_id');

                $formatted = $grouped->map(function ($items, $studentId) use ($subjectList) {
                    $student = $items->first();
                    $subjectMarks = [];

                    foreach ($items as $item) {
                        $code = $item->subject_code;
                        $subjectMarks[$code] = $item->mark;
                    }

                    // Reorder marks
                    $orderedSubjects = [];
                    foreach ($subjectList as $name => $code) {
                        $orderedSubjects[$code] = $subjectMarks[$code] ?? '-';
                    }

                    return [
                        'student_id'   => $studentId,
                        'name'         => $student->first_name . ' ' . $student->last_name,
                        'subjects'     => $orderedSubjects,
                        'total'        => $student->total_marks,
                        'average'      => round($student->total_marks / count($subjectList), 1),
                        'grade'        => $student->grade_name,
                        'position'     => $student->position,
                    ];
                })->sortBy('position')->values();

                return [
                    'results' => $formatted,
                    'subjects' => array_values($subjectList),
                ];

            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Failed to generate results',
                    'message' => $e->getMessage()
                ], 500);
            }
        }


    public function searchPDF($request)
    {
        $students = ExaminationResult::query();
        $students = $students->where('session_id', setting('session'));

        if($request->class != "") {
            $students = $students->where('classes_id', $request->class);
        }
        if($request->section != "") {
            $students = $students->where('section_id', $request->section);
        }        
        if($request->exam_type != "") {
            $students = $students->where('exam_type_id', $request->exam_type);
        }        
        
        $students  = $students->orderBy('position')->get();
        return $students;
    }
}
