<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'member_number' => $this->member_number,
            'full_name'     => $this->full_name,
            'first_name'    => $this->first_name,
            'last_name'     => $this->last_name,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'status'        => $this->status,
            'joined_at'     => $this->joined_at?->format('Y-m-d'),
            'qr_token'      => $this->qr_token,
        ];
    }
}
