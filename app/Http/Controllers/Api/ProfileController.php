<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\CampusTeacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Actualitza dades bàsiques del perfil propi (telèfon, contrasenya, i
     * `bio` per a professorat). Mateix criteri conservador que el portal
     * web: cap dada sensible (DNI, IBAN, adreça, email) és editable aquí.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ];

        if ($user instanceof CampusTeacher) {
            $rules['bio'] = ['nullable', 'string', 'max:1000'];
        }

        $data = $request->validate($rules);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return new UserResource($user->fresh());
    }
}
