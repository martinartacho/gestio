<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberResource;
use App\Models\AssociatMember;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function profile(Request $request)
    {
        $member = $request->user();
        abort_unless($member instanceof AssociatMember, 403);

        return new MemberResource($member);
    }
}
