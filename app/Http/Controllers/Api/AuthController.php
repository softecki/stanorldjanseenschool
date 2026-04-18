<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Mail\ForgotPassword;
use Illuminate\Http\Request;
use App\Traits\ReturnFormatTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ParentPanel\MarksheetController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\Fees\FeesAssignChildren;
use App\Models\StudentInfo\Student;
use Illuminate\Support\Facades\DB;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\SubjectRepository;
use App\Repositories\Academic\SectionRepository;
use App\Repositories\Examination\MarksRegisterRepository;
use App\Repositories\Report\MarksheetRepository;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\StudentInfo\StudentRepository;
use App\Repositories\Examination\ExamTypeRepository;
use Illuminate\Support\Facades\Log;
use App\Interfaces\Fees\FeesMasterInterface;
use App\Interfaces\Fees\FeesTypeInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use App\Models\StudentInfo\ParentGuardian;
use Illuminate\Support\Facades\Http;
class AuthController extends Controller
{
    use ReturnFormatTrait;

    protected $marksheetController;
    private $classRepo; 
    private $subjectRepo;
    private $sectionRepo;
    private $marksrepo;
    
    private $repo;
    private $classSetupRepo;
    private $studentRepo;
    private $feesMasterRepo;
    private $Feestype;
    private $examTypeRepo;

    public function __construct(MarksheetController $marksheetController,
                        ClassesRepository $classRepo,
                        ExamTypeRepository $examTypeRepo,
                        SubjectRepository $subjectRepo,
                        SectionRepository $sectionRepo,
                        MarksRegisterRepository $marksrepo,
                        MarksheetRepository    $repo,
        ClassSetupRepository   $classSetupRepo,
        StudentRepository      $studentRepo,
        FeesMasterInterface $feesMasterRepo,FeesTypeInterface $Feestype)
    {
        $this->marksheetController = $marksheetController;
        $this->classRepo = $classRepo;
        $this->subjectRepo        = $subjectRepo;
        $this->sectionRepo        = $sectionRepo;
        $this->marksrepo        = $marksrepo;
        $this->repo               = $repo;
        $this->classSetupRepo     = $classSetupRepo;
        $this->studentRepo        = $studentRepo;
        $this->feesMasterRepo     = $feesMasterRepo;
        $this->Feestype        = $Feestype;
        $this->examTypeRepo        = $examTypeRepo;
    }

    public function splash(){
         $examTypes = $this->examTypeRepo->all();
        $classes = $this->classRepo->all();
        $subjects = $this->subjectRepo->all();
        $sections = $this->sectionRepo->all();
        $marks_registers = $this->marksrepo->all();
        $marks_registerschildren = $this->marksrepo->allForApp();
        $fees_masters = $this->feesMasterRepo->all();
        $Feestype = $this->Feestype->all();
        $feesGroup = DB::select("SELECT * FROM fees_groups");

        $bank_accounts = DB::select("SELECT * FROM bank_accounts");
        // $subject_assign_childrens = DB::select("SELECT * FROM subject_assign_childrens");
        // $subject_assigns = DB::select("SELECT * FROM subject_assigns");
        $grades = DB::select("SELECT * FROM marks_grades");
        // $studentsList = DB::select("SELECT * FROM students");
        $notice = DB::select("
        SELECT 
            id,
            title,
            date,
            publish_date,
            REPLACE(REPLACE(description, '<p>', ''), '</p>', '') AS description,
            attachment,
            is_visible_web,
            status,
            visible_to,
            created_at,
            updated_at
        FROM notice_boards
    ");

    return $this->responseWithSuccess(___('alert.login_successfully'), [
            'token_type'    => 'Bearer',
            // 'student_list'       => $studentsList,
            'examTypes'     => $examTypes,
            'grades'     => $grades,
            'classes'       => $classes,
            'subjects'      => $subjects,
            'sections'      => $sections,
            'fees_group'      => $feesGroup,
            'marks_registers' => $marks_registers,
            'marks_register_childrens' => $marks_registerschildren,
            'fees_masters'  => $fees_masters,
            'fees_type'     => $Feestype,
            'bank_accounts' => $bank_accounts,
            // 'subject_assign_childrens' => $subject_assign_childrens,
            // 'subject_assigns' => $subject_assigns,
            'notice' => $notice,
        ]);

    }

    public function register(Request $request)
    {
        try {
            $data = Validator::make($request->all(),[
                'phone'      => 'required',
            ]);

            if ($data->fails()) {
                return $this->responseWithError(___('alert.validation_error'), $data->errors());
            }

            // $phone = $request->phone;

            $phone = trim($request->phone); // Clean up any whitespace

            if (strpos($phone, '0') === 0) {
                // Starts with 0 → remove it and prepend +255
                $phone = '+255' . substr($phone, 1);
            } elseif (strpos($phone, '+255') === 0) {
                // Already properly formatted → do nothing
                // Optional: normalize spacing or characters here
            } elseif (strpos($phone, '255') === 0) {
                // Starts with 255 without + → add the plus
                $phone = '+' . $phone;
            } else {
                // Doesn't start with 0, 255, or +255 → assume it's missing the code
                $phone = '+255' . $phone;
            }

                // Search for the user with the formatted phone number
                $user = User::where('phone', $phone)->get()->first();
                
                if(!$user){
                    $user_id = ParentGuardian::where('father_mobile', $phone)->value('user_id');
                    $user = User::where('id', $user_id)->first();
                }

                if(!$user){
                    $user_id = ParentGuardian::where('mother_mobile', $phone)->value('user_id');
                    $user = User::where('id', $user_id)->first();
                }

            $result = [];
            if($user){
                
                $otp = rand(1000, 9999);
                $uniqueOtp = $otp . str_pad($user->id, 3, '0', STR_PAD_LEFT);
                Log::info($uniqueOtp);
                // Step 2: Update OTP in DB
                $user->otp = $uniqueOtp;
                $user->save();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://messaging-service.co.tz/api/sms/v1/text/single');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
         
            // Updated data payload for multiple messages
            $data = [
                'from' => 'SCHOOL',
                'to' =>  $phone,
                'text' => "Dear {$user->name},\n\nYour login credential for the application is:\n\nOTP: {$uniqueOtp}\n\nThank you!",
                'reference' => 'softeckigroup'
            ];

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Encode the data as JSON
            $headers = [
                'Authorization: Basic ZmlsYmVydG46RXVzYWJpdXMxNzEwLg==', // Replace with your actual auth header
                'Content-Type: application/json',
                'Accept: application/json'
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
        }

            return $this->responseWithSuccess(___('alert.registered_successfully'), [
                // 'access_token'  => $token,
                'token_type'    => 'Bearer',
                'user' => $user,
                'result' => $result
            ]);

        } catch (\Throwable $th) {
            Log::error($th);
            return $this->responseWithError(___('alert.something_went_wrong'), $th->getMessage());
        }
    }


    

    public function login(Request $request)
{
    try {
        $data = Validator::make($request->all(), [
            'email'     => 'required',
        ]);
        if ($data->fails()) {
             return response()->json([
        'status'  => false,
        'message' => ___('alert.validation_error'),
        'data'    => $data->errors(),
    ], 404);
        }
          $phone = trim($request->email); // Clean up any whitespace

            if (strpos($phone, '0') === 0) {
                // Starts with 0 → remove it and prepend +255
                $phone = '+255' . substr($phone, 1);
            } elseif (strpos($phone, '+255') === 0) {
                // Already properly formatted → do nothing
                // Optional: normalize spacing or characters here
            } elseif (strpos($phone, '255') === 0) {
                // Starts with 255 without + → add the plus
                $phone = '+' . $phone;
            } else {
                // Doesn't start with 0, 255, or +255 → assume it's missing the code
                $phone = '+255' . $phone;
            }

        $user = User::where('phone', $phone)
            ->select('id', 'name', 'email', 'phone', 'status', 'role_id', 'upload_id', 'password')
            ->first();

        if (!$user) {
            return response()->json([
        'status'  => false,
        'message' => 'User not found',
        'data'    => [],
    ], 404);
        }

        $students = Student::query()
            ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
            ->join('classes', 'session_class_students.classes_id', '=', 'classes.id')
            ->join('sections', 'session_class_students.section_id', '=', 'sections.id')
            ->where('students.user_id', $user->id)
            ->select('students.*', 'classes.name as class_name', 'sections.name as section_name')
            ->get();

        $parents = ParentGuardian::query()
            ->where('parent_guardians.user_id', $user->id)
            ->select('parent_guardians.*')
            ->get();

        $fees = FeesAssignChildren::query()
        ->join('students', 'fees_assign_childrens.student_id', '=', 'students.id')
        ->where('students.user_id', $user->id)
        ->select('fees_assign_childrens.*')
        ->get();

       

        $trans = DB::select("SELECT fees_collects.* FROM fees_collects
                 INNER JOIN students ON students.id = fees_collects.student_id 
                 WHERE students.user_id = ?", [$user->id]);
            if ($students->isEmpty()) {
    $user_id = ParentGuardian::where('father_mobile', $user->phone)->value('user_id');

    if (!$user_id) {
        $user_id = ParentGuardian::where('mother_mobile', $user->phone)->value('user_id');
    }

    if ($user_id) {
        $parentUser = User::find($user_id);

        if ($parentUser) {
            $students = Student::query()
                ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
                ->join('classes', 'session_class_students.classes_id', '=', 'classes.id')
                ->join('sections', 'session_class_students.section_id', '=', 'sections.id')
                ->where('students.user_id', $parentUser->id)
                ->select('students.*', 'classes.name as class_name', 'sections.name as section_name')
                ->get();

            $parents = ParentGuardian::query()
                ->where('user_id', $parentUser->id)
                ->select('parent_guardians.*')
                ->get();

            $fees = FeesAssignChildren::query()
                ->join('students', 'fees_assign_childrens.student_id', '=', 'students.id')
                ->where('students.user_id', $parentUser->id)
                ->select('fees_assign_childrens.*')
                ->get();

            $trans = DB::select("SELECT fees_collects.* FROM fees_collects
                 INNER JOIN students ON students.id = fees_collects.student_id 
                 WHERE students.user_id = ?", [$parentUser->id]);
        }
    }
}
    
         $staff = DB::select("SELECT id, user_id, staff_id, role_id, designation_id,
            department_id, first_name, last_name, email, gender_id, status, created_at,
            updated_at FROM staff WHERE user_id = ?", [$user->id]);

     

        $user['avatar'] = @globalAsset($user->upload->path, '40X40.webp');

        return $this->responseWithSuccess(___('alert.login_successfully'), [
            'token_type'    => 'Bearer',
            'user'          => $user,
            'student'       => $students,
            'parent'       => $parents,
            'feesAssignChildren' => $fees,
            'transactions'  => $trans,
         
            'staff'         => $staff ?? [],
        ]);
    } catch (\Throwable $th) {
         return response()->json([
        'status'  => false,
        'message' => ___('alert.something_went_wrong'),
        'data'    =>  $th->getMessage(),
    ], 404);
    }
}

  public function balance_update(Request $request)
{
    try {
        $data = Validator::make($request->all(), [
            'email'     => 'required',
        ]);
        if ($data->fails()) {
            return $this->responseWithError(___('alert.validation_error'), $data->errors());
        }
          $phone = trim($request->email); // Clean up any whitespace

            if (strpos($phone, '0') === 0) {
                // Starts with 0 → remove it and prepend +255
                $phone = '+255' . substr($phone, 1);
            } elseif (strpos($phone, '+255') === 0) {
                // Already properly formatted → do nothing
                // Optional: normalize spacing or characters here
            } elseif (strpos($phone, '255') === 0) {
                // Starts with 255 without + → add the plus
                $phone = '+' . $phone;
            } else {
                // Doesn't start with 0, 255, or +255 → assume it's missing the code
                $phone = '+255' . $phone;
            }

        $user = User::where('phone', $phone)
            ->select('id', 'name', 'email', 'phone', 'status', 'role_id', 'upload_id', 'password')
            ->first();

        if (!$user) {
            return $this->responseWithError('User not found.');
        }

        $students = Student::query()
            ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
            ->join('classes', 'session_class_students.classes_id', '=', 'classes.id')
            ->join('sections', 'session_class_students.section_id', '=', 'sections.id')
            ->where('students.user_id', $user->id)
            ->select('students.*', 'classes.name as class_name', 'sections.name as section_name')
            ->get();

        $parents = ParentGuardian::query()
            ->where('parent_guardians.user_id', $user->id)
            ->select('parent_guardians.*')
            ->get();

        $fees = FeesAssignChildren::query()
        ->join('students', 'fees_assign_childrens.student_id', '=', 'students.id')
        ->where('students.user_id', $user->id)
        ->select('fees_assign_childrens.*')
        ->get();

       

        $trans = DB::select("SELECT fees_collects.* FROM fees_collects
                 INNER JOIN students ON students.id = fees_collects.student_id 
                 WHERE students.user_id = ?", [$user->id]);
            if ($students->isEmpty()) {
    $user_id = ParentGuardian::where('father_mobile', $user->phone)->value('user_id');

    if (!$user_id) {
        $user_id = ParentGuardian::where('mother_mobile', $user->phone)->value('user_id');
    }

    if ($user_id) {
        $parentUser = User::find($user_id);

        if ($parentUser) {
            $students = Student::query()
                ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
                ->join('classes', 'session_class_students.classes_id', '=', 'classes.id')
                ->join('sections', 'session_class_students.section_id', '=', 'sections.id')
                ->where('students.user_id', $parentUser->id)
                ->select('students.*', 'classes.name as class_name', 'sections.name as section_name')
                ->get();

            $parents = ParentGuardian::query()
                ->where('user_id', $parentUser->id)
                ->select('parent_guardians.*')
                ->get();

            $fees = FeesAssignChildren::query()
                ->join('students', 'fees_assign_childrens.student_id', '=', 'students.id')
                ->where('students.user_id', $parentUser->id)
                ->select('fees_assign_childrens.*')
                ->get();

            $trans = DB::select("SELECT fees_collects.* FROM fees_collects
                 INNER JOIN students ON students.id = fees_collects.student_id 
                 WHERE students.user_id = ?", [$parentUser->id]);
        }
    }
}
    
    
       

        if (!$user->status) {
            return $this->responseWithError(___('alert.your_account_is_inactive'), null);
        }

        $user['avatar'] = @globalAsset($user->upload->path, '40X40.webp');

        return $this->responseWithSuccess(___('alert.login_successfully'), [
            'token_type'    => 'Bearer',
            'user'          => $user,
            'student'       => $students,
            'parent'       => $parents,
            'feesAssignChildren' => $fees,
            'transactions'  => $trans,
        ]);
    } catch (\Throwable $th) {
        return $this->responseWithError(___('alert.something_went_wrong'), $th->getMessage());
    }
}


    public function getStudents(Request $request)
    {
        $students = Student::query()
        ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
        ->where('session_class_students.classes_id', $request->class)
            ->where('session_class_students.section_id', $request->section)
            ->select('students.id', DB::raw("CONCAT(students.first_name, ' ', students.last_name) AS name"))
            ->get();

        return response()->json([
            'status' => 'success',
            'students' => $students
        ]);
    }

    public function getResultsPerSubject(Request $request)
    {

    $filteredMarks = DB::table('marks_register_childrens')
    ->join('marks_registers', 'marks_registers.id', '=', 'marks_register_childrens.marks_register_id')
    ->where('marks_registers.classes_id', $request->class)
    ->where('marks_registers.section_id', $request->section)
    ->where('marks_registers.exam_type_id', $request->exam_type)
    ->where('marks_registers.subject_id', $request->subject)
    ->select('marks_register_childrens.student_id', 'marks_register_childrens.mark');

$students = Student::query()
    ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
    ->leftJoinSub($filteredMarks, 'filtered_marks', function ($join) {
        $join->on('filtered_marks.student_id', '=', 'students.id');
    })
    ->where('session_class_students.classes_id', $request->class)
    ->where('session_class_students.section_id', $request->section)
    ->select(
        'students.id',
        DB::raw("CONCAT(students.first_name, ' ', students.last_name) AS name"),
        DB::raw("COALESCE(filtered_marks.mark, 0) AS mark")
    )
    ->orderBy('mark', 'desc')
    ->get();

        return response()->json([
            'status' => 'success',
            'students' => $students
        ]);
    }

    public function getResultsForEditingPerSubject(Request $request)
    {

    $filteredMarks = DB::table('marks_register_childrens')
    ->join('marks_registers', 'marks_registers.id', '=', 'marks_register_childrens.marks_register_id')
    ->where('marks_registers.classes_id', $request->class)
    ->where('marks_registers.section_id', $request->section)
    ->where('marks_registers.exam_type_id', $request->exam_type)
    ->where('marks_registers.subject_id', $request->subject)
    ->select('marks_register_childrens.student_id', 'marks_register_childrens.mark');

$students = Student::query()
    ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
    ->leftJoinSub($filteredMarks, 'filtered_marks', function ($join) {
        $join->on('filtered_marks.student_id', '=', 'students.id');
    })
    ->where('session_class_students.classes_id', $request->class)
    ->where('session_class_students.section_id', $request->section)
    ->select(
        'students.id',
        DB::raw("CONCAT(students.first_name, ' ', students.last_name) AS name"),
        DB::raw("COALESCE(filtered_marks.mark, 0) AS mark")
    )
    ->orderByRaw("CONCAT(students.first_name, ' ', students.last_name)")
    ->get();

        return response()->json([
            'status' => 'success',
            'students' => $students
        ]);
    }

    public function search(Request $request)
    {
        // $data['student']      = $this->studentRepo->show($request->student);
        $data['resultData']   = $this->repo->search($request);
        $data['request']      = $request;
        // $data['classes']      = $this->classRepo->assignedAll();
        // $data['sections']     = $this->classSetupRepo->getSections($request->class);
        // $data['students']     = $this->studentRepo->getStudents($request);
        
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
   

    public function storeFromApp(){
        dd("test");
    }
    public function forgotPassword(Request $request)
    {
        try {

            $data = Validator::make($request->all(),[
                'email' => 'required'
            ]);

            if ($data->fails()) {
                return $this->responseWithError(___('alert.validation_error'), $data->errors());
            }

            $user = User::where('email', $request['email'])->first();

            if (!$user) {
                return $this->responseWithError(___('alert.user_not_found'), []);
            }

            $otp = rand(111111, 999999);

            $data = [
                'email' => $user->email,
                'otp'   => $otp
            ];

            $user->update(['reset_password_otp' => $otp]);

            Config::set('mail.mailers.smtp.password', Crypt::decrypt(setting('mail_password')));

            $forgotPassword = new ForgotPassword($data);

            Mail::to($user->email)->send($forgotPassword);

            return $this->responseWithSuccess(___('alert.we_will_send_you_an_otp_on_this_email'), ['otp' => $otp]);

        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'), $th->getMessage());
        }
    }


    public function resetPassword(Request $request)
    {
        try {
            $data = Validator::make($request->all(),[
                'email'     => 'required',
                'otp'       => 'required',
                'password'  => 'required|confirmed',
            ]);

            if ($data->fails()) {
                return $this->responseWithError(___('alert.validation_error'), $data->errors());
            }

            $user = User::where('email', $request['email'])->first();

            if (!$user) {
                return $this->responseWithError(___('alert.user_not_found'), []);
            }

            if (@$user->reset_password_otp != $request['otp']) {
                return $this->responseWithError(___('alert.otp_is_invalid'), []);
            }

            $user->update([
                'password' => Hash::make($request['password']),
                'reset_password_otp' => null
            ]);

            return $this->responseWithSuccess(___('alert.password_has_been_updated_successfully'), []);

        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'), $th->getMessage());
        }
    }


    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {

            User::where('id', auth()->id())->update(['password' => Hash::make($request->password)]);

            return $this->responseWithSuccess(___('alert.password_has_been_changed_successfully'), []);

        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'), $th->getMessage());
        }
    }


    public function logout()
    {
        try {
            Auth::logout();
            return $this->responseWithSuccess(___('alert.you_have_successfully_logged_out'), []);

        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'), $th->getMessage());
        }
    }

     public function payment(Request $request)
    {
        try {
            $data = Validator::make($request->all(), [
                'phone'          => 'required',
                'sender_account' => 'required',
                'amount'         => 'required',
                'reference'      => 'required',
                'service'        => 'required',
                'control_number'        => 'required',
                'fees_group_id'  => 'required'
            ]);

            if ($data->fails()) {
                return response()->json([
                    'data' => [
                        'message' => $data->errors(),
                    ],
                ], 404);
            }

            // Normalize phone and sender_account
            $phone = trim($request->phone);
            if (strpos($phone, '0') === 0) {
                $phone = '+255' . substr($phone, 1);
            } elseif (strpos($phone, '+255') === 0) {
                // already formatted
            } elseif (strpos($phone, '255') === 0) {
                $phone = '+' . $phone;
            } else {
                $phone = '+255' . $phone;
            }

            $sender_account = trim($request->sender_account);
            if (strpos($sender_account, '0') === 0) {
                $sender_account = '+255' . substr($sender_account, 1);
            } elseif (strpos($sender_account, '+255') === 0) {
                // already formatted
            } elseif (strpos($sender_account, '255') === 0) {
                $sender_account = '+' . $sender_account;
            } else {
                $sender_account = '+255' . $sender_account;
            }

            $amount = (float) trim($request->amount);
            $reference = trim($request->reference);
            $service = trim($request->service);
            $control_number = trim($request->control_number);
            $fees_group_id = trim($request->fees_group_id);

          $date = date('Y-m-d H:i:s');


            // Insert only the required fields into push_transactions
            DB::table('push_transactions')->insert([
                'phone' => $phone,
                'sender_account' => $sender_account,
                'amount' => $amount,
                'reference' => $reference,
                'service' => $service,
                'payment_status' => 0,
                'settlement_status' => 0,
                'control_number' => $control_number,
                'is_processed' => 0,
                'created_at' => $date,
                'updated_at' => $date,
                'fees_assign_children_id' => $fees_group_id,
                'account_id' => 0,
            ]);

            $url = 'https://m-malipo.ubx.co.tz/api/transaction_external';
            $data = $request->all();       // Convert request to array
            $data['phone'] = '0748570608'; // Change the phone value
            $payload = json_encode($data);
            Log::info($payload);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload),
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            Log::info('transaction_external response', [
                'http_code' => $httpCode,
                'response' => $response,
            ]);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                return response()->json([
                    'error' => $error
                ], 500);
            }

            curl_close($ch);

            return response()->json([
                'data' => [
                    'message' => 'Payment data inserted successfully',
                ],
            ], 200);

        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'data' => [
                    'transaction' => $th->getMessage(),
                ],
            ], 404);
        }
    }


     public function callback(Request $request)
    {
        try {
            Log::info('callbackreceived');
            Log::info($request->all());
            // Your secret key
            $secret_key = "jBYIn7U8bfsNFPe6pPhXVKN3uq9PJfDQZPtU5dl26Y0=";

            // Validate the required fields
            $validator = Validator::make($request->all(), [
                'transaction_id'   => 'required',
                'payment_amount'   => 'required',
                'payment_channel'  => 'required',
                'reference_number' => 'required',
                'transaction_date' => 'required',
                'msisdn'           => 'required',
                'checksum'         => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'data' => [
                        'message' => $validator->errors(),
                    ],
                ], 422);
            }

            // Extract fields
            $transaction_id    = $request->transaction_id;
            $payment_channel   = $request->payment_channel;
            $amount            = $request->payment_amount;
            $reference_number  = $request->reference_number;
            $received_checksum = $request->checksum;

            // Generate the expected checksum
            $checksum_string     = $transaction_id . $payment_channel . $amount . $reference_number . $secret_key;
            $calculated_checksum = base64_encode(hash('sha256', $checksum_string, true));

            // Compare checksums
            // if ($received_checksum !== $calculated_checksum) {
            //     Log::info("checksum failed");
            //     return response()->json([
            //         'data' => [
            //             'message' => 'Checksum validation failed',
            //         ],
            //     ], 403);
            // }

            // Update transaction status if found (payment_date = current date)
            $transactionResult = DB::select('SELECT * FROM push_transactions WHERE reference = ?', [$reference_number])[0];

            if (! empty($transactionResult)) {
                $id = $transactionResult->id;
                $paymentDate = date('Y-m-d H:i:s');
                DB::update('UPDATE push_transactions SET payment_status = ?, payment_receipt = ?, payment_date = ? WHERE id = ?', ["1", $request->transaction_id, $paymentDate, $id]);
            }
         

return response()->json([
                'data' => [
                    'message' => "Success",
                ],
            ], 200);

        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'data' => [
                    'transaction' => $th->getMessage(),
                ],
            ], 500);
        }
    }

       public function settlement(Request $request)
    {
        try {
            Log::info('settlementreceived');
            Log::info($request->all());
            $validator = Validator::make($request->all(), [
                'transaction_id'                                           => 'required',
                'payment_amount'                                           => 'required',
                'payment_channel'                                          => 'required',
                'reference_number'                                         => 'required',
                'transaction_date'                                         => 'required|date',
                'msisdn'                                                   => 'required',
                'payment_transactions'                                     => 'required',
                'payment_transactions.*.beneficiary_id'                    => 'required',
                'payment_transactions.*.amount'                            => 'required',
                'payment_transactions.*.beneficiary_fi_transaction_status' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'data' => [
                        'message' => $validator->errors(),
                    ],
                ], 422);
            }
            $reference      = trim($request->reference_number);
            $transaction_id = $request->transaction_id;
            if (isset($request->payment_transactions) || is_array($request->payment_transactions)) {
                if ($request->payment_transactions[0]['beneficiary_fi_transaction_status'] == "completed") {
                    $settlementStatus = "1";
                    $settlement_id    = $request->payment_transactions[0]['beneficiary_fi_transaction_id'];
                    $settlement_date  = $request->payment_transactions[0]['beneficiary_fi_transaction_date'];
                } else if ($request->payment_transactions[0]['beneficiary_fi_transaction_status'] == "processing") {
                    $settlementStatus = "1";
                    $settlement_id    = $request->payment_transactions[0]['beneficiary_fi_transaction_id'] ?? '';
                    $settlement_date  = $request->payment_transactions[0]['beneficiary_fi_transaction_date'] ?? '';
                } else if ($request->payment_transactions[0]['beneficiary_fi_transaction_status'] == "suspended") {
                    $settlementStatus = "2";
                    $settlement_id    = $request->payment_transactions[0]['beneficiary_fi_transaction_id'] ?? '';
                    $settlement_date  = $request->payment_transactions[0]['beneficiary_fi_transaction_date'] ?? '';
                }
            } else {
                $settlementStatus = "2";
            }
            $transactionResult = DB::select('SELECT * FROM push_transactions WHERE reference = ?', [$reference]);
            if (! empty($transactionResult)) {
                Log::info($transactionResult);
                Log::info("Data inafika");
                $push = $transactionResult[0];
                $id = $push->id;

                DB::update(
                    'UPDATE push_transactions SET settlement_status = ?, settlement_receipt = ?, settlement_date = ?, is_processed = ? WHERE id = ?',
                    [$settlementStatus, $settlement_id, $settlement_date, '1', $id]
                );

                // Custom logic: If completed, process fee collection
                if (isset($request->payment_transactions) && $request->payment_transactions[0]['beneficiary_fi_transaction_status'] == "completed") {
                    // Prefer the exact fees_assign_children_id stored on the push_transactions row
                    $fees_assign_children_id = $push->fees_assign_children_id ?? null;
                    $student_id = null;

                    if ($fees_assign_children_id) {
                        $fees_assign_child = DB::table('fees_assign_childrens')->where('id', $fees_assign_children_id)->first();
                        if ($fees_assign_child) {
                            $student_id = $fees_assign_child->student_id;
                        }
                    }

                    // Fallback: derive by control_number if linkage is missing
                    if (!$student_id) {
                        $control_number = $push->control_number;
                        $student = DB::table('students')->where('control_number', $control_number)->first();
                        if ($student) {
                            $student_id = $student->id;
                            // Try to find a fees_assign_children row for this student (latest)
                            $fees_assign_child = DB::table('fees_assign_childrens')
                                ->where('student_id', $student_id)
                                ->orderByDesc('id')
                                ->first();
                            if ($fees_assign_child) {
                                $fees_assign_children_id = $fees_assign_child->id;
                            }
                        }
                    }

                    if ($student_id && $fees_assign_children_id) {
                        $amount = $push->amount;
                        // Prepare request and call storeOnline
                        $repo = app(\App\Repositories\Fees\FeesCollectRepository::class);
                        $storeRequest = new \Illuminate\Http\Request();
                        $storeRequest->replace([
                            'date' => $settlement_date,
                            'payment_method' => 2,
                            'fees_assign_children_id' => $fees_assign_children_id,
                            'student_id' => $student_id,
                            'amounts' => $amount,
                            'account_id' => "1",
                            'comment' => $settlement_id,
                            // Ensure status=1 so updateFeesAssigned processes it
                            'status' => 1,
                        ]);
                        Log::info('storeOnline request from settlement', $storeRequest->all());
                        $repo->storeOnline($storeRequest);
                    } else {
                        Log::warning('Settlement: unable to resolve student/fees_assign_children_id for push transaction', [
                            'push_id' => $push->id,
                            'control_number' => $push->control_number ?? null,
                            'fees_assign_children_id' => $push->fees_assign_children_id ?? null,
                        ]);
                    }
                }
                return response()->json([
                    "status_code"      => "00",
                    "reference_number" => $reference,
                    "transaction_id"   => $transaction_id,
                ], 200);
            } else {
                return response()->json([
                    'data' => [
                        'message' => "Transaction not found",
                    ],
                ], 404);
            }
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'data' => [
                    'message' => $th->getMessage(),
                ],
            ], 500);
        }
    }



}
