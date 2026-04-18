<?php

namespace App\Http\Controllers\StudentInfo;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\GenderRepository;
use App\Repositories\ReligionRepository;
use App\Repositories\BloodGroupRepository;
use App\Repositories\Academic\ShiftRepository;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\SectionRepository;
use App\Repositories\StudentInfo\StudentRepository;
use App\Repositories\StudentInfo\ParentGuardianRepository;
use App\Http\Requests\StudentInfo\Student\StudentStoreRequest;
use App\Http\Requests\StudentInfo\Student\StudentUpdateRequest;
use App\Interfaces\StudentInfo\StudentCategoryInterface;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\Examination\ExamAssignRepository;
use App\Repositories\StudentInfo\StudentCategoryRepository;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Validator;
use App\Models\StudentInfo\Student;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class StudentController extends Controller
{
    private $repo;
    private $classRepo;
    private $sectionRepo;
    private $classSetupRepo;
    private $shiftRepo;
    private $bloodRepo;
    private $religionRepo;
    private $genderRepo;
    private $categoryRepo;
    private $examAssignRepo;

    function __construct(
        StudentRepository $repo,
        ClassesRepository $classRepo,
        SectionRepository $sectionRepo,
        ClassSetupRepository $classSetupRepo,
        ShiftRepository   $shiftRepo,
        BloodGroupRepository         $bloodRepo,
        ReligionRepository           $religionRepo,
        GenderRepository             $genderRepo,
        StudentCategoryRepository    $categoryRepo,
        ExamAssignRepository         $examAssignRepo,
        )
    {
        $this->repo         = $repo;
        $this->classRepo    = $classRepo;
        $this->sectionRepo  = $sectionRepo;
        $this->classSetupRepo  = $classSetupRepo;
        $this->shiftRepo    = $shiftRepo;
        $this->bloodRepo    = $bloodRepo;
        $this->religionRepo = $religionRepo;
        $this->genderRepo   = $genderRepo;
        $this->categoryRepo = $categoryRepo;
        $this->examAssignRepo = $examAssignRepo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['classes']  = $this->classRepo->assignedAll();
        $data['sections'] = [];
        $data['title']    = ___('student_info.student_list');
         $data['students'] = $this->repo->getPaginateAllFormOne();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['students'],
                'meta' => [
                    'title' => $data['title'],
                    'classes' => $data['classes'],
                    'sections' => $data['sections'],
                ],
            ]);
        }

        return view('backend.student-info.student.index', compact('data'));
    }

    public function formtwo()
    {
        $data['classes']  = $this->classRepo->assignedAll();
        $data['sections'] = [];
        $data['title']    = ___('student_info.student_list');
         $data['students'] = $this->repo->getPaginateAllFormTwo();

        return view('backend.student-info.student.formtwo', compact('data'));
    }

    
    public function search(Request $request)
    {
        $data['classes']  = $this->classRepo->assignedAll();
        $data['sections'] = $this->classSetupRepo->getSections($request->class);
        $data['request']  = $request;
        $data['title']    = ___('student_info.student_list');
        $data['students'] = $this->repo->searchStudents($request);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['students'],
                'meta' => [
                    'title' => $data['title'],
                    'classes' => $data['classes'],
                    'sections' => $data['sections'],
                ],
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return view('backend.student-info.student.partials.table', compact('data'))->render();
        }

        return view('backend.student-info.student.index', compact('data'));
    }

    /**
     * Live search by student name (CONCAT first_name, last_name) LIKE query.
     * Returns JSON for JavaScript autocomplete.
     */
    public function searchByName(Request $request)
    {
        $q = $request->get('q', '');
        $students = $this->repo->searchStudentsByName($q, 25);
        return response()->json($students);
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']     = ___('student_info.student_create');
        $data['classes']   = $this->classRepo->assignedAll();
        $data['sections']  = [];
        $data['shifts']    = $this->shiftRepo->all();

        $data['bloods']       = $this->bloodRepo->all();
        $data['religions']    = $this->religionRepo->all();
        $data['genders']      = $this->genderRepo->all();
        $data['categories']   = $this->categoryRepo->all();

        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(url('/app/students/create'));
    }


    public function upload()
    {
        $data['title']     = ___('student_info.student_create');
        $data['classes']   = $this->classRepo->assignedAll();
        $data['sections']  = [];
        $data['shifts']    = $this->shiftRepo->all();

        $data['bloods']       = $this->bloodRepo->all();
        $data['religions']    = $this->religionRepo->all();
        $data['genders']      = $this->genderRepo->all();
        $data['categories']   = $this->categoryRepo->all();

        return view('backend.student-info.student.upload', compact('data'));
    }

    public function downloadTemplate()
    {
        return Excel::download(new \App\Exports\StudentTemplateExport(), 'student_upload_format.xlsx');
    }

    public function uploadOutstandingFees()
    {
        $data['title'] = 'Upload Outstanding Fees';
        return view('backend.student-info.student.upload-outstanding-fees', compact('data'));
    }

    public function uploadOutstandingFeesStore(Request $request)
    {
        $result = $this->repo->uploadOutstandingFees($request);
        if($result['status']){
            return redirect()->back()->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function updatefees(){
        $data['title']     = 'Update Fees';
        $data['classes']   = $this->classRepo->assignedAll();
        $data['sections']  = [];
        $data['shifts']    = $this->shiftRepo->all();

        $data['bloods']       = $this->bloodRepo->all();
        $data['religions']    = $this->religionRepo->all();
        $data['genders']      = $this->genderRepo->all();
        $data['categories']   = $this->categoryRepo->all();

        return view('backend.student-info.student.updatefees', compact('data'));
    }

    public function addNewDocument(Request $request)
    {
        $counter = $request->counter;
        return view('backend.student-info.student.add-document', compact('counter'))->render();
    }
    public function getStudents(Request $request)
    {
        $examAssign = $this->examAssignRepo->getExamAssign($request);
        // dd($examAssign->mark_distribution);
        $students = $this->repo->getStudents($request);
        return view('backend.student-info.student.students-list', compact('students','examAssign'))->render();
    }



    public function store(StudentStoreRequest $request): JsonResponse|RedirectResponse
    {

        $existing = Student::where('first_name', $request->first_name)
        ->where('last_name', $request->last_name)
        ->where('student_category_id', $request->category)
        ->first();

        if ($existing) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Student already exists.'], 422);
            }
            return back()->with('danger', 'Student already exists.');
        }
        $result = $this->repo->store($request);

        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('student.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }


    public function uploadStudentsDetails(Request $request)
    {
       

        $result = $this->repo->upload($request);

        if($result['status']){
            return redirect()->route('student.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }

    public function updateStudentFees(Request $request){

        $validator = Validator::make($request->all(), [
            'document_files' => 'required|mimes:xlsx,xls,csv|max:2048',
            'document_type'  => 'required|in:1,2,3',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput()
                             ->with('danger', 'Please correct the highlighted errors and try again.');
        }
        $result = $this->repo->updateStudentFees($request);

        if($result['status']){
            return redirect()->route('fees-collect.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        try {

            $data['title']     = ___('student_info.student_edit');
            $data['session_class_student'] = $this->repo->getSessionStudentWithId($id);
            $data['student']   = $this->repo->show($data['session_class_student']->student_id);
            $data['classes']   = $this->classRepo->assignedAll();
            $data['sections']  = $this->classSetupRepo->getSections($data['session_class_student']->classes_id);
            $data['shifts']    = $this->shiftRepo->all();
            $data['bloods']       = $this->bloodRepo->all();
            $data['religions']    = $this->religionRepo->all();
            $data['genders']      = $this->genderRepo->all();
            $data['categories']   = $this->categoryRepo->all();
            if ($request->expectsJson()) {
                return response()->json([
                    'data' => $data['student'],
                    'meta' => [
                        'title' => $data['title'],
                        'session_class_student' => $data['session_class_student'],
                        'classes' => $data['classes'],
                        'sections' => $data['sections'],
                        'shifts' => $data['shifts'],
                        'bloods' => $data['bloods'],
                        'religions' => $data['religions'],
                        'genders' => $data['genders'],
                        'categories' => $data['categories'],
                    ],
                ]);
            }

            return redirect()->to(url('/app/students/'.$id.'/edit'));
        }catch (\Exception $e){
            dd($e);
        }

    }


    public function show(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data = $this->repo->show($id);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data]);
        }

        return redirect()->to(url('/app/students/'.$id));
    }

    public function qrCode($id)
    {
        $data['title'] = 'QR Code';
        $student = Student::findOrFail($id);
        
        if ($student && $student->control_number) {
            $data['control_number'] = $student->control_number;
            $data['student_name'] = $student->full_name ?? ($student->first_name . ' ' . $student->last_name);
            $data['student_id'] = $student->id;
            // Generate QR code HTML in controller
            $data['qr_code_html'] = QrCode::size(300)->generate($student->control_number);
        } else {
            $data['control_number'] = null;
            $data['student_name'] = null;
            $data['student_id'] = $student->id;
            $data['qr_code_html'] = null;
        }
        
        return view('backend.student-info.student.qr-code', compact('data'));
    }

    public function update(StudentUpdateRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $request->id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('student.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {

        $result = $this->repo->destroy($id);
        if($result['status']):
            $success[0] = $result['message'];
            $success[1] = 'success';
            $success[2] = ___('alert.deleted');
            $success[3] = ___('alert.OK');
            return response()->json($success);
        else:
            $success[0] = $result['message'];
            $success[1] = 'error';
            $success[2] = ___('alert.oops');
            return response()->json($success);
        endif;
    }

    /**
     * Delete student after copying to history tables (student + fees assign + fees collect).
     */
    public function deleteWithHistory($id)
    {
        $result = $this->repo->destroyWithHistory($id);
        if ($result['status']) {
            return redirect()->route('student_deleted_history.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }

    public function exportStudents()
    {
        // Define the database fields and their custom column headings
        $columns = [
            'students.admission_no' => 'admission_no',
            'students.first_name' => 'student_first_name',
            'students.last_name' => 'student_last_name',
            'parent_guardians.guardian_name' => 'parent_name',
            'mobile' => 'phone_number',
            'classes.name as class_name' => 'class_name', // Alias for class name
            'genders.name as gender' => 'gender',
            'religions.name as religion' => 'religion',
            'sections.name as section' => 'section',
            'dob' => 'date_of_birth',
            'guardian_email' => 'email',
            'balance'=>'balance',
        ];

        $data = DB::table('students')
            ->join('parent_guardians', 'students.parent_guardian_id', '=', 'parent_guardians.id')
            ->join('session_class_students', 'students.id', '=', 'session_class_students.student_id')
            ->join('classes', 'session_class_students.classes_id', '=', 'classes.id')
            ->join('sections', 'session_class_students.section_id', '=', 'sections.id')
            ->join('genders', 'students.gender_id', '=', 'genders.id')
            ->join('religions', 'students.religion_id', '=', 'religions.id')
            ->select([
                'students.admission_no',
                'students.first_name',
                'students.last_name',
                'guardian_name',
                'mobile',
                'classes.name as class_name',  // Alias for clarity
                'genders.name as gender',
                'religions.name as religion',
                'sections.name as section',
                'dob',
                'guardian_email',
            ])
            ->get()
            ->toArray();

        $export = new class($data, $columns) implements FromArray, WithHeadings, WithEvents {
            protected $data;
            protected $columns;

            public function __construct(array $data, array $columns)
            {
                $this->data = $data;
                $this->columns = $columns;
            }

            public function array(): array
            {
                // Convert objects to arrays and only include specified columns
                return array_map(function ($item) {
                    return (array)$item;
                }, $this->data);
            }

            public function headings(): array
            {
                // Return the custom column headings
                return array_values($this->columns);
            }

            public function registerEvents(): array
            {
                // Apply sheet protection before export
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->getSheet(); // Accessing the sheet

                        // Protect the entire sheet
                        $sheet->getProtection()->setSheet(true); // Protect the whole sheet

                        // Lock the first row (header row)
                        $sheet->getStyle('A1:Z1')->getProtection()->setLocked(true); // Protect first row

                        // Unlock the rest of the rows (from row 2 to 1000)
                        $sheet->getStyle('A2:Z1000')->getProtection()->setLocked(false);

                        // Optionally set a password for sheet protection (you can skip this line if no password is needed)
                        $sheet->getProtection()->setPassword('yourpassword');
                    },
                ];
            }
        };

        return Excel::download($export, 'Students.xlsx');
    }

    public function exportExams(Request $request)
    {
        // Define the database fields and their custom column headings

        $class = $request->query('class');
        $section = $request->query('section');
        $columns = [
            'students.roll_no' => 'reg_number',
            'full_name' => 'student_name',
            'marks' => 'marks',
        ];

        $data = DB::table('students')
            ->join('parent_guardians', 'students.parent_guardian_id', '=', 'parent_guardians.id')
            ->join('session_class_students', 'students.id', '=', 'session_class_students.student_id')
            ->join('classes', 'session_class_students.classes_id', '=', 'classes.id')
            ->join('sections', 'session_class_students.section_id', '=', 'sections.id')
            ->where('session_class_students.classes_id', $class)
            ->where('session_class_students.section_id', $section)
            ->select([
                'students.roll_no',
                DB::raw("CONCAT(students.first_name, ' ', students.last_name) as full_name"),
            ])
            ->get()
            ->toArray();

        $export = new class($data, $columns) implements FromArray, WithHeadings, WithEvents {
            protected $data;
            protected $columns;

            public function __construct(array $data, array $columns)
            {
                $this->data = $data;
                $this->columns = $columns;
            }

            public function array(): array
            {
                // Convert objects to arrays and only include specified columns
                return array_map(function ($item) {
                    return (array)$item;
                }, $this->data);
            }

            public function headings(): array
            {
                // Return the custom column headings
                return array_values($this->columns);
            }

            public function registerEvents(): array
            {
                // Apply sheet protection before export
                return [
                    AfterSheet::class => function (AfterSheet $event) {
//                        $sheet = $event->getSheet(); // Accessing the sheet

//                        // Protect the entire sheet
//                        $sheet->getProtection()->setSheet(true); // Protect the whole sheet
//
//                        // Lock the first row (header row)
//                        $sheet->getStyle('A1:Z1')->getProtection()->setLocked(true); // Protect first row
//
//                        // Unlock the rest of the rows (from row 2 to 1000)
//                        $sheet->getStyle('A2:Z1000')->getProtection()->setLocked(false);
//
//                        // Optionally set a password for sheet protection (you can skip this line if no password is needed)
//                        $sheet->getProtection()->setPassword('yourpassword');
                    },
                ];
            }
        };

        return Excel::download($export, 'ExamsFomart.xlsx');
    }
}
