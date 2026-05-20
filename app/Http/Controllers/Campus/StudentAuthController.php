<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class StudentAuthController extends Controller
{
    public function showLogin()
    {
        return view('campus.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('student')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('campus.portal.courses'));
        }

        return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('campus.auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name'   => ['required', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'unique:campus_students,email'],
            'password'     => ['required', 'confirmed', Password::min(8)],
            'phone'        => ['nullable', 'string', 'max:20'],
            'data_consent' => ['accepted'],
        ]);

        $data['data_consent'] = true;

        $student = CampusStudent::create($data);

        Auth::guard('student')->login($student);
        $request->session()->regenerate();

        return redirect()->route('campus.portal.courses')
            ->with('success', 'Benvingut/da, ' . $student->first_name . '!');
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('campus.catalog.index');
    }
}
