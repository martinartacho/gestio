<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $student = CampusStudent::where('email', $credentials['email'])->first();

        if ($student && Hash::check($credentials['password'], $student->password)) {
            if ($student->isSuspended()) {
                throw ValidationException::withMessages([
                    'email' => 'El compte ha estat suspès. Contacteu amb l\'administració.',
                ]);
            }

            return $this->respondWithToken($student, 'student');
        }

        $teacher = CampusTeacher::where('email', $credentials['email'])->first();

        if ($teacher && Hash::check($credentials['password'], $teacher->password)) {
            return $this->respondWithToken($teacher, 'teacher');
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sessió tancada correctament.']);
    }

    private function respondWithToken(CampusStudent|CampusTeacher $user, string $role)
    {
        $token = $user->createToken('gestio-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'role'  => $role,
            'user'  => new UserResource($user),
        ]);
    }
}
