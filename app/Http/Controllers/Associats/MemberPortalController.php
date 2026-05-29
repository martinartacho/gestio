<?php

namespace App\Http\Controllers\Associats;

use App\Http\Controllers\Controller;
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
}
