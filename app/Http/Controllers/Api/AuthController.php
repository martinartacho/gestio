<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\AssociatMember;
use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Una mateixa persona pot tenir compte com a alumne, professor i/o soci
     * amb el mateix email+contrasenya. Comprovem els tres i, si n'hi ha més
     * d'un, demanem al client que esculli abans d'emetre el token (paràmetre
     * `role` opcional). Si només n'hi ha un, es comporta exactament igual
     * que abans (cap canvi per als usuaris amb un sol rol).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'role'     => ['nullable', 'in:student,teacher,member'],
        ]);

        $matches = [];

        $student = CampusStudent::where('email', $credentials['email'])->first();
        if ($student && Hash::check($credentials['password'], $student->password)) {
            $matches['student'] = $student;
        }

        $teacher = CampusTeacher::where('email', $credentials['email'])->first();
        if ($teacher && Hash::check($credentials['password'], $teacher->password)) {
            $matches['teacher'] = $teacher;
        }

        $member = AssociatMember::where('email', $credentials['email'])->first();
        if ($member && Hash::check($credentials['password'], $member->password)) {
            $matches['member'] = $member;
        }

        if (empty($matches)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if (! empty($credentials['role'])) {
            if (! isset($matches[$credentials['role']])) {
                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            return $this->respondWithRole($matches[$credentials['role']], $credentials['role']);
        }

        if (count($matches) > 1) {
            return response()->json([
                'multiple_roles' => true,
                'roles'          => array_keys($matches),
            ]);
        }

        $role = array_key_first($matches);

        return $this->respondWithRole($matches[$role], $role);
    }

    private function respondWithRole(CampusStudent|CampusTeacher|AssociatMember $user, string $role)
    {
        if ($user instanceof CampusStudent && $user->isSuspended()) {
            throw ValidationException::withMessages([
                'email' => 'El compte ha estat suspès. Contacteu amb l\'administració.',
            ]);
        }

        return $this->respondWithToken($user, $role);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Rols disponibles per a l'usuari autenticat (mateix email a més d'una
     * taula). Serveix perquè l'app mostri el botó de "canviar de rol" només
     * quan té sentit.
     */
    public function availableRoles(Request $request)
    {
        $email = $request->user()->email;

        return response()->json([
            'roles' => array_keys($this->matchingAccounts($email)),
        ]);
    }

    /**
     * Canvia de rol dins la mateixa sessió, sense tornar a demanar la
     * contrasenya: la confiança ja l'estableix el token vàlid actual: només
     * cal que hi hagi un compte amb el mateix email per al rol demanat.
     */
    public function switchRole(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', 'in:student,teacher,member'],
        ]);

        $matches = $this->matchingAccounts($request->user()->email);

        abort_unless(isset($matches[$data['role']]), 404, 'No tens cap compte amb aquest rol.');

        $request->user()->currentAccessToken()->delete();

        return $this->respondWithRole($matches[$data['role']], $data['role']);
    }

    /** @return array<string, CampusStudent|CampusTeacher|AssociatMember> */
    private function matchingAccounts(string $email): array
    {
        $matches = [];

        if ($student = CampusStudent::where('email', $email)->first()) {
            $matches['student'] = $student;
        }
        if ($teacher = CampusTeacher::where('email', $email)->first()) {
            $matches['teacher'] = $teacher;
        }
        if ($member = AssociatMember::where('email', $email)->first()) {
            $matches['member'] = $member;
        }

        return $matches;
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sessió tancada correctament.']);
    }

    private function respondWithToken(CampusStudent|CampusTeacher|AssociatMember $user, string $role)
    {
        $token = $user->createToken('gestio-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'role'  => $role,
            'user'  => new UserResource($user),
        ]);
    }
}
