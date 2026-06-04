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

        // ── Sessió 3: El Catàleg (PUBLICADA) ─────────────────────────────────
        LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 3],
            [
                'title'      => 'El Catàleg — cursos, espais i temporades',
                'subtitle'   => 'Guia d\'Introducció · Sessió 3 de 6',
                'duration'   => '25–35 min',
                'status'     => 'published',
                'sort_order' => 3,

                'quote_text'   => 'Un catàleg ben organitzat és la promesa que l\'entitat fa al públic.',
                'quote_author' => 'Principi de gestió formativa',
                'quote_work'   => null,
                'intro_text'   => 'El Catàleg és el cor del mòdul Campus. Conté tots els elements '
                    . 'que permeten definir i publicar l\'oferta formativa: temporades, categories, '
                    . 'espais, franges horàries, professors i cursos. El Manager és el responsable '
                    . 'de mantenir-lo actualitzat.',

                'topic_text' => 'Els elements del Catàleg s\'organitzen en una jerarquia: primer '
                    . 'es creen els elements base (temporades, categories, espais, franges), '
                    . 'i després els cursos que els referencien. Un curs sense temporada activa '
                    . 'no apareix al portal públic fins que no s\'activa manualment.',

                'concepts' => [
                    [
                        'icon'        => 'calendar',
                        'title'       => 'Temporada',
                        'description' => 'Període lectiu (ex: "Primavera 2026"). Cada curs pertany '
                            . 'a una temporada. Una temporada pot estar en estat: preparació, '
                            . 'oberta (inscripcions actives), activa (formació en curs) o tancada. '
                            . 'L\'estat de la temporada afecta la visibilitat dels cursos.',
                    ],
                    [
                        'icon'        => 'tag',
                        'title'       => 'Categories i espais',
                        'description' => 'Les categories agrupen els cursos per àrea temàtica '
                            . '(Salut, Educació, Arts...) i permeten filtrar al catàleg públic. '
                            . 'Els espais defineixen on es fa el curs (aula, sala, online). '
                            . 'Tots dos elements es configuren una vegada i es reutilitzen.',
                    ],
                    [
                        'icon'        => 'book-open',
                        'title'       => 'Curs i LMS',
                        'description' => 'Un curs pot ser presencial, online o semipresencial. '
                            . 'Si té el LMS activat, permet afegir sessions interactives '
                            . 'amb contingut, preguntes i exercicis accessible als alumnes matriculats. '
                            . 'El curs "GestorApp — Guia d\'Introducció" que estàs fent ara és un exemple.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'reference',
                        'title'    => 'Flux de creació d\'un curs',
                        'author'   => 'Panell → Catàleg',
                        'year'     => null,
                        'extract'  => '1. Crear temporada (si no existeix) → '
                            . '2. Crear categoria (si no existeix) → '
                            . '3. Crear espai (si és presencial) → '
                            . '4. Crear franja horària → '
                            . '5. Crear el curs assignant-hi tots els elements → '
                            . '6. Assignar professor/s → '
                            . '7. Publicar (status: actiu + is_public: true)',
                        'analysis' => 'Els passos 1-4 es fan una sola vegada per temporada. '
                            . 'Els passos 5-7 es repeteixen per a cada curs. '
                            . 'Un curs amb status "esborrany" o "planificació" no apareix '
                            . 'al catàleg públic independentment del flag is_public.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Gestió de professors',
                        'author'   => 'Panell → Cursos → Professorat',
                        'year'     => null,
                        'extract'  => 'Cada professor té: codi, nom, email, estat (actiu/inactiu) '
                            . 'i flag d\'emissió de factura. Pot ser assignat a múltiples cursos '
                            . 'com a professor principal o de suport. '
                            . 'Té accés al portal de professors per gestionar els seus cursos.',
                        'analysis' => 'El Manager crea i gestiona els professors. '
                            . 'Tresoreria gestiona les seves liquidacions econòmiques. '
                            . 'Secretaria pot consultar-los en mode lectura. '
                            . 'El professor té el seu propi portal independent del panell admin.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Curs visible al catàleg',
                    'right_label'  => 'Curs no visible',
                    'left_points'  => [
                        'Status: actiu',
                        'is_public: true',
                        'Temporada: oberta o activa',
                        'Té places disponibles (o places il·limitades)',
                    ],
                    'right_points' => [
                        'Status: esborrany o planificació',
                        'is_public: false',
                        'Temporada: tancada o en preparació',
                        'Places exhaurides',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'Quin és l\'element del catàleg que crearàs amb més freqüència? Quin et sembla que trigaràs més a configurar?'],
                    ['question' => 'Si un curs apareix al panell però no al portal públic, quines causes pot tenir?'],
                    ['question' => 'Com gestionaria el teu equip la creació d\'una nova temporada? Qui fa cada pas?'],
                ],

                'exercise' => [
                    'title'     => 'Crea el primer curs de "Cultura Viva"',
                    'duration'  => '15–20 min',
                    'statement' => 'Accedeix al demo i crea un curs complet seguint el flux de la sessió:',
                    'examples'  => [
                        '1. Catàleg → Temporades → Crea "Tardor 2026" (any: 2026, quadrimestre: 1)',
                        '2. Catàleg → Categories → Crea "Música i Arts Escèniques" (color: teal)',
                        '3. Catàleg → Espais → Crea "Sala Gran" (tipus: presencial, aforament: 30)',
                        '4. Cursos → Crea "Iniciació al Piano" assignant temporada, categoria i espai',
                        '5. Assigna un professor existent al curs',
                        '6. Activa el curs (status: actiu, is_public: true)',
                    ],
                    'tips' => [
                        'Accedeix a: https://demo.artacho.org/admin/seasons',
                        'Observa com el curs apareix al catàleg públic un cop activat.',
                        'Si no veus el curs al portal, comprova que el mòdul Campus és ON a Settings.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        // ── Sessió 4: Tresoreria (PUBLICADA) ─────────────────────────────────
        LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 4],
            [
                'title'      => 'Tresoreria — inscripcions i pagaments',
                'subtitle'   => 'Guia d\'Introducció · Sessió 4 de 6',
                'duration'   => '25–35 min',
                'status'     => 'published',
                'sort_order' => 4,

                'quote_text'   => 'Els diners no gestionen sols. Però amb les eines correctes, quasi.',
                'quote_author' => 'Principi de gestió econòmica',
                'quote_work'   => null,
                'intro_text'   => 'La Tresoreria és el hub central de tots els fluxos econòmics de '
                    . 'GestorApp: inscripcions d\'alumnes, pagaments rebuts, liquidacions a professors '
                    . 'i —si el mòdul Associats és actiu— quotes i remeses SEPA dels socis. '
                    . 'El rol Tresoreria té accés complet a tot això.',

                'topic_text' => 'La Tresoreria s\'organitza en recursos independents: Inscripcions '
                    . '(qui s\'ha matriculat i quin estat té el pagament), Pagaments (transaccions '
                    . 'reals per mètode), Liquidacions (el que es deu als professors) i el Dashboard '
                    . 'de Resum Financer (visió consolidada de tot).',

                'concepts' => [
                    [
                        'icon'        => 'user-plus',
                        'title'       => 'Inscripció vs Pagament',
                        'description' => 'Una inscripció registra que un alumne vol fer un curs i '
                            . 'quin mètode de pagament ha triat. Un pagament registra que el '
                            . 'pagament s\'ha rebut efectivament (per a pagaments manuals: '
                            . 'transferència, efectiu, targeta). Stripe gestiona els pagaments '
                            . 'electrònics automàticament.',
                    ],
                    [
                        'icon'        => 'calculator',
                        'title'       => 'Liquidació de professor',
                        'description' => 'Document que recull el que se li deu a un professor '
                            . 'per un curs: nombre de sessions, preu per sessió, import brut, '
                            . 'retenció IRPF i import net a pagar. Pot estar en estat: '
                            . 'esborrany, enviada, pagada o cancel·lada.',
                    ],
                    [
                        'icon'        => 'chart-bar',
                        'title'       => 'Dashboard Resum Financer',
                        'description' => 'Pàgina consolidada accessible des del primer element '
                            . 'del grup Tresoreria. Mostra KPIs per estat de totes les '
                            . 'inscripcions, pagaments, liquidacions i quotes de socis. '
                            . 'S\'adapta automàticament als mòduls actius.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'reference',
                        'title'    => 'Cicle de vida d\'una inscripció',
                        'author'   => 'Tresoreria → Inscripcions',
                        'year'     => null,
                        'extract'  => 'Pendent → Pagada → Confirmada (per l\'admin) → Completada '
                            . '· O bé: Pendent → Cancel·lada · O bé: Pagada → Retornada',
                        'analysis' => 'Per a pagaments Stripe: la inscripció passa a "Pagada" '
                            . 'automàticament quan el webhook confirma el pagament. '
                            . 'Per a pagaments manuals (transferència, Bizum...): l\'admin '
                            . 'ha de confirmar manualment creant un registre a Pagaments.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Resum Financer — exemple',
                        'author'   => 'Tresoreria → Resum Financer',
                        'year'     => null,
                        'extract'  => 'Inscripcions: Pendent 3 (45€) · Pagada 10 (165€) · '
                            . 'Confirmada 0 · Cancel·lada 1 · Retornada 1 — '
                            . 'Pagaments: Transfer 2 · Cash 2 · Card 2 — '
                            . 'Liquidacions: Enviada 8 (2950€ brut) · Pagada 2 (800€ brut)',
                        'analysis' => 'El dashboard mostra exactament el que té el demo ara mateix. '
                            . 'Cada bloc s\'activa o desactiva automàticament '
                            . 'segons els flags de Settings. Si desactives Liquidacions a Settings, '
                            . 'el bloc desapareix del dashboard.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Pagament Stripe (automàtic)',
                    'right_label'  => 'Pagament manual (transfer/cash)',
                    'left_points'  => [
                        'L\'alumne paga en línia amb targeta',
                        'El webhook actualitza l\'estat automàticament',
                        'No cal cap acció de l\'admin per confirmar',
                        'Genera stripe_payment_intent a la inscripció',
                    ],
                    'right_points' => [
                        'L\'alumne paga fora de la plataforma',
                        'L\'admin crea el registre a "Pagaments"',
                        'L\'admin canvia l\'estat de la inscripció a "Pagada"',
                        'Genera un registre a campus_payments',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'A la teva entitat, quin és el mètode de pagament més habitual? Stripe, transferència o efectiu?'],
                    ['question' => 'Quan un professor et demana la seva liquidació, quins passos fas per generar-la i enviar-la?'],
                    ['question' => 'Quina informació del Dashboard Resum Financer et semblaria més útil per al dia a dia?'],
                ],

                'exercise' => [
                    'title'     => 'Explora la Tresoreria del demo',
                    'duration'  => '10–15 min',
                    'statement' => 'Accedeix al demo i explora els recursos de Tresoreria:',
                    'examples'  => [
                        '1. Ves a Tresoreria → Resum financer i llegeix els KPIs actuals',
                        '2. Ves a Inscripcions → observa els estats i els mètodes de pagament',
                        '3. Ves a Pagaments → veus les diferències entre transfer, cash i card',
                        '4. Ves a Liquidacions professors → comprova els estats (draft, sent, paid)',
                        '5. Canvia l\'estat d\'una liquidació "draft" a "sent"',
                    ],
                    'tips' => [
                        'Accedeix a: https://demo.artacho.org/admin/tresoreria-dashboard',
                        'El Dashboard mostra dades reals del demo (seeder).',
                        'Pots editar registres al demo sense por — es reinicia en cada desplegament.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        // ── Sessió 5: Mòdul Associats (PUBLICADA) ────────────────────────────
        LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 5],
            [
                'title'      => 'El Mòdul Associats — socis, quotes i SEPA',
                'subtitle'   => 'Guia d\'Introducció · Sessió 5 de 6',
                'duration'   => '25–35 min',
                'status'     => 'published',
                'sort_order' => 5,

                'quote_text'   => 'Digitalitzar un carnet no és convertir-lo en un fitxer. És preservar el que significa.',
                'quote_author' => 'Principi de digitalització cultural',
                'quote_work'   => null,
                'intro_text'   => 'El mòdul Associats permet gestionar els socis d\'una entitat cultural: '
                    . 'el seu carnet digital amb QR, les quotes periòdiques i la domiciliació bancària '
                    . 'via remeses SEPA. Dissenyat per preservar el número de soci històric '
                    . 'i la identitat de l\'entitat.',

                'topic_text' => 'El mòdul s\'organitza en tres àrees amb responsabilitats separades: '
                    . 'Secretaria gestiona els socis com a persones (alta, edició, estat). '
                    . 'Tresoreria gestiona la part financera (quotes i remeses SEPA). '
                    . 'Els socis accedeixen al seu portal independent (/socis) per veure el carnet digital.',

                'concepts' => [
                    [
                        'icon'        => 'identification',
                        'title'       => 'Soci i carnet digital',
                        'description' => 'Cada soci té un número únic (preservat de la numeració '
                            . 'física original), email, estat (actiu/pendent/cancel·lat) '
                            . 'i un QR únic generat automàticament. El carnet digital és accessible '
                            . 'al portal /socis un cop el soci s\'ha identificat.',
                    ],
                    [
                        'icon'        => 'currency-euro',
                        'title'       => 'Quotes periòdiques',
                        'description' => 'Cada quota registra l\'any, la periodicitat '
                            . '(anual, semestral, trimestral, mensual), l\'import i l\'estat '
                            . '(pendent, pagada, fallida, cancel·lada). Les quotes es poden '
                            . 'crear manualment o generar en bloc per a tots els socis actius.',
                    ],
                    [
                        'icon'        => 'document-text',
                        'title'       => 'Remesa SEPA',
                        'description' => 'Agrupa les quotes pendents en un fitxer XML pain.008 '
                            . 'que s\'envia al banc per executar el càrrec en compte. '
                            . 'Requereix les credencials SEPA de l\'entitat (configurades a Settings). '
                            . 'Estats: esborrany → XML generat → enviat al banc → processat.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'reference',
                        'title'    => 'Alta d\'un nou soci',
                        'author'   => 'Associats → Socis',
                        'year'     => null,
                        'extract'  => '1. Assignar número de soci (manual o seqüencial) → '
                            . '2. Dades personals (nom, email, telèfon, DNI) → '
                            . '3. Estat inicial (actiu o pendent) → '
                            . '4. Dades SEPA opcionals (IBAN, titular, mandat) → '
                            . '5. Desar → el QR es genera automàticament',
                        'analysis' => 'El número de soci és l\'identificador visible '
                            . 'que apareix al carnet digital. El sistema no l\'assigna '
                            . 'automàticament per preservar la numeració original de l\'entitat. '
                            . 'El DNI s\'emmagatzema xifrat a la base de dades.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Generació d\'una remesa SEPA',
                        'author'   => 'Tresoreria → Remeses SEPA socis',
                        'year'     => null,
                        'extract'  => '1. Crear remesa (any, data d\'execució) → '
                            . '2. La remesa queda en estat "esborrany" → '
                            . '3. Generar XML (pain.008) → estat "XML generat" → '
                            . '4. Descarregar i enviar al banc → estat "enviat al banc" → '
                            . '5. Confirmar quan el banc processa → estat "processat"',
                        'analysis' => 'La data d\'execució és quan el banc farà els càrrecs. '
                            . 'Ha de ser almenys 5 dies hàbils després de l\'enviament al banc. '
                            . 'Sense credencials SEPA a Settings, el pas 3 no estarà disponible.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Secretaria gestiona',
                    'right_label'  => 'Tresoreria gestiona',
                    'left_points'  => [
                        'Alta i edició de socis',
                        'Estat del soci (actiu/pendent/cancel·lat)',
                        'Dades personals i de contacte',
                        'Lectura de les quotes (no crea ni edita)',
                    ],
                    'right_points' => [
                        'Quotes: crear, editar, canviar estat',
                        'Remeses SEPA: crear, generar XML, marcar com enviada',
                        'Accés des del grup Tresoreria al panell',
                        'Dashboard amb resum financer de quotes',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'La teva entitat té numeració física de socis? Com us plantegeu preservar-la en la transició digital?'],
                    ['question' => 'Quina periodicitat de quota és la habitual a la teva entitat? Anual, trimestral o mensual?'],
                    ['question' => 'El vostre banc us ha proporcionat un identificador de creditor SEPA? Sabeu on trobar-lo?'],
                ],

                'exercise' => [
                    'title'     => 'Dona d\'alta el primer soci de "Cultura Viva"',
                    'duration'  => '10–15 min',
                    'statement' => 'Accedeix al demo i crea un soci de prova:',
                    'examples'  => [
                        '1. Ves a Associats → Socis → Crear nou soci',
                        '2. Número de soci: 2001 · Nom: Anna Garcia · Email: anna@test.cat',
                        '3. Estat: actiu',
                        '4. Desa i comprova que el QR s\'ha generat',
                        '5. Ves a Tresoreria → Quotes socis → Crea una quota anual de 45€ per a la Anna',
                        '6. Comprova que apareix al Dashboard Resum Financer (bloc Quotes socis)',
                    ],
                    'tips' => [
                        'Accedeix a: https://demo.artacho.org/admin/associat-members',
                        'Recorda que Associats ha d\'estar ON a Settings per veure el menú.',
                        'El QR del carnet es pot veure desde el panell editant el soci.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        // ── Sessió 6: Rols i Usuaris (PUBLICADA) ─────────────────────────────
        LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 6],
            [
                'title'      => 'Rols i Usuaris — control d\'accés',
                'subtitle'   => 'Guia d\'Introducció · Sessió 6 de 6',
                'duration'   => '20–30 min',
                'status'     => 'published',
                'sort_order' => 6,

                'quote_text'   => 'L\'accés correcte a la persona correcta en el moment correcte. No més. No menys.',
                'quote_author' => 'Principi del mínim privilegi',
                'quote_work'   => null,
                'intro_text'   => 'GestorApp separa clarament dos tipus d\'accés: els usuaris del '
                    . 'panell d\'administració (amb rols específics) i els perfils dels portals '
                    . 'públics (alumnes, professors, socis). Cada grup té les seves credencials '
                    . 'i accedeix a seccions completament diferenciades.',

                'topic_text' => 'Els rols del panell admin defineixen el que cada persona pot fer '
                    . 'dins de la plataforma. Els permisos són granulars: veure, crear, editar '
                    . 'i eliminar per a cada recurs. L\'admin assigna rols als usuaris del panell. '
                    . 'Els portals (alumnes, professors, socis) funcionen amb guards independents '
                    . 'i no es gestionen des d\'aquí.',

                'concepts' => [
                    [
                        'icon'        => 'shield',
                        'title'       => 'Rols del panell admin',
                        'description' => 'Admin (tècnic, configuració), Manager (catàleg i cursos), '
                            . 'Tresoreria (finances), Secretaria (persones: professors, alumnes, socis), '
                            . 'Viewer (lectura mínima). Cada rol té permisos específics '
                            . 'que reflecteixen la seva responsabilitat operativa.',
                    ],
                    [
                        'icon'        => 'users',
                        'title'       => 'Perfils de portals (no rols)',
                        'description' => 'Alumnes (/campus), professors (/professorat) i socis (/socis) '
                            . 'són perfils d\'usuari amb accés als seus portals específics. '
                            . 'NO accedeixen al panell admin. Es gestionen des dels seus '
                            . 'respectius recursos (Alumnes, Professorat, Socis).',
                    ],
                    [
                        'icon'        => 'key',
                        'title'       => 'Permisos granulars',
                        'description' => 'Cada acció (view/create/edit/delete) té el seu propi '
                            . 'permís. Un Secretaria pot crear i editar socis però no pot '
                            . 'veure les Remeses SEPA. Un Tresoreria pot gestionar quotes '
                            . 'però no pot crear socis nous.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'reference',
                        'title'    => 'Resum de responsabilitats per rol',
                        'author'   => 'Panell → Gestió → Rols',
                        'year'     => null,
                        'extract'  => 'Admin: Settings, Usuaris, Rols — '
                            . 'Manager: Catàleg (temporades, categories, espais, cursos, professors) — '
                            . 'Tresoreria: Inscripcions, Pagaments, Liquidacions, Quotes, SEPA — '
                            . 'Secretaria: Professors (editar), Alumnes (veure), Socis (CRUD), Quotes (veure)',
                        'analysis' => 'El Manager no pot accedir a Settings ni crear usuaris. '
                            . 'La Secretaria no veu les Remeses SEPA ni les liquidacions de professors. '
                            . 'Tresoreria no pot crear socis, però gestiona totes les seves finances. '
                            . 'Cada restricció és una decisió de disseny, no una limitació tècnica.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Crear un nou usuari del panell',
                        'author'   => 'Panell → Administració → Usuaris',
                        'year'     => null,
                        'extract'  => '1. Gestió → Usuaris → Nou usuari → '
                            . '2. Nom, email, contrasenya temporal → '
                            . '3. Assignar rol → Desar → '
                            . '4. L\'usuari rep les credencials i accedeix al panell',
                        'analysis' => 'Només l\'Admin pot crear i gestionar usuaris del panell. '
                            . 'Un usuari pot tenir un únic rol. Si necessita accés a funcionalitats '
                            . 'de dos rols, cal parlar amb l\'Admin per valorar si cal un nou rol '
                            . 'o si les responsabilitats s\'han de redistribuir.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Usuaris del panell admin',
                    'right_label'  => 'Perfils de portals',
                    'left_points'  => [
                        'Accedeixen a /admin amb les seves credencials',
                        'Gestionen la plataforma (configuren, creen, editen)',
                        'Rols: admin, manager, tresoreria, secretaria, viewer',
                        'Creats per l\'Admin des de Gestió → Usuaris',
                    ],
                    'right_points' => [
                        'Accedeixen a /campus, /professorat o /socis',
                        'Consulten i usen els seus continguts i dades',
                        'Alumnes, professors, socis (guards independents)',
                        'Creats des dels seus recursos respectius',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'A la teva entitat, qui hauria de tenir cada rol? Teniu una persona per a cada rol o algunes persones n\'haurien de tenir més d\'un?'],
                    ['question' => 'Hi ha alguna funcionalitat del panell que creus que hauries de veure i no veus? O alguna que veus i no hauries de veure?'],
                    ['question' => 'Un professor necessita accedir al panell admin per gestionar els seus cursos LMS, o és suficient amb el portal de professors?'],
                ],

                'exercise' => [
                    'title'     => 'Configura l\'equip de "Cultura Viva"',
                    'duration'  => '10–15 min',
                    'statement' => 'Accedeix al demo i crea tres usuaris del panell per a "Cultura Viva":',
                    'examples'  => [
                        '1. Ves a Gestió → Usuaris → Crear: Maria López · maria@culturaviva.cat · rol: manager',
                        '2. Crear: Pau Riba · pau@culturaviva.cat · rol: tresoreria',
                        '3. Crear: Laia Font · laia@culturaviva.cat · rol: secretaria',
                        '4. Comprova a Rols que cada rol té els permisos que esperes',
                        '5. Tanca la sessió i intenta entrar amb les credencials del Manager',
                        '6. Comprova que el Manager NO veu Settings ni la pestanya d\'Usuaris',
                    ],
                    'tips' => [
                        'Accedeix a: https://demo.artacho.org/admin/users',
                        'Usa contrasenyes de prova simples per al demo (ex: Demo.2026)',
                        'Observa com el menú lateral canvia completament segons el rol.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        $this->command->info("✅ LmsIntroAppSeeder: curs «{$course->title}» (públic) — 6 sessions publicades.");
    }
}
