<?php

namespace Database\Seeders;

use App\Models\CampusCategory;
use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusSeason;
use App\Models\CampusSpace;
use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use App\Models\CampusTimeSlot;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    // ── Configuració ────────────────────────────────────────────────────────
    // Canvia DEMO_YEAR per adaptar la demo a un altre any.
    // Per activar / desactivar: admin → Períodes → canviar estat de "Demo XXXX".
    const DEMO_YEAR = 2026;

    public function run(): void
    {
        // ── Temporada demo ───────────────────────────────────────────────────
        // quadrimester=9 és reservat per a demo (no col·lisiona amb 1=tardor, 2=primavera)
        $season = CampusSeason::firstOrCreate(
            ['year' => self::DEMO_YEAR, 'quadrimester' => 9],
            [
                'name'                  => 'Demo ' . self::DEMO_YEAR,
                'start_date'            => now()->subDays(30)->toDateString(),
                'end_date'              => now()->addYear()->toDateString(),
                'start_date_enrollment' => now()->subDays(30)->toDateString(),
                'end_date_enrollment'   => now()->addDays(180)->toDateString(),
                'status'                => 'active',
            ]
        );

        // ── Recursos existents ───────────────────────────────────────────────
        $catSalut = CampusCategory::where('name', 'Salut i Benestar')->first();
        $catTech  = CampusCategory::where('name', 'Informàtica i Competències Digitals')->first();
        $catArts  = CampusCategory::where('name', 'Arts i Humanitats')->first();
        $catMedi  = CampusCategory::where('name', 'Activitat Física i Natura')->first();
        $catSoc   = CampusCategory::where('name', 'Societat i Desenvolupament Personal')->first();

        $spAM1    = CampusSpace::where('code', 'AM1')->first();
        $spAM2    = CampusSpace::where('code', 'AM2')->first();
        $spAP1    = CampusSpace::where('code', 'AP1')->first();
        $spOnline = CampusSpace::where('code', 'ONLINE')->first();
        $spUPC    = CampusSpace::where('code', 'UPC')->first();

        $tAnna    = CampusTeacher::where('code', 'ANNBER')->first();
        $tMarta   = CampusTeacher::where('code', 'MARSOL')->first();
        $tLaura   = CampusTeacher::where('code', 'LAUMAR')->first();
        $tJoan    = CampusTeacher::where('code', 'JOASEG')->first();
        $tClaudia = CampusTeacher::where('code', 'CLAROS')->first();
        $tMiquel  = CampusTeacher::where('code', 'MIGPUI')->first();
        $tNuria   = CampusTeacher::where('code', 'NURFIT')->first();

        $slDL10 = CampusTimeSlot::where('code', 'DL10')->first();
        $slDL16 = CampusTimeSlot::where('code', 'DL16')->first();
        $slDT18 = CampusTimeSlot::where('code', 'DT18')->first();
        $slDC16 = CampusTimeSlot::where('code', 'DC16')->first();
        $slDJ10 = CampusTimeSlot::where('code', 'DJ10')->first();
        $slDV10 = CampusTimeSlot::where('code', 'DV10')->first();

        // ── Helper ────────────────────────────────────────────────────────────
        $mkCourse = function (array $data, CampusTeacher $teacher, ?CampusTeacher $assistant = null) {
            $slug = $data['slug'];
            unset($data['slug']);
            $course = CampusCourse::firstOrCreate(['slug' => $slug], $data);
            $course->teachers()->syncWithoutDetaching([$teacher->id => ['role' => 'main']]);
            if ($assistant) {
                $course->teachers()->syncWithoutDetaching([$assistant->id => ['role' => 'assistant']]);
            }
            return $course;
        };

        // ── Cursos demo ───────────────────────────────────────────────────────
        // 6 cursos de tipologia variada: salut×2, tech, arts, societat, medi (1 gratuït)
        $c1 = $mkCourse([
            'slug'            => 'demo-primers-auxilis',
            'code'            => 'DEMO-SAN01',
            'title'           => 'Primers Auxilis i RCP',
            'description'     => 'Tècniques bàsiques de socorrisme i reanimació cardiopulmonar. Curs homologat.',
            'objectives'      => 'Capacitar per actuar en situacions d\'emergència amb seguretat i eficàcia.',
            'season_id'       => $season->id,
            'category_id'     => $catSalut->id,
            'space_id'        => $spAM1->id,
            'time_slot_id'    => $slDL10->id,
            'sessions'        => 8,
            'hours'           => 12,
            'max_students'    => 20,
            'price'           => 20.00,
            'format'          => 'presencial',
            'status'          => 'active',
            'is_public'       => true,
            'open_enrollment' => true,
            'start_date'      => now()->addDays(10)->toDateString(),
            'end_date'        => now()->addDays(70)->toDateString(),
            'calendar_notes'  => '8 sessions cada dilluns 10:00–11:30',
        ], $tAnna);

        $c2 = $mkCourse([
            'slug'            => 'demo-python-basics',
            'code'            => 'DEMO-TEC01',
            'title'           => 'Python per a No Programadors',
            'description'     => 'Introducció a la programació amb Python. Sense coneixements previs.',
            'objectives'      => 'Automatitzar tasques quotidianes i analitzar dades amb codi Python.',
            'season_id'       => $season->id,
            'category_id'     => $catTech->id,
            'space_id'        => $spOnline->id,
            'time_slot_id'    => $slDT18->id,
            'sessions'        => 10,
            'hours'           => 20,
            'max_students'    => null,
            'price'           => 25.00,
            'format'          => 'online',
            'status'          => 'active',
            'is_public'       => true,
            'open_enrollment' => true,
            'start_date'      => now()->addDays(14)->toDateString(),
            'end_date'        => now()->addDays(85)->toDateString(),
            'requirements'    => 'Ordinador amb connexió a internet. Sense experiència en programació.',
        ], $tJoan);

        $c3 = $mkCourse([
            'slug'            => 'demo-aquarella',
            'code'            => 'DEMO-ART01',
            'title'           => 'Aquarel·la per a Principiants',
            'description'     => 'Aprèn la tècnica de l\'aquarel·la des de zero amb materials inclosos.',
            'season_id'       => $season->id,
            'category_id'     => $catArts->id,
            'space_id'        => $spAP1->id,
            'time_slot_id'    => $slDV10->id,
            'sessions'        => 6,
            'hours'           => 9,
            'max_students'    => 12,
            'price'           => 22.00,
            'format'          => 'presencial',
            'status'          => 'active',
            'is_public'       => true,
            'open_enrollment' => true,
            'start_date'      => now()->addDays(7)->toDateString(),
            'end_date'        => now()->addDays(50)->toDateString(),
            'requirements'    => 'Cap experiència necessària. Materials inclosos en el preu.',
        ], $tClaudia);

        $c4 = $mkCourse([
            'slug'            => 'demo-nutricio-salut',
            'code'            => 'DEMO-SAN02',
            'title'           => 'Nutrició i Salut',
            'description'     => 'Alimentació equilibrada per a adults. Mites, realitats i consells pràctics.',
            'objectives'      => 'Entendre els principis de la nutrició i aplicar-los a la vida diària.',
            'season_id'       => $season->id,
            'category_id'     => $catSalut->id,
            'space_id'        => $spAM2->id,
            'time_slot_id'    => $slDJ10->id,
            'sessions'        => 6,
            'hours'           => 9,
            'max_students'    => 20,
            'price'           => 18.00,
            'format'          => 'presencial',
            'status'          => 'active',
            'is_public'       => true,
            'open_enrollment' => true,
            'start_date'      => now()->addDays(5)->toDateString(),
            'end_date'        => now()->addDays(45)->toDateString(),
        ], $tNuria);

        $c5 = $mkCourse([
            'slug'            => 'demo-comunicacio-efectiva',
            'code'            => 'DEMO-SOC01',
            'title'           => 'Comunicació Efectiva',
            'description'     => 'Eines pràctiques per millorar la comunicació interpersonal i professional.',
            'objectives'      => 'Gestionar conflictes, millorar l\'escolta activa i l\'assertivitat.',
            'season_id'       => $season->id,
            'category_id'     => $catSoc->id,
            'space_id'        => $spUPC->id,
            'time_slot_id'    => $slDC16->id,
            'sessions'        => 5,
            'hours'           => 7,
            'max_students'    => 25,
            'price'           => 15.00,
            'format'          => 'semipresencial',
            'status'          => 'active',
            'is_public'       => true,
            'open_enrollment' => true,
            'start_date'      => now()->addDays(12)->toDateString(),
            'end_date'        => now()->addDays(55)->toDateString(),
        ], $tLaura, $tMarta);

        $c6 = $mkCourse([
            'slug'            => 'demo-medi-natural',
            'code'            => 'DEMO-MED01',
            'title'           => 'Medi Natural i Senderisme',
            'description'     => 'Curs gratuït d\'introducció al medi natural i les rutes de senderisme locals.',
            'season_id'       => $season->id,
            'category_id'     => $catMedi->id,
            'space_id'        => $spOnline->id,
            'time_slot_id'    => $slDL16->id,
            'sessions'        => 4,
            'hours'           => 6,
            'max_students'    => null,
            'price'           => 0.00,
            'format'          => 'online',
            'status'          => 'active',
            'is_public'       => true,
            'open_enrollment' => true,
            'start_date'      => now()->addDays(3)->toDateString(),
            'end_date'        => now()->addDays(30)->toDateString(),
        ], $tMiquel);

        // ── Inscripcions demo ─────────────────────────────────────────────────
        $mail     = config('seeder.mail', 'app.test');
        $students = CampusStudent::whereIn('email', [
            "marc.puig@{$mail}",
            "laura.ferrer@{$mail}",
            "jordi.vila@{$mail}",
            "marta.soler@{$mail}",
        ])->get()->keyBy('email');

        $marc  = $students["marc.puig@{$mail}"]   ?? null;
        $laura = $students["laura.ferrer@{$mail}"] ?? null;
        $jordi = $students["jordi.vila@{$mail}"]   ?? null;
        $marta = $students["marta.soler@{$mail}"]  ?? null;

        // Pagades — demostren el flux complet
        $this->seedEnrollment($marc,  $c1, 'paid',    'stripe',   20.00);
        $this->seedEnrollment($marc,  $c2, 'paid',    'transfer', 25.00);
        $this->seedEnrollment($laura, $c3, 'paid',    'stripe',   22.00);
        $this->seedEnrollment($jordi, $c5, 'paid',    'cash',     15.00);
        $this->seedEnrollment($marta, $c4, 'paid',    'stripe',   18.00);
        $this->seedEnrollment($marta, $c6, 'paid',    'free',      0.00);

        // Pendents — demostren el flux d'aprovació manual
        $this->seedEnrollment($laura, $c1, 'pending', 'bizum',    20.00);
        $this->seedEnrollment($jordi, $c2, 'pending', 'transfer', 25.00);

        $this->command->info('✅ DemoSeeder: "Demo ' . self::DEMO_YEAR . '" — 6 cursos, 8 inscripcions.');
        $this->command->info('   → Per desactivar: Filament → Períodes → estat "Esborrany" o "Tancat".');
    }

    private function seedEnrollment(
        ?CampusStudent $student,
        ?CampusCourse  $course,
        string         $status,
        string         $method,
        float          $amount
    ): void {
        if (! $student || ! $course) return;

        CampusEnrollment::firstOrCreate(
            ['student_id' => $student->id, 'course_id' => $course->id],
            [
                'first_name'      => $student->first_name,
                'last_name'       => $student->last_name,
                'email'           => $student->email,
                'phone'           => $student->phone,
                'enrollment_date' => now()->subDays(rand(1, 10))->toDateString(),
                'status'          => $status,
                'payment_method'  => $method,
                'amount'          => $amount,
                'paid_at'         => $status === 'paid' ? now()->subDays(rand(1, 7)) : null,
            ]
        );
    }
}
