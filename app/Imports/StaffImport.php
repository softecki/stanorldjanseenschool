<?php

namespace App\Imports;

use App\Models\Staff\Staff;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StaffImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Staff([
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email'],
            'gender' => $row['gender'],
            'phone' => $row['phone'],
            'subject' => $row['subject'],
            'class' => $row['class'],
            'section' => $row['section']
        ]);
    }
}
