<?php

namespace App\Repositories\StudentInfo;

use App\Imports\StudentsImport;
use App\Models\Academic\Classes;
use Illuminate\Support\Facades\Log;
use App\Models\Academic\ClassSetup;
use App\Models\Academic\ClassSetupChildren;
use App\Models\Academic\Section;
use App\Models\Fees\FeesAssign;
use App\Models\Fees\FeesAssignChildren;
use App\Models\Fees\FeesGroup;
use App\Models\Fees\FeesMaster;
use App\Models\Fees\FeesMasterChildren;
use App\Models\Fees\FeesType;
use App\Models\Library\Member;
use App\Models\Library\MemberCategory;
use App\Models\Religion;
use App\Models\Role;
use App\Models\StudentInfo\ParentGuardian;
use App\Models\User;
use App\Enums\Settings;
use App\Enums\ApiStatus;
use App\Traits\CommonHelperTrait;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use App\Models\StudentInfo\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use App\Models\StudentInfo\SessionClassStudent;
use App\Interfaces\StudentInfo\StudentInterface;
use App\Models\StudentInfo\StudentCategory;
use App\Models\Examination\MarksRegisterChildren;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Fees\FeesCollect;
use Illuminate\Support\Facades\Auth;
use App\Models\Accounts\Income;
use App\Models\StudentInfo\StudentDeletedHistory;
use App\Models\StudentInfo\StudentFeesAssignDeletedHistory;
use App\Models\StudentInfo\StudentFeesCollectDeletedHistory;
use Illuminate\Support\Facades\Schema;
use App\Models\TransportMonth;

class StudentRepository implements StudentInterface
{
    use ReturnFormatTrait;
    use CommonHelperTrait;

    private $model;

    public function __construct(Student $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->active()->get();
    }

    // public function getStudents($request)
    // {
    //     return  SessionClassStudent::query()
    //             ->where('session_id', setting('session'))
    //             ->where('classes_id', $request->class)
    //             ->where('section_id', $request->section)
    //             ->when(request()->filled('gender'), function ($q) use ($request) {
    //                 $q->whereHas('student', fn ($q) => $q->where('gender_id', $request->gender));
    //             })
    //             ->with('student')
    //             ->get();
    // }

    public function getStudents($request)
{
    return SessionClassStudent::query()
        ->where('session_id', setting('session'))
        ->where('classes_id', $request->class)
        ->where('section_id', $request->section)
        ->join('students', 'students.id', '=', 'session_class_students.student_id')
        ->orderBy('students.first_name')
        ->orderBy('students.last_name')
        ->select('session_class_students.*')
        ->with('student')
        ->get();
}



    public function getPaginateAll()
    {
        return SessionClassStudent::whereHas('student')->where('session_id', setting('session'))->latest()->with('student')->paginate(Settings::PAGINATE);
    }
    public function getPaginateAllFormOne()
    {
        return SessionClassStudent::whereHas('student')
            ->where('session_id', setting('session'))
            ->latest()
            ->with(['student', 'class', 'section'])
            ->paginate(Settings::PAGINATE);
    }

    public function getPaginateAllFormTwo()
    {
        return SessionClassStudent::whereHas('student')->where('session_id', setting('session'))->latest()->with('student')->paginate(Settings::PAGINATE);
    }
    public function getSessionStudent($id)
    {
        return SessionClassStudent::where('id', $id)->first();
    }

    public function getSessionStudentWithId($id)
    {
        return SessionClassStudent::where('student_id', $id)->first();
    }


    public function searchStudents($request)
{
    $students = SessionClassStudent::query()
        ->where('session_id', setting('session'))
        ->join('students', 'students.id', '=', 'session_class_students.student_id'); // join required for ordering

    // Filter by class if provided
    if ($request->filled('class') && $request->class != "") {
        $students = $students->where('classes_id', $request->class);
    }
    
    // Filter by section if provided
    if ($request->filled('section') && $request->section != "") {
        $students = $students->where('section_id', $request->section);
    }
    
    // Search by name or keyword - works dynamically with LIKE on both sides
    if ($request->filled('name') && $request->name != "") {
        $name = trim($request->name);
        $students = $students->where(function ($query) use ($name) {
            $query->where('students.first_name', 'LIKE', "%{$name}%")
                ->orWhere('students.last_name', 'LIKE', "%{$name}%")
                ->orWhereRaw("CONCAT(students.first_name, ' ', students.last_name) LIKE ?", ["%{$name}%"])
                ->orWhereRaw("CONCAT(students.last_name, ' ', students.first_name) LIKE ?", ["%{$name}%"]);
        });
    }
    
    // Also support keyword field for backward compatibility
    if ($request->filled('keyword') && $request->keyword != "") {
        $keyword = trim($request->keyword);
        $students = $students->where(function ($query) use ($keyword) {
            $query->where('students.admission_no', 'LIKE', "%{$keyword}%")
                ->orWhere('students.first_name', 'LIKE', "%{$keyword}%")
                ->orWhere('students.last_name', 'LIKE', "%{$keyword}%")
                ->orWhereRaw("CONCAT(students.first_name, ' ', students.last_name) LIKE ?", ["%{$keyword}%"])
                ->orWhereRaw("CONCAT(students.last_name, ' ', students.first_name) LIKE ?", ["%{$keyword}%"])
                ->orWhere('students.roll_no', 'LIKE', "%{$keyword}%")
                ->orWhere('students.dob', 'LIKE', "%{$keyword}%");
        });
    }

    $students = $students->orderBy('students.first_name', 'asc');

    return $students
        ->select('session_class_students.*')
        ->with(['student', 'class', 'section'])
        ->paginate(Settings::PAGINATE);
}

    /**
     * Search students by name (CONCAT first_name, last_name) LIKE %q% for current session.
     * Used for live search / autocomplete.
     */
    public function searchStudentsByName($q, $limit = 25)
    {
        $term = trim($q);
        if ($term === '') {
            return collect([]);
        }
        $list = SessionClassStudent::query()
            ->where('session_id', setting('session'))
            ->join('students', 'students.id', '=', 'session_class_students.student_id')
            ->where('students.status', 1)
            ->where(function ($query) use ($term) {
                $query->whereRaw("CONCAT(students.first_name, ' ', students.last_name) LIKE ?", ["%{$term}%"])
                    ->orWhereRaw("CONCAT(students.last_name, ' ', students.first_name) LIKE ?", ["%{$term}%"]);
            })
            ->orderBy('students.first_name')
            ->select('students.id', 'students.first_name', 'students.last_name')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'first_name' => $row->first_name,
                    'last_name' => $row->last_name,
                    'full_name' => trim($row->first_name . ' ' . $row->last_name),
                ];
            });
        return $list;
    }

    // public function searchStudents($request)
    // {
    //     $students = SessionClassStudent::query();
    //     $students = $students->where('session_id', setting('session'));

    //     if($request->class != "") {
    //         $students = $students->where('classes_id', $request->class);
    //     }
    //     if($request->section != "") {
    //         $students = $students->where('section_id', $request->section);
    //     }
    //     if($request->keyword != "") {
    //         $students = $students->whereHas('student', function ($query) use ($request) {
    //             $query->where('admission_no', 'LIKE', "%{$request->keyword}%")
    //             ->orWhere('first_name', 'LIKE', "%{$request->keyword}%")
    //             ->orWhere('last_name', 'LIKE', "%{$request->keyword}%")
    //             ->orWhere('roll_no', 'LIKE', "%{$request->keyword}%")
    //             ->orWhere('dob', 'LIKE', "%{$request->keyword}%");
    //         });
    //     }

    //     return $students->paginate(Settings::PAGINATE);
    // }

    public function store($request)
    {
        DB::beginTransaction();
        try {

    
                    $firstStudentName = $request->first_name .' '.$request->last_name;
                    $firstName = $firstStudentName;
                    $firstName = preg_replace('/\s+/', ' ', trim($firstName));
                    $firstNameParts = explode(' ', $firstName);
                    $studentFirstName = $firstNameParts[0];
                    $studentSecondName = $firstNameParts[1];
                    $lastNameToUse = $studentSecondName;
                    $parentName = $studentSecondName;
                    if (isset($firstNameParts[2]) && !empty($firstNameParts[2])) {
                        $studentMiddleName = $firstNameParts[2];
                        $lastNameToUse .= $studentMiddleName;
                        $parentName = $studentSecondName ." ". $studentMiddleName;
                    }
                      $receiver_account = trim($request->mobile);
                        if (strpos($receiver_account, '0') === 0) {
                            $receiver_account = '+255' . substr($receiver_account, 1);
                        } elseif (strpos($receiver_account, '+255') === 0) {
                        } elseif (strpos($receiver_account, '255') === 0) {
                            $receiver_account = '+' . $receiver_account;
                        } else {
                            $receiver_account = '+255' . $receiver_account;
                        }
                        $request->mobile =  $receiver_account;
                        
                    $parentEmail = strtolower(!empty($row['email']) ? $request->email : (trim($studentFirstName) . trim($lastNameToUse) . '@gmail.com'));

                    // Use guardian_mobile to avoid duplicate parents: reuse existing parent if found
                    $existingParentId = $this->getParentIdByPhone($request->mobile);
                    if (!empty($existingParentId)) {
                        $parent = ParentGuardian::find($existingParentId);
                        $user_id = $parent->user_id;
                        $parent_id = $existingParentId;
                        // Update parent and user with latest info from form
                        $parent->father_mobile       = $request->second_mobile ?? $parent->father_mobile;
                        $parent->guardian_name       = $request->last_name;
                        $parent->guardian_email      = $request->email;
                        $parent->guardian_mobile     = $request->mobile;
                        $parent->save();
                        $user = User::find($user_id);
                        if ($user) {
                            $user->name  = $request->last_name;
                            $user->email = $request->email;
                            $user->phone = $request->mobile;
                            $user->save();
                        }
                    } elseif (empty($this->getUserId($parentEmail))) {
            $user_id = DB::SELECT("SELECT * FROM users where phone = ?",[$request->mobile ]);

            if(!empty($user_id )){
            $user_id = $user_id[0]->id;
            $parent_id = DB::SELECT("SELECT * FROM parent_guardians WHERE user_id = ?",[$user_id]);
            if(!empty($parent_id)){
            $parent_id = $parent_id[0]->id;
            }else{
            $parent                      = new ParentGuardian();
            $parent->user_id             = $user_id;
            $parent->father_mobile       = $request->second_mobile;
            $parent->guardian_name       = $request->last_name;
            $parent->guardian_email      = $request->email;
            $parent->guardian_mobile     = $request->mobile;
            $parent->status              = "1";
            $parent->save();
            $parent_id = $parent->id;
            }
            }else{
            $role                     = Role::find(7);
             $user                     = new User();
            $user->name               = $request->last_name;
            $user->email              = $request->email;
            $user->phone              = $request->mobile;
            $user->password           = Hash::make('12345678');
            $user->email_verified_at  = now();
            $user->role_id            = $role->id;
            $user->permissions        = $role->permissions;
            $user->save();
            $user_id = $user->id;

            $parent                      = new ParentGuardian();
            $parent->user_id             = $user->id;
            $parent->father_mobile       = $request->second_mobile;
            $parent->guardian_name       = $request->last_name;
            $parent->guardian_email      = $request->email;
            $parent->guardian_mobile     = $request->mobile;
            $parent->status              = "1";
            $parent->save();
            $parent_id = $parent->id;

            }
            } else {
            // User exists by email: get or create parent by user_id
            $user_id = $this->getUserId($parentEmail);
            $parentRecord = DB::SELECT("SELECT * FROM parent_guardians WHERE user_id = ?", [$user_id]);
            if (!empty($parentRecord)) {
                $parent_id = $parentRecord[0]->id;
            } else {
                $parent = new ParentGuardian();
                $parent->user_id             = $user_id;
                $parent->father_mobile       = $request->second_mobile;
                $parent->guardian_name       = $request->last_name;
                $parent->guardian_email      = $request->email;
                $parent->guardian_mobile     = $request->mobile;
                $parent->status              = "1";
                $parent->save();
                $parent_id = $parent->id;
            }
            }

            $row                       = new $this->model;
            $row->user_id              = $user_id;
            $row->first_name           = $request->first_name;
            $row->last_name            = $request->last_name;
            $row->admission_no         = $request->admission_no;
            $row->roll_no              = $this->generateUniqueTrackingNumber();
            $row->mobile               = $request->mobile != "" ? $request->mobile  : NULL;
            $row->email                = $request->email != ""? $request->email : NULL;
            $row->dob                  = $request->date_of_birth != ""?$request->date_of_birth : NULL;
            $row->religion_id          = $request->religion != ""? $request->religion :  NULL;
            $row->gender_id            = $request->gender != ""? $request->gender :  NULL;
            $row->admission_date       = $request->admission_date != ""? $request->admission_date : "2024-01-01";
            $row->parent_guardian_id   = $parent_id!= ""? $parent_id :  NULL;
            $row->student_category_id  = $request->category != ""? $request->category :  NULL;
            $row->previous_school_image_id = $this->UploadImageCreate($request->previous_school_image, 'backend/uploads/students');
            $row->residance_address = $request->residance_address;
            $row->status               = "1";
            $row->upload_documents     = $this->uploadDocuments($request);
            $row->residance_address = $request->residance_address??'';
            $row->control_number = "00".$this->generateUniquecONTROLNumber();
            $row->save();
            $student_id = $row->id;

            $session_class                      = new SessionClassStudent();
            $session_class->session_id          = setting('session');
            $session_class->classes_id          = $request->class;
            $session_class->section_id          = $request->section != ""? $request->section :  NULL;
            $session_class->shift_id            = $request->shift != ""? $request->shift :  NULL;
            $session_class->student_id          = $row->id;
            $session_class->roll                = $request->roll_no != ""? $request->roll_no : NULL;
            $session_class->save();


            $member                   = new Member();
            $member->user_id          = $student_id;
            $member->name      = $request->first_name.' '.$request->last_name;
            $member->category_id      = "1";
            $member->status           = "1";
            $member->save();

            if (empty($this->checkFeesAssign($request->class,$this->getFeeGroupId($this->getFeeTypeId($request->class)),$request->section))) {
                $rowFeesAssign = new FeesAssign();
                $rowFeesAssign->session_id = setting('session');
                $rowFeesAssign->classes_id = $request->class;
                $rowFeesAssign->section_id =  $request->section;
                $rowFeesAssign->fees_group_id = $this->getFeeGroupId($this->getFeeTypeId($request->class));
                $rowFeesAssign->save();
                $feesAssignId = $rowFeesAssign->id;
            }else{
                $feesAssignId = $this->checkFeesAssign($request->class,$this->getFeeGroupId($this->getFeeTypeId($request->class)),$request->section);
            }

            $quaters = $this->getFeeMasterAmount($this->getFeeMasterId($this->getFeeTypeId($request->class))) / 4;
            if (empty($this->checkFeesAssignChildren($feesAssignId, $this->getFeeMasterId($this->getFeeTypeId($request->class)), $student_id))) {
                $feesChield = new FeesAssignChildren();
                $feesChield->fees_assign_id = $feesAssignId;
                $feesChield->fees_master_id = $this->getFeeMasterId($this->getFeeTypeId($request->class));
                $feesChield->student_id = $student_id;
                $feesChield->fees_amount = $this->getFeeMasterAmount($this->getFeeMasterId($this->getFeeTypeId($request->class)));
                $feesChield->paid_amount = '0';
                $feesChield->remained_amount = $this->getFeeMasterAmount($this->getFeeMasterId($this->getFeeTypeId($request->class)));
                $feesChield->quater_one = $quaters;
                $feesChield->quater_two = $quaters;
                $feesChield->quater_three = $quaters;
                $feesChield->quater_four = $quaters;
                $feesChield->quater_amount = $quaters;
                $feesChield->control_number = $this->getStudentControlNumber($student_id);
                $feesChield->fee_group = "2";
                $feesChield->save();

        }

        if (str_contains(strtoupper($this->getCategory($request->category)), "BOARDING")) {
            $feeAmount = 2000000; // Assign as an integer
            
            // Fetch the ID
            $result = DB::select("SELECT fees_assign_childrens.id FROM fees_assign_childrens 
                                  INNER JOIN fees_assigns 
                                  ON fees_assign_childrens.fees_assign_id = fees_assigns.id 
                                  WHERE fees_assigns.fees_group_id = ? and fees_assign_childrens.student_id = ? ", [2, $student_id ]);
        
            // Ensure there's a result
            if (!empty($result)) {
                $id = $result[0]->id; // Extract the first ID
                
                // Find the record and update it
                $feesChield = FeesAssignChildren::findOrFail($id);
                $feesChield->fees_master_id = 20; // Update the fees_master_id
                $feesChield->fees_amount = $feeAmount;
                $feesChield->remained_amount = $feeAmount;
                $feesChield->quater_one = $feeAmount / 4;
                $feesChield->quater_two = $feeAmount / 4;
                $feesChield->quater_three = $feeAmount / 4;
                $feesChield->quater_four = $feeAmount / 4;
                $feesChield->fee_group = "2";
                $feesChield->save();
            } else {
                // Handle the case where no records are found
                // throw new Exception("No records found for the specified fees_group_id.");
            }
        }

        //Assign transportation automatically
        $feeTypeId = 0;
        $feeAssignId = DB::select('SELECT id FROM fees_assigns WHERE classes_id = ? AND session_id = ? AND fees_group_id = ?', [$request->class,setting('session'),"3"]);
        if (empty($feeAssignId)) {
            $row                = new FeesAssign();
            $row->session_id    = setting('session');
            $row->classes_id      = $request->class;
            $row->section_id    = $request->section;
            $row->fees_group_id = "3";
            $row->save();
            $feeAssignId = $row->id;
        }else{
            $feeAssignId = DB::select('SELECT id FROM fees_assigns WHERE classes_id = ? AND session_id = ? AND fees_group_id = ?', [$request->class,setting('session'),"3"])[0]->id;
        }


        $transportProfile = DB::select("
        SELECT student_categories.name 
                FROM student_categories
                INNER JOIN students ON students.student_category_id = student_categories.id
                WHERE students.id = ?
            ", [$student_id]);
    
            if(!empty($transportProfile)){
                $transportProfile =$transportProfile[0]->name;
            // Split transport profile based on 'DAY'
            $transportProfileParts = explode(' DAY ', $transportProfile);
            if (!empty($transportProfileParts[1])) {
                Log::info("transportProfileParts".$transportProfileParts[1]);
                // Get fee type ID based on transport profile part
                $feeTypeResult = DB::select("
                    SELECT id 
                    FROM fees_types 
                    WHERE code = ?
                ", [trim($transportProfileParts[1])]);
                
                if (!empty($feeTypeResult)) {
                    $feeTypeId = $feeTypeResult[0]->id;
                    Log::info($feeTypeId);

                    // Get fees master ID based on fee type ID and session (2026)
                    $feesMasterResult = DB::select("
                        SELECT id 
                        FROM fees_masters 
                        WHERE fees_type_id = ? AND session_id = ?
                    ", [$feeTypeId, setting('session')]);
                    
                    if (!empty($feesMasterResult)) {
                        $fees_master = $feesMasterResult[0]->id;
                    }
                }
            }

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
                        $feesChild = new FeesAssignChildren();
                        $feesChild->fees_assign_id = $feeAssignId;
                        $feesChild->fees_master_id = $fees_master;
                        $feesChild->student_id = $student_id;
                        $feesChild->fees_amount = $this->getFeesAmount($fees_master);
                        $feesChild->remained_amount = $this->getFeesAmount($fees_master);
                        $feesChild->control_number = $controlNumber;
    
                        // Divide fees into quarters if due date allows
                        if ($this->getDueDate($fees_master) > 8) {
                            $quarterAmount = $this->getFeesAmount($fees_master) / 4;
                            $feesChild->quater_one = $quarterAmount;
                            $feesChild->quater_two = $quarterAmount;
                            $feesChild->quater_three = $quarterAmount;
                            $feesChild->quater_four = $quarterAmount;
                        }
                        $feesChild->fee_group = "2";
                        $feesChild->save();
                        $feesId = $feesChild->id;
                        $months = DB::table('months_list')->get();
                        foreach($months as $month){
                            TransportMonth::create([
                                'student_id' => $student_id,
                                'fee_assign_children_id' => $feesId,
                                'user_id' => Auth::id(),
                                'month' => $month->id,
                                'amount' => $this->getFeesAmount($fees_master)/10,
                                'status' => '1',
                                'state' => '1'
                            ]);
                        }
                    } else {
                        return $this->responseWithError('Fees has already been assigned', []);
                    }
                }
            }
        }
            DB::commit();
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::alert($th);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
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


    public function generateUniqueTrackingNumber() {
        do {
            // Generate a random 4-digit number
            $trackingNumber = random_int(1000, 9999);

            // Check if it exists in the tracking_number table
            $exists = DB::table('tracking_number')
                ->where('tracking_number', $trackingNumber)
                ->exists();
        } while ($exists); // Keep generating until the number is unique

        // Insert the unique tracking number into the table
        DB::table('tracking_number')->insert([
            'tracking_number' => $trackingNumber,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $trackingNumber; // Return the generated tracking number
    }

    public function generateUniquecONTROLNumber() {
        do {
            // Generate a random 4-digit number
            $trackingNumber = random_int(100000, 999999);

            // Check if it exists in the tracking_number table
            $exists = DB::table('tracking_number')
                ->where('tracking_number', $trackingNumber)
                ->exists();
        } while ($exists); // Keep generating until the number is unique

        // Insert the unique tracking number into the table
        DB::table('tracking_number')->insert([
            'tracking_number' => $trackingNumber,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $trackingNumber; // Return the generated tracking number
    }

    public function upload($request)
    {
        $request->validate([
            'document_files' => 'required|mimes:xlsx,xls,csv',
            'document_format' => 'nullable|in:1,2',
        ]);

        if ((string) $request->input('document_format', '1') === '2') {
            return $this->uploadFormatTwo($request);
        }

        DB::beginTransaction();
        try {

            $data = Excel::toArray(new StudentsImport, $request->file('document_files'));
            foreach ($data[0] as $row) {
                if (empty(array_filter($row))) {
                    return $this->responseWithSuccess(___('alert.created_successfully'), []);
                }
                if (!empty($row)) {
                    $row['category'] = $row['category'] ?? 'Day';
                    
                    // Normalize phone number - remove spaces and format
                    $phoneNumber = $row['phone_number'] ?? null;
                    $normalizedPhone = $this->normalizePhoneNumber($phoneNumber);
                    
                    // Check for duplicate by phone number first
                    $existingStudentId = $this->getStudentIdByPhone($phoneNumber);
                    if (!empty($existingStudentId)) {
                        // Student with this phone number already exists, skip or update
                        Log::info("Upload: Duplicate student found by phone number", [
                            'phone' => $phoneNumber,
                            'normalized_phone' => $normalizedPhone,
                            'existing_student_id' => $existingStudentId,
                            'row' => $row
                        ]);
                        // Skip this row to prevent duplication
                        continue;
                    }
                    
                    $firstName = $row['student_name'];
                    $firstName = preg_replace('/\s+/', ' ', trim($firstName));
                    $firstNameParts = explode(' ', $firstName);
                    $studentFirstName = $firstNameParts[0];
                    $studentSecondName = $firstNameParts[1];
                    $lastNameToUse = $studentSecondName;
                    $parentName = $studentSecondName;
                    if (isset($firstNameParts[2]) && !empty($firstNameParts[2])) {
                        $studentMiddleName = $firstNameParts[2];
                        $lastNameToUse .= $studentMiddleName;
                        $parentName = $studentSecondName ." ". $studentMiddleName;
                    }
                    $parentEmail = strtolower(!empty($row['email']) ? $row['email'] : (trim($studentFirstName) . trim($lastNameToUse) . '@gmail.com'));

                    // Reuse parent/user by phone — do not register duplicate User or ParentGuardian
                    $existingParentId = $this->getParentIdByPhone($phoneNumber);
                    $userIdByPhone = $this->getUserIdByPhone($phoneNumber);
                    $user_id = '';

                    if (!empty($existingParentId)) {
                        $parent = ParentGuardian::find($existingParentId);
                        $user_id = $parent->user_id ?? $this->getUserIdByParentId($existingParentId);
                    } elseif (!empty($userIdByPhone)) {
                        $user_id = $userIdByPhone;
                        $parentIdForUser = $this->getParentIdByUserId($user_id);
                        if (!empty($parentIdForUser)) {
                            $existingParentId = $parentIdForUser;
                        }
                    }

                    if ($user_id === '' || $user_id === null) {
                        if (empty($this->getUserId($parentEmail))) {
                            $role = Role::find(7); // Guardian role id 7
                            $user = new User();
                            $user->name = $parentName;
                            $user->email = strtolower($parentEmail);
                            $user->phone = $normalizedPhone; // Use normalized phone
                            $user->password = Hash::make('12345678');
                            $user->email_verified_at = now();
                            $user->role_id = $role->id;
                            $user->permissions = $role->permissions;
                            $user->save();
                            $user_id = $user->id;
                        } else {
                            $user_id = $this->getUserId($parentEmail);
                        }
                    }

                    if (empty($this->getClassId($row['class']))) {
                        $classesStore = new Classes();
                        $classesStore->name = $row['class'];
                        $classesStore->status = "1";
                        $classesStore->orders = "0";
                        $classesStore->save();
                        $classesStore_id = $classesStore->id;
                    } else {
                        $classesStore_id = $this->getClassId($row['class']);
                    }

                    if (empty($this->getStudentCategory($row['category']))) {
                        $category = new StudentCategory();
                        $category->name = $row['category'];
                        $category->status = "1";
                        $category->save();
                        $category_id = $category->id;
                    } else {
                        $category_id = $this->getStudentCategory($row['category']);
                    }

                    if (empty($this->getClassSetupId($classesStore_id, setting('session')))) {
                        $setup = new ClassSetup();
                        $setup->session_id = setting('session');
                        $setup->classes_id = $classesStore_id;
                        $setup->save();
                        $setup_id = $setup->id;
                    } else {
                        $setup_id = $this->getClassSetupId($classesStore_id, setting('session'));
                    }

                    if (empty($this->getSectionId(trim($row['section'])))) {
                        $sectionStore = new Section();
                        $sectionStore->name = $row['section'];
                        $sectionStore->status = "1";
                        $sectionStore->save();
                        $sectionStore_id = $sectionStore->id;
                    } else {
                        $sectionStore_id = $this->getSectionId(trim($row['section']));
                    }

                    if (empty($this->getClassSetupChildren($setup_id, $sectionStore_id))) {
                        $section = new ClassSetupChildren();
                        $section->class_setup_id = $setup_id;
                        $section->section_id = $sectionStore_id;
                        $section->save();
                        $section_id = $section->id;
                    } else {
                        $section_id = $this->getClassSetupChildren($setup_id, $sectionStore_id);
                    }

                    if (empty($existingParentId)) {
                        if (empty($this->getParentId(trim($parentEmail)))) {
                            $parent = new ParentGuardian();
                            $parent->user_id = $user_id;
                            $parent->guardian_name = $parentName;
                            $parent->guardian_email = strtolower($parentEmail);
                            $parent->guardian_mobile = $normalizedPhone; // Use normalized phone
                            $parent->status = "1";
                            $parent->save();
                            $parent_id = $parent->id;
                        } else {
                            $parent_id = $this->getParentId(trim($parentEmail));
                            // Update phone number if it's different
                            $parent = ParentGuardian::find($parent_id);
                            if ($parent && $parent->guardian_mobile !== $normalizedPhone && !empty($normalizedPhone)) {
                                $parent->guardian_mobile = $normalizedPhone;
                                $parent->save();
                            }
                        }
                    } else {
                        $parent_id = $existingParentId;
                    }


                   
                    // Check again by phone number before creating (double check)
                    $existingStudentIdByPhone = $this->getStudentIdByPhone($phoneNumber);
                    if (!empty($existingStudentIdByPhone)) {
                        // Student already exists, update class/section if needed
                        $student_id = $existingStudentIdByPhone;
                        $student = Student::findOrFail($student_id);
                        $student->student_category_id = $category_id;
                        $student->category_id = $category_id;
                        if (!empty($normalizedPhone)) {
                            $student->mobile = $normalizedPhone;
                        }
                        $student->save();
                    } else if (empty($this->getStudentId($parent_id,$classesStore_id))) {
                        $student = new $this->model;
                        $student->user_id = $user_id;
                        $student->first_name = $studentFirstName;
                        $student->last_name = $parentName;
                        $student->admission_no = $row['admission_no']??NULL;
                        $student->roll_no = $this->generateUniqueTrackingNumber();
                        $student->mobile = $normalizedPhone; // Use normalized phone
                        $student->email = strtolower($row['email'] ?? (trim($studentFirstName) . trim($lastNameToUse) . '@gmail.com'));
                        $gender_id = $this->getGenderId($row['gender']);
                        $student->gender_id = $gender_id;
                        $student->admission_date = $row['admission_date'] ?? "2026-01-01";
                        $student->student_category_id  = $this->getCategoryId($row['category']);
                        $student->parent_guardian_id = $parent_id;
                        $student->status = "1";
                        $student->category_id =  $category_id;
                        $student->control_number = "00".$this->generateUniquecONTROLNumber();
                        $student->save();
                        $student_id = $student->id;

                    } else {
                        $student_id = $this->getStudentId($parent_id,$classesStore_id);
                        $student = Student::findOrFail($student_id);
                        $student->student_category_id  = $category_id;
                        $student->category_id =  $category_id;
                        if (!empty($normalizedPhone)) {
                            $student->mobile = $normalizedPhone;
                        }
                        $student->save();
                    }

                    if (empty($this->getSessionClassStudent($classesStore_id, setting('session'), $student_id))) {

                        $session_class = new SessionClassStudent();
                        $session_class->session_id = setting('session');
                        $session_class->classes_id = $classesStore_id;
                        $session_class->section_id = $sectionStore_id;
                        $session_class->student_id = $student_id;
                        $session_class->save();
                        $session_class_id = $session_class->id;
                    } else {
                        $session_class_id = $this->getSessionClassStudent($classesStore_id, setting('session'), $student_id);
                        $student = SessionClassStudent::findOrFail($session_class_id);
                        $student->section_id =  $sectionStore_id;
                        $student->save();
                    }

                    if (empty($this->getMemberId($student_id))) {
                        $member = new Member();
                        $member->user_id = $student_id;
                        $member->name = $studentFirstName . ' ' . $parentName;
                        $member->category_id = "1";
                        $member->status = "1";
                        $member->save();
                    }

                    if (empty($this->checkIfOutstandingBalanceExist())) {
                        $rowFeesGroup = new FeesGroup();
                        $rowFeesGroup->name = "Outstanding Balance";
                        $rowFeesGroup->description = "Outstanding Balance";
                        $rowFeesGroup->status = "1";
                        $rowFeesGroup->online_admission_fees = 0;
                        $rowFeesGroup->save();
                        $fees_group_id = $rowFeesGroup->id;
                    }else{
                        $fees_group_id = $this->checkIfOutstandingBalanceExist();
                    }


                    if (empty($this->checkFeeType())) {
                        $rowFeesType = new FeesType();
                        $rowFeesType->name = "Outstanding Balance Fee";
                        $rowFeesType->code = "001";
                        $rowFeesType->description = "Outstanding Balance Fee";
                        $rowFeesType->status = "1";
                        $rowFeesType->save();
                        $fee_type_id = $rowFeesType->id;
                    }else{
                        $fee_type_id = $this->checkFeeType();
                    }

                    if (empty($this->checkFeeMaster($fees_group_id,$fee_type_id))) {
                        $rowFeesMaster = new FeesMaster();
                        $rowFeesMaster->session_id = setting('session');
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
                        $fees_master_id = $this->checkFeeMaster($fees_group_id,$fee_type_id);
                    }

                    if(empty($this->checkFeesMasterChildren($fee_type_id, $fees_master_id))){
                        $feesChield                 = new FeesMasterChildren();
                        $feesChield->fees_master_id =  $fees_master_id;
                        $feesChield->fees_type_id   = $fee_type_id;
                        $feesChield->save();
                        $feesChield_id =  $feesChield->id;
                    }else{
                        $feesChield_id = $this->checkFeesMasterChildren($fees_master_id, $fee_type_id);
                    }



                    if (empty($this->checkFeesAssign($classesStore_id,$fees_group_id,$sectionStore_id))) {
                        $rowFeesAssign = new FeesAssign();
                        $rowFeesAssign->session_id = setting('session');
                        $rowFeesAssign->classes_id = $classesStore_id;
                        $rowFeesAssign->section_id =  $sectionStore_id;
                        $rowFeesAssign->fees_group_id = $fees_group_id;
                        $rowFeesAssign->save();
                        $feesAssignId = $rowFeesAssign->id;
                    }else{
                        $feesAssignId = $this->checkFeesAssign($classesStore_id,$fees_group_id,$sectionStore_id);
                    }



                    if (empty($this->checkIfOutstandingBalanceTransportExist())) {
                        $rowFeesGroup = new FeesGroup();
                        $rowFeesGroup->name = "Outstanding Transport";
                        $rowFeesGroup->description = "Outstanding Transport";
                        $rowFeesGroup->status = "1";
                        $rowFeesGroup->online_admission_fees = 0;
                        $rowFeesGroup->save();
                        $fees_group_id_transport = $rowFeesGroup->id;
                    }else{
                        $fees_group_id_transport = $this->checkIfOutstandingBalanceTransportExist();
                    }


                    if (empty($this->checkFeeTypeTransport())) {
                        $rowFeesType = new FeesType();
                        $rowFeesType->name = "Outstanding Transport Fee";
                        $rowFeesType->code = "001";
                        $rowFeesType->description = "Outstanding Transport Fee";
                        $rowFeesType->status = "1";
                        $rowFeesType->save();
                        $fee_type_id_transport = $rowFeesType->id;
                    }else{
                        $fee_type_id_transport = $this->checkFeeTypeTransport();
                    }

                    if (empty($this->checkFeeMaster($fees_group_id_transport,$fee_type_id_transport))) {
                        $rowFeesMaster = new FeesMaster();
                        $rowFeesMaster->session_id = setting('session');
                        $rowFeesMaster->fees_group_id = $fees_group_id_transport;
                        $rowFeesMaster->fees_type_id = $fee_type_id_transport;
                        $rowFeesMaster->due_date = Date('Y-12-31');
                        $rowFeesMaster->amount = "0";
                        $rowFeesMaster->fine_type = "0";
                        $rowFeesMaster->percentage = "0";
                        $rowFeesMaster->fine_amount = "0";
                        $rowFeesMaster->status = "1";
                        $rowFeesMaster->save();
                        $fees_master_id_transport = $rowFeesMaster->id;
                    }else{
                        $fees_master_id_transport = $this->checkFeeMaster($fees_group_id_transport,$fee_type_id_transport);
                    }

                    if(empty($this->checkFeesMasterChildren($fee_type_id_transport, $fees_master_id_transport))){
                        $feesChield                 = new FeesMasterChildren();
                        $feesChield->fees_master_id =  $fees_master_id_transport;
                        $feesChield->fees_type_id   = $fee_type_id_transport;
                        $feesChield->save();
                        $feesChield_id_transport =  $feesChield->id;
                    }else{
                        $feesChield_id_transport = $this->checkFeesMasterChildren($fees_master_id_transport, $fee_type_id_transport);
                    }



                    if (empty($this->checkFeesAssign($classesStore_id,$fees_group_id_transport,$sectionStore_id))) {
                        $rowFeesAssign = new FeesAssign();
                        $rowFeesAssign->session_id = setting('session');
                        $rowFeesAssign->classes_id = $classesStore_id;
                        $rowFeesAssign->section_id =  $sectionStore_id;
                        $rowFeesAssign->fees_group_id = $fees_group_id_transport;
                        $rowFeesAssign->save();
                        $feesAssignId_transport = $rowFeesAssign->id;
                    }else{
                        $feesAssignId_transport = $this->checkFeesAssign($classesStore_id,$fees_group_id_transport,$sectionStore_id);
                    }

                    DB::commit();
                    //  $feesGroupId4 = '4';
                    //  if (empty($this->checkFeesAssign($classesStore_id,$feesGroupId4,$sectionStore_id))) {
                    //     $rowFeesAssign = new FeesAssign();
                    //     $rowFeesAssign->session_id = setting('session');
                    //     $rowFeesAssign->classes_id = $classesStore_id;
                    //     $rowFeesAssign->section_id =  $sectionStore_id;
                    //     $rowFeesAssign->fees_group_id = $feesGroupId4;
                    //     $rowFeesAssign->save();
                    //     $feesAssignId4 = $rowFeesAssign->id;
                    // }else{
                    //     $feesAssignId4 = $this->checkFeesAssign($classesStore_id,$feesGroupId4,$sectionStore_id);
                    // }

                    // $feesGroupId6 = '6';
                    //  if (empty($this->checkFeesAssign($classesStore_id,$feesGroupId6,$sectionStore_id))) {
                    //     $rowFeesAssign = new FeesAssign();
                    //     $rowFeesAssign->session_id = setting('session');
                    //     $rowFeesAssign->classes_id = $classesStore_id;
                    //     $rowFeesAssign->section_id =  $sectionStore_id;
                    //     $rowFeesAssign->fees_group_id = $feesGroupId6;
                    //     $rowFeesAssign->save();
                    //     $feesAssignId6 = $rowFeesAssign->id;
                    // }else{
                    //     $feesAssignId6 = $this->checkFeesAssign($classesStore_id,$feesGroupId6,$sectionStore_id);
                    // }

                    // $feesGroupId7 = '7';
                    //  if (empty($this->checkFeesAssign($classesStore_id,$feesGroupId7,$sectionStore_id))) {
                    //     $rowFeesAssign = new FeesAssign();
                    //     $rowFeesAssign->session_id = setting('session');
                    //     $rowFeesAssign->classes_id = $classesStore_id;
                    //     $rowFeesAssign->section_id =  $sectionStore_id;
                    //     $rowFeesAssign->fees_group_id = $feesGroupId7;
                    //     $rowFeesAssign->save();
                    //     $feesAssignId7 = $rowFeesAssign->id;
                    // }else{
                    //     $feesAssignId7 = $this->checkFeesAssign($classesStore_id,$feesGroupId7,$sectionStore_id);
                    // }

                    // $feesGroupId8 = '8';
                    //  if (empty($this->checkFeesAssign($classesStore_id,$feesGroupId8,$sectionStore_id))) {
                    //     $rowFeesAssign = new FeesAssign();
                    //     $rowFeesAssign->session_id = setting('session');
                    //     $rowFeesAssign->classes_id = $classesStore_id;
                    //     $rowFeesAssign->section_id =  $sectionStore_id;
                    //     $rowFeesAssign->fees_group_id = $feesGroupId8;
                    //     $rowFeesAssign->save();
                    //     $feesAssignId8 = $rowFeesAssign->id;
                    // }else{
                    //     $feesAssignId8 = $this->checkFeesAssign($classesStore_id,$feesGroupId8,$sectionStore_id);
                    // }

                    //   $feesGroupId9 = '9';
                    //  if (empty($this->checkFeesAssign($classesStore_id,$feesGroupId9,$sectionStore_id))) {
                    //     $rowFeesAssign = new FeesAssign();
                    //     $rowFeesAssign->session_id = setting('session');
                    //     $rowFeesAssign->classes_id = $classesStore_id;
                    //     $rowFeesAssign->section_id =  $sectionStore_id;
                    //     $rowFeesAssign->fees_group_id = $feesGroupId9;
                    //     $rowFeesAssign->save();
                    //     $feesAssignId9 = $rowFeesAssign->id;
                    // }else{
                    //     $feesAssignId9 = $this->checkFeesAssign($classesStore_id,$feesGroupId9,$sectionStore_id);
                    // }
                    
                    $feeAmountInitial = (int) str_replace(',', '', $row['fee_amount']);
                    $feeAmount = (int) $feeAmountInitial  ;
                    // $newPaid = str_replace(',', '', $row['paid_amount']);
                    $newBalance = (int) str_replace(',', '', $row['balance_amount']);
                    // if($newBalance >= $feeAmount){
                    // //$newBalance = $feeAmountInitial; 
                    // $Outstanding = $newBalance -  $feeAmount;
                    // $newPaid = 0;
                    // }else{
                     $newPaid = (int)$feeAmount - (int)$newBalance;
                     $Outstanding = 0;
                    // }
                                       
                    // $newBalance =  $feeAmount - $newPaid;
                    $row['balance'] = $newBalance;

                   if (!empty(trim($row['balance']))) {
                    if (str_replace(',', '', $row['balance'] ?? '0') > 0) {
                            if (empty($this->checkFeesAssignChildren($feesAssignId, $fees_master_id, $student_id))) {
                                $feesChield = new FeesAssignChildren();
                                $feesChield->fees_assign_id = $feesAssignId;
                                $feesChield->fees_master_id = $fees_master_id;
                                $feesChield->student_id = $student_id;
                                $feesChield->fees_amount = str_replace(',', '', $row['fee_amount'] ?? '0');
                                $feesChield->paid_amount = str_replace(',', '',  $newPaid);
                                $feesChield->remained_amount = str_replace(',', '', $row['balance_amount'] ?? '0');
                                $feesChield->outstandingbalance = str_replace(',', '', $row['balance_amount'] ?? '0');
                                $feesChield->control_number = $this->getStudentControlNumber($student_id);
                                $feesChield->save();

                        }else{
                            $feesChildrenId = $this->checkFeesAssignChildren($feesAssignId, $fees_master_id, $student_id);
                            $feesChield = FeesAssignChildren::find($feesChildrenId);
                            $feesChield->fees_assign_id = $feesAssignId;
                            $feesChield->fees_master_id = $fees_master_id;
                            $feesChield->student_id = $student_id;
                            $feesChield->fees_amount = str_replace(',', '', $row['fee_amount'] ?? '0');
                            $feesChield->paid_amount = str_replace(',', '',  $newPaid);
                            $feesChield->remained_amount = str_replace(',', '', $row['balance_amount'] ?? '0');
                            $feesChield->outstandingbalance = str_replace(',', '', $row['balance_amount'] ?? '0');
                            $feesChield->control_number = $this->getStudentControlNumber($student_id);
                            $feesChield->save();
                        }
                   }
                }



                if (!empty(trim($row['remained_amount_t']))) {
                    if (str_replace(',', '', $row['remained_amount_t'] ?? '0') > 0) {
                            if (empty($this->checkFeesAssignChildren($feesAssignId_transport, $fees_master_id_transport, $student_id))) {
                                $feesChield = new FeesAssignChildren();
                                $feesChield->fees_assign_id = $feesAssignId_transport;
                                $feesChield->fees_master_id = $fees_master_id_transport;
                                $feesChield->student_id = $student_id;
                                $feesChield->fees_amount = str_replace(',', '', $row['fees_amount_t'] ?? '0');
                                $feesChield->paid_amount = str_replace(',', '',  $row['paid_amount_t']);
                                $feesChield->remained_amount = str_replace(',', '', $row['remained_amount_t'] ?? '0');
                                $feesChield->outstandingbalance = str_replace(',', '', $row['remained_amount_t'] ?? '0');
                                $feesChield->control_number = $this->getStudentControlNumber($student_id);
                                $feesChield->save();

                        }else{
                            $feesChildrenId = $this->checkFeesAssignChildren($feesAssignId_transport, $fees_master_id_transport, $student_id);
                            $feesChield = FeesAssignChildren::find($feesChildrenId);
                            $feesChield->fees_assign_id = $feesAssignId_transport;
                            $feesChield->fees_master_id = $fees_master_id_transport;
                            $feesChield->student_id = $student_id;
                            $feesChield->fees_amount = str_replace(',', '', $row['fees_amount_t'] ?? '0');
                            $feesChield->paid_amount = str_replace(',', '',  $row['paid_amount_t']);
                            $feesChield->remained_amount = str_replace(',', '', $row['remained_amount_t'] ?? '0');
                            $feesChield->outstandingbalance = str_replace(',', '', $row['remained_amount_t'] ?? '0');
                            $feesChield->control_number = $this->getStudentControlNumber($student_id);
                            $feesChield->save();
                        }
                   }
                }

                //Transport Outstanding fees
                // $fees_master_id = '28';
                //  if (!empty(trim($row['outstanding_transport']))) {
                //     if (str_replace(',', '', $row['outstanding_transport'] ?? '0') > 0) {
                //             if (empty($this->checkFeesAssignChildren($feesAssignId9, $fees_master_id, $student_id))) {
                //                 $feesChield = new FeesAssignChildren();
                //                 $feesChield->fees_assign_id = $feesAssignId9;
                //                 $feesChield->fees_master_id = "28";
                //                 $feesChield->student_id = $student_id;
                //                 $feesChield->fees_amount = str_replace(',', '', $row['outstanding_transport'] ?? '0');
                //                 $feesChield->paid_amount = str_replace(',', '', $row['balance_transport'] ?? '0');
                //                 $feesChield->remained_amount = str_replace(',', '', $row['outstanding_remained'] ?? '0');
                //                 $feesChield->control_number = $this->getStudentControlNumber($student_id);
                //                 $feesChield->save();

                //         }else{
                //             $feesChildrenId = $this->checkFeesAssignChildren($feesAssignId9, $fees_master_id, $student_id);
                //             $feesChield = FeesAssignChildren::find($feesChildrenId);
                //             $feesChield->fees_assign_id = $feesAssignId9;
                //             $feesChield->fees_master_id = "28";
                //             $feesChield->student_id = $student_id;
                //             $feesChield->fees_amount = str_replace(',', '', $row['outstanding_transport'] ?? '0');
                //             $feesChield->paid_amount = str_replace(',', '', $row['balance_transport'] ?? '0');
                //             $feesChield->remained_amount = str_replace(',', '', $row['outstanding_remained'] ?? '0');
                //             $feesChield->control_number = $this->getStudentControlNumber($student_id);
                //             $feesChield->save();
                //         }
                //    }
                // }


                // if (!empty(trim($row['lunch']))) {
                //     if (trim($row['lunch']) !== '') {
                //             if (empty($this->checkFeesAssignChildren($feesAssignId4, "21", $student_id))) {
                //                 $feesChield = new FeesAssignChildren();
                //                 $feesChield->fees_assign_id = $feesAssignId4;
                //                 $feesChield->fees_master_id = "21";
                //                 $feesChield->student_id = $student_id;
                //                 $feesChield->fees_amount = str_replace(',', '', $row['lunch_fee'] ?? '0');
                //                 $feesChield->paid_amount = str_replace(',', '', $row['lunch_paid'] ?? '0');
                //                 $feesChield->remained_amount = str_replace(',', '', $row['lunch'] ?? '0');
                //                 $feesChield->control_number = $this->getStudentControlNumber($student_id);
                //                 $feesChield->save();

                //         }else{
                //             $feesChildrenId = $this->checkFeesAssignChildren($feesAssignId4, "21", $student_id);
                //             $feesChield = FeesAssignChildren::find($feesChildrenId);
                //             $feesChield->fees_assign_id = $feesAssignId4;
                //             $feesChield->fees_master_id = "21";
                //             $feesChield->student_id = $student_id;
                //             $feesChield->fees_amount = str_replace(',', '', $row['lunch_fee'] ?? '0');
                //             $feesChield->paid_amount = str_replace(',', '', $row['lunch_paid'] ?? '0');
                //             $feesChield->remained_amount = str_replace(',', '', $row['lunch'] ?? '0');
                //             $feesChield->control_number = $this->getStudentControlNumber($student_id);
                //             $feesChield->save();
                //         }
                //    }
                // }

                // if (!empty(trim($row['accomodationn']))) {
                //     if (trim($row['accomodationn']) !== '') {
                //             if (empty($this->checkFeesAssignChildren($feesAssignId6, "24", $student_id))) {
                //                 $feesChield = new FeesAssignChildren();
                //                 $feesChield->fees_assign_id = $feesAssignId6;
                //                 $feesChield->fees_master_id = "24";
                //                 $feesChield->student_id = $student_id;
                //                 $feesChield->fees_amount = str_replace(',', '', $row['accomodation_fee'] ?? '0');
                //                 $feesChield->paid_amount = str_replace(',', '', $row['accomodation_paid'] ?? '0');;
                //                 $feesChield->remained_amount = str_replace(',', '', $row['accomodationn'] ?? '0');;
                //                 $feesChield->control_number = $this->getStudentControlNumber($student_id);
                //                 $feesChield->save();

                //         }else{
                //             $feesChildrenId = $this->checkFeesAssignChildren($feesAssignId6, "24", $student_id);
                //             $feesChield = FeesAssignChildren::find($feesChildrenId);
                //             $feesChield->fees_assign_id = $feesAssignId6;
                //             $feesChield->fees_master_id = "24";
                //             $feesChield->student_id = $student_id;
                //             $feesChield->fees_amount = str_replace(',', '', $row['accomodation_fee'] ?? '0');
                //             $feesChield->paid_amount = str_replace(',', '', $row['accomodation_paid'] ?? '0');;
                //             $feesChield->remained_amount = str_replace(',', '', $row['accomodationn'] ?? '0');;
                //             $feesChield->control_number = $this->getStudentControlNumber($student_id);
                //             $feesChield->save();
                //         }
                //    }
                // }

                // if (!empty(trim($row['uniformm']))) {
                //     if (trim($row['uniformm']) !== '') {
                //             if (empty($this->checkFeesAssignChildren($feesAssignId7, "25", $student_id))) {
                //                 $feesChield = new FeesAssignChildren();
                //                 $feesChield->fees_assign_id = $feesAssignId7;
                //                 $feesChield->fees_master_id = "25";
                //                 $feesChield->student_id = $student_id;
                //                 $feesChield->fees_amount = str_replace(',', '', $row['uniform_fee'] ?? '0');
                //                 $feesChield->paid_amount =  str_replace(',', '', $row['uniform_paid'] ?? '0');
                //                 $feesChield->remained_amount =  str_replace(',', '', $row['uniformm'] ?? '0');
                //                 $feesChield->control_number = $this->getStudentControlNumber($student_id);
                //                 $feesChield->save();

                //         }else{
                //             $feesChildrenId = $this->checkFeesAssignChildren($feesAssignId7, "25", $student_id);
                //             $feesChield = FeesAssignChildren::find($feesChildrenId);
                //             $feesChield->fees_assign_id = $feesAssignId7;
                //             $feesChield->fees_master_id = "25";
                //             $feesChield->student_id = $student_id;
                //             $feesChield->fees_amount = str_replace(',', '', $row['uniform_fee'] ?? '0');
                //             $feesChield->paid_amount =  str_replace(',', '', $row['uniform_paid'] ?? '0');
                //             $feesChield->remained_amount =  str_replace(',', '', $row['uniformm'] ?? '0');
                //             $feesChield->control_number = $this->getStudentControlNumber($student_id);
                //             $feesChield->save();
                //         }
                //    }
                // }

                //  if (!empty(trim($row['cautionn']))) {
                //     if (trim($row['cautionn']) !== '') {
                //             if (empty($this->checkFeesAssignChildren($feesAssignId8, "26", $student_id))) {
                //                 Log::info("test1" );
                //                 Log::info($row['cautionn'] );
                //                 $feesChield = new FeesAssignChildren();
                //                 $feesChield->fees_assign_id = $feesAssignId8;
                //                 $feesChield->fees_master_id = "26";
                //                 $feesChield->student_id = $student_id;
                //                 $feesChield->fees_amount = str_replace(',', '', $row['caution_fee'] ?? '0');
                //                 $feesChield->paid_amount = str_replace(',', '', $row['caution_paid'] ?? '0');
                //                 $feesChield->remained_amount = str_replace(',', '', $row['cautionn'] ?? '0');
                //                 $feesChield->control_number = $this->getStudentControlNumber($student_id);
                //                 $feesChield->save();

                //         }else{
                            
                //             $feesChildrenId = $this->checkFeesAssignChildren($feesAssignId8, "26", $student_id);
                //             $feesChield = FeesAssignChildren::find($feesChildrenId);
                //             $feesChield->fees_assign_id = $feesAssignId8;
                //             Log::info("testid" );
                //             Log::info($feesChildrenId );
                //             Log::info($row['cautionn'] );
                //             $feesChield->fees_master_id = "26";
                //             $feesChield->student_id = $student_id;
                //             $feesChield->fees_amount = str_replace(',', '', $row['caution_fee'] ?? '0');
                //             $feesChield->paid_amount = str_replace(',', '', $row['caution_paid'] ?? '0');
                //             $feesChield->remained_amount = str_replace(',', '', $row['cautionn'] ?? '0');
                //             $feesChield->control_number = $this->getStudentControlNumber($student_id);
                //             $feesChield->save();
                //         }
                //    }
                // }

                $row['fees_amount'] = '0';
                $row['current'] = '0';
                $row['paid_amount'] = '0';
                if (!empty(trim($row['fees_amount']))) {
                    if (trim($row['current']) !== '') {

                    if (empty($this->checkFeesAssign($classesStore_id,$this->getFeeGroupId($this->getFeeTypeId($classesStore_id)), $sectionStore_id))) {
                        $rowFeesAssign = new FeesAssign();
                        $rowFeesAssign->session_id = setting('session');
                        $rowFeesAssign->classes_id = $classesStore_id;
                        $rowFeesAssign->section_id =   $sectionStore_id;
                        $rowFeesAssign->fees_group_id = $this->getFeeGroupId($this->getFeeTypeId($classesStore_id));
                        $rowFeesAssign->save();
                        $feesAssignId = $rowFeesAssign->id;
                    }else{
                        $feesAssignId = $this->checkFeesAssign($classesStore_id,$this->getFeeGroupId($this->getFeeTypeId($classesStore_id)), $sectionStore_id);
                    }
        
                    $quaters = $this->getFeeMasterAmount($this->getFeeMasterId($this->getFeeTypeId($classesStore_id)))/4;
                    if (empty($this->checkFeesAssignChildren($feesAssignId, $this->getFeeMasterId($this->getFeeTypeId($classesStore_id)), $student_id))) {
                        $feesChield = new FeesAssignChildren();
                        $feesChield->fees_assign_id = $feesAssignId;
                        $feesChield->fees_master_id = $this->getFeeMasterId($this->getFeeTypeId($classesStore_id));
                        $feesChield->student_id = $student_id;
                        // $feesChield->fees_amount = $this->getFeeMasterAmount($this->getFeeMasterId($this->getFeeTypeId($classesStore_id)));
                        // $feesChield->paid_amount = '0';
                        // $feesChield->remained_amount = $this->getFeeMasterAmount($this->getFeeMasterId($this->getFeeTypeId($classesStore_id)));
                        // $feesChield->quater_one = $quaters;
                        // $feesChield->quater_two = $quaters;
                        // $feesChield->quater_three = $quaters;
                        // $feesChield->quater_four = $quaters;
                        $feesChield->fees_amount = $row['fees_amount']??'0';
                        $feesChield->paid_amount = $row['paid_amount']??'0';
                        $feesChield->remained_amount = $row['current']??'0';
                        // $feesChield->quater_one = !empty($row['quater_one']) && $row['quater_one'] != "" ? $row['quater_one'] : NULL;
                        // $feesChield->quater_two = !empty($row['quater_two']) && $row['quater_two'] != "" ? $row['quater_two'] : NULL;
                        // $feesChield->quater_three = !empty($row['quater_three']) && $row['quater_three'] != "" ? $row['quater_three'] : NULL;
                        // $feesChield->quater_four = !empty($row['quater_four']) && $row['quater_four'] != "" ? $row['quater_four'] : NULL;
                        $feesChield->control_number = $this->getStudentControlNumber($student_id);
                        $feesChield->save();
        
                }else{
                    $feesChildrenId = $this->checkFeesAssignChildren($feesAssignId, $this->getFeeMasterId($this->getFeeTypeId($classesStore_id)), $student_id);
                    $feesChield = FeesAssignChildren::find($feesChildrenId);
                    $feesChield->fees_assign_id = $feesAssignId;
                    $feesChield->fees_master_id = $this->getFeeMasterId($this->getFeeTypeId($classesStore_id));
                    $feesChield->student_id = $student_id;
                    $feesChield->fees_amount = $row['fees_amount']??'0';
                    $feesChield->paid_amount = $row['paid_amount']??'0';
                    $feesChield->remained_amount = $row['current']??'0';
                    $feesChield->save();
                }

               
        
                //Assign transportation automatically
                $feeTypeId = 0;
                $feeAssignId = DB::select('SELECT id FROM fees_assigns WHERE classes_id = ? AND
                 session_id = ? AND fees_group_id = ?',
                 [ $classesStore_id,setting('session'),"3"]);
                if (empty($feeAssignId)) {
                    $row                = new FeesAssign();
                    $row->session_id    = setting('session');
                    $row->classes_id      =  $classesStore_id;
                    $row->section_id    = $sectionStore_id;
                    $row->fees_group_id = "3";
                    $row->save();
                    $feeAssignId = $row->id;
                }else{
                    $feeAssignId = DB::select('SELECT id FROM fees_assigns WHERE classes_id = ? AND session_id = ? AND fees_group_id = ?',
                     [$classesStore_id,setting('session'),"3"])[0]->id;
                }
        
        
                $transportProfile = DB::select("
                SELECT student_categories.name 
                        FROM student_categories
                        INNER JOIN students ON students.student_category_id = student_categories.id
                        WHERE students.id = ?
                    ", [$student_id]);
            
                    $row['fees_amount_t'] = 0;
                    $row['remained_amount_t'] = 0;
                    $row['paid_amount_t'] = 0;
                    if(!empty($transportProfile)){
                        if($row['fees_amount_t'] != NULL){
                        $transportProfile =$transportProfile[0]->name;
                    // Split transport profile based on 'DAY'
                    $transportProfileParts = explode(' DAY ', $transportProfile);
                    if (!empty($transportProfileParts[1])) {
                        // Get fee type ID based on transport profile part
                        $feeTypeId = DB::select("
                            SELECT id 
                            FROM fees_types 
                            WHERE code = ?
                        ", [trim($transportProfileParts[1])])[0]->id;
        
                        // Get fees master ID based on fee type ID
                        $fees_master = DB::select("
                            SELECT id 
                            FROM fees_masters 
                            WHERE fees_type_id = ?
                        ", [$feeTypeId])[0]->id;
                    }
        
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
                              Log::info('Fees transport'.$row['fees_amount_t']);
                                $feesChild = new FeesAssignChildren();
                                $feesChild->fees_assign_id = $feeAssignId;
                                $feesChild->fees_master_id = $fees_master;
                                $feesChild->student_id = $student_id;
                                // $feesChild->fees_amount = $this->getFeesAmount($fees_master);
                                 $feesChild->fees_amount = str_replace(',', '', $row['fees_amount_t'] ?? '0') ;
                                $feesChild->remained_amount =  str_replace(',', '', $row['remained_amount_t'] ?? '0');
                                $feesChild->paid_amount = str_replace(',', '', $row['paid_amount_t'] ?? '0') ;
                                // $feesChild->remained_amount = $this->getFeesAmount($fees_master);
                                $feesChild->control_number = $controlNumber;
            
                                // Divide fees into quarters if due date allows
                                if ($this->getDueDate($fees_master) > 8) {
                                    // $quarterAmount = $this->getFeesAmount($fees_master) / 4;
                                    $quarterAmount = $row['fees_amount_t'] / 4;
                                    $feesChild->quater_one = $quarterAmount;
                                    $feesChild->quater_two = $quarterAmount;
                                    $feesChild->quater_three = $quarterAmount;
                                    $feesChild->quater_four = $quarterAmount;
                                }
            
                                $feesChild->save();
                                $feesId = $feesChild->id;
                                $months = DB::table('months_list')->get();
                        foreach($months as $month){
                            TransportMonth::create([
                                'student_id' => $student_id,
                                'fee_assign_children_id' => $feesId,
                                'user_id' => Auth::id(),
                                'month' => $month->id,
                                'amount' => $row['fees_amount_t']/10,
                                'status' => '1',
                                'state' => '1'
                            ]);
                        }
                           
                            } else {
                                return $this->responseWithError('Fees has already been assigned', []);
                            }
                        }else{
                            $feeAssignChild = collect($feeAssignChildren)->first();
                           $feesChield = FeesAssignChildren::find($feeAssignChild->id);
                            $feesChield->fees_amount = str_replace(',', '', $row['fees_amount_t'] ?? '0') ;
                                $feesChield->remained_amount =  str_replace(',', '', $row['remained_amount_t'] ?? '0');
                                $feesChield->paid_amount = str_replace(',', '', $row['paid_amount_t'] ?? '0') ;
                                 $feesChield->save();
                                    $feesId = $feesChield->id;
                                $months = DB::table('months_list')->get();
                        foreach($months as $month){
                            TransportMonth::create([
                                'student_id' => $student_id,
                                'fee_assign_children_id' => $feesId,
                                'user_id' => Auth::id(),
                                'month' => $month->id,
                                'amount' => $row['fees_amount_t']/10,
                                'status' => '1',
                                'state' => '1'
                            ]);
                        } 
                        }
                    }
                }}//here
                }
            }

                if (str_contains($row['category'], "BOARDING")) {
                    $feeAmount = $row['fees_amount']; // Assign as an integer
                    
                    // Fetch the ID
                    $result = DB::select("SELECT fees_assign_childrens.id FROM fees_assign_childrens 
                                          INNER JOIN fees_assigns 
                                          ON fees_assign_childrens.fees_assign_id = fees_assigns.id 
                                          WHERE fees_assigns.fees_group_id = ? and fees_assign_childrens.student_id = ? ", [2, $student_id ]);
                
                    // Ensure there's a result
                    if (!empty($result)) {
                        $id = $result[0]->id; // Extract the first ID
                        
                        // Find the record and update it
                        $feesChield = FeesAssignChildren::findOrFail($id);
                        $feesChield->fees_master_id = 20; // Update the fees_master_id
                         $feesChield->fees_amount = $row['fees_amount']??'0';
                        $feesChield->paid_amount = $row['paid_amount']??'0';
                        $feesChield->remained_amount = $row['current']??'0';
                        $feesChield->quater_one = $feeAmount / 4;
                        $feesChield->quater_two = $feeAmount / 4;
                        $feesChield->quater_three = $feeAmount / 4;
                        $feesChield->quater_four = $feeAmount / 4;
                        $feesChield->save();
                    } else {
                        // Handle the case where no records are found
                        // throw new Exception("No records found for the specified fees_group_id.");
                    }
                }
                  

                }else{
                    return $this->responseWithSuccess(___('alert.created_successfully'), []);
                }
            }

            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollback();
            dd($th);
            Log::error($th);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function updateStudentFees($request)
    {
        DB::beginTransaction();
        try {

        
            $data = Excel::toArray(new StudentsImport, $request->file('document_files'));
            Log::alert("Start Students Names That are nnot available on system");
            foreach ($data[0] as $row) {
                if (empty(array_filter($row))) {
                    return $this->responseWithSuccess(___('alert.something_went_wrong_please_try_again'), []);
                }
                if (!empty($row)) {
                    $firstStudentName = $row['student_name'];
                    $class = $row['class'];
                    $section = $row['section']??'A';
                     $row['category'] = $row['category'] ?? 'Day';
                    $firstName = $row['student_name'];
                    $firstName = preg_replace('/\s+/', ' ', trim($firstName));
                    $firstNameParts = explode(' ', $firstName);
                    $studentFirstName = $firstNameParts[0];
                    $studentSecondName = $firstNameParts[1];
                    $lastNameToUse = $studentSecondName;
                    $parentName = $studentSecondName;
                    if (isset($firstNameParts[2]) && !empty($firstNameParts[2])) {
                        $studentMiddleName = $firstNameParts[2];
                        $lastNameToUse .= $studentMiddleName;
                        $parentName = $studentSecondName ." ". $studentMiddleName;
                    }
                    $parentEmail = strtolower(!empty($row['email']) ? $row['email'] : (trim($studentFirstName) . trim($lastNameToUse) . '@gmail.com'));

                    $parent_id = $this->getParentId(trim($parentEmail));
                    $classesStore_id = $this->getClassId($row['class']);

                    //  if (empty($this->getSectionId(trim($row['section'])))) {
                    //     $sectionStore = new Section();
                    //     $sectionStore->name = $row['section'];
                    //     $sectionStore->status = "1";
                    //     $sectionStore->save();
                    //     $sectionStore_id = $sectionStore->id;
                    // } else {
                    //     $sectionStore_id = $this->getSectionId(trim($row['section']));
                    // }

                 
                    // if(empty($this->getStudentId($parent_id,$classesStore_id))){
                    if (empty($this->getStudentIdByName($firstStudentName,$class,$section))) {
                        Log::alert("Unregistered student ".$firstStudentName);
                        // $student = new $this->model;
                        // $student->user_id = $user_id;
                        // $student->first_name = $studentFirstName;
                        // $student->last_name = $parentName;
                        // $student->admission_no = $row['admission_no']??NULL;
                        // $student->roll_no = $this->generateUniqueTrackingNumber();
                        // $student->mobile = $row['phone_number'] ?? NULL;
                        // $student->email = strtolower($row['email'] ?? (trim($studentFirstName) . trim($lastNameToUse) . '@gmail.com'));
                        // // $student->religion_id = $religionStore_id ?? NULL;
                        // $gender_id = $this->getGenderId($row['gender']);
                        // $student->gender_id = $gender_id;
                        // $student->admission_date = $row['admission_date'] ?? "2025-01-01";
                        // $student->parent_guardian_id = $parent_id;
                        // $student->status = "1";
                        // $student->control_number = "00".$this->generateUniquecONTROLNumber();
                        // $student->save();
                        // $student_id = $student->id;

                    } else {
                        $student_id = $this->getStudentIdByName($firstStudentName,$class,$section);
                        Log::info("Student info ".$student_id." name ".$firstStudentName);
                        // $student_id = $this->getStudentId($parent_id,$classesStore_id);
                        $student = Student::findOrFail($student_id);
                        // $student->student_category_id =  $category_id;
                        // $student->category_id =  $category_id;
                        // $student->control_number = $row['reference_no'];
                        $student->save();
                    //       if (empty($this->getSessionClassStudent($classesStore_id, setting('session'), $student_id))) {

                    //     $session_class = new SessionClassStudent();
                    //     $session_class->session_id = setting('session');
                    //     $session_class->classes_id = $classesStore_id;
                    //     $session_class->section_id = $sectionStore_id;
                    //     $session_class->student_id = $student_id;
                    //     $session_class->save();
                    //     $session_class_id = $session_class->id;
                    // } else {
                    //     $session_class_id = $this->getSessionClassStudent($classesStore_id, setting('session'), $student_id);
                    //     $student = SessionClassStudent::findOrFail($session_class_id);
                    //     $student->section_id =  $sectionStore_id;
                    //     $student->save();
                    // }

                        $studentClass = DB::select('SELECT classes_id FROM session_class_students WHERE student_id = ?', [$student_id]);
                        if (!empty($studentClass)) {
                            $studentClassId = $studentClass[0]->classes_id;
                         Log::info("Student info ".$student_id." name ".$firstStudentName ." class ".$studentClassId );

                        $Outstanding = 0;
                        // if(false){
                            if ($request->document_type == 1) {
                                $feesMaster = DB::select('
                                    SELECT fees_masters.id FROM fees_masters
                                    INNER JOIN fees_types ON fees_types.id = fees_masters.fees_type_id
                                    WHERE fees_types.class_id = ?', [$studentClassId]);
                                    $feeAssignId = DB::select('SELECT id FROM fees_assigns WHERE classes_id = ? AND session_id = ? AND fees_group_id = ?',
                                     [$studentClassId,setting('session'),"2"])[0]->id;
                        
                                if (!empty($feesMaster)) {
                                    $feesMasterId = $feesMaster[0]->id;
                        
                                    // ✅ First: fetch existing fee record before update
                                    $existingFee = DB::select('SELECT * FROM fees_assign_childrens WHERE student_id = ? AND fees_master_id = ?', [
                                        $student_id, $feesMasterId
                                    ]);
                                   
                                        $feeAmountInitial = (int) str_replace(',', '', $row['fee_amount']) ;
                                        $feeAmount = (int) $feeAmountInitial  ;
                                        // $newPaid = str_replace(',', '', $row['paid_amount']);
                                        $newBalance = (int) str_replace(',', '', $row['balance_amount']);
                                        if($newBalance >= $feeAmount){
                                        //    $newBalance = $feeAmountInitial; 
                                           $Outstanding = $newBalance -  $feeAmount;
                                           $newPaid = 0;
                                        }else{
                                             $newPaid = (int)$feeAmount - (int)$newBalance;
                                        }
                                       
                                        $newBalance =  $feeAmount - $newPaid;
                        
                                    if (is_array($existingFee) && !empty($existingFee[0])) {
                                         Log::info("Student info ".$student_id." name 
                                    ".$firstStudentName ." class ".$studentClassId ." existingfee ".$existingFee[0]->id );
                                       
                                        $currentPaid = str_replace(',', '', $existingFee[0]->paid_amount);
                                        $currentRemainedPaid = str_replace(',', '', $existingFee[0]->remained_amount);
                                     
                                        // $amount = $newPaid - $currentPaid;
                                       
                                        // if ($newPaid > $currentPaid) {
                                            // $amount = str_replace(',', '', $amount);
                                            // $student->admission_no =  $row['admission_no'];
                                            // $student->control_number = $row['reference_no'];
                                            // $student->save();
                                            // $row                   = new FeesCollect();
                                            // $row->date             = now();
                                            // $row->fees_assign_children_id   = $existingFee[0]->id;
                                            // $row->amount           = $amount ?? 0;
                                            // // $row->fine_amount      = $request->fine_amounts[$key];
                                            // $row->fees_collect_by  = Auth::user()->id;
                                            // $row->transaction_id = $this->generateUniqueTrackingNumber();
                                            // $row->student_id       = $existingFee[0]->student_id;
                                            // $row->account_id       = "1";
                                            // $row->comments       = ''??NULL;
                                            // $row->session_id       = setting('session');
                                            // $row->save();

                                            // $incomeStore                   = new Income();
                                            // $incomeStore->fees_collect_id  = $row->id;
                                            // $incomeStore->name             = $existingFee[0]->id;
                                            // $incomeStore->session_id       = setting('session'); 
                                            // $incomeStore->income_head      = 1; // Because, Fees id 1.
                                            // $incomeStore->date             = now();
                                            // $incomeStore->amount           = $amount;
                                            // $incomeStore->account_number    = '1';
                                            // $incomeStore->save();
                                            // Log::info("Amount ". $amount);
                                            // $this->updateFeesAssigned($existingFee[0]->id,$amount,"2");

                                        // ✅ Insert into fees_assign_childrens_history
                                        // DB::table('fees_assign_childrens_history')->insert([
                                        //     'student_id'     => $existingFee[0]->student_id,
                                        //     'fees_master_id' => $existingFee[0]->id,
                                        //     'paid_amount'    => $currentPaid,
                                        //     'remained_amount' => $currentRemainedPaid,
                                        //     'created_at'     => now(),
                                        //     'updated_at'     => now(),
                                        // ]);
                        
                                        // ✅ Then update main table
                                        Log::info("fee amount".$feeAmount." student id ". $student_id." existing fee ".$existingFee[0]->id);
                                        DB::update('UPDATE fees_assign_childrens SET fees_amount = ?, paid_amount = ?,remained_amount = ?
                                         WHERE student_id = ? AND id = ?', [
                                            $feeAmount,$newPaid,$newBalance, $student_id, $existingFee[0]->id
                                        ]);
                                    //     $description = $row['description'];
                                    //      $phone_number = $row['phone_number']??'00';
                                    //      $date = date('Y-m-d');
                                    //      $user_id = '0';
                                    // DB::insert('INSERT INTO amendments (fees_assign_id,description,parent_name,phonenumber,date,user_id) VALUES (?,?,?,?,?,?)', [$feesMasterId,$description,$parent,$phone_number,$date,$user_id]);
                                    // }
                                    }else{
                          Log::info("fee amount".$feeAmount." student id ". $student_id." duplication");


                                
                                $controlNumber = $this->getStudentControlNumber($student_id);
                                if (!empty($controlNumber)) {
                                    $feesChild = new FeesAssignChildren();
                                    $feesChild->fees_assign_id = $feeAssignId;
                                    $feesChild->fees_master_id = $feesMasterId;
                                    $feesChild->student_id = $student_id;
                                    $feesChild->fees_amount = $feeAmount;
                                    $feesChild->remained_amount = $newBalance;
                                    $feesChild->paid_amount = $newPaid;
                                    $feesChild->control_number = $controlNumber;
                
                                    // Divide fees into quarters if due date allows
                                    // if ($this->getDueDate($feesMasterId) > 8) {
                                        $quarterAmount = $feeAmount / 4;
                                        $feesChild->quater_one = $quarterAmount;
                                        $feesChild->quater_two = $quarterAmount;
                                        $feesChild->quater_three = $quarterAmount;
                                        $feesChild->quater_four = $quarterAmount;
                                    // }
                
                                    $feesChild->save();
                            }
                                    }
                                }
                            } else if ($request->document_type == 2)  {
                                $feesMasterId = 11; 
                                $existingFee = DB::select('SELECT * FROM fees_assign_childrens WHERE student_id = ? AND fees_master_id = ?', [
                                    $student_id, $feesMasterId
                                ]);
                        
                                if (!empty($existingFee)) {
                                     $feeAmountInitial = (int) str_replace(',', '', $row['fee_amount']) ;
                                        $feeAmount = (int) $feeAmountInitial * 2 ;
                                        // $newPaid = str_replace(',', '', $row['paid_amount']);
                                        $newBalance = (int) str_replace(',', '', $row['balance_amount']);
                                        if($newBalance >= $feeAmount){
                                        //    $newBalance = $feeAmountInitial; 
                                           $Outstanding = $newBalance -  $feeAmount;
                                           $newPaid = 0;
                                        }else{
                                             $newPaid = (int)$feeAmount - (int)$newBalance;
                                        }
                                       
                                        $newBalance =  $feeAmount - $newPaid;
                                    $currentPaid = str_replace(',', '', $existingFee[0]->paid_amount);
                                    $currentRemainedPaid = str_replace(',', '', $existingFee[0]->remained_amount);
                                    // $newPaid = str_replace(',', '', $row['paid_amount']);
                                    $feeAmount = str_replace(',', '', $row['fee_amount']);
                                    $newBalance = str_replace(',', '', $row['balance_amount']);
                                    // $amount = $newPaid - $currentPaid;
                                    
                                    // if ($newPaid > $currentPaid) {
                                        // $amount = str_replace(',', '', $amount);
                                        // // $student->admission_no =  $row['admission_no'];
                                        // // $student->control_number = $row['reference_no'];
                                        // // $student->save();
                                        // $row                   = new FeesCollect();
                                        // $row->date             = now();
                                        // $row->fees_assign_children_id   = $existingFee[0]->id;
                                        // $row->amount           = $amount ?? 0;
                                        // // $row->fine_amount      = $request->fine_amounts[$key];
                                        // $row->fees_collect_by  = Auth::user()->id;
                                        // $row->transaction_id = $this->generateUniqueTrackingNumber();
                                        // $row->student_id       = $existingFee[0]->student_id;
                                        // $row->account_id       = "1";
                                        // $row->comments       = ''??NULL;
                                        // $row->session_id       = setting('session');
                                        // $row->save();

                                        // $incomeStore                   = new Income();
                                        // $incomeStore->fees_collect_id  = $row->id;
                                        // $incomeStore->name             = $existingFee[0]->id;
                                        // $incomeStore->session_id       = setting('session'); 
                                        // $incomeStore->income_head      = 1; // Because, Fees id 1.
                                        // $incomeStore->date             = now();
                                        // $incomeStore->amount           = $amount;
                                        // $incomeStore->account_number    = '1';
                                        // $incomeStore->save();

                                    //     $this->updateFeesAssigned($existingFee[0]->id,$amount,"1");
                                    // DB::table('fees_assign_childrens_history')->insert([
                                    //     'student_id'     => $existingFee[0]->student_id,
                                    //     'fees_master_id' => $existingFee[0]->id,
                                    //     'paid_amount'    => $currentPaid,
                                    //     'remained_amount' => $currentRemainedPaid,
                                    //     'created_at'     => now(),
                                    //     'updated_at'     => now(),
                                    // ]);

                                    DB::update('UPDATE fees_assign_childrens SET fees_amount = ?,paid_amount = ?,remained_amount = ?
                                     WHERE student_id = ? AND id = ?', [
                                        $feeAmount,$newPaid,$newBalance, $student_id, $existingFee[0]->id
                                    ]);
                                // }
                            }
                        
                                // Then update
                               
                            }else  {
                                $feesMasterId = 19; // fallback default
                        
                                // Save existing data to history
                                $existingFee = DB::select('SELECT * FROM fees_assign_childrens WHERE student_id = ? AND fees_master_id = ?', [
                                    $student_id, $feesMasterId
                                ]);
                        
                                if (!empty($existingFee)) {
                                     $feeAmountInitial = (int) str_replace(',', '', $row['fee_amount']) ;
                                        $feeAmount = (int) $feeAmountInitial * 2 ;
                                        // $newPaid = str_replace(',', '', $row['paid_amount']);
                                        $newBalance = (int) str_replace(',', '', $row['balance_amount']);
                                        if($newBalance >= $feeAmount){
                                        //    $newBalance = $feeAmountInitial; 
                                           $Outstanding = $newBalance -  $feeAmount;
                                           $newPaid = 0;
                                        }else{
                                             $newPaid = (int)$feeAmount - (int)$newBalance;
                                        }
                                       
                                        $newBalance =  $feeAmount - $newPaid;
                                    $currentPaid = str_replace(',', '', $existingFee[0]->paid_amount);
                                    $currentRemainedPaid = str_replace(',', '', $existingFee[0]->remained_amount);
                                    // $newPaid = str_replace(',', '', $row['paid_amount']);
                                    // $feeAmount = str_replace(',', '', $row['fee_amount']);
                                    // $newBalance = str_replace(',', '', $row['balance_amount']);
                                    // $amount = $newPaid - $currentPaid;
                                    
                                    // if ($newPaid > $currentPaid) {
                                        // $amount = str_replace(',', '', $amount);
                                        // // $student->admission_no =  $row['admission_no'];
                                        // // $student->control_number = $row['reference_no'];
                                        // // $student->save();
                                        // $row                   = new FeesCollect();
                                        // $row->date             = now();
                                        // $row->fees_assign_children_id   = $existingFee[0]->id;
                                        // $row->amount           = $amount ?? 0;
                                        // // $row->fine_amount      = $request->fine_amounts[$key];
                                        // $row->fees_collect_by  = Auth::user()->id;
                                        // $row->transaction_id = $this->generateUniqueTrackingNumber();
                                        // $row->student_id       = $existingFee[0]->student_id;
                                        // $row->account_id       = "1";
                                        // $row->comments       = ''??NULL;
                                        // $row->session_id       = setting('session');
                                        // $row->save();

                                        // $incomeStore                   = new Income();
                                        // $incomeStore->fees_collect_id  = $row->id;
                                        // $incomeStore->name             = $existingFee[0]->id;
                                        // $incomeStore->session_id       = setting('session'); 
                                        // $incomeStore->income_head      = 1; // Because, Fees id 1.
                                        // $incomeStore->date             = now();
                                        // $incomeStore->amount           = $amount;
                                        // $incomeStore->account_number    = '1';
                                        // $incomeStore->save();

                                    //     $this->updateFeesAssigned($existingFee[0]->id,$amount,"1");
                                    // DB::table('fees_assign_childrens_history')->insert([
                                    //     'student_id'     => $existingFee[0]->student_id,
                                    //     'fees_master_id' => $existingFee[0]->id,
                                    //     'paid_amount'    => $currentPaid,
                                    //     'remained_amount' => $currentRemainedPaid,
                                    //     'created_at'     => now(),
                                    //     'updated_at'     => now(),
                                    // ]);

                                    DB::update('UPDATE fees_assign_childrens SET fees_amount = ?,paid_amount = ?,remained_amount = ?
                                     WHERE student_id = ? AND id = ?', [
                                        $feeAmount,$newPaid,$newBalance, $student_id, $existingFee[0]->id
                                    ]);
                                // }
                            } else {
                                 $feeAmountInitial = (int) str_replace(',', '', $row['fee_amount']) ;
                                        $feeAmount = (int) $feeAmountInitial * 2 ;
                                        // $newPaid = str_replace(',', '', $row['paid_amount']);
                                        $newBalance = (int) str_replace(',', '', $row['balance_amount']);
                                        if($newBalance >= $feeAmount){
                                        //    $newBalance = $feeAmountInitial; 
                                           $Outstanding = $newBalance -  $feeAmount;
                                           $newPaid = 0;
                                        }else{
                                             $newPaid = (int)$feeAmount - (int)$newBalance;
                                        }
                                       
                                        $newBalance =  $feeAmount - $newPaid;

                                $feeAssignId = DB::select('SELECT id FROM fees_assigns WHERE classes_id = ? AND session_id = ? AND fees_group_id = ?',
                     [$studentClassId,setting('session'),"3"])[0]->id;
                                  $controlNumber = $this->getStudentControlNumber($student_id);
                                if (!empty($controlNumber)) {
                                    $feesChild = new FeesAssignChildren();
                                    $feesChild->fees_assign_id = $feeAssignId;
                                    $feesChild->fees_master_id = $feesMasterId;
                                    $feesChild->student_id = $student_id;
                                    $feesChild->fees_amount = $feeAmount;
                                    $feesChild->remained_amount = $newBalance;
                                    $feesChild->paid_amount = $newPaid;
                                    $feesChild->control_number = $controlNumber;
                
                                    // Divide fees into quarters if due date allows
                                    if ($this->getDueDate($feesMasterId) > 8) {
                                        $quarterAmount = $feeAmount / 4;
                                        $feesChild->quater_one = $quarterAmount;
                                        $feesChild->quater_two = $quarterAmount;
                                        $feesChild->quater_three = $quarterAmount;
                                        $feesChild->quater_four = $quarterAmount;
                                    }
                
                                    $feesChild->save();
                            }
                            }
                        
                                // Then update
                               
                            }
                        }

                            if($Outstanding > 0){
                                 $feesMasterId = 11; // fallback default
                          $feeAssignId = DB::select('SELECT id FROM fees_assigns WHERE classes_id = ? AND session_id = ? AND fees_group_id = ?',
                     [$studentClassId,setting('session'),"1"])[0]->id;
                                // Save existing data to history
                                $existingFee = DB::select('SELECT * FROM fees_assign_childrens WHERE student_id = ? AND fees_master_id = ?', [
                                    $student_id, $feesMasterId
                                ]);
                                    $newPaid = 0;
                                    $feeAmount = $Outstanding;
                                    $newBalance = $Outstanding;
                                if (!empty($existingFee)) {
                                    $currentPaid = str_replace(',', '', $existingFee[0]->paid_amount);
                                    $currentRemainedPaid = str_replace(',', '', $existingFee[0]->remained_amount);
                                    
                                    $amount = $newPaid - $currentPaid;
                                    
                                    // if ($newPaid > $currentPaid) {
                                        // $amount = str_replace(',', '', $amount);
                                        // // $student->admission_no =  $row['admission_no'];
                                        // // $student->control_number = $row['reference_no'];
                                        // // $student->save();
                                        // $row                   = new FeesCollect();
                                        // $row->date             = now();
                                        // $row->fees_assign_children_id   = $existingFee[0]->id;
                                        // $row->amount           = $amount ?? 0;
                                        // // $row->fine_amount      = $request->fine_amounts[$key];
                                        // $row->fees_collect_by  = Auth::user()->id;
                                        // $row->transaction_id = $this->generateUniqueTrackingNumber();
                                        // $row->student_id       = $existingFee[0]->student_id;
                                        // $row->account_id       = "1";
                                        // $row->comments       = ''??NULL;
                                        // $row->session_id       = setting('session');
                                        // $row->save();

                                        // $incomeStore                   = new Income();
                                        // $incomeStore->fees_collect_id  = $row->id;
                                        // $incomeStore->name             = $existingFee[0]->id;
                                        // $incomeStore->session_id       = setting('session'); 
                                        // $incomeStore->income_head      = 1; // Because, Fees id 1.
                                        // $incomeStore->date             = now();
                                        // $incomeStore->amount           = $amount;
                                        // $incomeStore->account_number    = '1';
                                        // $incomeStore->save();

                                    //     $this->updateFeesAssigned($existingFee[0]->id,$amount,"1");
                                    // DB::table('fees_assign_childrens_history')->insert([
                                    //     'student_id'     => $existingFee[0]->student_id,
                                    //     'fees_master_id' => $existingFee[0]->id,
                                    //     'paid_amount'    => $currentPaid,
                                    //     'remained_amount' => $currentRemainedPaid,
                                    //     'created_at'     => now(),
                                    //     'updated_at'     => now(),
                                    // ]);

                                    DB::update('UPDATE fees_assign_childrens SET fees_amount = ?,paid_amount = ?,remained_amount = ?
                                     WHERE student_id = ? AND fees_master_id = ?', [
                                        $feeAmount,$newPaid,$newBalance, $student_id, $feesMasterId
                                    ]);
                                // }
                            }else{
                                  $controlNumber = $this->getStudentControlNumber($student_id);
                                if (!empty($controlNumber)) {
                                    $feesChild = new FeesAssignChildren();
                                    $feesChild->fees_assign_id = $feeAssignId;
                                    $feesChild->fees_master_id = $feesMasterId;
                                    $feesChild->student_id = $student_id;
                                    $feesChild->fees_amount = $feeAmount;
                                    $feesChild->remained_amount = $newBalance;
                                    $feesChild->paid_amount = $newPaid;
                                    $feesChild->control_number = $controlNumber;
                
                                    // Divide fees into quarters if due date allows
                                    if ($this->getDueDate($feesMasterId) > 8) {
                                        $quarterAmount = $feeAmount / 4;
                                        $feesChild->quater_one = $quarterAmount;
                                        $feesChild->quater_two = $quarterAmount;
                                        $feesChild->quater_three = $quarterAmount;
                                        $feesChild->quater_four = $quarterAmount;
                                    }
                
                                    $feesChild->save();
                            }
                            }
                            }
                        }
                    // }

               
                    DB::commit();
                    // DB::statement("CALL UpdateFeesAmount()");
                    // DB::statement("CALL UpdateQuarters()");
                    

                }else{
                    Log::alert("End Of Students Names That are nnot available on system");
                    return $this->responseWithSuccess(___('alert.something_went_wrong_please_try_again'), []);
                }
            }
            Log::alert("End Of Students Names That are nnot available on system");
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollback();
            dd($th);
            Log::error($th);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    /**
     * Upload handler for Excel Format 2 (class assignment only, no fees assignment).
     */
    private function uploadFormatTwo($request)
    {
        DB::beginTransaction();
        try {
            $data = Excel::toArray(new StudentsImport, $request->file('document_files'));

            foreach ($data[0] as $row) {
                if (empty(array_filter($row))) {
                    continue;
                }

                $studentName = trim((string) $this->getRowValue($row, ['student_name', 'studentname', 'name'], ''));
                if ($studentName === '') {
                    continue;
                }

                $className = trim((string) $this->getRowValue($row, ['class'], ''));
                $sectionName = trim((string) $this->getRowValue($row, ['stream_cc', 'stream_c', 'stream', 'section'], 'A'));
                $genderName = trim((string) $this->getRowValue($row, ['gender'], 'Male'));
                $admissionNo = trim((string) $this->getRowValue($row, ['admission_no', 'admission', 'admission_1'], ''));
                $parentName = trim((string) $this->getRowValue($row, ['parent', 'parent_name', 'guardian_name'], ''));
                $phoneNumber = trim((string) $this->getRowValue($row, ['parent_mobile', 'phone_number', 'phone'], ''));
                $parentEmailRaw = trim((string) $this->getRowValue($row, ['parent_em', 'parent_email', 'email'], ''));
                $categoryName = trim((string) $this->getRowValue($row, ['category'], 'Day'));

                $nameParts = preg_split('/\s+/', preg_replace('/\s+/', ' ', $studentName));
                $studentFirstName = $nameParts[0] ?? $studentName;
                $studentLastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : $studentFirstName;
                if ($parentName === '') {
                    $parentName = $studentLastName;
                }

                $normalizedPhone = $this->normalizePhoneNumber($phoneNumber);
                $safeEmailBase = strtolower(trim($studentFirstName . $studentLastName));
                $safeEmailBase = preg_replace('/[^a-z0-9]/', '', $safeEmailBase);
                $parentEmail = strtolower($parentEmailRaw !== '' ? $parentEmailRaw : ($safeEmailBase . '@gmail.com'));

                // Class
                if ($className === '') {
                    continue;
                }
                if (empty($this->getClassId($className))) {
                    $classesStore = new Classes();
                    $classesStore->name = $className;
                    $classesStore->status = "1";
                    $classesStore->orders = "0";
                    $classesStore->save();
                    $classesStore_id = $classesStore->id;
                } else {
                    $classesStore_id = $this->getClassId($className);
                }

                // Category
                if (empty($this->getStudentCategory($categoryName))) {
                    $category = new StudentCategory();
                    $category->name = $categoryName;
                    $category->status = "1";
                    $category->save();
                    $category_id = $category->id;
                } else {
                    $category_id = $this->getStudentCategory($categoryName);
                }

                // Setup and section
                if (empty($this->getClassSetupId($classesStore_id, setting('session')))) {
                    $setup = new ClassSetup();
                    $setup->session_id = setting('session');
                    $setup->classes_id = $classesStore_id;
                    $setup->save();
                    $setup_id = $setup->id;
                } else {
                    $setup_id = $this->getClassSetupId($classesStore_id, setting('session'));
                }

                if (empty($this->getSectionId($sectionName))) {
                    $sectionStore = new Section();
                    $sectionStore->name = $sectionName;
                    $sectionStore->status = "1";
                    $sectionStore->save();
                    $sectionStore_id = $sectionStore->id;
                } else {
                    $sectionStore_id = $this->getSectionId($sectionName);
                }

                if (empty($this->getClassSetupChildren($setup_id, $sectionStore_id))) {
                    $section = new ClassSetupChildren();
                    $section->class_setup_id = $setup_id;
                    $section->section_id = $sectionStore_id;
                    $section->save();
                }

                // Parent / user — reuse by phone; never duplicate User or ParentGuardian
                $existingParentId = $this->getParentIdByPhone($phoneNumber);
                $userIdByPhone = $this->getUserIdByPhone($phoneNumber);
                $user_id = '';

                if (!empty($existingParentId)) {
                    $parent = ParentGuardian::find($existingParentId);
                    $user_id = $parent->user_id ?? $this->getUserIdByParentId($existingParentId);
                } elseif (!empty($userIdByPhone)) {
                    $user_id = $userIdByPhone;
                    $parentIdForUser = $this->getParentIdByUserId($user_id);
                    if (!empty($parentIdForUser)) {
                        $existingParentId = $parentIdForUser;
                    }
                }

                if ($user_id === '' || $user_id === null) {
                    if (empty($this->getUserId($parentEmail))) {
                        $role = Role::find(7);
                        $user = new User();
                        $user->name = $parentName;
                        $user->email = strtolower($parentEmail);
                        $user->phone = $normalizedPhone;
                        $user->password = Hash::make('12345678');
                        $user->email_verified_at = now();
                        $user->role_id = $role->id;
                        $user->permissions = $role->permissions;
                        $user->save();
                        $user_id = $user->id;
                    } else {
                        $user_id = $this->getUserId($parentEmail);
                    }
                }

                if (empty($existingParentId)) {
                    if (empty($this->getParentId(trim($parentEmail)))) {
                        $parent = new ParentGuardian();
                        $parent->user_id = $user_id;
                        $parent->guardian_name = $parentName;
                        $parent->guardian_email = strtolower($parentEmail);
                        $parent->guardian_mobile = $normalizedPhone;
                        $parent->status = "1";
                        $parent->save();
                        $parent_id = $parent->id;
                    } else {
                        $parent_id = $this->getParentId(trim($parentEmail));
                    }
                } else {
                    $parent_id = $existingParentId;
                }

                // Student
                $existingStudentIdByPhone = $this->getStudentIdByPhone($phoneNumber);
                if (!empty($existingStudentIdByPhone)) {
                    $student_id = $existingStudentIdByPhone;
                    $student = Student::findOrFail($student_id);
                    $student->student_category_id = $category_id;
                    $student->category_id = $category_id;
                    if (!empty($normalizedPhone)) {
                        $student->mobile = $normalizedPhone;
                    }
                    $student->save();
                } else if (empty($this->getStudentId($parent_id, $classesStore_id))) {
                    $student = new $this->model;
                    $student->user_id = $user_id;
                    $student->first_name = $studentFirstName;
                    $student->last_name = $studentLastName;
                    $student->admission_no = $admissionNo !== '' ? $admissionNo : null;
                    $student->roll_no = $this->generateUniqueTrackingNumber();
                    $student->mobile = $normalizedPhone;
                    $student->email = strtolower($parentEmail);
                    $student->gender_id = $this->getGenderId($genderName);
                    $student->admission_date = "2026-01-01";
                    $student->student_category_id = $category_id;
                    $student->parent_guardian_id = $parent_id;
                    $student->status = "1";
                    $student->category_id = $category_id;
                    $student->control_number = "00" . $this->generateUniquecONTROLNumber();
                    $student->save();
                    $student_id = $student->id;
                } else {
                    $student_id = $this->getStudentId($parent_id, $classesStore_id);
                }

                // Session class assignment (no fees assignment for format 2)
                if (empty($this->getSessionClassStudent($classesStore_id, setting('session'), $student_id))) {
                    $session_class = new SessionClassStudent();
                    $session_class->session_id = setting('session');
                    $session_class->classes_id = $classesStore_id;
                    $session_class->section_id = $sectionStore_id;
                    $session_class->student_id = $student_id;
                    $session_class->save();
                } else {
                    $session_class_id = $this->getSessionClassStudent($classesStore_id, setting('session'), $student_id);
                    $sessionClass = SessionClassStudent::findOrFail($session_class_id);
                    $sessionClass->section_id = $sectionStore_id;
                    $sessionClass->save();
                }

                // Library member record
                if (empty($this->getMemberId($student_id))) {
                    $member = new Member();
                    $member->user_id = $student_id;
                    $member->name = trim($studentFirstName . ' ' . $studentLastName);
                    $member->category_id = "1";
                    $member->status = "1";
                    $member->save();
                }
            }

            DB::commit();
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    private function getRowValue(array $row, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && trim((string) $row[$key]) !== '') {
                return $row[$key];
            }
        }
        return $default;
    }

    public function updateFeesAssigned($id, $amount,$status)
    {
         DB::statement("CALL UpdateFeesAmount()");
                    DB::statement("CALL UpdateQuarters()");
        // Get the student ID based on the given fee assignment child ID
        $studentId = DB::table('fees_assign_childrens')->where('id', $id)->value('student_id');
    
        // Try to retrieve the Outstanding Fee ID
        $outstandingId = DB::table('fees_assign_childrens')
            ->join('fees_masters', 'fees_assign_childrens.fees_master_id', '=', 'fees_masters.id')
            ->where('fees_masters.fees_group_id', 1)
            ->where('fees_assign_childrens.student_id', $studentId)
            ->value('fees_assign_childrens.id');
            if($status==1){
            if ($outstandingId) {
                // Handle Outstanding Fee Payment
                $remainedAmount = DB::table('fees_assign_childrens')->where('id', $outstandingId)->sum('remained_amount');
    
                if ($remainedAmount >= $amount) {
                    DB::update(
                        'update fees_assign_childrens set paid_amount = paid_amount + ?, remained_amount = remained_amount - ? where id = ?',
                        [$amount, $amount, $outstandingId]
                    );
                    return; // Exit as the amount is fully covered by the outstanding fee
                } else {
                    DB::update(
                        'update fees_assign_childrens set paid_amount = paid_amount + ?, remained_amount = remained_amount - ? where id = ?',
                        [$remainedAmount, $remainedAmount, $outstandingId]
                    );
    
                    // Adjust remaining amount to be collected
                    $amount -= $remainedAmount;
                }
            }
        }
    
        // If no Outstanding Fee or remaining amount after Outstanding Fee, process other fees
        $this->processOtherFees($studentId, $amount,$status,$id);
    }
    
    /**
     * Function to process other fees by quarter
     */
    private function processOtherFees($studentId, $amount,$status,$id)
    {
        if($status==1){
            $feeIds = DB::table('fees_assign_childrens')
            ->join('fees_masters', 'fees_masters.id', '=', 'fees_assign_childrens.fees_master_id')
            ->whereNotIn('fees_masters.fees_group_id', [4, 5]) // More concise condition
            ->where('fees_assign_childrens.student_id', $studentId)
            ->orderBy('fees_assign_childrens.fees_master_id') // Ensures ordering is correct
            ->pluck('fees_assign_childrens.id');
        }else{
            $feeIds = DB::table('fees_assign_childrens')
            ->where('student_id', $studentId)
            ->where('id',$id)
            ->orderBy('fees_master_id') // Assuming fees_master_id determines the order of fees
            ->pluck('id'); 
        }
    
        foreach (['quater_one', 'quater_two', 'quater_three', 'quater_four'] as $quarter) {
            foreach ($feeIds as $feeId) {
                $quarterAmount = DB::table('fees_assign_childrens')->where('id', $feeId)->value($quarter);
    
                if ($quarterAmount > 0) {
                    if ($amount >= $quarterAmount) {
                        DB::update(
                            "update fees_assign_childrens set paid_amount = paid_amount + ?, remained_amount = remained_amount - ?, $quarter = $quarter - ? where id = ?",
                            [$quarterAmount, $quarterAmount, $quarterAmount, $feeId]
                        );
                        $amount -= $quarterAmount;
                    } else {
                        DB::update(
                            "update fees_assign_childrens set paid_amount = paid_amount + ?, remained_amount = remained_amount - ?, $quarter = $quarter - ? where id = ?",
                            [$amount, $amount, $amount, $feeId]
                        );
                        return; // Exit as the amount is fully utilized
                    }
                }
            }
        }
    }

    public function getParentId($email){
        $guardian_id = DB::select('SELECT id from parent_guardians where guardian_email = ?',[$email]);
        if (!empty($guardian_id)) {
            return $guardian_id[0]->id;
        }else{
            return "";
        }
    }

    public function getCategory($category_id){
        $guardian_id = DB::select('SELECT name from student_categories where id = ?',[$category_id]);
        if (!empty($guardian_id)) {
            return $guardian_id[0]->name;
        }else{
            return "";
        }
    }
    public function getCategoryId($category_id){
        $guardian_id = DB::select('SELECT id from student_categories where name = ?',[$category_id]);
        if (!empty($guardian_id)) {
            return $guardian_id[0]->id;
        }else{
            return "";
        }
    }

    public function getFeeTypeId($class){
        $guardian_id = DB::select('SELECT id from fees_types where class_id = ?',[$class]);
        if (!empty($guardian_id)) {
            return $guardian_id[0]->id;
        }else{
            return "";
        }
    }


    public function getFeeGroupId($fees_type_id){
        $guardian_id = DB::select('SELECT fees_group_id from fees_masters where fees_type_id = ? AND session_id = ?',[$fees_type_id, setting('session')]);
        if (!empty($guardian_id)) {
            return $guardian_id[0]->fees_group_id;
        }else{
            return "";
        }
    }

    public function getFeeMasterId($fees_type_id){
        $guardian_id = DB::select('SELECT id from fees_masters where fees_type_id = ? AND session_id = ?',[$fees_type_id, setting('session')]);
        if (!empty($guardian_id)) {
            return $guardian_id[0]->id;
        }else{
            return "";
        }
    }


    public function getFeeMasterAmount($fees_master_id){
        $guardian_id = DB::select('SELECT amount from fees_masters where id = ?',[$fees_master_id]);
        if (!empty($guardian_id)) {
            return $guardian_id[0]->amount;
        }else{
            return "";
        }
    }

    public function getClassId($class_name){
        $class_id = DB::select('SELECT id from classes where name = ?',[$class_name]);
        if(!empty($class_id)){
            return $class_id[0]->id;
        }else{
            return "";
        }
    }

    public function getStudentCategory($category){
        $class_id = DB::select('SELECT id from student_categories where name = ?',[$category]);
        if(!empty($class_id)){
            return $class_id[0]->id;
        }else{
            return "";
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
            $studentClass = DB::select("Select classes_id FROM session_class_students 
                    WHERE student_id = ?",[$id])[0]->classes_id;

            $studentSection = DB::select("Select section_id FROM session_class_students 
                    WHERE student_id = ?",[$id])[0]->section_id;

            $studentAddress = DB::select("Select residance_address FROM students 
                    WHERE id = ?",[$id])[0]->residance_address;

            $studentCategoryId = DB::select("Select student_category_id FROM students 
                    WHERE id = ?",[$id])[0]->student_category_id;



            $row                      = $this->model->find($id);
            // $user                     = User::where('id',$row->user_id)->first();
            $parent                     = ParentGuardian::where('id',$request->parent)->first();
            $row->first_name           = $request->first_name;
            $row->last_name            = $request->last_name;
            $row->user_id              = $parent->user_id;
            $row->admission_no         = $request->admission_no??NULL;
            $row->mobile               = $request->mobile??NULL;
            $row->email                = $request->email??NULL;
            $row->dob                  = $request->date_of_birth != "" ? $request->date_of_birth : NULL;
            $row->religion_id          = $request->religion != ""? $request->religion :  NULL;
            $row->gender_id            = $request->gender != ""? $request->gender :  NULL;
            $row->admission_date       = $request->admission_date??NULL;
            $row->parent_guardian_id   = $request->parent != ""? $request->parent :  NULL;
            $row->previous_school = $request->previous_school ?? 0;
            $row->previous_school_info = $request->previous_school?$request->previous_school_info:null;
            $row->residance_address = $request->residance_address??NULL;
            $row->status               = $request->status;
            $row->student_category_id  = $request->category != ""? $request->category :  NULL;
            $row->category_id  = $request->category != ""? $request->category :  NULL;

            $row->save();

            $session_class                      = SessionClassStudent::where('session_id', setting('session'))->where('student_id', $row->id)->first();
            $session_class->classes_id          = $request->class;
            $session_class->section_id          = $request->section != ""? $request->section :  NULL;
            $session_class->student_id          = $row->id;
            $session_class->roll                = $request->admission_no;
            $session_class->save();


            if ($studentAddress != $request->residance_address) {
                DB::insert('INSERT INTO student_address_history (student_id, student_address) VALUES (?, ?)', [
                    $id,
                    $request->residance_address
                ]);
            }

            // Update transport fees when category changes (using category like in create)
            if ($studentCategoryId != $request->category) {
                // Get or find fees assign for transport (group 3) for the current class
                $feeAssignResult = DB::select('SELECT id FROM fees_assigns WHERE classes_id = ? AND session_id = ? AND fees_group_id = ?',
                     [$request->class, setting('session'), "3"]);
                
                if (!empty($feeAssignResult)) {
                    $feeAssignId = $feeAssignResult[0]->id;
                    
                    // Get transport profile from new category (same logic as create)
                    $newTransportProfile = DB::select("
                        SELECT name 
                        FROM student_categories
                        WHERE id = ?
                    ", [$request->category]);
                    
                    $newFeeTypeId = 0;
                    $newFeesMaster = null;
                    
                    if (!empty($newTransportProfile) && !empty($newTransportProfile[0]->name)) {
                        $transportProfileName = $newTransportProfile[0]->name;
                        // Split transport profile based on 'DAY'
                        $transportProfileParts = explode(' DAY ', $transportProfileName);
                        
                        if (!empty($transportProfileParts[1])) {
                            // Get fee type ID based on transport profile part
                            $feeTypeResult = DB::select("
                                SELECT id 
                                FROM fees_types 
                                WHERE code = ?
                            ", [trim($transportProfileParts[1])]);
                            
                            if (!empty($feeTypeResult)) {
                                $newFeeTypeId = $feeTypeResult[0]->id;
                                
                                // Get fees master ID based on fee type ID and session (2026)
                                $feesMasterResult = DB::select("
                                    SELECT id 
                                    FROM fees_masters 
                                    WHERE fees_type_id = ? AND session_id = ?
                                ", [$newFeeTypeId, setting('session')]);
                                
                                if (!empty($feesMasterResult)) {
                                    $newFeesMaster = $feesMasterResult[0]->id;
                                }
                            }
                        }
                    }
                    
                    // Update existing or create new transport fees assign children when category has transport
                    if ($newFeeTypeId != 0 && !empty($newFeesMaster)) {
                        // Find existing transport fees assign children for this student
                        $existingTransportFee = DB::table('fees_assign_childrens')
                            ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
                            ->where('fees_assign_childrens.student_id', $id)
                            ->where('fees_assigns.fees_group_id', 3)
                            ->where('fees_assigns.session_id', setting('session'))
                            ->select('fees_assign_childrens.id', 'fees_assign_childrens.fees_master_id')
                            ->first();
                        
                        if ($existingTransportFee) {
                            // Update the fees master ID to the new one based on category
                            DB::table('fees_assign_childrens')
                                ->where('id', $existingTransportFee->id)
                                ->update([
                                    'fees_master_id' => $newFeesMaster,
                                    'fees_amount' => $this->getFeesAmount($newFeesMaster),
                                    'remained_amount' => $this->getFeesAmount($newFeesMaster),
                                ]);
                            
                            // Update quarters if due date allows
                            if ($this->getDueDate($newFeesMaster) > 8) {
                                $quarterAmount = $this->getFeesAmount($newFeesMaster) / 4;
                                DB::table('fees_assign_childrens')
                                    ->where('id', $existingTransportFee->id)
                                    ->update([
                                        'quater_one' => $quarterAmount,
                                        'quater_two' => $quarterAmount,
                                        'quater_three' => $quarterAmount,
                                        'quater_four' => $quarterAmount,
                                    ]);
                            }
                        } else {
                            // Category changed to a transport category but student had no transport fee: create it (same as store)
                            $feeAssignChildren = DB::select('
                                SELECT id FROM fees_assign_childrens
                                WHERE fees_assign_id = ? AND fees_master_id = ? AND student_id = ?
                            ', [$feeAssignId, $newFeesMaster, $id]);
                            if (empty($feeAssignChildren)) {
                                $controlNumber = $this->getStudentControlNumber($id);
                                if (!empty($controlNumber)) {
                                    $feesChild = new FeesAssignChildren();
                                    $feesChild->fees_assign_id = $feeAssignId;
                                    $feesChild->fees_master_id = $newFeesMaster;
                                    $feesChild->student_id = $id;
                                    $feesChild->fees_amount = $this->getFeesAmount($newFeesMaster);
                                    $feesChild->remained_amount = $this->getFeesAmount($newFeesMaster);
                                    $feesChild->control_number = $controlNumber;
                                    if ($this->getDueDate($newFeesMaster) > 8) {
                                        $quarterAmount = $this->getFeesAmount($newFeesMaster) / 4;
                                        $feesChild->quater_one = $quarterAmount;
                                        $feesChild->quater_two = $quarterAmount;
                                        $feesChild->quater_three = $quarterAmount;
                                        $feesChild->quater_four = $quarterAmount;
                                    }
                                    $feesChild->fee_group = "2";
                                    $feesChild->save();
                                    $feesId = $feesChild->id;
                                    $months = DB::table('months_list')->get();
                                    foreach ($months as $month) {
                                        TransportMonth::create([
                                            'student_id' => $id,
                                            'fee_assign_children_id' => $feesId,
                                            'user_id' => Auth::id(),
                                            'month' => $month->id,
                                            'amount' => $this->getFeesAmount($newFeesMaster) / 10,
                                            'status' => '1',
                                            'state' => '1'
                                        ]);
                                    }
                                }
                            }
                        }
                    } else {
                        // New category has no transport profile: remove existing transport fee if any
                        $existingTransportFee = DB::table('fees_assign_childrens')
                            ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
                            ->where('fees_assign_childrens.student_id', $id)
                            ->where('fees_assigns.fees_group_id', 3)
                            ->where('fees_assigns.session_id', setting('session'))
                            ->select('fees_assign_childrens.id')
                            ->first();
                        if ($existingTransportFee) {
                            DB::table('fees_assign_childrens')->where('id', $existingTransportFee->id)->delete();
                        }
                    }
                }
            }



            // Update school fees when class changes (using class like in create)
            if ($studentClass != $request->class) {
                // Get fee type ID for the NEW class (same logic as create)
                $newFeesTypeId = $this->getFeeTypeId($request->class);
                
                if (!empty($newFeesTypeId)) {
                    // Get fees master ID for the new class (filters by session automatically now)
                    $newFeesMasterId = $this->getFeeMasterId($newFeesTypeId);
                    
                    if (!empty($newFeesMasterId)) {
                        // Get or find fees assign for the new class
                        $newFeesGroupId = $this->getFeeGroupId($newFeesTypeId);
                        $newFeesAssignId = $this->checkFeesAssign($request->class, $newFeesGroupId, $request->section);
                        
                        if (!empty($newFeesAssignId)) {
                            // Find existing school fees assign children for this student (group 2)
                            $existingSchoolFee = DB::table('fees_assign_childrens')
                                ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
                                ->where('fees_assign_childrens.student_id', $id)
                                ->where('fees_assigns.fees_group_id', 2)
                                ->where('fees_assigns.session_id', setting('session'))
                                ->select('fees_assign_childrens.id')
                                ->first();
                            
                            if ($existingSchoolFee) {
                                // Update the fees assign and fees master to the new class
                                DB::table('fees_assign_childrens')
                                    ->where('id', $existingSchoolFee->id)
                                    ->update([
                                        'fees_assign_id' => $newFeesAssignId,
                                        'fees_master_id' => $newFeesMasterId,
                                        'fees_amount' => $this->getFeeMasterAmount($newFeesMasterId) / 2,
                                        'remained_amount' => $this->getFeeMasterAmount($newFeesMasterId) / 2,
                                        'quater_one' => '0',
                                        'quater_two' => '0',
                                        'quater_three' => ($this->getFeeMasterAmount($newFeesMasterId) / 2) / 2,
                                        'quater_four' => ($this->getFeeMasterAmount($newFeesMasterId) / 2) / 2,
                                    ]);
                            }
                        }
                    }
                }

                    //Update for the results that has been uploaded and a student has change a class
                    $previousMarksRegisterIds = $this->getMarksRegisterId($studentClass, $studentSection);

                    if (!empty($previousMarksRegisterIds)) {
                        foreach ($previousMarksRegisterIds as $register) {
                            $marksRegisterId = $register->id;

                            // Get the mark row for the given student and register
                            $markRow = DB::table('marks_register_childrens')
                                ->where('marks_register_id', $marksRegisterId)
                                ->where('student_id', $id)
                                ->first();

                            if ($markRow && !empty($markRegisterId)) {
                                $exam_type_id = $this->getExamType($marksRegisterId);
                                $subject_id = $this->getSubjectId($marksRegisterId);
                                $currentMarksRegisterIds = $this->getMarksRegisterIdDetails($request->class, $request->section,$exam_type_id,$subject_id);
                                $rowChild = MarksRegisterChildren::find($markRow->id); // use correct row id
                                if ($rowChild) {
                                    $rowChild->marks_register_id = $currentMarksRegisterIds; // move to new register
                                    $rowChild->save();
                                }
                            }
                        }
                    }

            }else if ($studentSection != $request->section) {
                $previousMarksRegisterIds = $this->getMarksRegisterId($studentClass, $studentSection);
                Log::info($previousMarksRegisterIds);
                    if (!empty($previousMarksRegisterIds)) {
                        foreach ($previousMarksRegisterIds as $register) {
                            $marksRegisterId = $register->id;
                            Log::info("previousMarksRegisterIds".$marksRegisterId);
                            // Get the mark row for the given student and register
                            $markRow = DB::table('marks_register_childrens')
                                ->where('marks_register_id', $marksRegisterId)
                                ->where('student_id', $id)
                                ->first();
                                Log::info("Student id".$id);
                               
                            if ($markRow) {
                                Log::info("Mark Row Data".$markRow->id);
                                $exam_type_id = $this->getExamType($marksRegisterId);
                                Log::info("exam_type_id id".$exam_type_id);
                                $subject_id = $this->getSubjectId($marksRegisterId);
                                Log::info("subject_id id".$subject_id);
                                $currentMarksRegisterIds = $this->getMarksRegisterIdDetails($request->class, $request->section,$exam_type_id,$subject_id);
                                Log::info("Mark Row".$markRow->id);
                                Log::info("currentMarksRegisterIds".$currentMarksRegisterIds);
                                $rowChild = MarksRegisterChildren::find($markRow->id); // use correct row id
                                if ($rowChild) {
                                    $rowChild->marks_register_id = $currentMarksRegisterIds; // move to new register
                                    $rowChild->save();
                                }
                            }
                        }
                    }
            }
            DB::commit();
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollback();
            dd($th);
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    private function getMarksRegisterId($class,$section)
    {
       
        $guardian_id = DB::select('SELECT id from marks_registers where classes_id = ? AND
         section_id = ? ',
            [$class,$section]);
        if (!empty($guardian_id)) {
            return $guardian_id;
        }else{
            return "";
        }
    }

    private function getMarksRegisterIdDetails($class,$section,$exam_type_id,$subject_id)
    {
       
        $guardian_id = DB::select('SELECT id from marks_registers where classes_id = ? AND
         section_id = ? AND exam_type_id = ? and subject_id = ? ',
            [$class,$section,$exam_type_id,$subject_id]);
        if (!empty($guardian_id)) {
            return $guardian_id[0]->id;
        }else{
            return "";
        }
    }

    /**
     * Delete student after copying to history tables (student, fees assign, fees collect).
     */
    public function destroyWithHistory($id)
    {
        DB::beginTransaction();
        try {
            $student = $this->model->find($id);
            if (!$student) {
                return $this->responseWithError(__('alert.not_found'), []);
            }

            $deletedAt = now();
            $deletedBy = Auth::id();

            $historyColumns = [
                'admission_no', 'roll_no', 'first_name', 'last_name', 'mobile', 'email', 'dob', 'admission_date',
                'student_category_id', 'religion_id', 'blood_group_id', 'gender_id', 'category_id', 'image_id',
                'parent_guardian_id', 'user_id', 'upload_documents', 'status', 'previous_school', 'previous_school_info',
                'previous_school_image_id', 'place_of_birth', 'nationality', 'cpr_no', 'spoken_lang_at_home', 'residance_address',
            ];
            $studentRow = array_intersect_key($student->getAttributes(), array_flip($historyColumns));
            $studentRow['original_student_id'] = $student->id;
            $studentRow['deleted_at'] = $deletedAt;
            $studentRow['deleted_by'] = $deletedBy;

            $history = StudentDeletedHistory::create($studentRow);

            $assignChildren = FeesAssignChildren::where('student_id', $id)->get();
            foreach ($assignChildren as $row) {
                $assignRow = [
                    'student_deleted_history_id' => $history->id,
                    'original_fees_assign_children_id' => $row->id,
                    'original_student_id' => $row->student_id,
                    'fees_assign_id' => $row->fees_assign_id ?? null,
                    'fees_master_id' => $row->fees_master_id ?? null,
                    'deleted_at' => $deletedAt,
                ];
                if (Schema::hasColumn('fees_assign_childrens', 'fees_amount')) {
                    $assignRow['fees_amount'] = $row->fees_amount ?? null;
                }
                if (Schema::hasColumn('fees_assign_childrens', 'paid_amount')) {
                    $assignRow['paid_amount'] = $row->paid_amount ?? null;
                }
                if (Schema::hasColumn('fees_assign_childrens', 'remained_amount')) {
                    $assignRow['remained_amount'] = $row->remained_amount ?? null;
                }
                StudentFeesAssignDeletedHistory::create($assignRow);
            }

            $collects = FeesCollect::where('student_id', $id)->get();
            foreach ($collects as $row) {
                StudentFeesCollectDeletedHistory::create([
                    'student_deleted_history_id' => $history->id,
                    'original_fees_collect_id' => $row->id,
                    'original_student_id' => $row->student_id,
                    'date' => $row->date,
                    'payment_method' => $row->payment_method,
                    'fees_assign_children_id' => $row->fees_assign_children_id ?? null,
                    'fees_collect_by' => $row->fees_collect_by ?? null,
                    'session_id' => $row->session_id ?? null,
                    'amount' => $row->amount ?? null,
                    'fine_amount' => $row->fine_amount ?? null,
                    'deleted_at' => $deletedAt,
                ]);
            }

            if ($student->user_id) {
                $user = User::find($student->user_id);
                if ($user) $user->delete();
            }
            if ($student->parent_guardian_id) {
                $parent = ParentGuardian::find($student->parent_guardian_id);
                if ($parent) $parent->delete();
            }
            DB::delete('DELETE FROM session_class_students WHERE student_id = ?', [$id]);
            DB::delete('DELETE FROM members WHERE user_id = ?', [$student->user_id]);
            DB::delete('DELETE FROM fees_collects WHERE student_id = ?', [$id]);
            DB::delete('DELETE FROM fees_assign_childrens WHERE student_id = ?', [$id]);
            DB::delete('DELETE FROM marks_register_childrens WHERE student_id = ?', [$id]);
            $student->delete();

            DB::commit();
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Student deleteWithHistory error: ' . $th->getMessage());
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function destroy($id)
{
    DB::beginTransaction();

    try {
        $student = $this->model->find($id);
        if (!$student) {
            return $this->responseWithError(__('alert.not_found'), []);
        }

        // Delete related User
        if ($student->user_id) {
            $user = User::find($student->user_id);
            if ($user) {
                $user->delete();
            }
        }

        // Delete related Parent
        if ($student->parent_guardian_id) {
            $parent = ParentGuardian::find($student->parent_guardian_id);
            if ($parent) {
                $parent->delete();
            }
        }

        // Related table cleanup
        DB::delete('DELETE FROM session_class_students WHERE student_id = ?', [$id]);
        DB::delete('DELETE FROM members WHERE user_id = ?', [$student->user_id]); // use actual user_id
        DB::delete('DELETE FROM fees_assign_childrens WHERE student_id = ?', [$id]);
        DB::delete('DELETE FROM marks_register_childrens WHERE student_id = ?', [$id]);

        // Finally delete student
        $student->delete();

        DB::commit();
        return $this->responseWithSuccess(___('alert.deleted_successfully'), []);

    } catch (\Throwable $th) {
        DB::rollBack();
        Log::error('Student delete error: ' . $th->getMessage());
        return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
    }
}


    private function getClassSetupId($class,$session_id)
    {
        $class_setup = DB::select('SELECT id from class_setups where session_id = ? and classes_id = ?',[$session_id,$class]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }

    }

    private function getExamType($id)
    {
        $class_setup = DB::select('SELECT exam_type_id from marks_registers where id = ?',[$id]);
        if(!empty($class_setup)){
            return $class_setup[0]->exam_type_id;
        }else{
            return "";
        }

    }

    private function getSubjectId($id)
    {
        $class_setup = DB::select('SELECT subject_id from marks_registers where id = ?',[$id]);
        if(!empty($class_setup)){
            return $class_setup[0]->subject_id;
        }else{
            return "";
        }

    }

    


    private function getUserId($email)
    {
        $user_id = DB::select('SELECT id from users where email = ?',[$email]);
        if(!empty($user_id)) {
            return $user_id[0]->id;
        }else{
            return "";
        }
    }

    /**
     * Find user id by phone (original or normalized) — reuse existing guardian login.
     */
    private function getUserIdByPhone($phone)
    {
        if ($phone === null || $phone === '') {
            return '';
        }
        $normalizedPhone = $this->normalizePhoneNumber($phone);
        if ($normalizedPhone === null || $normalizedPhone === '') {
            return '';
        }
        $user = DB::select(
            'SELECT id FROM users WHERE phone = ? OR phone = ? LIMIT 1',
            [$phone, $normalizedPhone]
        );
        return !empty($user) ? $user[0]->id : '';
    }

    /**
     * Parent row linked to a user account (one parent per user in typical setup).
     */
    private function getParentIdByUserId($userId)
    {
        if ($userId === '' || $userId === null) {
            return '';
        }
        $row = DB::select('SELECT id FROM parent_guardians WHERE user_id = ? LIMIT 1', [$userId]);
        return !empty($row) ? $row[0]->id : '';
    }

    private function getUserIdByParentId($parentId)
    {
        $user_id = DB::select('SELECT user_id from parent_guardians where id = ?',[$parentId]);
        if(!empty($user_id)) {
            return $user_id[0]->user_id;
        }else{
            return "";
        }
    }

    private function getSessionClassStudent($classesStore_id,$session,$student_id)
    {
        $session_id = DB::select('SELECT id from session_class_students where session_id = ?  and student_id = ? and classes_id = ?',[$session,$student_id,$classesStore_id]);
        if (!empty($session_id)) {
            return $session_id[0]->id;
        }else{
            return "";
        }

    }

    private function getSessionClassStudentDefault($classesStore_id,$session,$student_id)
    {
        $session_id = DB::select('SELECT id from session_class_students where session_id = ?  and student_id = ? and classes_id = ?',[$session,$student_id,$classesStore_id]);
        if (!empty($session_id)) {
            return $session_id[0]->id;
        }else{
            return "";
        }

    }

    private function getStudentId($parent_id,$class_id)
    {
        $student_id = DB::select('SELECT students.id from students
            inner join session_class_students on session_class_students.student_id = students.id
             where parent_guardian_id = ? and session_class_students.classes_id =?',[$parent_id,$class_id]);
        if (!empty($student_id)) {
            return $student_id[0]->id;
        }else{
            return "";
        }
    }

//     private function getStudentIdByName($student_name,$class)
// {
//     // Clean and normalize the input name
//     $student_name = preg_replace('/\s+/', ' ', trim($student_name));
//     // $name_parts = explode(' ', $student_name);

//     // Require at least two name parts (first and rest)
//     // if (count($name_parts) < 2) {
//     //     return "";
//     // }

//     // $first = strtoupper($name_parts[0]);
//     // $last_input = strtoupper(implode(' ', array_slice($name_parts, 1)));
//     $result = DB::select('SELECT id FROM classes WHERE name = ?', [$class]);

//     $class_id = !empty($result) ? $result[0]->id : null;

//     // Get all students with the same first name
//     $students = DB::table('students')
//         ->join('session_class_students','session_class_students.student_id','=','students.id')
//         ->select('students.id', 'first_name', 'last_name')
//         ->where('session_class_students.classes_id', '=', $class_id)
//         ->whereRaw('CONCAT (UPPER(first_name),last_name) = ?', [$student_name])
//         ->get();

//     // foreach ($students as $student) {
//     //     $db_last = strtoupper(trim($student->last_name));
//     //     similar_text($db_last, $last_input, $percent);

//     //     // Match if it's a strong similarity or the DB last name is a prefix of the uploaded one
//     //     if ($percent >= 80 || str_starts_with($last_input, $db_last)) {
//     //         // Optional: update the DB last name to the longer version
//     //         if ($db_last !== $last_input) {
//     //             DB::table('students')
//     //                 ->where('id', $student->id)
//     //                 ->update(['last_name' => ucwords(strtolower($last_input))]);
//     //         }

//     //         return $student->id;
//     //     }
//     // }

//     return "";
// }

private function getStudentIdByName($student_name, $class,$section)
{
    // Normalize the input name
    $student_name = preg_replace('/\s+/', ' ', trim($student_name));
    $student_name_upper = strtoupper($student_name);

    // Retrieve the class ID
    $classRecord = DB::table('classes')->where('name', $class)->first();
    if (!$classRecord) {
        return "";
    }

    // $sectionRecord = DB::table('sections')->where('name', $section)->first();
    // if (!$sectionRecord) {
    //     return "";
    // }

    // Search for the student by concatenating first and last names
    $student = DB::table('students')
        ->join('session_class_students', 'session_class_students.student_id', '=', 'students.id')
        ->where('session_class_students.classes_id', $classRecord->id)
        //  ->where('session_class_students.section_id', $sectionRecord->id)
        ->whereRaw("UPPER(CONCAT(first_name, ' ', last_name)) = ?", [$student_name_upper])
        ->select('students.id')
        ->first();

        if($student){
            DB::update('update students set available = ? where id = ?',["1",$student->id]);
        }

    return $student ? $student->id : "";
}


    // private function getStudentIdByName($student_name)
    //     {
    //         $student_name = preg_replace('/\s+/', ' ', trim($student_name));
    //         // $student_id = DB::select('SELECT id FROM students 
    //         // WHERE CONCAT(first_name, " ", last_name) = ?', [$student_name]);
    //         $student_id = DB::select('
    //         SELECT id FROM students 
    //         WHERE LOWER(
    //             REPLACE(
    //                 REPLACE(
    //                     REPLACE(TRIM(CONCAT(first_name, " ", last_name)), "  ", " "), 
    //                 "  ", " "), 
    //             "  ", " ")
    //         ) = LOWER(?)
    //     ', [$student_name]);
            
    //         if (!empty($student_id)) {
    //             return $student_id[0]->id;
    //         } else {
    //             return "";
    //         }
    //     }




    private function getGenderId($gender_name)
    {

        $gender_id = DB::select('SELECT id from genders where name = ? ',[$gender_name]);

        if (!empty($gender_id)) {
            return $gender_id[0]->id;
        }else{
            return "1";
        }
    }

    private function getReligionId($religion_name)
    {
        $religion_id = DB::select('SELECT id from religions where name = ? ',[$religion_name]);
        if (!empty($religion_id)) {
            return $religion_id[0]->id;
        }else{
            return "";
        }
    }

    private function getClassSetupChildren( $setup_id,  $section)
    {
        $class_setup = DB::select('SELECT id from class_setup_childrens where class_setup_id = ? and section_id = ?',[$setup_id,$section]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }

    private function getSectionId( $section)
    {
        $class_setup = DB::select('SELECT id from sections where name  = ?',[$section]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
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

    private function checkIfOutstandingBalanceTransportExist()
    {

        $class_setup = DB::select('SELECT id from fees_groups where name  = ?',["Outstanding Transport"]);
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

    private function checkFeeTypeTransport()
    {
        $class_setup = DB::select('SELECT id from fees_types where name  = ?',["Outstanding Transport Fee"]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }

    private function checkFeeMaster( $fees_group_id,  $fee_type_id)
    {
        $class_setup = DB::select('SELECT id from fees_masters where fees_group_id  = ? and fees_type_id = ?'
            ,[$fees_group_id,$fee_type_id]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }

    private function checkFeesMasterChildren( $fee_type_id,  $fees_master_id)
    {
        $class_setup = DB::select('SELECT id from fees_master_childrens where fees_master_id  = ? and fees_type_id = ?'
            ,[$fees_master_id,$fee_type_id]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }

    private function checkFeesAssign( $classesStore_id,  $fees_group_id,$sectionStore_id)
    {
        $class_setup = DB::select('SELECT id from fees_assigns where classes_id  = ? and fees_group_id = ? and section_id = ? and session_id = ?'
            ,[$classesStore_id,$fees_group_id,$sectionStore_id,setting('session')]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }

    private function checkFeesAssignChildren( $feesAssignId,  $fees_master_id,  $student_id)
    {
        $class_setup = DB::select('SELECT id from fees_assign_childrens where 
        fees_assign_id  = ? and fees_master_id = ? and student_id = ?'
            ,[$feesAssignId,$fees_master_id,$student_id]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
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

    private function getMemberId( $student_id)
    {

        $result = DB::select('SELECT id FROM members where user_id = ?',[$student_id]);
        if(!empty($result)){
            return $result[0]->id;
        }else{
            return "";
        }
    }

    /**
     * Normalize phone number - remove spaces and format consistently
     * Examples: "0766 738708" -> "0766738708", "+255766738708" -> "255766738708"
     */
    private function normalizePhoneNumber($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all spaces, dashes, parentheses, and other non-numeric characters except +
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Remove + if present
        $phone = str_replace('+', '', $phone);
        
        // Keep only digits
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 255, keep as is (12 digits: 255XXXXXXXXX)
        // If starts with 0, keep as is (10 digits: 0XXXXXXXXX)
        // Otherwise, assume it's a local number and keep as is
        
        return $phone ?: null;
    }

    /**
     * Get student ID by phone number (checks both student.mobile and parent_guardians.guardian_mobile)
     */
    private function getStudentIdByPhone($phone)
    {
        $normalizedPhone = $this->normalizePhoneNumber($phone);
        
        if (empty($normalizedPhone)) {
            return "";
        }

        // Check student.mobile
        $studentByMobile = DB::select('SELECT id FROM students WHERE mobile = ? OR mobile = ? LIMIT 1', [
            $phone, // Original phone
            $normalizedPhone // Normalized phone
        ]);
        
        if (!empty($studentByMobile)) {
            return $studentByMobile[0]->id;
        }

        // Check parent_guardians.guardian_mobile
        $studentByParentPhone = DB::select('
            SELECT students.id 
            FROM students 
            INNER JOIN parent_guardians ON parent_guardians.id = students.parent_guardian_id 
            WHERE parent_guardians.guardian_mobile = ? OR parent_guardians.guardian_mobile = ? 
            LIMIT 1
        ', [$phone, $normalizedPhone]);
        
        if (!empty($studentByParentPhone)) {
            return $studentByParentPhone[0]->id;
        }

        return "";
    }

    /**
     * Get parent ID by phone number
     */
    private function getParentIdByPhone($phone)
    {
        $normalizedPhone = $this->normalizePhoneNumber($phone);
        
        if (empty($normalizedPhone)) {
            return "";
        }

        $parent = DB::select('SELECT id FROM parent_guardians WHERE guardian_mobile = ? OR guardian_mobile = ? LIMIT 1', [
            $phone, // Original phone
            $normalizedPhone // Normalized phone
        ]);
        
        if (!empty($parent)) {
            return $parent[0]->id;
        }

        return "";
    }

    /**
     * Upload outstanding fees from Excel file
     * Excel format: Date, Num, Name (CLASS X Y: STUDENT NAME), Amount, Open Balance
     */
    public function uploadOutstandingFees($request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'document_files' => 'required|mimes:xlsx,xls,csv',
            ]);

            $data = Excel::toArray([], $request->file('document_files'));
            $processedCount = 0;
            $skippedCount = 0;
            $errors = [];

            // Get Outstanding Balance fees group and type
            $fees_group_id = $this->checkIfOutstandingBalanceExist();
            if (empty($fees_group_id)) {
                return $this->responseWithError('Outstanding Balance fees group not found. Please create it first.', []);
            }

            $fee_type_id = $this->checkFeeType();
            if (empty($fee_type_id)) {
                return $this->responseWithError('Outstanding Balance Fee type not found. Please create it first.', []);
            }

            $fees_master_id = $this->checkFeeMaster($fees_group_id, $fee_type_id);
            if (empty($fees_master_id)) {
                return $this->responseWithError('Outstanding Balance Fee master not found. Please create it first.', []);
            }

            foreach ($data[0] as $index => $row) {
                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Skip header row or total row
                    $nameValue = $row['Name'] ?? $row['name'] ?? '';
                    if (empty($nameValue) || stripos($nameValue, 'Total') !== false || stripos($nameValue, 'TRANSPORT') !== false) {
                        continue;
                    }

                    // Parse the Name column: "CLASS 1 B: STUDENT NAME"
                    // Format: "CLASS [NUMBER] [SECTION]: [STUDENT NAME]"
                    $nameStr = trim($nameValue);
                    
                    // Extract class, section, and student name
                    if (preg_match('/CLASS\s+(\d+)\s+([A-Z])\s*:\s*(.+)/i', $nameStr, $matches)) {
                        $classNumber = $matches[1];
                        $sectionLetter = strtoupper($matches[2]);
                        $studentName = trim($matches[3]);
                    } else {
                        // Try alternative format without "CLASS" prefix
                        if (preg_match('/(\d+)\s+([A-Z])\s*:\s*(.+)/i', $nameStr, $matches)) {
                            $classNumber = $matches[1];
                            $sectionLetter = strtoupper($matches[2]);
                            $studentName = trim($matches[3]);
                        } else {
                            $errors[] = "Row " . ($index + 2) . ": Could not parse name format: " . $nameStr;
                            $skippedCount++;
                            continue;
                        }
                    }

                    // Get class name (e.g., "1", "2", "3" or "Form 1", "Standard 1")
                    $className = $classNumber; // You may need to adjust this based on your class naming
                    
                    // Find student by name and class
                    $student_id = $this->getStudentIdByName($studentName, $className, $sectionLetter);
                    
                    if (empty($student_id)) {
                        $errors[] = "Row " . ($index + 2) . ": Student not found: " . $studentName . " (Class: " . $className . " " . $sectionLetter . ")";
                        $skippedCount++;
                        continue;
                    }

                    // Get student's current class and section from session_class_students
                    $studentClassInfo = DB::select('
                        SELECT classes_id, section_id 
                        FROM session_class_students 
                        WHERE student_id = ? AND session_id = ? 
                        LIMIT 1
                    ', [$student_id, setting('session')]);

                    if (empty($studentClassInfo)) {
                        $errors[] = "Row " . ($index + 2) . ": Student not found in current session: " . $studentName;
                        $skippedCount++;
                        continue;
                    }

                    $classesStore_id = $studentClassInfo[0]->classes_id;
                    $sectionStore_id = $studentClassInfo[0]->section_id;

                    // Get or create fees_assign
                    $feesAssignId = $this->checkFeesAssign($classesStore_id, $fees_group_id, $sectionStore_id);
                    if (empty($feesAssignId)) {
                        $rowFeesAssign = new FeesAssign();
                        $rowFeesAssign->session_id = setting('session');
                        $rowFeesAssign->classes_id = $classesStore_id;
                        $rowFeesAssign->section_id = $sectionStore_id;
                        $rowFeesAssign->fees_group_id = $fees_group_id;
                        $rowFeesAssign->save();
                        $feesAssignId = $rowFeesAssign->id;
                    }

                    // Get Amount and Open Balance
                    $amount = $row['Amount'] ?? $row['amount'] ?? 0;
                    $openBalance = $row['Open Balance'] ?? $row['open_balance'] ?? $row['OpenBalance'] ?? 0;

                    // Remove commas and convert to number
                    $fees_amount = (float) str_replace(',', '', $amount);
                    $remained_amount = (float) str_replace(',', '', $openBalance);
                    $paid_amount = $fees_amount - $remained_amount;

                    // Check if outstanding fees already assigned
                    $existingFeesChildId = $this->checkFeesAssignChildren($feesAssignId, $fees_master_id, $student_id);

                    if (empty($existingFeesChildId)) {
                        // Create new outstanding fees assignment
                        $feesChield = new FeesAssignChildren();
                        $feesChield->fees_assign_id = $feesAssignId;
                        $feesChield->fees_master_id = $fees_master_id;
                        $feesChield->student_id = $student_id;
                        $feesChield->fees_amount = $fees_amount;
                        $feesChield->paid_amount = max(0, $paid_amount);
                        $feesChield->remained_amount = max(0, $remained_amount);
                        $feesChield->outstandingbalance = $remained_amount;
                        $feesChield->quater_one = 0;
                        $feesChield->quater_two = 0;
                        $feesChield->quater_three = 0;
                        $feesChield->quater_four = 0;
                        $feesChield->control_number = $this->getStudentControlNumber($student_id);
                        $feesChield->save();
                        $processedCount++;
                    } else {
                        // Update existing outstanding fees
                        $feesChield = FeesAssignChildren::find($existingFeesChildId);
                        $feesChield->fees_amount = $fees_amount;
                        $feesChield->paid_amount = max(0, $paid_amount);
                        $feesChield->remained_amount = max(0, $remained_amount);
                        $feesChield->outstandingbalance = $remained_amount;
                        $feesChield->save();
                        $processedCount++;
                    }

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                    $skippedCount++;
                    Log::error("Error processing outstanding fees row", [
                        'row_index' => $index + 2,
                        'row_data' => $row,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            DB::commit();

            $message = "Outstanding fees uploaded successfully. Processed: {$processedCount}, Skipped: {$skippedCount}";
            if (!empty($errors)) {
                $message .= ". Errors: " . count($errors);
            }

            return $this->responseWithSuccess($message, [
                'processed' => $processedCount,
                'skipped' => $skippedCount,
                'errors' => $errors
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Outstanding fees upload error: ' . $th->getMessage());
            return $this->responseWithError(
                ___('alert.something_went_wrong_please_try_again'),
                ['error' => $th->getMessage()]
            );
        }
    }

    /**
     * Find duplicate students by name+class or phone+class
     */
    public function findDuplicateStudents()
    {
        $duplicates = [];
        
        try {
            // Find duplicates by name and class
            $duplicatesByName = DB::select("
                SELECT 
                    s1.id as student_id_1,
                    s1.first_name as first_name_1,
                    s1.last_name as last_name_1,
                    s1.mobile as mobile_1,
                    s2.id as student_id_2,
                    s2.first_name as first_name_2,
                    s2.last_name as last_name_2,
                    s2.mobile as mobile_2,
                    scs1.classes_id,
                    c.name as class_name,
                    scs1.section_id,
                    sec.name as section_name,
                    CONCAT(s1.first_name, ' ', s1.last_name) as full_name
                FROM students s1
                INNER JOIN session_class_students scs1 ON scs1.student_id = s1.id
                INNER JOIN students s2 ON s2.id > s1.id
                INNER JOIN session_class_students scs2 ON scs2.student_id = s2.id
                INNER JOIN classes c ON c.id = scs1.classes_id
                LEFT JOIN sections sec ON sec.id = scs1.section_id
                WHERE scs1.session_id = ?
                  AND scs2.session_id = ?
                  AND scs1.classes_id = scs2.classes_id
                  AND UPPER(TRIM(CONCAT(s1.first_name, ' ', s1.last_name))) = UPPER(TRIM(CONCAT(s2.first_name, ' ', s2.last_name)))
                  AND s1.status != 0
                  AND s2.status != 0
                ORDER BY c.name, sec.name, s1.first_name
            ", [setting('session'), setting('session')]);

            foreach ($duplicatesByName as $dup) {
                $duplicates[] = [
                    'type' => 'name',
                    'student_1' => [
                        'id' => $dup->student_id_1,
                        'name' => $dup->first_name_1 . ' ' . $dup->last_name_1,
                        'mobile' => $dup->mobile_1,
                    ],
                    'student_2' => [
                        'id' => $dup->student_id_2,
                        'name' => $dup->first_name_2 . ' ' . $dup->last_name_2,
                        'mobile' => $dup->mobile_2,
                    ],
                    'class' => $dup->class_name,
                    'section' => $dup->section_name ?? '',
                ];
            }

            // Find duplicates by phone number and class
            $duplicatesByPhone = DB::select("
                SELECT 
                    s1.id as student_id_1,
                    s1.first_name as first_name_1,
                    s1.last_name as last_name_1,
                    s1.mobile as mobile_1,
                    s2.id as student_id_2,
                    s2.first_name as first_name_2,
                    s2.last_name as last_name_2,
                    s2.mobile as mobile_2,
                    scs1.classes_id,
                    c.name as class_name,
                    scs1.section_id,
                    sec.name as section_name,
                    s1.mobile as phone_number
                FROM students s1
                INNER JOIN session_class_students scs1 ON scs1.student_id = s1.id
                INNER JOIN students s2 ON s2.id > s1.id
                INNER JOIN session_class_students scs2 ON scs2.student_id = s2.id
                INNER JOIN classes c ON c.id = scs1.classes_id
                LEFT JOIN sections sec ON sec.id = scs1.section_id
                WHERE scs1.session_id = ?
                  AND scs2.session_id = ?
                  AND scs1.classes_id = scs2.classes_id
                  AND s1.mobile IS NOT NULL
                  AND s1.mobile != ''
                  AND s2.mobile IS NOT NULL
                  AND s2.mobile != ''
                  AND REPLACE(REPLACE(REPLACE(s1.mobile, ' ', ''), '-', ''), '+', '') = REPLACE(REPLACE(REPLACE(s2.mobile, ' ', ''), '-', ''), '+', '')
                  AND s1.status != 0
                  AND s2.status != 0
                ORDER BY c.name, sec.name, s1.mobile
            ", [setting('session'), setting('session')]);

            foreach ($duplicatesByPhone as $dup) {
                $duplicates[] = [
                    'type' => 'phone',
                    'student_1' => [
                        'id' => $dup->student_id_1,
                        'name' => $dup->first_name_1 . ' ' . $dup->last_name_1,
                        'mobile' => $dup->mobile_1,
                    ],
                    'student_2' => [
                        'id' => $dup->student_id_2,
                        'name' => $dup->first_name_2 . ' ' . $dup->last_name_2,
                        'mobile' => $dup->mobile_2,
                    ],
                    'class' => $dup->class_name,
                    'section' => $dup->section_name ?? '',
                ];
            }

            Log::info('Duplicate students search completed', [
                'name_duplicates' => count($duplicatesByName),
                'phone_duplicates' => count($duplicatesByPhone),
                'total' => count($duplicates)
            ]);

        } catch (\Exception $e) {
            Log::error('Error finding duplicate students: ' . $e->getMessage());
        }

        return $duplicates;
    }
}
