<?php

namespace Database\Seeders;

use App\Models\CampusCategory;
use App\Models\CampusCourse;
use App\Models\CampusSeason;
use App\Models\CampusTeacher;
use App\Models\LmsLesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LmsIntroAppSeeder extends Seeder
{
    public function run(): void
    {
        $seederMail  = config('seeder.mail', 'gestio.test');
        $teacherPass = config('seeder.teacher_password')
            ?? throw new \RuntimeException('SEEDER_TEACHER_PASSWORD no està definit al .env');

        // ── Temporada (reutilitzem Primavera 2026) ────────────────────────────
        $season = CampusSeason::firstOrCreate(
            ['year' => 2026, 'quadrimester' => 2],
            [
                'name'       => 'Primavera 2026',
                'start_date' => '2026-02-01',
                'end_date'   => '2026-06-30',
                'status'     => 'active',
            ]
        );

        // ── Categoria "Administració" ─────────────────────────────────────────
        $catAdmin = CampusCategory::firstOrCreate(
            ['name' => 'Administració'],
            ['slug' => 'administracio', 'color' => 'indigo', 'order' => 99, 'is_active' => true]
        );

        // ── Professor: Guia de l'App ──────────────────────────────────────────
        $teacher = CampusTeacher::firstOrCreate(
            ['code' => 'GESTAPP'],
            [
                'first_name' => 'Guia',
                'last_name'  => 'GestorApp',
                'email'      => "guia@{$seederMail}",
                'password'   => Hash::make($teacherPass),
                'status'     => 'active',
            ]
        );

        // ── Curs d'introducció ────────────────────────────────────────────────
        $course = CampusCourse::firstOrCreate(
            ['code' => 'INTRO-APP'],
            [
                'title'       => 'GestorApp — Guia de Configuració i Ús',
                'slug'        => 'intro-gestorapp',
                'season_id'   => $season->id,
                'category_id' => $catAdmin->id,
                'format'      => 'online',
                'sessions'    => 6,
                'hours'       => 3,
                'price'       => 0,
                'status'      => 'active',
                'is_public'   => false,
                'description' => 'Guia pràctica per a administradors i gestors de la plataforma GestorApp. '
                    . 'Six sessions que cobreixen la configuració inicial, el catàleg de cursos, '
                    . 'la tresoreria, el mòdul d\'associats i la gestió de rols i usuaris.',
            ]
        );

        $course->teachers()->syncWithoutDetaching([$teacher->id => ['role' => 'main']]);

        // ── Sessió 1: Benvingut a GestorApp (PUBLICADA) ───────────────────────
        LmsLesson::firstOrCreate(
            ['course_id' => $course->id, 'session_number' => 1],
            [
                'title'      => 'Benvingut a GestorApp',
                'subtitle'   => 'Guia d\'Introducció · Sessió 1 de 6',
                'duration'   => '20–30 min',
                'status'     => 'published',
                'sort_order' => 1,

                'quote_text'   => 'Una eina ben configurada és la meitat de la feina feta.',
                'quote_author' => 'Principi de gestió de plataformes',
                'quote_work'   => null,
                'intro_text'   => 'GestorApp és una plataforma modular de gestió per a entitats culturals i formatives. '
                    . 'Cada mòdul és independent i activable des del panell de configuració. '
                    . 'Aquesta sessió t\'ofereix una visió global de l\'estructura, els rols i la filosofia '
                    . 'de disseny de l\'aplicació.',

                'topic_text' => 'La plataforma té tres capes: el panell d\'administració (Filament), '
                    . 'els portals públics per als usuaris finals (alumnes, professors, socis) '
                    . 'i la configuració del lloc. Cada rol del panell té una responsabilitat clara '
                    . 'i accés únicament al que necessita.',

                'concepts' => [
                    [
                        'icon'        => 'puzzle',
                        'title'       => 'Arquitectura modular',
                        'description' => 'GestorApp s\'organitza en mòduls independents: Campus (cursos i formació), '
                            . 'Tresoreria (gestió financera) i Associats (socis i digitalització). '
                            . 'Cada mòdul es pot activar o desactivar des de Configuració → Mòduls '
                            . 'sense afectar la resta de la plataforma.',
                    ],
                    [
                        'icon'        => 'users',
                        'title'       => 'Rols i responsabilitats',
                        'description' => 'Cada usuari del panell té un rol amb responsabilitats concretes: '
                            . 'Admin (tècnic), Manager (catàleg), Tresoreria (finances), '
                            . 'Secretaria (persones). Els portals d\'alumnes, professors i socis '
                            . 'funcionan de forma independent amb els seus propis accessos.',
                    ],
                    [
                        'icon'        => 'shield-check',
                        'title'       => 'Permisos granulars',
                        'description' => 'Cada acció del panell (veure, crear, editar, eliminar) '
                            . 'té un permís específic. Els rols agrupen permisos de forma coherent '
                            . 'amb la responsabilitat de cada perfil.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'reference',
                        'title'    => 'Mòdul Campus',
                        'author'   => 'GestorApp',
                        'year'     => null,
                        'extract'  => 'Gestió completa de la formació: catàleg de cursos, inscripcions d\'alumnes, '
                            . 'portal de professors, liquidacions i LMS integrat. '
                            . 'Configurable per temporades, categories, espais i franges horàries.',
                        'analysis' => 'El Manager és el responsable del Catàleg. '
                            . 'Tresoreria gestiona les inscripcions i els pagaments. '
                            . 'Secretaria té accés de lectura als alumnes i professors. '
                            . 'Els alumnes i professors accedeixen als seus portals específics.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Mòdul Tresoreria',
                        'author'   => 'GestorApp',
                        'year'     => null,
                        'extract'  => 'Hub central de tots els fluxos econòmics: inscripcions, pagaments, '
                            . 'liquidacions de professors, quotes de socis i remeses SEPA. '
                            . 'Dissenyat per créixer cap a futurs mòduls (Tickets, Botiga...).',
                        'analysis' => 'El rol Tresoreria té accés complet a tots els recursos financers. '
                            . 'El Dashboard de Resum Financer ofereix una visió consolidada '
                            . 'de tots els fluxos en una sola pàgina.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Mòdul Associats',
                        'author'   => 'GestorApp',
                        'year'     => null,
                        'extract'  => 'Digitalització dels socis de l\'entitat: carnet digital amb QR, '
                            . 'portal de socis, gestió de quotes periòdiques i remeses SEPA pain.008. '
                            . 'El número de soci històric es preserva.',
                        'analysis' => 'Secretaria gestiona els socis com a persones (alta, edició). '
                            . 'Tresoreria gestiona la part financera (quotes i SEPA). '
                            . 'Els socis accedeixen al portal /socis amb el seu carnet digital.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Panell d\'administració',
                    'right_label'  => 'Portals públics',
                    'left_points'  => [
                        'Accés per rols (admin, manager, tresoreria, secretaria)',
                        'Gestió de dades, configuració i operativa',
                        'URL: /admin — requereix autenticació Filament',
                        'Protegit per permisos granulars',
                    ],
                    'right_points' => [
                        'Alumnes (/campus), professors (/professors), socis (/socis)',
                        'Accés als seus propis continguts i dades',
                        'Guards independents (student, teacher, member)',
                        'Cada portal té el seu propi disseny i flux',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'Quin és el rol que millor s\'adapta a les teves responsabilitats actuals a l\'entitat? Tens accés a tot el que necessites?'],
                    ['question' => 'Hi ha algun mòdul que no uses però que podria ser útil per a l\'entitat en un futur?'],
                    ['question' => 'Alguna configuració de la plataforma que saps que caldria ajustar però no has trobat on?'],
                ],

                'exercise' => [
                    'title'     => 'Exploració inicial',
                    'duration'  => '10–15 min',
                    'statement' => 'Fes un recorregut pel panell d\'administració i identifica '
                        . 'els grups de navegació del menú lateral. Per a cada grup, anota: '
                        . '(1) quins recursos conté, (2) qui és el rol responsable, '
                        . '(3) si hi tens accés i si és l\'accés que esperaves.',
                    'examples'  => [
                        'Cursos → grup Cursos → responsable Manager',
                        'Inscripcions → grup Tresoreria → responsable Tresoreria',
                        'Socis → grup Associats → responsable Secretaria',
                        'Configuració → grup Sistema → responsable Admin',
                    ],
                    'tips' => [
                        'Accedeix a Configuració → pestanya Mòduls per veure quins mòduls tens activats.',
                        'Comprova a Rols → Admin → Permisos quins permisos té el teu rol.',
                        'Si no veus un recurs al menú, pot ser que el mòdul corresponent estigui desactivat.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        // ── Sessions 2-6: Esborranys (índex del curs) ─────────────────────────
        $drafts = [
            [2, 'Configuració inicial — Settings i mòduls',       'Aprèn a configurar els paràmetres del lloc, activar i desactivar mòduls i sub-mòduls, i ajustar les opcions de pagament, SEPA i cua d\'inscripcions.'],
            [3, 'El Catàleg — cursos, espais i temporades',        'Creació i gestió del catàleg de formació: temporades, categories, espais, franges horàries, professors i cursos.'],
            [4, 'Tresoreria — inscripcions i pagaments',           'Gestió de les inscripcions d\'alumnes, control de pagaments manuals i Stripe, liquidacions de professors i dashboard de resum financer.'],
            [5, 'El Mòdul Associats — socis, quotes i SEPA',       'Alta i gestió de socis, generació de quotes periòdiques, remeses SEPA pain.008 i portal del carnet digital.'],
            [6, 'Rols i Usuaris — control d\'accés',               'Creació d\'usuaris, assignació de rols i gestió de permisos. Diferència entre rols del panell i perfils dels portals (alumnes, professors, socis).'],
        ];

        foreach ($drafts as [$num, $title, $intro]) {
            LmsLesson::firstOrCreate(
                ['course_id' => $course->id, 'session_number' => $num],
                [
                    'title'      => $title,
                    'subtitle'   => "Guia d'Introducció · Sessió {$num} de 6",
                    'duration'   => '20–30 min',
                    'status'     => 'draft',
                    'sort_order' => $num,
                    'intro_text' => $intro,
                    'topic_text' => 'Contingut en preparació.',
                    'concepts'   => [],
                    'text_cards' => [],
                    'reflection_questions' => [],
                    'exercise'   => [],
                ]
            );
        }

        $this->command->info("✅ LmsIntroAppSeeder: curs «{$course->title}» amb 6 sessions (1 publicada, 5 esborrany).");
    }
}
