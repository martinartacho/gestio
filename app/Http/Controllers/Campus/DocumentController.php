<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusDocument;
use App\Models\CampusEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * Descàrrega segura d'un document.
     * Comprova que l'usuari (alumne o professor) té accés.
     */
    public function download(Request $request, CampusDocument $document): StreamedResponse
    {
        abort_if(! $document->isFile() || ! $document->file_path, 404);
        abort_if(! Storage::disk('local')->exists($document->file_path), 404);

        $this->checkAccess($document);

        return Storage::disk('local')->download(
            $document->file_path,
            $document->file_name ?? basename($document->file_path)
        );
    }

    private function checkAccess(CampusDocument $document): void
    {
        $student = auth('student')->user();
        $teacher = auth('teacher')->user();
        $admin   = auth('web')->user();

        // Admin sempre pot descarregar
        if ($admin) return;

        // ── Disponibilitat temporal (OR: data O sessió) ────────────────────
        // Professors bypassen la restricció temporal (veuen tots els seus docs)
        if (! $teacher && ! $this->isAvailable($document)) {
            abort(403, 'El document encara no és accessible.');
        }

        // ── Visibilitat ────────────────────────────────────────────────────
        if ($document->visibility === 'public') {
            return;
        }

        if ($document->visibility === 'private') {
            abort_unless(
                $teacher && $teacher->id === $document->teacher_id,
                403
            );
            return;
        }

        // visibility = 'enrolled'
        if ($teacher) {
            abort_unless(
                $teacher->courses()->where('campus_courses.id', $document->course_id)->exists()
                || $teacher->id === $document->teacher_id,
                403
            );
            return;
        }

        if ($student && $document->course_id) {
            $courseIds = collect([$document->course_id]);
            if ($document->course?->parent_id) {
                $courseIds->push($document->course->parent_id);
            }
            if ($document->inherit_to_editions) {
                $childIds = \App\Models\CampusCourse::where('parent_id', $document->course_id)
                    ->pluck('id');
                $courseIds = $courseIds->merge($childIds);
            }

            $enrolled = CampusEnrollment::where('student_id', $student->id)
                ->whereIn('course_id', $courseIds)
                ->whereIn('status', ['paid', 'pending'])
                ->exists();

            abort_unless($enrolled, 403);
            return;
        }

        abort(403);
    }

    /**
     * Comprova si el document és disponible ara per a l'alumne.
     *
     * Lògica OR: el document s'activa quan ES COMPLEIX qualsevol de les
     * condicions configurades (data OU sessió). Si no hi ha cap condició,
     * el document és immediatament accessible.
     *
     * Exemples:
     *   - Només available_from (passat)   → accessible
     *   - Només available_from (futur)    → bloquejat
     *   - Només session_number (assolit)  → accessible
     *   - Només session_number (no assolit) → bloquejat
     *   - Tots dos: si data passada OR sessió assolida → accessible
     */
    private function isAvailable(CampusDocument $document): bool
    {
        $conditions = [];

        if ($document->available_from) {
            $conditions[] = now()->gte($document->available_from);
        }

        if ($document->session_number && $document->course_id) {
            $course      = $document->course ?? $document->course()->first();
            $sessionsDone = $course?->sessionsPast() ?? 0;
            $conditions[] = $sessionsDone >= $document->session_number;
        }

        // Cap condició configurada → accessible sempre
        if (empty($conditions)) {
            return true;
        }

        // OR: n'hi ha prou amb que una condició es compleixi
        return in_array(true, $conditions, true);
    }
}
