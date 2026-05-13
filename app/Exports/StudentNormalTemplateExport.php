<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentNormalTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'student_name',
            'admission_no',
            'class',
            'section',
            'gender',
            'category',
            'phone_number',
            'email',
            'school_fees_amount',
            'school_paid_amount',
            'school_remained_amount',
            'transport_fees_amount',
            'transport_paid_amount',
            'transport_remained_amount',
            'outstanding_fees_amount',
            'outstanding_paid_amount',
            'outstanding_remained_amount',
            'outstanding_description_1',
            'outstanding_amount_1',
            'outstanding_description_2',
            'outstanding_amount_2',
            'outstanding_description_3',
            'outstanding_amount_3',
            'outstanding_description_4',
            'outstanding_amount_4',
            'outstanding_description_5',
            'outstanding_amount_5',
        ];
    }

    public function array(): array
    {
        return [[
            'JOHN DOE',
            'ADM001',
            'BABY 1',
            'A',
            'Male',
            'Day',
            '0712345678',
            'parent@example.com',
            '800000',
            '300000',
            '500000',
            '150000',
            '50000',
            '100000',
            '70000',
            '0',
            '70000',
            'Outstanding school',
            '50000',
            'Outstanding transport',
            '20000',
            '',
            '',
            '',
            '',
            '',
            '',
        ]];
    }
}

