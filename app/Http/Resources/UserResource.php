<?php

namespace App\Http\Resources;

use App\Models\AssociatMember;
use App\Models\CampusTeacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'full_name'  => $this->full_name,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            // locale = triat per l'usuari (null = el de l'entitat); effective_locale = el que s'aplica.
            'locale'           => $this->locale,
            'effective_locale' => $this->preferredLocale() ?? config('app.locale'),
            'role'       => match (true) {
                $this->resource instanceof CampusTeacher   => 'teacher',
                $this->resource instanceof AssociatMember  => 'member',
                default                                    => 'student',
            },
        ];
    }
}
