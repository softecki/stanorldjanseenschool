<?php

namespace App\Repositories;

use App\Imports\StaffImport;
use App\Imports\StudentsImport;
use App\Models\Academic\Classes;
use App\Models\Academic\ClassSetup;
use App\Models\Academic\Section;
use App\Models\Academic\Subject;
use App\Models\Academic\SubjectAssign;
use App\Models\Academic\SubjectAssignChildren;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Enums\ImageSize;
use App\Models\Staff\Staff;
use App\Interfaces\UserInterface;
use App\Traits\CommonHelperTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use App\Http\Requests\Profile\PasswordUpdateRequest;
use App\Traits\ApiReturnFormatTrait;
use Maatwebsite\Excel\Facades\Excel;

class UserRepository implements UserInterface
{
    use CommonHelperTrait;
    use ApiReturnFormatTrait;
    private $model;

    public function __construct(Staff $model)
    {
        $this->model = $model;
    }

    public function index($request)
    {
        $data =  $this->model->query()->with('upload', 'designation');

        $where = array();

        if ($request->search) {
            $where[] = ['name', 'like', '%' . $request->search . '%'];
        }

        if ($request->from && $request->to) {
            $data = $data->whereBetween('created_at', [Carbon::parse($request->from), Carbon::parse($request->to)->endOfDay()]);
        }

        if ($request->designation) {
            $data = $data->whereIn('designation_id', $request->designation);
        }

        $data = $data
            ->where($where)
            ->orderBy('id', 'DESC')
            ->paginate($request->show ?? 10);

        return $data;
    }

    public function status($request)
    {
        return $this->model->whereIn('id', $request->ids)->update(['status' => $request->status]);
    }

    public function deletes($request)
    {
        return $this->model->destroy((array)$request->ids);
    }

    public function all()
    {
        return $this->model->active()->where('role_id', 5)->get(); // Teacher role id 5
    }

    public function getAll()
    {
        return $this->model->query()->orderBy('id', 'DESC')->paginate(10);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            
            if(User::whereNotIn('role_id', [6,7])->count() >= activeSubscriptionStaffLimit() && env('APP_SAAS'))
                return 2;

            $role                     = Role::find($request->role);
            $user                     = new User();
            $user->name               = $request->first_name;
            $user->email              = $request->email;
            $user->phone              = $request->phone;
            $user->password           = Hash::make('123456');
            $user->email_verified_at  = now();
            $user->role_id            = $request->role;
            $user->upload_id          = $this->UploadImageCreate($request->image, 'backend/uploads/users');
            $user->permissions        = $role->permissions;
            $user->save();
            
            $staff                          = new $this->model;
            $staff->user_id                 = $user->id;
            $staff->staff_id                = $request->staff_id??NULL;
            $staff->role_id                 = $request->role;
            $staff->designation_id          = $request->designation;
            $staff->department_id           = $request->department;
            $staff->first_name              = $request->first_name;
            $staff->last_name               = $request->last_name;
            $staff->father_name             = $request->father_name;
            $staff->mother_name             = $request->mother_name;
            $staff->email                   = $request->email;
            $staff->gender_id               = $request->gender;
            $staff->dob                     = $request->dob;
            $staff->joining_date            = $request->joining_date;
            $staff->phone                   = $request->phone;
            $staff->emergency_contact       = $request->emergency_contact;
            $staff->marital_status          = $request->marital_status;
            $staff->status                  = $request->status;
            $staff->current_address         = $request->current_address;
            $staff->permanent_address       = $request->permanent_address;
            $staff->basic_salary            = $request->basic_salary;
            $staff->upload_id               = $user->upload_id;

            $staff->upload_documents        = $this->uploadDocuments($request);

            $staff->save();
            DB::commit();
            return 1;
        } catch (\Throwable $th) {
            DB::rollback();
            return false;
        }
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

    public function upload($request)
    {
        DB::beginTransaction();
        try {

            $request->validate([
                'document_files' => 'required|mimes:xlsx,xls,csv',
            ]);
            $data = Excel::toArray(new StaffImport, $request->file('document_files'));


            foreach ($data[0] as $row) {
                if (empty(array_filter($row))) {
                    return 1; 
                               }
                               $firstName = $row['first_name'];
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
            $userEmail = (!empty($row['email']) ? $row['email'] : (trim($studentFirstName).trim($lastNameToUse).'@gmail.com'));

                if (empty($this->getRoleId(trim($row['designation'])))) {
                    $role = new Role();
                    $role->name = $row['designation'];
                    $role->status = "1";
                    $role->save();
                    $role_id = $role->id;
                }else{
                    $role_id = $this->getRoleId(trim($row['designation']));
                }

                $role = Role::find($role_id);
                if (empty($this->getUserId(trim($userEmail)))) {
                    $user = new User();
                    $user->name = $studentFirstName . ' ' . $parentName;
                    $user->email = $userEmail;
                    $user->phone = $row['phone']??NULL;
                    $user->password = Hash::make('12345678');
                    $user->email_verified_at = now();
                    $user->role_id = $role_id;
                    $user->permissions = $role->permissions;
                    $user->save();
                    $user_id = $user->id;
                }else{
                    $user_id = $this->getUserId(trim($userEmail));
                }

                if (empty($this->getStaff($user_id))) {
                    $staff = new $this->model;
                    $staff->user_id = $user_id;
                    $staff->staff_id = $this->generateUniqueTrackingNumber();;
                    $staff->role_id = $role_id;
                    $staff->designation_id = "1";
                    $staff->department_id = "1";
                    $staff->first_name = $studentFirstName;
                    $staff->last_name = $parentName;
                    $staff->email = $userEmail;
                    $staff->gender_id = $this->getGenderId(trim($row['gender']));
                    $staff->phone = trim($row['phone'])??NULL;
                    $staff->status = "1";
                    $staff->basic_salary = trim($row['basic_salary'])??NULL;
                    $staff->upload_documents = [];
                    $staff->save();
                    $staff_id = $staff->id;
                }else{
                    $staff_id =  $this->getStaff($user_id);
                }

                if (!empty(trim($row['class']))) {
                    if (empty($this->getClassId(trim($row['class'])))) {
                        $classesStore = new Classes();
                        $classesStore->name = trim($row['class']);
                        $classesStore->status = "1";
                        $classesStore->save();
                        $classesStore_id = $classesStore->id;
                    } else {
                        $classesStore_id = $this->getClassId(trim($row['class']));
                    }

                    if (empty($this->getClassSetupId($classesStore_id,setting('session')))) {
                        $setup = new ClassSetup();
                        $setup->session_id = setting('session');
                        $setup->classes_id = $classesStore_id;
                        $setup->save();
                        $setup_id = $setup->id;
                    }else{
                        $setup_id = $this->getClassSetupId($classesStore_id,setting('session'));
                    }

                    if (empty($this->getSectionId(trim($row['section'])))) {
                        $sectionStore = new Section();
                        $sectionStore->name = trim($row['section']);
                        $sectionStore->status = "1";
                        $sectionStore->save();
                        $sectionStore_id = $sectionStore->id;
                    }else{
                        $sectionStore_id = $this->getSectionId(trim($row['section']));
                    }


                    if (empty($this->getSubjectAssign($classesStore_id, $sectionStore_id ))) {
                        $setup = new SubjectAssign();
                        $setup->session_id = setting('session');
                        $setup->classes_id = $classesStore_id;
                        $setup->section_id = $sectionStore_id;
                        $setup->status = "1";
                        $setup->save();
                        $setup_id = $setup->id;
                    }else{
                        $setup_id = $this->getSubjectAssign($classesStore_id, $sectionStore_id );
                    }


                    if (empty($this->getSubject(trim($row['subject'])))) {
                        $subjectStore              = new Subject();
                        $subjectStore->name        = trim($row['subject']);
                        $subjectStore->status      = "1";
                        $subjectStore->save();
                        $subjectId = $subjectStore->id;
                    }else{
                        $subjectId =  $this->getSubject(trim($row['subject']));
                    }


                    if(empty($this->getSubjectAssignCildren($setup_id,$subjectId,$staff_id))) {
                        $row = new SubjectAssignChildren();
                        $row->subject_assign_id = $setup_id;
                        $row->subject_id = $subjectId;
                        $row->staff_id = $staff_id;
                        $row->save();
                        $subjectId = $row->id;
                    }else{
                        $subjectId = $this->getSubjectAssignCildren($setup_id,$subjectId,$staff_id);
                    }
                }



                DB::commit();
            }


            return 1;
        } catch (\Throwable $th) {
            DB::rollback();
            dd($th);
            return false;
        }
    }

    private function getUserId($phone)
    {
        $user_id = DB::select('SELECT id from users where email = ?',[$phone]);
        if(!empty($user_id)) {
            return $user_id[0]->id;
        }else{
            return "";
        }
    }

    private function getRoleId($roleName)
    {
        $user_id = DB::select('SELECT id from roles where name = ?',[$roleName]);
        if(!empty($user_id)) {
            return $user_id[0]->id;
        }else{
            return "";
        }
    }

    private function getStaff($id)
    {
        $user_id = DB::select('SELECT id from staff where user_id = ?',[$id]);
        if(!empty($user_id)) {
            return $user_id[0]->id;
        }else{
            return "";
        }
    }


    private function getGenderId($gender_name)
    {
        $gender_id = DB::select('SELECT id from genders where name = ? ',[$gender_name]);
        if (!empty($gender_id)) {
            return $gender_id[0]->id;
        }else{
            return "";
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
    private function getClassSetupChildren( $setup_id,  $section)
    {
        $class_setup = DB::select('SELECT id from class_setup_childrens where class_setup_id = ? and section_id = ?',[$setup_id,$section]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
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

    private function getSectionId( $section)
    {
        $class_setup = DB::select('SELECT id from sections where name  = ?',[$section]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
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
            $role                     = Role::find($request->role);

            $staff                    = $this->model->findOrfail($id);
            
            $user                     = User::find($staff->user_id);
            $user->name               = $request->first_name;
            $user->email              = $request->email;
            $user->phone              = $request->phone;
            $user->role_id            = $request->role;

            if ($request->image) {
                $user->upload_id          = $this->UploadImageCreate($request->image, 'backend/uploads/users');
            }
            
            $user->permissions        = $role->permissions;
            $user->save();
            
            $staff->user_id                 = $user->id;
            $staff->staff_id                = $request->staff_id;
            $staff->role_id                 = $request->role;
            $staff->designation_id          = $request->designation;
            $staff->department_id           = $request->department;
            $staff->first_name              = $request->first_name;
            $staff->last_name               = $request->last_name;
            $staff->father_name             = $request->father_name;
            $staff->mother_name             = $request->mother_name;
            $staff->email                   = $request->email;
            $staff->gender_id               = $request->gender;
            $staff->dob                     = $request->dob;
            $staff->joining_date            = $request->joining_date;
            $staff->phone                   = $request->phone;
            $staff->emergency_contact       = $request->emergency_contact;
            $staff->marital_status          = $request->marital_status;
            $staff->status                  = $request->status;
            $staff->current_address         = $request->current_address;
            $staff->permanent_address       = $request->permanent_address;
            $staff->basic_salary            = $request->basic_salary;
            $staff->upload_id               = $user->upload_id;

            $staff->upload_documents        = $this->uploadDocuments($request, $staff->upload_documents);

            $staff->save();
            DB::commit();
            return true;
        } catch (\Throwable $th) {

            dd($th);
            DB::rollback();
            return false;
        }
    }

    public function profileUpdate($request, $id)
    {
        try {
            $userUpdate                 = User::findOrfail($id);
            $userUpdate->name           = $request->name;
            $userUpdate->phone          = $request->phone;
            if(Auth::user()->role_id != 7)
                $userUpdate->date_of_birth  = $request->date_of_birth;
            $userUpdate->upload_id       = $this->UploadImageUpdate($request->image, 'backend/uploads/users', $userUpdate->upload_id);
            $userUpdate->save();
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function destroy($id)
    {
        try {
            $user   = User::find($id);
            $this->UploadImageDelete($user->upload_id); // delete image & record
            $user->delete();
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }


    public function passwordUpdate($request, $id)
    {
        try {
            $userUpdate             = User::findOrfail($id);
            $userUpdate->password   = Hash::make($request->password);
            $userUpdate->save();
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    private function getSubjectAssign( $class,  $section)
    {
        $user_id = DB::select('SELECT id from subject_assigns where classes_id = ? and section_id = ?',[$class,$section]);
        if(!empty($user_id)) {
            return $user_id[0]->id;
        }else{
            return "";
        }
    }

    private function getSubject( $subject)
    {
        $user_id = DB::select('SELECT id from subjects where name = ?',[$subject]);
        if(!empty($user_id)) {
            return $user_id[0]->id;
        }else{
            return "";
        }
    }

    private function getSubjectAssignCildren( $setup_id,  $subjectId,  $staff_id)
    {

        $user_id = DB::select('SELECT id from subject_assign_childrens where subject_assign_id = ? and subject_id = ? and staff_id = ?',[$setup_id,$subjectId,$staff_id]);
        if(!empty($user_id)) {
            return $user_id[0]->id;
        }else{
            return "";
        }
    }
}
