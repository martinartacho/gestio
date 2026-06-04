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
                'is_public'   => true,
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

        // ── Sessió 2: Configuració inicial (PUBLICADA) ───────────────────────
        LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 2],
            [
                'title'      => 'Configuració inicial',
                'subtitle'   => 'Guia d\'Introducció · Sessió 2 de 6',
                'duration'   => '25–35 min',
                'status'     => 'published',
                'sort_order' => 2,

                'quote_text'   => 'Una bona configuració és invisible. Es nota quan no hi és.',
                'quote_author' => 'Principi de disseny de sistemes',
                'quote_work'   => null,
                'intro_text'   => 'La pàgina de Configuració és la sala de control de GestorApp. '
                    . 'Només l\'administrador hi té accés. Tots els canvis s\'apliquen immediatament '
                    . 'sense reiniciar ni desplegar res. Aquesta sessió cobreix les configuracions '
                    . 'essencials per posar en marxa una nova instància de l\'app.',

                'topic_text' => 'La configuració s\'organitza en pestanyes temàtiques: identitat del Campus, '
                    . 'aparença, correu, mòduls actius, Associats, pagament, cua d\'inscripcions i avançat. '
                    . 'El principi clau és la jerarquia de flags: cada mòdul té un flag principal '
                    . 'i sub-flags per a cada funcionalitat interna. Desactivar el flag principal '
                    . 'oculta tot el mòdul sense eliminar les dades.',

                'concepts' => [
                    [
                        'icon'        => 'toggle-right',
                        'title'       => 'Jerarquia de flags',
                        'description' => 'Cada mòdul té un flag mestre (ex: "Tresoreria ON/OFF") '
                            . 'i sub-flags per a cada funcionalitat (Inscripcions, Pagaments, Liquidacions...). '
                            . 'Si el flag mestre és OFF, tots els sub-flags queden inactius '
                            . 'independentment del seu estat individual.',
                    ],
                    [
                        'icon'        => 'building',
                        'title'       => 'Identitat de l\'entitat',
                        'description' => 'El nom del Campus, el logo i les dades de contacte '
                            . 'apareixen al portal d\'alumnes i als correus automàtics. '
                            . 'Per al mòdul Associats, el nom de l\'entitat apareix als carnets digitals '
                            . 'i als correus als socis.',
                    ],
                    [
                        'icon'        => 'credit-card',
                        'title'       => 'Mètodes de pagament',
                        'description' => 'Per defecte, Stripe és l\'únic mètode actiu. '
                            . 'Des de la pestanya "Pagament" es poden activar transferència bancària, '
                            . 'Bizum, efectiu i PayPal, cadascun amb les seves dades específiques '
                            . '(IBAN, número de Bizum, etc.).',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'reference',
                        'title'    => 'Pestanya: Mòduls',
                        'author'   => 'Configuració → Mòduls',
                        'year'     => null,
                        'extract'  => 'Campus ON → Catàleg, Cursos, Professorat · '
                            . 'Tresoreria ON → Inscripcions, Pagaments, Liquidacions, Alumnes · '
                            . 'Associats ON → Socis, Quotes, Remeses SEPA · '
                            . 'Gestió ON → Administració, Calendari',
                        'analysis' => 'Cada sub-mòdul és independent. Pots tenir Tresoreria activa '
                            . 'però desactivar les IPs bloquejades si no les necessites. '
                            . 'Els sub-mòduls de Quotes i Remeses SEPA de Tresoreria '
                            . 'només es poden activar si el mòdul Associats és ON.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Pestanya: Associats',
                        'author'   => 'Configuració → Associats',
                        'year'     => null,
                        'extract'  => 'Nom de l\'entitat (apareix als carnets i correus) · '
                            . 'Prefix del número de soci (opcional) · '
                            . 'Import de la quota anual per defecte · '
                            . 'Credencial SEPA: ID creditor, IBAN i BIC de l\'entitat receptora',
                        'analysis' => 'Les credencials SEPA les proporciona el banc de l\'entitat. '
                            . 'Són necessàries per generar els fitxers XML pain.008 de domiciliació. '
                            . 'Sense elles, les remeses SEPA es poden crear però no exportar.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Primera posada en marxa',
                    'right_label'  => 'Configuració recurrent',
                    'left_points'  => [
                        'Dades del Campus (nom, logo, contacte)',
                        'Activar els mòduls que necessiteu',
                        'Nom de l\'entitat a Associats',
                        'Credencials SEPA si useu domiciliació',
                    ],
                    'right_points' => [
                        'Activar/desactivar sub-mòduls per temporada',
                        'Actualitzar mètodes de pagament',
                        'Ajustar import de quota anual',
                        'Canviar aparença per campanyes',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'Quins mòduls necessita activats la teva entitat ara mateix? Algun que no usaràs mai?'],
                    ['question' => 'Tens les credencials SEPA del teu banc per configurar la domiciliació? On les trobes?'],
                    ['question' => 'Si el Manager et demana activar un nou sub-mòdul, quins passos has de fer?'],
                ],

                'exercise' => [
                    'title'     => 'Configura "Cultura Viva"',
                    'duration'  => '10–15 min',
                    'statement' => 'Imagina que has de posar en marxa GestorApp per a l\'entitat "Cultura Viva". '
                        . 'Accedeix a la pàgina de Configuració del demo i aplica els canvis següents:',
                    'examples'  => [
                        '1. Campus → Nom del Campus: "Cultura Viva Formació"',
                        '2. Campus → Tagline: "Aprèn. Participa. Transforma."',
                        '3. Mòduls → Activa: Campus, Tresoreria (Inscripcions + Pagaments), Associats',
                        '4. Mòduls → Desactiva: LMS, IPs bloquejades',
                        '5. Associats → Nom de l\'entitat: "Cultura Viva"',
                        '6. Associats → Import quota anual: 45',
                    ],
                    'tips' => [
                        'Accedeix a: https://demo.artacho.org/admin/settings-page',
                        'Cada canvi es desa automàticament en canviar el toggle o prémer Desar.',
                        'Observa com el menú lateral canvia quan actives o desactives mòduls.',
                        'Els canvis al demo es reinicien amb cada desplegament nou.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        // ── Sessions 3-6: Esborranys (índex del curs) ─────────────────────────
        $drafts = [
            [3, 'El Catàleg — cursos, espais i temporades',       'Creació i gestió del catàleg de formació: temporades, categories, espais, franges horàries, professors i cursos.'],
            [4, 'Tresoreria — inscripcions i pagaments',          'Gestió de les inscripcions d\'alumnes, control de pagaments manuals i Stripe, liquidacions de professors i dashboard de resum financer.'],
            [5, 'El Mòdul Associats — socis, quotes i SEPA',      'Alta i gestió de socis, generació de quotes periòdiques, remeses SEPA pain.008 i portal del carnet digital.'],
            [6, 'Rols i Usuaris — control d\'accés',              'Creació d\'usuaris, assignació de rols i gestió de permisos. Diferència entre rols del panell i perfils dels portals (alumnes, professors, socis).'],
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

        $this->command->info("✅ LmsIntroAppSeeder: curs «{$course->title}» (públic) — 2 sessions publicades, 4 esborrany.");
    }
}
