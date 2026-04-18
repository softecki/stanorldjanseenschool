<?php

namespace App\Http\Resources\Student;

use App\Models\StudentInfo\SessionClassStudent;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $sessionClassStudent    = sessionClassStudent();

        return [
            'student_id'        => $this->id,
            'avatar'            => @globalAsset($this->upload->path, '40X40.webp'),
            'name'              => $this->first_name . ' ' . $this->last_name,
            'class'             => @$sessionClassStudent->class->name,
            'section'           => @$sessionClassStudent->section->name,
            'roll'              => $sessionClassStudent->roll,
            'blood_group'       => @$this->blood->name,
            'gender'            => @$this->gender->name,
            'date_of_birth'     => date('d/m/Y', strtotime($this->dob)),
            'religion'          => @$this->religion->name
        ];
    }
}
