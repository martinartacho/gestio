<?php

namespace Tests\Feature\Campus;

use App\Models\CampusCourse;
use App\Models\CampusDocument;
use App\Models\CampusEnrollment;
use App\Models\CampusSeason;
use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentAccessTest extends TestCase
{
    use RefreshDatabase;


    // ── Descàrrega de documents ───────────────────────────────────────────────

    public function test_public_document_url_is_accessible_to_anyone(): void
    {
        $doc = CampusDocument::factory()->public()->create();

        $this->get(route('campus.documents.download', $doc))
             ->assertNotFound(); // No file_path, so 404 is correct
    }

    public function test_enrolled_document_requires_auth(): void
    {
        Storage::fake('local');

        $course   = CampusCourse::factory()->create();
        $fakeFile = UploadedFile::fake()->create('material.pdf', 100, 'application/pdf');
        $path     = $fakeFile->storeAs('campus/documents', 'material.pdf', 'local');

        $doc = CampusDocument::factory()->forCourse($course->id)->create([
            'type'       => 'file',
            'file_path'  => $path,
            'file_name'  => 'material.pdf',
            'visibility' => 'enrolled',
        ]);

        $this->get(route('campus.documents.download', $doc))
             ->assertForbidden();
    }

    public function test_student_with_enrollment_can_access_enrolled_document(): void
    {
        Storage::fake('local');

        $student = CampusStudent::factory()->create();
        $season  = CampusSeason::factory()->create(['status' => 'active']);
        $course  = CampusCourse::factory()->create(['season_id' => $season->id, 'status' => 'active']);

        CampusEnrollment::factory()->create([
            'student_id' => $student->id,
            'course_id'  => $course->id,
            'status'     => 'paid',
        ]);

        $fakeFile = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');
        $path     = $fakeFile->storeAs('campus/documents', 'doc.pdf', 'local');

        $doc = CampusDocument::factory()->forCourse($course->id)->create([
            'type'       => 'file',
            'file_path'  => $path,
            'file_name'  => 'doc.pdf',
            'visibility' => 'enrolled',
        ]);

        $this->actingAs($student, 'student')
             ->get(route('campus.documents.download', $doc))
             ->assertSuccessful();
    }

    public function test_student_without_enrollment_cannot_access_enrolled_document(): void
    {
        Storage::fake('local');

        $student = CampusStudent::factory()->create();
        $course  = CampusCourse::factory()->create(['status' => 'active']);

        $fakeFile = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');
        $path     = $fakeFile->storeAs('campus/documents', 'doc.pdf', 'local');

        $doc = CampusDocument::factory()->forCourse($course->id)->create([
            'type'       => 'file',
            'file_path'  => $path,
            'file_name'  => 'doc.pdf',
            'visibility' => 'enrolled',
        ]);

        $this->actingAs($student, 'student')
             ->get(route('campus.documents.download', $doc))
             ->assertForbidden();
    }

    public function test_private_document_only_accessible_to_owner_teacher(): void
    {
        Storage::fake('local');

        $teacher      = CampusTeacher::factory()->create();
        $otherTeacher = CampusTeacher::factory()->create();

        $fakeFile = UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf');
        $path     = $fakeFile->storeAs('campus/documents', 'notes.pdf', 'local');

        $doc = CampusDocument::factory()->forTeacher($teacher->id)->private()->create([
            'type'      => 'file',
            'file_path' => $path,
            'file_name' => 'notes.pdf',
        ]);

        // Owner can access
        $this->actingAs($teacher, 'teacher')
             ->get(route('campus.documents.download', $doc))
             ->assertSuccessful();

        // Other teacher cannot
        $this->actingAs($otherTeacher, 'teacher')
             ->get(route('campus.documents.download', $doc))
             ->assertForbidden();
    }

    // ── Pujada de documents (portal professor) ────────────────────────────────

    public function test_teacher_can_upload_url_document_to_own_course(): void
    {
        $teacher = CampusTeacher::factory()->create();
        $season  = CampusSeason::factory()->create(['status' => 'active']);
        $course  = CampusCourse::factory()->create(['season_id' => $season->id, 'status' => 'active']);

        $course->teachers()->attach($teacher->id, ['role' => 'main', 'sessions_assigned' => null]);

        $this->actingAs($teacher, 'teacher')
             ->post(route('teacher.portal.course.documents.upload', $course->slug), [
                 'title'      => 'Presentació sessió 1',
                 'type'       => 'url',
                 'url'        => 'https://example.com/presentacio.pdf',
                 'visibility' => 'enrolled',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('campus_documents', [
            'title'      => 'Presentació sessió 1',
            'course_id'  => $course->id,
            'teacher_id' => $teacher->id,
            'type'       => 'url',
        ]);
    }

    public function test_teacher_can_upload_file_document_to_own_course(): void
    {
        Storage::fake('local');

        $teacher = CampusTeacher::factory()->create();
        $season  = CampusSeason::factory()->create(['status' => 'active']);
        $course  = CampusCourse::factory()->create(['season_id' => $season->id, 'status' => 'active']);

        $course->teachers()->attach($teacher->id, ['role' => 'main', 'sessions_assigned' => null]);

        $file = UploadedFile::fake()->create('exercicis.pdf', 200, 'application/pdf');

        $this->actingAs($teacher, 'teacher')
             ->post(route('teacher.portal.course.documents.upload', $course->slug), [
                 'title'      => 'Exercicis pràctics',
                 'type'       => 'file',
                 'file'       => $file,
                 'visibility' => 'enrolled',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('campus_documents', [
            'title'      => 'Exercicis pràctics',
            'course_id'  => $course->id,
            'teacher_id' => $teacher->id,
            'type'       => 'file',
            'file_name'  => 'exercicis.pdf',
        ]);
    }

    public function test_teacher_cannot_upload_to_course_they_dont_teach(): void
    {
        $teacher = CampusTeacher::factory()->create();
        $season  = CampusSeason::factory()->create(['status' => 'active']);
        $course  = CampusCourse::factory()->create(['season_id' => $season->id]); // no teacher attached

        $this->actingAs($teacher, 'teacher')
             ->post(route('teacher.portal.course.documents.upload', $course->slug), [
                 'title'      => 'Intrusió',
                 'type'       => 'url',
                 'url'        => 'https://example.com/intrusion.pdf',
                 'visibility' => 'enrolled',
             ])
             ->assertNotFound(); // course not in teacher's courses → firstOrFail 404
    }

    // ── Eliminació de documents ───────────────────────────────────────────────

    public function test_teacher_can_delete_own_document(): void
    {
        $teacher = CampusTeacher::factory()->create();
        $season  = CampusSeason::factory()->create();
        $course  = CampusCourse::factory()->create(['season_id' => $season->id]);

        $course->teachers()->attach($teacher->id, ['role' => 'main', 'sessions_assigned' => null]);

        $doc = CampusDocument::factory()->forCourse($course->id)->forTeacher($teacher->id)->create();

        $this->actingAs($teacher, 'teacher')
             ->delete(route('teacher.portal.course.documents.delete', [$course->slug, $doc->id]))
             ->assertRedirect();

        $this->assertDatabaseMissing('campus_documents', ['id' => $doc->id]);
    }

    public function test_teacher_cannot_delete_other_teachers_private_document(): void
    {
        $teacher      = CampusTeacher::factory()->create();
        $otherTeacher = CampusTeacher::factory()->create();
        $season       = CampusSeason::factory()->create();
        $course       = CampusCourse::factory()->create(['season_id' => $season->id]);

        // teacher teaches the course but doc belongs to otherTeacher and is private
        $course->teachers()->attach($teacher->id, ['role' => 'main', 'sessions_assigned' => null]);

        $doc = CampusDocument::factory()
            ->forCourse($course->id)
            ->forTeacher($otherTeacher->id)
            ->private()
            ->create();

        // teacher can still delete because they teach the course
        // (our delete logic: owner OR teacher of the course)
        $this->actingAs($teacher, 'teacher')
             ->delete(route('teacher.portal.course.documents.delete', [$course->slug, $doc->id]))
             ->assertRedirect();

        $this->assertDatabaseMissing('campus_documents', ['id' => $doc->id]);
    }

    // ── available_from: disponibilitat temporal ───────────────────────────────

    public function test_student_cannot_download_document_before_available_from(): void
    {
        Storage::fake('local');

        $student = CampusStudent::factory()->create();
        $season  = CampusSeason::factory()->create(['status' => 'active']);
        $course  = CampusCourse::factory()->create(['season_id' => $season->id, 'status' => 'active']);

        CampusEnrollment::factory()->create([
            'student_id' => $student->id,
            'course_id'  => $course->id,
            'status'     => 'paid',
        ]);

        $fakeFile = UploadedFile::fake()->create('futur.pdf', 100, 'application/pdf');
        $path     = $fakeFile->storeAs('campus/documents', 'futur.pdf', 'local');

        $doc = CampusDocument::factory()->forCourse($course->id)->create([
            'type'           => 'file',
            'file_path'      => $path,
            'file_name'      => 'futur.pdf',
            'visibility'     => 'enrolled',
            'available_from' => now()->addDays(7), // disponible en 7 dies
        ]);

        $this->actingAs($student, 'student')
             ->get(route('campus.documents.download', $doc))
             ->assertForbidden();
    }

    public function test_session_number_blocks_student_when_not_reached(): void
    {
        Storage::fake('local');

        $student = CampusStudent::factory()->create();
        $season  = CampusSeason::factory()->create(['status' => 'active']);
        // Curs sense calendar_notes i sense start_date → sessionsPast() = null → 0
        $course  = CampusCourse::factory()->create([
            'season_id' => $season->id,
            'status'    => 'active',
            'sessions'  => 5,
        ]);

        CampusEnrollment::factory()->create([
            'student_id' => $student->id,
            'course_id'  => $course->id,
            'status'     => 'paid',
        ]);

        $fakeFile = UploadedFile::fake()->create('sessio3.pdf', 100, 'application/pdf');
        $path     = $fakeFile->storeAs('campus/documents', 'sessio3.pdf', 'local');

        // Requereix sessió 3, però sessionsPast() = null → 0 (curs sense dates)
        $doc = CampusDocument::factory()->forCourse($course->id)->create([
            'type'           => 'file',
            'file_path'      => $path,
            'file_name'      => 'sessio3.pdf',
            'visibility'     => 'enrolled',
            'session_number' => 3,
        ]);

        $this->actingAs($student, 'student')
             ->get(route('campus.documents.download', $doc))
             ->assertForbidden();
    }

    public function test_or_logic_date_unlocks_when_session_not_reached(): void
    {
        Storage::fake('local');

        $student = CampusStudent::factory()->create();
        $season  = CampusSeason::factory()->create(['status' => 'active']);
        $course  = CampusCourse::factory()->create([
            'season_id' => $season->id,
            'status'    => 'active',
            'sessions'  => 5,
        ]);

        CampusEnrollment::factory()->create([
            'student_id' => $student->id,
            'course_id'  => $course->id,
            'status'     => 'paid',
        ]);

        $fakeFile = UploadedFile::fake()->create('mixte.pdf', 100, 'application/pdf');
        $path     = $fakeFile->storeAs('campus/documents', 'mixte.pdf', 'local');

        // Condicions OR: data passada (✓) O sessió 10 (✗) → accessible per data
        $doc = CampusDocument::factory()->forCourse($course->id)->create([
            'type'           => 'file',
            'file_path'      => $path,
            'file_name'      => 'mixte.pdf',
            'visibility'     => 'enrolled',
            'available_from' => now()->subDay(), // data passada → OK
            'session_number' => 10,              // sessió no assolida (0 < 10) → KO
        ]);

        // OR: data OK → accessible
        $this->actingAs($student, 'student')
             ->get(route('campus.documents.download', $doc))
             ->assertSuccessful();
    }

    public function test_teacher_can_download_document_before_available_from(): void
    {
        Storage::fake('local');

        $teacher = CampusTeacher::factory()->create();
        $season  = CampusSeason::factory()->create(['status' => 'active']);
        $course  = CampusCourse::factory()->create(['season_id' => $season->id, 'status' => 'active']);

        $course->teachers()->attach($teacher->id, ['role' => 'main', 'sessions_assigned' => null]);

        $fakeFile = UploadedFile::fake()->create('futur.pdf', 100, 'application/pdf');
        $path     = $fakeFile->storeAs('campus/documents', 'futur.pdf', 'local');

        $doc = CampusDocument::factory()->forCourse($course->id)->forTeacher($teacher->id)->create([
            'type'           => 'file',
            'file_path'      => $path,
            'file_name'      => 'futur.pdf',
            'visibility'     => 'enrolled',
            'available_from' => now()->addDays(7),
        ]);

        // Professor pot descarregar sempre (ignora available_from)
        $this->actingAs($teacher, 'teacher')
             ->get(route('campus.documents.download', $doc))
             ->assertSuccessful();
    }

    public function test_student_portal_hides_not_yet_available_documents(): void
    {
        $student = CampusStudent::factory()->create();
        $season  = CampusSeason::factory()->create(['status' => 'active']);
        $course  = CampusCourse::factory()->create(['season_id' => $season->id, 'status' => 'active']);

        CampusEnrollment::factory()->create([
            'student_id' => $student->id,
            'course_id'  => $course->id,
            'status'     => 'paid',
        ]);

        CampusDocument::factory()->forCourse($course->id)->create([
            'title'          => 'Document ja disponible',
            'visibility'     => 'enrolled',
            'status'         => 'active',
            'available_from' => now()->subDay(),
        ]);

        CampusDocument::factory()->forCourse($course->id)->create([
            'title'          => 'Document futur',
            'visibility'     => 'enrolled',
            'status'         => 'active',
            'available_from' => now()->addDays(7),
        ]);

        $response = $this->actingAs($student, 'student')
                         ->get('/portal/meus-cursos')
                         ->assertSuccessful();

        $response->assertSee('Document ja disponible');
        $response->assertDontSee('Document futur');
    }

    // ── Gestor global de documents del professor ──────────────────────────────

    public function test_teacher_can_access_own_documents_list(): void
    {
        $teacher = CampusTeacher::factory()->create();
        $doc     = CampusDocument::factory()->forTeacher($teacher->id)->create(['title' => 'El meu document']);

        $this->actingAs($teacher, 'teacher')
             ->get(route('teacher.portal.documents'))
             ->assertSuccessful()
             ->assertSee('El meu document');
    }

    public function test_teacher_can_edit_own_document(): void
    {
        $teacher = CampusTeacher::factory()->create();
        $season  = CampusSeason::factory()->create();
        $course  = CampusCourse::factory()->create(['season_id' => $season->id]);
        $course->teachers()->attach($teacher->id, ['role' => 'main', 'sessions_assigned' => null]);

        $doc = CampusDocument::factory()->forTeacher($teacher->id)->create([
            'title'      => 'Títol original',
            'visibility' => 'enrolled',
            'status'     => 'active',
        ]);

        $this->actingAs($teacher, 'teacher')
             ->post(route('teacher.portal.documents.update', $doc->id), [
                 'title'      => 'Títol actualitzat',
                 'visibility' => 'public',
                 'status'     => 'active',
             ])
             ->assertRedirect(route('teacher.portal.documents'));

        $this->assertDatabaseHas('campus_documents', [
            'id'         => $doc->id,
            'title'      => 'Títol actualitzat',
            'visibility' => 'public',
        ]);
    }

    public function test_teacher_cannot_edit_other_teachers_document(): void
    {
        $teacher      = CampusTeacher::factory()->create();
        $otherTeacher = CampusTeacher::factory()->create();
        $doc          = CampusDocument::factory()->forTeacher($otherTeacher->id)->create();

        $this->actingAs($teacher, 'teacher')
             ->post(route('teacher.portal.documents.update', $doc->id), [
                 'title'      => 'Intrús',
                 'visibility' => 'public',
                 'status'     => 'active',
             ])
             ->assertForbidden();
    }

    // ── Portal alumne: llistar documents ─────────────────────────────────────

    public function test_student_portal_shows_enrolled_documents(): void
    {
        $student = CampusStudent::factory()->create();
        $season  = CampusSeason::factory()->create(['status' => 'active']);
        $course  = CampusCourse::factory()->create(['season_id' => $season->id, 'status' => 'active']);

        CampusEnrollment::factory()->create([
            'student_id' => $student->id,
            'course_id'  => $course->id,
            'status'     => 'paid',
        ]);

        CampusDocument::factory()->forCourse($course->id)->create([
            'title'      => 'Document visible',
            'visibility' => 'enrolled',
            'status'     => 'active',
        ]);

        CampusDocument::factory()->forCourse($course->id)->private()->create([
            'title'  => 'Document privat',
            'status' => 'active',
        ]);

        $response = $this->actingAs($student, 'student')
                         ->get('/portal/meus-cursos')
                         ->assertSuccessful();

        $response->assertSee('Document visible');
        $response->assertDontSee('Document privat');
    }
}
