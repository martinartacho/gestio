<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\AssociatMember;
use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * L'app porta el slug de la seva entitat fix (cada flavor/build és
     * d'una institució) i el passa a cada login — com que alumnat,
     * professorat i socis poden pertànyer a més d'una entitat amb el
     * mateix compte, cal saber amb quina es vol entrar.
     *
     * Una mateixa persona pot tenir compte com a alumne, professor i/o soci
     * amb el mateix email+contrasenya. Comprovem els tres i, si n'hi ha més
     * d'un, demanem al client que esculli abans d'emetre el token (paràmetre
     * `role` opcional). Si només n'hi ha un, es comporta exactament igual
     * que abans (cap canvi per als usuaris amb un sol rol).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'tenant'   => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'role'     => ['nullable', 'in:student,teacher,member'],
        ]);

        $tenant = Tenant::where('slug', $credentials['tenant'])->where('is_active', true)->first();

        if (! $tenant) {
            throw ValidationException::withMessages([
                'tenant' => 'Entitat no reconeguda.',
            ]);
        }

        $matches = [];

        $student = CampusStudent::where('email', $credentials['email'])->first();
        if ($student && Hash::check($credentials['password'], $student->password) && $student->belongsToTenant($tenant->id)) {
            $matches['student'] = $student;
        }

        $teacher = CampusTeacher::where('email', $credentials['email'])->first();
        if ($teacher && Hash::check($credentials['password'], $teacher->password) && $teacher->belongsToTenant($tenant->id)) {
            $matches['teacher'] = $teacher;
        }

        $member = AssociatMember::where('email', $credentials['email'])->first();
        if ($member && Hash::check($credentials['password'], $member->password) && $member->belongsToTenant($tenant->id)) {
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

            return $this->respondWithRole($matches[$credentials['role']], $credentials['role'], $tenant);
        }

        if (count($matches) > 1) {
            return response()->json([
                'multiple_roles' => true,
                'roles'          => array_keys($matches),
            ]);
        }

        $role = array_key_first($matches);

        return $this->respondWithRole($matches[$role], $role, $tenant);
    }

    private function respondWithRole(CampusStudent|CampusTeacher|AssociatMember $user, string $role, Tenant $tenant)
    {
        if ($user instanceof CampusStudent && $user->isSuspended()) {
            throw ValidationException::withMessages([
                'email' => 'El compte ha estat suspès. Contacteu amb l\'administració.',
            ]);
        }

        return $this->respondWithToken($user, $role, $tenant);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Rols disponibles per a l'usuari autenticat DINS LA MATEIXA ENTITAT amb
     * què va fer login (mateix email a més d'una taula). Serveix perquè
     * l'app mostri el botó de "canviar de rol" només quan té sentit.
     */
    public function availableRoles(Request $request)
    {
        $tenant = $this->currentTenant($request);
        $matches = $this->matchingAccounts($request->user()->email, $tenant);

        return response()->json([
            'roles' => array_keys($matches),
        ]);
    }

    /**
     * Canvia de rol dins la mateixa sessió i entitat, sense tornar a demanar
     * la contrasenya: la confiança ja l'estableix el token vàlid actual —
     * només cal que hi hagi un compte amb el mateix email per al rol
     * demanat, a la mateixa entitat amb què s'havia fet login.
     */
    public function switchRole(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', 'in:student,teacher,member'],
        ]);

        $tenant  = $this->currentTenant($request);
        $matches = $this->matchingAccounts($request->user()->email, $tenant);

        abort_unless(isset($matches[$data['role']]), 404, 'No tens cap compte amb aquest rol en aquesta entitat.');

        $request->user()->currentAccessToken()->delete();

        return $this->respondWithRole($matches[$data['role']], $data['role'], $tenant);
    }

    /** @return array<string, CampusStudent|CampusTeacher|AssociatMember> */
    private function matchingAccounts(string $email, Tenant $tenant): array
    {
        $matches = [];

        if (($student = CampusStudent::where('email', $email)->first()) && $student->belongsToTenant($tenant->id)) {
            $matches['student'] = $student;
        }
        if (($teacher = CampusTeacher::where('email', $email)->first()) && $teacher->belongsToTenant($tenant->id)) {
            $matches['teacher'] = $teacher;
        }
        if (($member = AssociatMember::where('email', $email)->first()) && $member->belongsToTenant($tenant->id)) {
            $matches['member'] = $member;
        }

        return $matches;
    }

    /**
     * L'entitat amb què es va fer login queda encastada al token (com a
     * "ability" `tenant:{id}`) — no hi ha cap altra manera de saber-ho en
     * una petició d'API posterior (a diferència del lloc públic, on ve del
     * primer segment de la URL).
     */
    private function currentTenant(Request $request): Tenant
    {
        $abilities = $request->user()->currentAccessToken()->abilities ?? [];

        foreach ($abilities as $ability) {
            if (str_starts_with($ability, 'tenant:')) {
                $tenant = Tenant::find((int) substr($ability, 7));
                if ($tenant) {
                    return $tenant;
                }
            }
        }

        abort(500, 'El token no té cap entitat associada.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sessió tancada correctament.']);
    }

    private function respondWithToken(CampusStudent|CampusTeacher|AssociatMember $user, string $role, Tenant $tenant)
    {
        $token = $user->createToken('gestio-app', ["tenant:{$tenant->id}"])->plainTextToken;

        return response()->json([
            'token'  => $token,
            'role'   => $role,
            'tenant' => ['slug' => $tenant->slug, 'name' => $tenant->name],
            'user'   => new UserResource($user),
        ]);
    }
}
