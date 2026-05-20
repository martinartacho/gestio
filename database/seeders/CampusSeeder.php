<?php

namespace Database\Seeders;

use App\Models\CampusCategory;
use App\Models\CampusCourse;
use App\Models\CampusSeason;
use App\Models\CampusSpace;
use App\Models\CampusTeacher;
use App\Models\CampusTimeSlot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        // ── Temporades ────────────────────────────────────────────────────
        $season = CampusSeason::firstOrCreate(
            ['year' => 2026, 'quadrimester' => 2],
            [
                'name'                  => 'Primavera 2026',
                'start_date'            => '2026-02-01',
                'end_date'              => '2026-06-30',
                'start_date_enrollment' => '2026-01-01',
                'end_date_enrollment'   => '2026-01-25',
                'is_active'             => true,
            ]
        );

        $seasonTardor = CampusSeason::firstOrCreate(
            ['year' => 2026, 'quadrimester' => 1],
            [
                'name'                  => 'Tardor 2025-2026',
                'start_date'            => '2025-09-01',
                'end_date'              => '2026-01-31',
                'start_date_enrollment' => '2025-07-01',
                'end_date_enrollment'   => '2025-09-15',
                'is_active'             => false,
            ]
        );

        $seasonFutura = CampusSeason::firstOrCreate(
            ['year' => 2027, 'quadrimester' => 1],
            [
                'name'                  => 'Tardor 2026-2027',
                'start_date'            => '2026-09-01',
                'end_date'              => '2027-01-31',
                'start_date_enrollment' => '2026-07-01',
                'end_date_enrollment'   => '2026-09-15',
                'is_active'             => false,
            ]
        );

        // ── Categories ────────────────────────────────────────────────────
        $categories = [
            ['name' => 'Salut i Infermeria',           'color' => 'red',    'order' => 1],
            ['name' => 'Educació i Pedagogia',          'color' => 'blue',   'order' => 2],
            ['name' => 'Ciències Socials i Humanitats', 'color' => 'purple', 'order' => 3],
            ['name' => 'Noves Tecnologies',             'color' => 'indigo', 'order' => 4],
            ['name' => 'Arts i Cultura',                'color' => 'pink',   'order' => 5],
            ['name' => 'Medi Ambient',                  'color' => 'green',  'order' => 6],
        ];

        foreach ($categories as $data) {
            CampusCategory::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['slug' => Str::slug($data['name']), 'is_active' => true])
            );
        }

        // ── Espais ────────────────────────────────────────────────────────
        $spaces = [
            ['name' => "Sala d'actes",  'code' => 'SA',     'capacity' => 80,  'type' => 'sala_actes'],
            ['name' => 'Aula mitjana 1','code' => 'AM1',    'capacity' => 30,  'type' => 'mitjana'],
            ['name' => 'Aula mitjana 2','code' => 'AM2',    'capacity' => 30,  'type' => 'mitjana'],
            ['name' => 'Aula petita 1', 'code' => 'AP1',    'capacity' => 15,  'type' => 'petita'],
            ['name' => 'Polivalent',    'code' => 'SP',     'capacity' => 50,  'type' => 'polivalent'],
            ['name' => 'CTUG Roca Umbert','code'=>'CTUG',   'capacity' => 40,  'type' => 'extern'],
            ['name' => 'UPC Vallès',    'code' => 'UPC',    'capacity' => 40,  'type' => 'extern'],
            ['name' => 'Virtual',       'code' => 'ONLINE', 'capacity' => 0,   'type' => 'virtual'],
        ];

        foreach ($spaces as $data) {
            CampusSpace::firstOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['is_active' => true])
            );
        }

        // ── Franges horàries ──────────────────────────────────────────────
        $slots = [
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

        foreach ($slots as $data) {
            CampusTimeSlot::firstOrCreate(
                ['day_of_week' => $data['day_of_week'], 'code' => $data['code']],
                array_merge($data, ['is_active' => true])
            );
        }

        // ── Professors ────────────────────────────────────────────────────
        $teachers = [
            ['first_name' => 'Anna',   'last_name' => 'Estapé',   'code' => 'ANNEST', 'specialization' => 'Salut i infermeria'],
            ['first_name' => 'Marta',  'last_name' => 'Soler',    'code' => 'MARSOL', 'specialization' => 'Educació i pedagogia'],
            ['first_name' => 'Laura',  'last_name' => 'Martínez', 'code' => 'LAUMAR', 'specialization' => 'Ciències socials'],
            ['first_name' => 'Joan',   'last_name' => 'Segura',   'code' => 'JOASEG', 'specialization' => 'Noves tecnologies'],
        ];

        foreach ($teachers as $data) {
            CampusTeacher::firstOrCreate(
                ['first_name' => $data['first_name'], 'last_name' => $data['last_name']],
                array_merge($data, ['status' => 'active'])
            );
        }

        // ── Cursos (des del CSV d'exemple) ────────────────────────────────
        $catSalut   = CampusCategory::where('name', 'Salut i Infermeria')->first();
        $catEduc    = CampusCategory::where('name', 'Educació i Pedagogia')->first();
        $catCienc   = CampusCategory::where('name', 'Ciències Socials i Humanitats')->first();
        $catTech    = CampusCategory::where('name', 'Noves Tecnologies')->first();

        $spaceCTUG  = CampusSpace::where('code', 'CTUG')->first();
        $spaceUPC   = CampusSpace::where('code', 'UPC')->first();
        $spaceOnline= CampusSpace::where('code', 'ONLINE')->first();

        $slotDL10   = CampusTimeSlot::where('code', 'DL10')->first();
        $slotDC16   = CampusTimeSlot::where('code', 'DC16')->first();
        $slotDJ18   = CampusTimeSlot::where('code', 'DJ18')->first();
        $slotDV10   = CampusTimeSlot::where('code', 'DV10')->first();

        $tAnna  = CampusTeacher::where('last_name', 'Estapé')->first();
        $tMarta = CampusTeacher::where('last_name', 'Soler')->first();
        $tLaura = CampusTeacher::where('last_name', 'Martínez')->first();
        $tJoan  = CampusTeacher::where('last_name', 'Segura')->first();

        // Curs 1: Pediatria (presencial)
        $c1 = CampusCourse::firstOrCreate(['slug' => 'san101'], [
            'code' => 'SAN101', 'title' => 'Pediatria',
            'season_id' => $season->id, 'category_id' => $catSalut?->id,
            'space_id' => $spaceCTUG?->id, 'time_slot_id' => $slotDL10?->id,
            'sessions' => 10, 'max_students' => 25, 'price' => 20.00,
            'format' => 'presencial', 'status' => 'active',
            'start_date' => '2026-02-16', 'end_date' => '2026-03-16',
            'calendar_notes' => '16/2, 23/2, 2/3, 9/3, 16/3',
            'requirements' => 'Títol d\'infermeria o medicina',
            'description' => 'Curs de pediatria per a professionals de la salut',
        ]);
        $c1->teachers()->syncWithoutDetaching([$tAnna->id => ['role' => 'main']]);

        // Curs 2: TDAH (semipresencial)
        $c2 = CampusCourse::firstOrCreate(['slug' => 'tdah'], [
            'code' => null, 'title' => 'TDAH',
            'season_id' => $season->id, 'category_id' => $catEduc?->id,
            'space_id' => $spaceUPC?->id, 'time_slot_id' => $slotDC16?->id,
            'sessions' => 8, 'max_students' => 30, 'price' => 25.00,
            'format' => 'semipresencial', 'status' => 'active',
            'start_date' => '2026-02-19', 'end_date' => '2026-04-02',
            'calendar_notes' => '19/2, 26/2, 5/3, 12/3, 19/3, 26/3',
            'requirements' => 'Interès en educació especial',
            'description' => 'Estratègies educatives per al TDAH',
        ]);
        $c2->teachers()->syncWithoutDetaching([$tMarta->id => ['role' => 'main']]);

        // Curs 3: Intel·ligència Emocional (online)
        $c3 = CampusCourse::firstOrCreate(['slug' => 'intelligencia-emocional'], [
            'code' => null, 'title' => 'Intel·ligència Emocional',
            'season_id' => $season->id, 'category_id' => $catCienc?->id,
            'space_id' => $spaceOnline?->id, 'time_slot_id' => $slotDJ18?->id,
            'sessions' => 8, 'max_students' => null, 'price' => 15.00,
            'format' => 'online', 'status' => 'active',
            'start_date' => '2026-02-20', 'end_date' => '2026-03-20',
            'calendar_notes' => '20/2, 27/2, 6/3, 13/3, 20/3',
            'requirements' => 'Cap requeriment previ',
            'description' => 'Desenvolupament d\'habilitats emocionals',
        ]);
        $c3->teachers()->syncWithoutDetaching([$tLaura->id => ['role' => 'main']]);

        // Cursos 4+5: Món Digital — curs PARE (plantilla híbrid)
        $parent = CampusCourse::firstOrCreate(['slug' => 'mon-digital'], [
            'code' => 'MON-DIGITAL', 'title' => 'Món Digital',
            'season_id' => $season->id, 'category_id' => $catTech?->id,
            'format' => 'hibrid', 'status' => 'planning',
            'description' => 'El món de les noves tecnologies',
        ]);

        // Fill presencial
        $c4 = CampusCourse::firstOrCreate(['slug' => 'mon-digital-p'], [
            'code' => 'PARENT-2', 'title' => 'Món Digital – Presencial',
            'parent_id' => $parent->id,
            'season_id' => $season->id, 'category_id' => $catTech?->id,
            'space_id' => null, 'time_slot_id' => $slotDV10?->id,
            'sessions' => 10, 'max_students' => 25, 'price' => 15.00,
            'format' => 'presencial', 'status' => 'active',
            'start_date' => '2026-02-20', 'end_date' => '2026-04-24',
            'calendar_notes' => '20/2, 27/2, 6/3, 13/3, 20/3, 27/3, 3/4, 10/4, 17/4, 24/4',
            'requirements' => 'Portàtil',
        ]);
        $c4->teachers()->syncWithoutDetaching([$tJoan->id => ['role' => 'main']]);

        // Fill online
        $c5 = CampusCourse::firstOrCreate(['slug' => 'mon-digital-ol'], [
            'code' => 'PARENT-3', 'title' => 'Món Digital – Online',
            'parent_id' => $parent->id,
            'season_id' => $season->id, 'category_id' => $catTech?->id,
            'space_id' => $spaceOnline?->id, 'time_slot_id' => $slotDV10?->id,
            'sessions' => 10, 'max_students' => null, 'price' => 15.00,
            'format' => 'online', 'status' => 'active',
            'start_date' => '2026-02-20', 'end_date' => '2026-04-24',
            'calendar_notes' => '20/2, 27/2, 6/3, 13/3, 20/3, 27/3, 3/4, 10/4, 17/4, 24/4',
            'requirements' => 'Accés al campus',
        ]);
        $c5->teachers()->syncWithoutDetaching([$tJoan->id => ['role' => 'main']]);

        // ── Cursos Tardor 2025-2026 (passada) ────────────────────────────
        $catArts = CampusCategory::where('name', 'Arts i Cultura')->first();
        $catMedi = CampusCategory::where('name', 'Medi Ambient')->first();
        $spaceAM1 = CampusSpace::where('code', 'AM1')->first();
        $slotDT10 = CampusTimeSlot::where('code', 'DT10')->first();
        $slotDT18 = CampusTimeSlot::where('code', 'DT18')->first();

        CampusCourse::firstOrCreate(['slug' => 'primers-auxilis-2025'], [
            'code' => 'SAN050', 'title' => 'Primers Auxilis',
            'season_id' => $seasonTardor->id, 'category_id' => $catSalut?->id,
            'space_id' => $spaceAM1?->id, 'time_slot_id' => $slotDT10?->id,
            'sessions' => 6, 'max_students' => 20, 'price' => 18.00,
            'format' => 'presencial', 'status' => 'active', 'is_public' => true,
            'start_date' => '2025-10-07', 'end_date' => '2025-11-11',
            'description' => 'Tècniques bàsiques de primers auxilis per a tothom.',
        ])?->teachers()->syncWithoutDetaching([$tAnna->id => ['role' => 'main']]);

        CampusCourse::firstOrCreate(['slug' => 'fotografia-digital-2025'], [
            'code' => null, 'title' => 'Fotografia Digital',
            'season_id' => $seasonTardor->id, 'category_id' => $catArts?->id,
            'space_id' => $spaceAM1?->id, 'time_slot_id' => $slotDT18?->id,
            'sessions' => 8, 'max_students' => 15, 'price' => 22.00,
            'format' => 'presencial', 'status' => 'active', 'is_public' => true,
            'start_date' => '2025-10-14', 'end_date' => '2025-12-02',
            'description' => 'Composició, llum i edició fotogràfica.',
        ])?->teachers()->syncWithoutDetaching([$tLaura->id => ['role' => 'main']]);

        CampusCourse::firstOrCreate(['slug' => 'canvi-climatic-2025'], [
            'code' => 'MA010', 'title' => 'Canvi Climàtic i Sostenibilitat',
            'season_id' => $seasonTardor->id, 'category_id' => $catMedi?->id,
            'space_id' => $spaceOnline?->id, 'time_slot_id' => null,
            'sessions' => 5, 'max_students' => null, 'price' => 0.00,
            'format' => 'online', 'status' => 'active', 'is_public' => true,
            'start_date' => '2025-11-03', 'end_date' => '2025-12-01',
            'description' => 'Impacte del canvi climàtic i estratègies de sostenibilitat local.',
        ])?->teachers()->syncWithoutDetaching([$tMarta->id => ['role' => 'main']]);

        // ── Cursos Tardor 2026-2027 (futura) ────────────────────────────
        $spaceAP1 = CampusSpace::where('code', 'AP1')->first();
        $slotDC10 = CampusTimeSlot::where('code', 'DC10')->first();
        $slotDJ10 = CampusTimeSlot::where('code', 'DJ10')->first();

        CampusCourse::firstOrCreate(['slug' => 'geriatria-2026'], [
            'code' => 'SAN201', 'title' => 'Cures Geriàtriques',
            'season_id' => $seasonFutura->id, 'category_id' => $catSalut?->id,
            'space_id' => $spaceCTUG?->id, 'time_slot_id' => $slotDL10?->id,
            'sessions' => 10, 'max_students' => 25, 'price' => 25.00,
            'format' => 'presencial', 'status' => 'active', 'is_public' => true,
            'start_date' => '2026-09-14', 'end_date' => '2026-11-23',
            'description' => 'Atenció i cures especialitzades per a persones grans.',
        ])?->teachers()->syncWithoutDetaching([$tAnna->id => ['role' => 'main']]);

        CampusCourse::firstOrCreate(['slug' => 'ia-educacio-2026'], [
            'code' => 'TEC201', 'title' => 'Intel·ligència Artificial a l\'Educació',
            'season_id' => $seasonFutura->id, 'category_id' => $catTech?->id,
            'space_id' => $spaceOnline?->id, 'time_slot_id' => $slotDC10?->id,
            'sessions' => 8, 'max_students' => null, 'price' => 20.00,
            'format' => 'online', 'status' => 'active', 'is_public' => true,
            'start_date' => '2026-09-16', 'end_date' => '2026-11-04',
            'description' => 'Aplicació de la IA en contextos educatius.',
        ])?->teachers()->syncWithoutDetaching([$tJoan->id => ['role' => 'main']]);

        CampusCourse::firstOrCreate(['slug' => 'mediacio-conflictes-2026'], [
            'code' => null, 'title' => 'Mediació i Gestió de Conflictes',
            'season_id' => $seasonFutura->id, 'category_id' => $catCienc?->id,
            'space_id' => $spaceAM1?->id, 'time_slot_id' => $slotDJ10?->id,
            'sessions' => 6, 'max_students' => 20, 'price' => 18.00,
            'format' => 'semipresencial', 'status' => 'active', 'is_public' => true,
            'start_date' => '2026-10-01', 'end_date' => '2026-11-05',
            'description' => 'Tècniques de mediació per a professionals i entitats.',
        ])?->teachers()->syncWithoutDetaching([$tLaura->id => ['role' => 'main']]);

        CampusCourse::firstOrCreate(['slug' => 'musica-creativa-2026'], [
            'code' => null, 'title' => 'Música Creativa i Expressió',
            'season_id' => $seasonFutura->id, 'category_id' => $catArts?->id,
            'space_id' => $spaceAP1?->id, 'time_slot_id' => $slotDV10?->id,
            'sessions' => 8, 'max_students' => 12, 'price' => 15.00,
            'format' => 'presencial', 'status' => 'active', 'is_public' => true,
            'start_date' => '2026-09-25', 'end_date' => '2026-11-13',
            'description' => 'Taller pràctic de creació musical i expressió artística.',
        ])?->teachers()->syncWithoutDetaching([$tMarta->id => ['role' => 'main']]);

        $this->command->info('✅ CampusSeeder completat: 3 temporades, 6 categories, 8 espais, 9 franges, 4 professors, 12 cursos.');
    }
}
