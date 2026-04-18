<?php

namespace Database\Seeders\StudentInfo;

use App\Models\StudentInfo\SessionClassStudent;
use App\Models\StudentInfo\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($c = 1; $c <= 3; $c++) { // class
            for ($s=1; $s <= 2 ; $s++) { // sections
                for ($i = 1; $i <= 14; $i++) { // students

                    $dob = date('Y-m-d', strtotime("-".$c.$s.$i." day"));
                    $user = User::create([
                        'name'              => 'Student'.$c.$s.$i,
                        'phone'             => '0147852'.$c.$s.$i,
                        'email'             => 'student'.$c.$s.$i.'@gmail.com',
                        'email_verified_at' => now(),
                        'password'          => Hash::make('123456'),
                        'role_id'           => 6,
                        'date_of_birth'     => $dob,
                        'permissions'       => []
                    ]);
                    $student = Student::create([
                        'user_id'                 => $user->id,
                        'admission_no'            => '2023'.$c.$s.$i,
                        'roll_no'                 => $i,
                        'first_name'              => 'Student',
                        'last_name'               => ''.$c.$s.$i,
                        'mobile'                  => '0147852'.$c.$s.$i,
                        'email'                   => 'student'.$c.$s.$i.'@gmail.com',
                        'dob'                     => $dob,
                        'admission_date'          => date('Y-m-d', strtotime("+".$c.$s.$i." day")),
                        'religion_id'             => rand(1, 3),
                        'blood_group_id'          => rand(1, 8),
                        'gender_id'               => rand(1, 2),
                        'parent_guardian_id'      => rand(1, 10),
                        'student_category_id'     => rand(1, 2),
                        'status'                  => 1,
                        'upload_documents'        => []
                    ]);
                    SessionClassStudent::create([
                        'session_id'                 => setting('session'),
                        'student_id'                 => $student->id,
                        'classes_id'                 => $c,
                        'section_id'                 => $s,
                        'shift_id'                   => rand(1, 3),
                        'roll'                       => $i
                    ]);
                }
            }
        }
    }
}
