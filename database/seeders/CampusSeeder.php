<?php

namespace Database\Seeders;

use App\Models\CampusCategory;
use App\Models\CampusCourse;
use App\Models\CampusSeason;
use App\Models\CampusSpace;
use App\Models\CampusTeacher;
use App\Models\CampusTeacherPayment;
use App\Models\CampusTimeSlot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        // ── Categories (10 categories principals) ────────────────────────
        $catDefs = [
            ['name' => 'Arts i Humanitats',                 'color' => 'pink',   'order' =>  1],
            ['name' => 'Salut i Benestar',                  'color' => 'red',    'order' =>  2],
            ['name' => 'Ciència i Tecnologia',              'color' => 'orange', 'order' =>  3],
            ['name' => 'Informàtica i Competències Digitals','color' => 'indigo', 'order' =>  4],
            ['name' => 'Empresa i Emprenedoria',            'color' => 'yellow', 'order' =>  5],
            ['name' => 'Educació i Pedagogia',              'color' => 'blue',   'order' =>  6],
            ['name' => 'Llengües i Comunicació',            'color' => 'purple', 'order' =>  7],
            ['name' => 'Societat i Desenvolupament Personal','color' => 'green',  'order' =>  8],
            ['name' => 'Oficis i Competències Pràctiques',  'color' => 'gray',   'order' =>  9],
            ['name' => 'Activitat Física i Natura',         'color' => 'green',  'order' => 10],
        ];
        foreach ($catDefs as $d) {
            CampusCategory::firstOrCreate(
                ['name' => $d['name']],
                array_merge($d, ['slug' => Str::slug($d['name']), 'is_active' => true])
            );
        }

        // Àlies de compatibilitat (noms antics → nous)
        $catArts   = CampusCategory::where('name', 'Arts i Humanitats')->first();
        $catSalut  = CampusCategory::where('name', 'Salut i Benestar')->first();
        $catCienc  = CampusCategory::where('name', 'Ciència i Tecnologia')->first();
        $catTech   = CampusCategory::where('name', 'Informàtica i Competències Digitals')->first();
        $catEduc   = CampusCategory::where('name', 'Educació i Pedagogia')->first();
        $catMedi   = CampusCategory::where('name', 'Activitat Física i Natura')->first();

        // ── Espais ────────────────────────────────────────────────────────
        $spacesDefs = [
            ['name' => "Sala d\'actes",    'code' => 'SA',     'capacity' => 80, 'type' => 'sala_actes'],
            ['name' => 'Aula mitjana 1',  'code' => 'AM1',    'capacity' => 30, 'type' => 'mitjana'],
            ['name' => 'Aula mitjana 2',  'code' => 'AM2',    'capacity' => 30, 'type' => 'mitjana'],
            ['name' => 'Aula petita 1',   'code' => 'AP1',    'capacity' => 15, 'type' => 'petita'],
            ['name' => 'Polivalent',      'code' => 'SP',     'capacity' => 50, 'type' => 'polivalent'],
            ['name' => 'CTUG Roca Umbert','code' => 'CTUG',   'capacity' => 40, 'type' => 'extern'],
            ['name' => 'UPC Vallès',      'code' => 'UPC',    'capacity' => 40, 'type' => 'extern'],
            ['name' => 'Virtual',         'code' => 'ONLINE', 'capacity' => 0,  'type' => 'virtual'],
        ];
        foreach ($spacesDefs as $d) {
            CampusSpace::firstOrCreate(['code' => $d['code']], array_merge($d, ['is_active' => true]));
        }
        $spSA     = CampusSpace::where('code', 'SA')->first();
        $spAM1    = CampusSpace::where('code', 'AM1')->first();
        $spAM2    = CampusSpace::where('code', 'AM2')->first();
        $spAP1    = CampusSpace::where('code', 'AP1')->first();
        $spCTUG   = CampusSpace::where('code', 'CTUG')->first();
        $spUPC    = CampusSpace::where('code', 'UPC')->first();
        $spOnline = CampusSpace::where('code', 'ONLINE')->first();

        // ── Franges horàries ──────────────────────────────────────────────
        $slotsDefs = [
            ['day_of_week' => 1, 'code' => 'DL10', 'start_time' => '10:00', 'end_time' => '11:30', 'description' => 'Dilluns 10:00–11:30'],
            ['day_of_week' => 1, 'code' => 'DL16', 'start_time' => '16:00', 'end_time' => '17:30', 'description' => 'Dilluns 16:00–17:30'],
            ['day_of_week' => 2, 'code' => 'DT10', 'start_time' => '10:00', 'end_time' => '11:30', 'description' => 'Dimarts 10:00–11:30'],
            ['day_of_week' => 2, 'code' => 'DT18', 'start_time' => '18:00', 'end_time' => '19:30', 'description' => 'Dimarts 18:00–19:30'],
            ['day_of_week' => 3, 'code' => 'DC10', 'start_time' => '10:00', 'end_time' => '11:30', 'description' => 'Dimecres 10:00–11:30'],
            ['day_of_week' => 3, 'code' => 'DC16', 'start_time' => '16:00', 'end_time' => '17:30', 'description' => 'Dimecres 16:00–17:30'],
            ['day_of_week' => 4, 'code' => 'DJ10', 'start_time' => '10:00', 'end_time' => '11:30', 'description' => 'Dijous 10:00–11:30'],
            ['day_of_week' => 4, 'code' => 'DJ18', 'start_time' => '18:00', 'end_time' => '20:00', 'description' => 'Dijous 18:00–20:00'],
            ['day_of_week' => 5, 'code' => 'DV10', 'start_time' => '10:00', 'end_time' => '11:30', 'description' => 'Divendres 10:00–11:30'],
        ];
        foreach ($slotsDefs as $d) {
            CampusTimeSlot::firstOrCreate(['code' => $d['code']], array_merge($d, ['is_active' => true]));
        }
        $slDL10 = CampusTimeSlot::where('code', 'DL10')->first();
        $slDL16 = CampusTimeSlot::where('code', 'DL16')->first();
        $slDT10 = CampusTimeSlot::where('code', 'DT10')->first();
        $slDT18 = CampusTimeSlot::where('code', 'DT18')->first();
        $slDC10 = CampusTimeSlot::where('code', 'DC10')->first();
        $slDC16 = CampusTimeSlot::where('code', 'DC16')->first();
        $slDJ10 = CampusTimeSlot::where('code', 'DJ10')->first();
        $slDJ18 = CampusTimeSlot::where('code', 'DJ18')->first();
        $slDV10 = CampusTimeSlot::where('code', 'DV10')->first();

        // ── Professors ────────────────────────────────────────────────────
        $teachersDefs = [
            [
                'code' => 'ANNBER', 'first_name' => 'Anna',    'last_name' => 'Bernad',
                'email' => 'anna.bernad@campus.cat', 'phone' => '600 111 001',
                'specialization' => 'Salut i infermeria', 'status' => 'active',
                'dni' => '40100001A', 'city' => 'Granollers', 'postal_code' => '08401',
                'fiscal_situation' => 'autonom', 'needs_payment' => true,
                'invoice' => false, 'payment_type' => 'own',
                'payment_status' => 'pending', 'data_consent' => true,
                'fiscal_responsibility' => true, 'ceded_confirmation' => true,
            ],
            [
                'code' => 'MARSOL', 'first_name' => 'Marta',   'last_name' => 'Soler',
                'email' => 'marta.soler@campus.cat', 'phone' => '600 111 002',
                'specialization' => 'Educació i pedagogia', 'status' => 'active',
                'dni' => '40100002B', 'city' => 'La Garriga', 'postal_code' => '08530',
                'fiscal_situation' => 'autonom', 'needs_payment' => true,
                'invoice' => true, 'payment_type' => 'own',
                'payment_status' => 'confirmed', 'data_consent' => true,
                'fiscal_responsibility' => true, 'ceded_confirmation' => true,
            ],
            [
                'code' => 'LAUMAR', 'first_name' => 'Laura',   'last_name' => 'Martínez',
                'email' => 'laura.martinez@campus.cat', 'phone' => '600 111 003',
                'specialization' => 'Ciències socials', 'status' => 'active',
                'dni' => '40100003C', 'city' => 'Barcelona', 'postal_code' => '08002',
                'fiscal_situation' => 'particular', 'needs_payment' => false,
                'invoice' => false, 'payment_type' => 'waived',
                'payment_status' => 'confirmed', 'data_consent' => true,
                'fiscal_responsibility' => false, 'ceded_confirmation' => true,
            ],
            [
                'code' => 'JOASEG', 'first_name' => 'Joan',    'last_name' => 'Segura',
                'email' => 'joan.segura@campus.cat', 'phone' => '600 111 004',
                'specialization' => 'Noves tecnologies', 'status' => 'active',
                'dni' => '40100004D', 'city' => 'Montornès del Vallès', 'postal_code' => '08170',
                'fiscal_situation' => 'empresa', 'needs_payment' => true,
                'invoice' => true, 'payment_type' => 'own',
                'payment_status' => 'paid', 'data_consent' => true,
                'fiscal_responsibility' => true, 'ceded_confirmation' => true,
            ],
            [
                'code' => 'CLAROS', 'first_name' => 'Clàudia', 'last_name' => 'Ros',
                'email' => 'claudia.ros@campus.cat', 'phone' => '600 111 005',
                'specialization' => 'Arts visuals i creativitat', 'status' => 'active',
                'dni' => '40100005E', 'city' => 'Cardedeu', 'postal_code' => '08440',
                'fiscal_situation' => 'autonom', 'needs_payment' => true,
                'invoice' => false, 'payment_type' => 'own',
                'payment_status' => 'pending', 'data_consent' => true,
                'fiscal_responsibility' => true, 'ceded_confirmation' => false,
            ],
            [
                'code' => 'MIGPUI', 'first_name' => 'Miquel',  'last_name' => 'Puig',
                'email' => 'miquel.puig@campus.cat', 'phone' => '600 111 006',
                'specialization' => 'Medi ambient i sostenibilitat', 'status' => 'active',
                'dni' => '40100006F', 'city' => 'Llinars del Vallès', 'postal_code' => '08450',
                'fiscal_situation' => 'entitat', 'needs_payment' => true,
                'invoice' => true, 'payment_type' => 'beneficiary',
                'payment_status' => 'pending', 'data_consent' => true,
                'fiscal_responsibility' => true, 'ceded_confirmation' => true,
            ],
            [
                'code' => 'BERPLA', 'first_name' => 'Bernat',  'last_name' => 'Planes',
                'email' => 'bernat.planes@campus.cat', 'phone' => '600 111 007',
                'specialization' => 'Psicologia i benestar', 'status' => 'inactive',
                'dni' => '40100007G', 'city' => 'Mollet del Vallès', 'postal_code' => '08100',
                'fiscal_situation' => 'autonom', 'needs_payment' => true,
                'invoice' => false, 'payment_type' => 'own',
                'payment_status' => 'cancelled', 'data_consent' => false,
                'fiscal_responsibility' => false, 'ceded_confirmation' => false,
            ],
            [
                'code' => 'NURFIT', 'first_name' => 'Núria',   'last_name' => 'Fité',
                'email' => 'nuria.fite@campus.cat', 'phone' => '600 111 008',
                'specialization' => 'Nutrició i dietètica', 'status' => 'active',
                'dni' => '40100008H', 'city' => 'Canovelles', 'postal_code' => '08420',
                'fiscal_situation' => 'autonom', 'needs_payment' => true,
                'invoice' => true, 'payment_type' => 'own',
                'payment_status' => 'pending', 'data_consent' => true,
                'fiscal_responsibility' => true, 'ceded_confirmation' => true,
            ],
        ];

        $teacherPassword = env('SEEDER_TEACHER_PASSWORD')
            ?? throw new \RuntimeException('SEEDER_TEACHER_PASSWORD no definit al .env');

        foreach ($teachersDefs as $d) {
            CampusTeacher::firstOrCreate(
                ['code' => $d['code']],
                array_merge($d, [
                    'areas'             => [],
                    'hiring_date'       => '2022-09-01',
                    'password'          => $teacherPassword,
                    'needs_payment'     => $d['needs_payment'],
                    'data_consent'      => $d['data_consent'],
                    'fiscal_responsibility' => $d['fiscal_responsibility'],
                    'ceded_confirmation' => $d['ceded_confirmation'],
                ])
            );
        }

        $tAnna   = CampusTeacher::where('code', 'ANNBER')->first();
        $tMarta  = CampusTeacher::where('code', 'MARSOL')->first();
        $tLaura  = CampusTeacher::where('code', 'LAUMAR')->first();
        $tJoan   = CampusTeacher::where('code', 'JOASEG')->first();
        $tClaudia= CampusTeacher::where('code', 'CLAROS')->first();
        $tMiquel = CampusTeacher::where('code', 'MIGPUI')->first();
        $tNuria  = CampusTeacher::where('code', 'NURFIT')->first();

        // ── Temporades ────────────────────────────────────────────────────
        // Any anterior: 2024-2025
        $sAnt1 = CampusSeason::firstOrCreate(
            ['year' => 2024, 'quadrimester' => 1],
            ['name' => 'Tardor 2024', 'start_date' => '2024-09-01', 'end_date' => '2025-01-31',
             'start_date_enrollment' => '2024-07-01', 'end_date_enrollment' => '2024-09-15', 'status' => 'closed']
        );
        $sAnt2 = CampusSeason::firstOrCreate(
            ['year' => 2025, 'quadrimester' => 2],
            ['name' => 'Primavera 2025', 'start_date' => '2025-02-01', 'end_date' => '2025-06-30',
             'start_date_enrollment' => '2025-01-01', 'end_date_enrollment' => '2025-01-25', 'status' => 'closed']
        );
        // Any actual: 2025-2026
        $sTardor = CampusSeason::firstOrCreate(
            ['year' => 2025, 'quadrimester' => 1],
            ['name' => 'Tardor 2025', 'start_date' => '2025-09-01', 'end_date' => '2026-01-31',
             'start_date_enrollment' => '2025-07-01', 'end_date_enrollment' => '2025-09-15', 'status' => 'closed']
        );
        $sPrimavera = CampusSeason::firstOrCreate(
            ['year' => 2026, 'quadrimester' => 2],
            ['name' => 'Primavera 2026', 'start_date' => '2026-02-01', 'end_date' => '2026-06-30',
             'start_date_enrollment' => '2026-01-01', 'end_date_enrollment' => '2026-01-25', 'status' => 'active']
        );
        // En preparació: tardor 2026-2027
        $sPrep = CampusSeason::firstOrCreate(
            ['year' => 2026, 'quadrimester' => 1],
            ['name' => 'Tardor 2026', 'start_date' => '2026-09-01', 'end_date' => '2027-01-31',
             'start_date_enrollment' => '2026-07-01', 'end_date_enrollment' => '2026-09-15', 'status' => 'draft']
        );

        // ── Cursos per temporada ──────────────────────────────────────────
        // Helper: crea curs, associa professor principal i opcionalment assistent
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

        // ── ANY ANTERIOR – Tardor 2024 ────────────────────────────────────
        $mkCourse([
            'slug' => 'san-td24', 'code' => 'SAN-TD24', 'title' => 'Primeres Cures i RCP',
            'season_id' => $sAnt1->id, 'category_id' => $catSalut->id,
            'space_id' => $spCTUG->id, 'time_slot_id' => $slDL10->id,
            'sessions' => 8, 'max_students' => 25, 'price' => 20.00, 'format' => 'presencial',
            'status' => 'closed', 'start_date' => '2024-10-07', 'end_date' => '2024-11-25',
            'description' => 'Tècniques bàsiques de socorrisme i reanimació.',
        ], $tAnna);

        $mkCourse([
            'slug' => 'edu-td24', 'code' => 'EDU-TD24', 'title' => 'Aprenentatge Cooperatiu',
            'season_id' => $sAnt1->id, 'category_id' => $catEduc->id,
            'space_id' => $spAM1->id, 'time_slot_id' => $slDC16->id,
            'sessions' => 6, 'max_students' => 20, 'price' => 18.00, 'format' => 'presencial',
            'status' => 'closed', 'start_date' => '2024-10-09', 'end_date' => '2024-11-13',
            'description' => 'Metodologies d\'aprenentatge col·laboratiu a l\'aula.',
        ], $tMarta);

        $mkCourse([
            'slug' => 'tec-td24', 'code' => 'TEC-TD24', 'title' => 'Python per a no programadors',
            'season_id' => $sAnt1->id, 'category_id' => $catTech->id,
            'space_id' => $spUPC->id, 'time_slot_id' => $slDJ18->id,
            'sessions' => 10, 'max_students' => 20, 'price' => 25.00, 'format' => 'presencial',
            'status' => 'closed', 'start_date' => '2024-10-10', 'end_date' => '2024-12-12',
            'description' => 'Introducció a la programació amb Python.',
        ], $tJoan);

        $mkCourse([
            'slug' => 'art-td24', 'code' => 'ART-TD24', 'title' => 'Aquarel·la per a principiants',
            'season_id' => $sAnt1->id, 'category_id' => $catArts->id,
            'space_id' => $spAP1->id, 'time_slot_id' => $slDV10->id,
            'sessions' => 6, 'max_students' => 12, 'price' => 22.00, 'format' => 'presencial',
            'status' => 'closed', 'start_date' => '2024-10-11', 'end_date' => '2024-11-15',
            'description' => 'Tècnica d\'aquarel·la des de zero.',
        ], $tClaudia);

        $mkCourse([
            'slug' => 'med-td24', 'code' => 'MED-TD24', 'title' => 'Compostatge urbà',
            'season_id' => $sAnt1->id, 'category_id' => $catMedi->id,
            'space_id' => $spOnline->id, 'time_slot_id' => $slDT18->id,
            'sessions' => 4, 'max_students' => null, 'price' => 10.00, 'format' => 'online',
            'status' => 'closed', 'start_date' => '2024-11-05', 'end_date' => '2024-11-26',
            'description' => 'Guia pràctica per fer compost a casa.',
        ], $tMiquel);

        // ── ANY ANTERIOR – Primavera 2025 ─────────────────────────────────
        $mkCourse([
            'slug' => 'san-pr25', 'code' => 'SAN-PR25', 'title' => 'Nutrició per a la Gent Gran',
            'season_id' => $sAnt2->id, 'category_id' => $catSalut->id,
            'space_id' => $spAM2->id, 'time_slot_id' => $slDL10->id,
            'sessions' => 8, 'max_students' => 20, 'price' => 20.00, 'format' => 'presencial',
            'status' => 'closed', 'start_date' => '2025-02-17', 'end_date' => '2025-04-07',
            'description' => 'Hàbits alimentaris saludables en la tercera edat.',
        ], $tNuria);

        $mkCourse([
            'slug' => 'edu-pr25', 'code' => 'EDU-PR25', 'title' => 'Educació Emocional a l\'Aula',
            'season_id' => $sAnt2->id, 'category_id' => $catEduc->id,
            'space_id' => $spAM1->id, 'time_slot_id' => $slDC10->id,
            'sessions' => 6, 'max_students' => 20, 'price' => 18.00, 'format' => 'presencial',
            'status' => 'closed', 'start_date' => '2025-02-19', 'end_date' => '2025-03-26',
            'description' => 'Intel·ligència emocional aplicada a l\'entorn educatiu.',
        ], $tLaura);

        $mkCourse([
            'slug' => 'tec-pr25', 'code' => 'TEC-PR25', 'title' => 'Excel Avançat',
            'season_id' => $sAnt2->id, 'category_id' => $catTech->id,
            'space_id' => $spOnline->id, 'time_slot_id' => $slDT18->id,
            'sessions' => 8, 'max_students' => null, 'price' => 22.00, 'format' => 'online',
            'status' => 'closed', 'start_date' => '2025-02-18', 'end_date' => '2025-04-08',
            'description' => 'Taules dinàmiques, macros i Power Query.',
        ], $tJoan);

        $mkCourse([
            'slug' => 'cso-pr25', 'code' => 'CSO-PR25', 'title' => 'Història Local del Vallès',
            'season_id' => $sAnt2->id, 'category_id' => $catCienc->id,
            'space_id' => $spSA->id, 'time_slot_id' => $slDJ10->id,
            'sessions' => 5, 'max_students' => 60, 'price' => 12.00, 'format' => 'presencial',
            'status' => 'closed', 'start_date' => '2025-03-06', 'end_date' => '2025-04-03',
            'description' => 'Recorregut per la història i el patrimoni del Vallès Oriental.',
        ], $tLaura);

        // ── ANY ACTUAL – Tardor 2025 (tancada) ────────────────────────────
        $mkCourse([
            'slug' => 'san-td25', 'code' => 'SAN-TD25', 'title' => 'Cures Pal·liatives',
            'season_id' => $sTardor->id, 'category_id' => $catSalut->id,
            'space_id' => $spCTUG->id, 'time_slot_id' => $slDL10->id,
            'sessions' => 10, 'max_students' => 25, 'price' => 20.00, 'format' => 'presencial',
            'status' => 'closed', 'start_date' => '2025-10-06', 'end_date' => '2025-12-08',
            'description' => 'Atenció integral al pacient al final de la vida.',
        ], $tAnna, $tNuria);

        $mkCourse([
            'slug' => 'edu-td25', 'code' => 'EDU-TD25', 'title' => 'Inclusió a l\'Aula',
            'season_id' => $sTardor->id, 'category_id' => $catEduc->id,
            'space_id' => $spAM1->id, 'time_slot_id' => $slDC16->id,
            'sessions' => 8, 'max_students' => 20, 'price' => 18.00, 'format' => 'presencial',
            'status' => 'closed', 'start_date' => '2025-10-08', 'end_date' => '2025-11-26',
            'description' => 'Estratègies per a una educació inclusiva i diversa.',
        ], $tMarta, $tLaura);

        $mkCourse([
            'slug' => 'tec-td25', 'code' => 'TEC-TD25', 'title' => 'Introducció a la Intel·ligència Artificial',
            'season_id' => $sTardor->id, 'category_id' => $catTech->id,
            'space_id' => $spOnline->id, 'time_slot_id' => $slDT18->id,
            'sessions' => 8, 'max_students' => null, 'price' => 25.00, 'format' => 'online',
            'status' => 'closed', 'start_date' => '2025-10-07', 'end_date' => '2025-11-25',
            'description' => 'Conceptes bàsics d\'IA i aplicacions pràctiques.',
        ], $tJoan);

        $mkCourse([
            'slug' => 'art-td25', 'code' => 'ART-TD25', 'title' => 'Fotografia Digital',
            'season_id' => $sTardor->id, 'category_id' => $catArts->id,
            'space_id' => $spAP1->id, 'time_slot_id' => $slDV10->id,
            'sessions' => 6, 'max_students' => 12, 'price' => 22.00, 'format' => 'presencial',
            'status' => 'closed', 'start_date' => '2025-10-10', 'end_date' => '2025-11-14',
            'description' => 'Composició, llum i edició digital.',
        ], $tClaudia);

        $mkCourse([
            'slug' => 'med-td25', 'code' => 'MED-TD25', 'title' => 'Jardins Verticals i Horts Urbans',
            'season_id' => $sTardor->id, 'category_id' => $catMedi->id,
            'space_id' => $spAM2->id, 'time_slot_id' => $slDL16->id,
            'sessions' => 5, 'max_students' => 20, 'price' => 15.00, 'format' => 'presencial',
            'status' => 'closed', 'start_date' => '2025-10-06', 'end_date' => '2025-11-03',
            'description' => 'Disseny i manteniment d\'espais verds en entorns urbans.',
        ], $tMiquel);

        $mkCourse([
            'slug' => 'san-td25b', 'code' => 'SAN-TD25B', 'title' => 'Alimentació i Salut Cardiovascular',
            'season_id' => $sTardor->id, 'category_id' => $catSalut->id,
            'space_id' => $spAM2->id, 'time_slot_id' => $slDJ10->id,
            'sessions' => 6, 'max_students' => 20, 'price' => 18.00, 'format' => 'presencial',
            'status' => 'closed', 'start_date' => '2025-10-09', 'end_date' => '2025-11-13',
            'description' => 'Dieta mediterrània i prevenció de malalties cardiovasculars.',
        ], $tNuria);

        // ── ANY ACTUAL – Primavera 2026 (ACTIVA) ──────────────────────────
        $c = $mkCourse([
            'slug' => 'san-pr26', 'code' => 'SAN-PR26', 'title' => 'Pediatria Bàsica',
            'season_id' => $sPrimavera->id, 'category_id' => $catSalut->id,
            'space_id' => $spCTUG->id, 'time_slot_id' => $slDL10->id,
            'sessions' => 10, 'max_students' => 25, 'price' => 20.00, 'format' => 'presencial',
            'status' => 'active', 'start_date' => '2026-02-16', 'end_date' => '2026-04-27',
            'calendar_notes' => '16/2, 23/2, 2/3, 9/3, 16/3, 23/3, 30/3, 20/4, 27/4',
            'description' => 'Curs de pediatria per a professionals de la salut.',
        ], $tAnna, $tNuria);

        $mkCourse([
            'slug' => 'edu-pr26', 'code' => 'EDU-PR26', 'title' => 'TDAH i Neurodiversitat',
            'season_id' => $sPrimavera->id, 'category_id' => $catEduc->id,
            'space_id' => $spUPC->id, 'time_slot_id' => $slDC16->id,
            'sessions' => 8, 'max_students' => 30, 'price' => 25.00, 'format' => 'semipresencial',
            'status' => 'active', 'start_date' => '2026-02-18', 'end_date' => '2026-04-08',
            'calendar_notes' => '18/2, 4/3, 18/3, 25/3, 1/4, 8/4',
            'description' => 'Estratègies per a la gestió del TDAH i la neurodiversitat.',
        ], $tMarta, $tLaura);

        $mkCourse([
            'slug' => 'cie-pr26', 'code' => 'CIE-PR26', 'title' => 'Intel·ligència Emocional',
            'season_id' => $sPrimavera->id, 'category_id' => $catCienc->id,
            'space_id' => $spOnline->id, 'time_slot_id' => $slDJ18->id,
            'sessions' => 8, 'max_students' => null, 'price' => 15.00, 'format' => 'online',
            'status' => 'active', 'start_date' => '2026-02-19', 'end_date' => '2026-04-09',
            'calendar_notes' => '19/2, 26/2, 5/3, 12/3, 19/3, 26/3, 2/4, 9/4',
            'description' => 'Eines pràctiques per al benestar emocional.',
        ], $tLaura, $tMarta);

        // Curs pare híbrid + 2 fills (presencial + online)
        $pare = CampusCourse::firstOrCreate(['slug' => 'mon-digital-pare'], [
            'code' => 'MON-DIG', 'title' => 'Món Digital',
            'season_id' => $sPrimavera->id, 'category_id' => $catTech->id,
            'format' => 'hibrid', 'status' => 'active',
            'description' => 'El món de les noves tecnologies (plantilla híbrid).',
        ]);
        $mkCourse([
            'slug' => 'mon-digital-p26', 'code' => 'MON-DIG-P', 'title' => 'Món Digital – Presencial',
            'parent_id' => $pare->id,
            'season_id' => $sPrimavera->id, 'category_id' => $catTech->id,
            'space_id' => $spAM1->id, 'time_slot_id' => $slDV10->id,
            'sessions' => 10, 'max_students' => 20, 'price' => 22.00, 'format' => 'presencial',
            'status' => 'active', 'start_date' => '2026-02-20', 'end_date' => '2026-04-24',
            'calendar_notes' => '20/2, 27/2, 6/3, 13/3, 20/3, 27/3, 17/4, 24/4',
            'requirements' => 'Portàtil amb bateria',
        ], $tJoan, $tClaudia);
        $mkCourse([
            'slug' => 'mon-digital-ol26', 'code' => 'MON-DIG-OL', 'title' => 'Món Digital – Online',
            'parent_id' => $pare->id,
            'season_id' => $sPrimavera->id, 'category_id' => $catTech->id,
            'space_id' => $spOnline->id, 'time_slot_id' => $slDV10->id,
            'sessions' => 10, 'max_students' => null, 'price' => 22.00, 'format' => 'online',
            'status' => 'active', 'start_date' => '2026-02-20', 'end_date' => '2026-04-24',
            'calendar_notes' => '20/2, 27/2, 6/3, 13/3, 20/3, 27/3, 17/4, 24/4',
            'requirements' => 'Accés al campus virtual',
        ], $tJoan);

        $mkCourse([
            'slug' => 'art-pr26', 'code' => 'ART-PR26', 'title' => 'Ceràmica i Modelatge',
            'season_id' => $sPrimavera->id, 'category_id' => $catArts->id,
            'space_id' => $spAP1->id, 'time_slot_id' => $slDT10->id,
            'sessions' => 8, 'max_students' => 10, 'price' => 28.00, 'format' => 'presencial',
            'status' => 'active', 'start_date' => '2026-02-17', 'end_date' => '2026-04-07',
            'calendar_notes' => '17/2, 3/3, 17/3, 31/3, 7/4',
            'description' => 'Introducció a la ceràmica manual i el torn.',
        ], $tClaudia);

        $mkCourse([
            'slug' => 'med-pr26', 'code' => 'MED-PR26', 'title' => 'Canvi Climàtic i Biodiversitat',
            'season_id' => $sPrimavera->id, 'category_id' => $catMedi->id,
            'space_id' => $spOnline->id, 'time_slot_id' => $slDT18->id,
            'sessions' => 5, 'max_students' => null, 'price' => 12.00, 'format' => 'online',
            'status' => 'active', 'start_date' => '2026-03-03', 'end_date' => '2026-03-31',
            'calendar_notes' => '3/3, 10/3, 17/3, 24/3, 31/3',
            'description' => 'Impacte del canvi climàtic sobre els ecosistemes locals.',
        ], $tMiquel);

        $mkCourse([
            'slug' => 'san-pr26b', 'code' => 'SAN-PR26B', 'title' => 'Nutrició Esportiva',
            'season_id' => $sPrimavera->id, 'category_id' => $catSalut->id,
            'space_id' => $spAM2->id, 'time_slot_id' => $slDL16->id,
            'sessions' => 6, 'max_students' => 20, 'price' => 18.00, 'format' => 'presencial',
            'status' => 'planning', 'start_date' => '2026-04-06', 'end_date' => '2026-05-11',
            'description' => 'Estratègies nutricionals per a esportistes amateurs.',
        ], $tNuria);

        // ── EN PREPARACIÓ – Tardor 2026 ───────────────────────────────────
        $mkCourse([
            'slug' => 'san-td26', 'code' => 'SAN-TD26', 'title' => 'Salut Mental i Resiliència',
            'season_id' => $sPrep->id, 'category_id' => $catSalut->id,
            'space_id' => $spAM1->id, 'time_slot_id' => $slDL10->id,
            'sessions' => 8, 'max_students' => 20, 'price' => 22.00, 'format' => 'presencial',
            'status' => 'planning', 'start_date' => '2026-10-05', 'end_date' => '2026-11-23',
            'description' => 'Eines per gestionar l\'estrès i enfortir la resiliència.',
        ], $tAnna, $tNuria);

        $mkCourse([
            'slug' => 'tec-td26', 'code' => 'TEC-TD26', 'title' => 'Ciberseguretat Bàsica',
            'season_id' => $sPrep->id, 'category_id' => $catTech->id,
            'space_id' => $spUPC->id, 'time_slot_id' => $slDJ18->id,
            'sessions' => 10, 'max_students' => 20, 'price' => 28.00, 'format' => 'presencial',
            'status' => 'planning', 'start_date' => '2026-10-08', 'end_date' => '2026-12-10',
            'description' => 'Protecció digital per a particulars i petites empreses.',
        ], $tJoan);

        $mkCourse([
            'slug' => 'edu-td26', 'code' => 'EDU-TD26', 'title' => 'Robòtica Educativa',
            'season_id' => $sPrep->id, 'category_id' => $catEduc->id,
            'space_id' => $spAM2->id, 'time_slot_id' => $slDC16->id,
            'sessions' => 8, 'max_students' => 16, 'price' => 25.00, 'format' => 'presencial',
            'status' => 'planning', 'start_date' => '2026-10-07', 'end_date' => '2026-11-25',
            'description' => 'Programació i robòtica per a docents d\'infantil i primària.',
        ], $tMarta, $tJoan);

        $mkCourse([
            'slug' => 'med-td26', 'code' => 'MED-TD26', 'title' => 'Energia Solar i Autoconsum',
            'season_id' => $sPrep->id, 'category_id' => $catMedi->id,
            'space_id' => $spOnline->id, 'time_slot_id' => $slDT18->id,
            'sessions' => 5, 'max_students' => null, 'price' => 12.00, 'format' => 'online',
            'status' => 'planning', 'start_date' => '2026-10-13', 'end_date' => '2026-11-10',
            'description' => 'Instal·lació i gestió de plaques solars per a la llar.',
        ], $tMiquel);

        // ── Liquidacions professors (temporades tancades) ─────────────────
        // Tardor 2025 – professors cobrats
        $this->seedPayment($sTardor, $tAnna,  CampusCourse::where('slug', 'san-td25')->first(),  'SAN-TD25', 10, 50, 0);
        $this->seedPayment($sTardor, $tMarta, CampusCourse::where('slug', 'edu-td25')->first(),  'EDU-TD25', 8,  50, 15);
        $this->seedPayment($sTardor, $tJoan,  CampusCourse::where('slug', 'tec-td25')->first(),  'TEC-TD25', 8,  50, 15);
        $this->seedPayment($sTardor, $tClaudia,CampusCourse::where('slug','art-td25')->first(),  'ART-TD25', 6,  50, 0);
        $this->seedPayment($sTardor, $tMiquel,CampusCourse::where('slug', 'med-td25')->first(),  'MED-TD25', 5,  50, 0);
        $this->seedPayment($sTardor, $tNuria, CampusCourse::where('slug', 'san-td25b')->first(), 'SAN-TD25B', 6, 50, 0);

        $n  = 3 + 6 + 8 + 4 + 5 + 9;
        $nc = 5 + 4 + 6 + 3 + 4;
        $this->command->info("✅ CampusSeeder completat: 5 temporades amb dates inscripció, 10 categories, 8 espais, 9 franges, 8 professors, {$n} cursos, {$nc} liquidacions.");
    }

    private function seedPayment(
        CampusSeason $season,
        CampusTeacher $teacher,
        ?CampusCourse $course,
        string $description,
        int $sessions,
        float $pricePerSession,
        float $retentionPct
    ): void {
        if (!$course) return;

        $gross     = $sessions * $pricePerSession;
        $retAmount = round($gross * $retentionPct / 100, 2);
        $net       = $gross - $retAmount;

        CampusTeacherPayment::firstOrCreate(
            ['season_id' => $season->id, 'teacher_id' => $teacher->id, 'course_id' => $course->id],
            [
                'sessions_description' => "{$sessions} sessions",
                'sessions_count'       => $sessions,
                'price_per_session'    => $pricePerSession,
                'gross_amount'         => $gross,
                'retention_pct'        => $retentionPct,
                'retention_amount'     => $retAmount,
                'net_amount'           => $net,
                'issues_invoice'       => $teacher->invoice ?? false,
                'status'               => $retentionPct > 0 ? 'paid' : 'sent',
                'paid_at'              => $retentionPct > 0 ? now()->subMonths(2) : null,
            ]
        );
    }
}
