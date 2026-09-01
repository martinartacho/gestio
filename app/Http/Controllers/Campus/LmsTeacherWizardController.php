<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusSeason;
use App\Models\LmsLesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LmsTeacherWizardController extends Controller
{
    // ─── Pas 1: dades bàsiques ────────────────────────────────────────────────

    public function step1(): View
    {
        $seasons = CampusSeason::where('tenant_id', current_tenant()?->id)->orderBy('year', 'desc')->get();
        $draft   = session('wizard_course', []);

        return view('campus.lms.teacher.wizard.step1', compact('seasons', 'draft'));
    }

    public function storeStep1(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'format'         => 'required|in:presencial,online,semipresencial,hibrid',
            'season_id'      => ['required', Rule::exists('campus_seasons', 'id')
                ->where('tenant_id', current_tenant()?->id)],
            'sessions_count' => 'required|integer|min:1|max:30',
            'description'    => 'nullable|string|max:2000',
            'objectives'     => 'nullable|string|max:2000',
        ]);

        session(['wizard_course' => $validated]);

        return redirect()->route('teacher.lms.wizard.step2');
    }

    // ─── Pas 2: títols de sessió ──────────────────────────────────────────────

    public function step2(): RedirectResponse|View
    {
        $draft = session('wizard_course');

        if (! $draft) {
            return redirect()->route('teacher.lms.wizard.step1');
        }

        $sessionSlots   = range(1, (int) $draft['sessions_count']);
        $sessionTitles  = session('wizard_sessions', []);

        return view('campus.lms.teacher.wizard.step2', compact('draft', 'sessionSlots', 'sessionTitles'));
    }

    public function storeStep2(Request $request): RedirectResponse
    {
        $request->validate([
            'sessions'          => 'required|array',
            'sessions.*.title'  => 'required|string|max:255',
        ]);

        session(['wizard_sessions' => $request->input('sessions')]);

        return redirect()->route('teacher.lms.wizard.step3');
    }

    // ─── Pas 3: revisió ───────────────────────────────────────────────────────

    public function step3(): RedirectResponse|View
    {
        $draft         = session('wizard_course');
        $sessionTitles = session('wizard_sessions');

        if (! $draft || ! $sessionTitles) {
            return redirect()->route('teacher.lms.wizard.step1');
        }

        $season  = CampusSeason::find($draft['season_id']);
        $formats = CampusCourse::FORMATS;

        return view('campus.lms.teacher.wizard.step3', compact('draft', 'sessionTitles', 'season', 'formats'));
    }

    // ─── Confirm: crear curs i sessions ──────────────────────────────────────

    public function confirm(Request $request): RedirectResponse
    {
        $draft         = session('wizard_course');
        $sessionTitles = session('wizard_sessions');

        abort_if(! $draft || ! $sessionTitles, 422);

        $teacher = auth('teacher')->user();

        $course = DB::transaction(function () use ($draft, $sessionTitles, $teacher) {
            // Codi únic format TCH-YYYYMM-XXXX
            $code = 'TCH-' . now()->format('Ym') . '-' . strtoupper(Str::random(4));

            $course = CampusCourse::create([
                'tenant_id'   => current_tenant()?->id,
                'code'        => $code,
                'title'       => $draft['title'],
                'season_id'   => $draft['season_id'],
                'sessions'    => count($sessionTitles),
                'format'      => $draft['format'],
                'description' => $draft['description'] ?? null,
                'objectives'  => $draft['objectives'] ?? null,
                'status'      => 'draft',
                'is_public'   => false,
            ]);

            $course->teachers()->attach($teacher->id, [
                'role'              => 'main',
                'sessions_assigned' => count($sessionTitles),
            ]);

            foreach ($sessionTitles as $i => $session) {
                $num = (int) $i;
                LmsLesson::create([
                    'course_id'      => $course->id,
                    'title'          => $session['title'],
                    'session_number' => $num,
                    'sort_order'     => $num,
                    'status'         => 'draft',
                ]);
            }

            return $course;
        });

        session()->forget(['wizard_course', 'wizard_sessions']);

        return redirect()
            ->route('teacher.lms.course', $course->slug)
            ->with('success', '✓ Curs creat correctament. En espera d\'aprovació de l\'administrador.');
    }
}
