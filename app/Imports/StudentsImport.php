<?php

namespace App\Imports;

use App\Models\StudentInfo\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Student([
            'student_first_name' => $row['student_first_name'],
            'student_last_name' => $row['student_last_name'],
            'parent_name' => $row['parent_name'],
            'phone_number' => $row['phone_number'],
            'class_name' => $row['class_name'],
            'gender' => $row['gender'],
            'section' => $row['section'],
            'religion' => $row['religion'],
            'admission_no' => $row['admission_no'],
        ]);
    }
}
