<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrolledStudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'enrollment_id' => $this->id,
            'status'        => $this->status,
            'first_name'    => $this->student->first_name ?? $this->first_name,
            'last_name'     => $this->student->last_name ?? $this->last_name,
            'email'         => $this->student->email ?? $this->email,
            'phone'         => $this->student->phone ?? $this->phone,
        ];
    }
}
