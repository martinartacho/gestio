<?php

namespace App\Http\Controllers\Associats;

use App\Http\Controllers\Controller;
use App\Support\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberPortalController extends Controller
{
    public function card(): View
    {
        $member = auth('member')->user();

        return view('associats.portal.card', compact('member'));
    }

    public function profile(): View
    {
        $member = auth('member')->user();

        return view('associats.portal.profile', compact('member'));
    }

    public function updateLocale(Request $request): RedirectResponse
    {
        $data = $request->validate(['locale' => Locales::rule()]);

        $member = auth('member')->user();
        $member->update($data);

        // El missatge de confirmació ja en el nou idioma.
        app()->setLocale($member->locale ?? app()->getLocale());

        return back()->with('success', __('site.language_saved'));
    }
}
