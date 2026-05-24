<?php

namespace Database\Seeders;

use App\Models\CampusCategory;
use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusSeason;
use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use App\Models\LmsLesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LmsArtMedicinaSeeder extends Seeder
{
    public function run(): void
    {
        $seederMail  = config('seeder.mail', 'gestio.test');
        $teacherPass = config('seeder.teacher_password')
            ?? throw new \RuntimeException('SEEDER_TEACHER_PASSWORD no està definit al .env');
        $studentPass = config('seeder.student_password')
            ?? throw new \RuntimeException('SEEDER_STUDENT_PASSWORD no està definit al .env');

        // ── Temporada ──────────────────────────────────────────────────────────
        $season = CampusSeason::firstOrCreate(
            ['year' => 2026, 'quadrimester' => 2],
            [
                'name'       => 'Primavera 2026',
                'start_date' => '2026-02-01',
                'end_date'   => '2026-06-30',
                'status'     => 'active',
            ]
        );

        // ── Categoria ─────────────────────────────────────────────────────────
        $catArts = CampusCategory::firstOrCreate(
            ['name' => 'Arts i Humanitats'],
            ['slug' => 'arts-i-humanitats', 'color' => 'pink', 'order' => 1, 'is_active' => true]
        );

        // ── Curs ART-01 ────────────────────────────────────────────────────────
        $course = CampusCourse::firstOrCreate(
            ['code' => 'ART-01'],
            [
                'title'       => 'Art i Medicina',
                'slug'        => 'art-i-medicina',
                'season_id'   => $season->id,
                'category_id' => $catArts->id,
                'format'      => 'online',
                'sessions'    => 8,
                'hours'       => 8,
                'price'       => 0,
                'status'      => 'active',
                'is_public'   => true,
                'description' => 'El cos, la malaltia i la cura a través de la història de l\'art · 8 sessions de 45–60 min. Un curs que no és ni d\'art ni de medicina, sinó de la mirada humana davant del dolor, la fragilitat i l\'esperança. Cada sessió parteix d\'una obra, arriba a un concepte i acaba amb una reflexió que podria ser mèdica, filosòfica o artística — o les tres alhora.',
            ]
        );
        if (! $course->category_id) {
            $course->update(['category_id' => $catArts->id]);
        }

        // ── Professor ──────────────────────────────────────────────────────────
        $teacher = CampusTeacher::firstOrCreate(
            ['email' => "profe@{$seederMail}"],
            [
                'code'       => 'CLAHRT',
                'first_name' => 'Claudi',
                'last_name'  => 'Hartacho',
                'email'      => "profe@{$seederMail}",
                'password'   => Hash::make($teacherPass),
                'status'     => 'active',
            ]
        );
        $course->teachers()->syncWithoutDetaching([$teacher->id => ['role' => 'main']]);

        // ── Alumne de prova ────────────────────────────────────────────────────
        $student = CampusStudent::firstOrCreate(
            ['email' => "student@{$seederMail}"],
            [
                'first_name'   => 'Alumne',
                'last_name'    => 'Prova',
                'email'        => "student@{$seederMail}",
                'password'     => Hash::make($studentPass),
                'data_consent' => true,
            ]
        );

        $existing = CampusEnrollment::where('student_id', $student->id)
            ->where('course_id', $course->id)->first();

        if (! $existing) {
            $enrollment = CampusEnrollment::create([
                'student_id'            => $student->id,
                'course_id'             => $course->id,
                'first_name'            => $student->first_name,
                'last_name'             => $student->last_name,
                'email'                 => $student->email,
                'enrollment_date'       => now()->toDateString(),
                'status'                => 'paid',
                'amount'                => 0,
                'paid_at'               => now(),
                'stripe_session_id'     => 'cs_test_art_seed',
                'stripe_payment_intent' => 'pi_test_art_seed',
            ]);
            $course->students()->syncWithoutDetaching([
                $student->id => ['enrollment_id' => $enrollment->id, 'enrolled_at' => now()],
            ]);
        }

        $this->command->info("🎨 Curs: {$course->title} (slug: {$course->slug})");

        // ══════════════════════════════════════════════════════════════════════
        // SESSIÓ 1 — El cos com a misteri: les primeres mirades
        // ══════════════════════════════════════════════════════════════════════
        $s1 = LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 1],
            [
                'title'      => 'El cos com a misteri: les primeres mirades',
                'subtitle'   => 'De les pintures rupestres als papirs egipcis. Quan curar era també pregar.',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 1,

                'quote_text'   => "El primer acte mèdic de la humanitat no va ser una recepta.\nVa ser una pregunta: per a qué em fa mal?",
                'quote_author' => 'Dita de taller · curs Art i Medicina',
                'quote_work'   => null,

                'intro_text' => 'Molt abans que existissin els hospitals, les universitats o els fàrmacs, els éssers humans ja intentaven curar. I ja representaven aquella cura en imatges, objectes i documents. L\'art i la medicina no van néixer separats. Van néixer junts, de la mateixa necessitat: entendre el cos i alleujar el sofriment.

Avui comencem al principi. No amb un quadre al museu, sinó amb una pregunta molt antiga: com representem allò que no entenem?',

                'topic_text' => 'Durant milers d\'anys, la medicina i la màgia van ser la mateixa cosa. Curar un cos malalt requeria alhora coneixement pràctic i intervenció sobrenatural. Les primeres representacions visuals de la cura ho mostren clarament: el metge i el sacerdot sovint eren la mateixa persona.

Avui observem tres obres molt allunyades en el temps però unides per la mateixa pregunta: com va aprendre la humanitat a mirar el cos malalt?',

                'concepts' => [
                    [
                        'icon'        => '🏺',
                        'title'       => 'Medicina ritual',
                        'description' => 'En moltes cultures antigues, la malaltia s\'entenia com un desequilibri — entre el cos i l\'univers, entre la persona i els déus. Curar era, alhora, un acte tècnic (herbes, cirurgia, dieta) i un acte ritual (pregàries, amulets, invocacions). L\'art n\'era el testimoni i, sovint, l\'eina.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'reference',
                        'title'    => 'Pintures rupestres amb figures xamàniques',
                        'author'   => 'Diverses localitzacions · ~15.000–40.000 aC · Domini públic',
                        'year'     => null,
                        'extract'  => 'Representació de figura amb atributs animals interpretada com a xamà o curador ritual · Exemples: Lascaux, Altamira, Cova dels Tres Germans',
                        'analysis' => 'A les coves de Lascaux, Altamira i desenes d\'altres llocs d\'Europa, Àsia i Àfrica, hi ha representades figures humanes amb característiques animals: banyes, potes, plomes. Durant dècades, els arqueòlegs les van interpretar simplement com a disfresses de caça. Avui, molts investigadors creuen que representen xemans — figures especialitzades en la mediació entre el món humà i el món espiritual.

El que interessa des de la perspectiva mèdica és que el xamà era, en aquelles societats, el primer professional de la salut. Diagnosticava, tractava i curava. Les seves eines eren plantes, rituals i imatges. I les imatges que deixava a les parets de les coves podien ser, alhora, mapes de l\'invisible i registres de la cura.

No podem saber amb certesa si aquelles figures "tractaven malalties" tal com les entenem avui. Però sabem que representaven algú amb poder sobre la vida i la mort del grup.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'El Papir Ebers',
                        'author'   => 'Egipte · ~1550 aC · Universitat de Leipzig (Alemanya) · Domini públic',
                        'year'     => null,
                        'extract'  => 'Papir Ebers · ~1550 aC · 20 m de llarg, 108 columnes de text hieràtic · 877 prescripcions mèdiques per a més de 200 afeccions',
                        'analysis' => 'El Papir Ebers és un dels documents mèdics més antics i complets que coneixem. Té uns 20 metres de llarg, conté 877 prescripcions per a més de 200 afeccions i data d\'aproximadament el 1550 aC. Va ser descobert per Georg Ebers el 1873 a Luxor i avui es conserva a la Universitat de Leipzig.

Però el que el fa fascinant per a aquest curs no és només el seu contingut mèdic — és la seva estructura dual. Al costat de tractaments racionals (herbes, dietes, ungüents), hi apareixen encantaments i fórmules màgiques. El metge egipci no distingia entre les dues coses: una cataplasma d\'herbes i una invocació al déu Thoth eren parts complementàries d\'un mateix tractament.

A més, el papir és en si mateix una obra visual: l\'escriptura hieràtica és un sistema d\'imatges convertides en text. Llegir el papir era veure, a la vegada, un document mèdic i un artefacte artístic.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Panells de fusta de la tomba d\'Hesy-Ra',
                        'author'   => 'Egipte · ~2650 aC · Museu Egipci del Caire · Domini públic',
                        'year'     => null,
                        'extract'  => 'Primera representació coneguda d\'un professional mèdic · Hesy-Ra, "cap dels dentistes i dels metges" del faraó Djoser',
                        'analysis' => 'Hesy-Ra va ser "metge en cap" del faraó Djoser fa gairebé cinc mil anys. La seva tomba a Saqqara conté uns dels relleus de fusta més extraordinaris de l\'Egipte antic: onze panells que el mostren de peu, en postura formal, portant objectes que podrien ser eines mèdiques o quirúrgiques.

El que demostra aquesta obra és notable: la medicina egípcia ja tenia, fa 4.600 anys, professionals reconeixibles, titulats i prou importants per merèixer una tomba elaborada. Hesy-Ra portava el títol de "cap dels dentistes i dels metges" — la primera distinció especialitzada mèdica de la qual tenim constància visual.

Mirar aquests panells avui és trobar-se amb el primer "retrat de metge" de la historia. Cinc mil anys separen Hesy-Ra d\'un metge de família actual — però la figura social que representen és, en molts sentits, la mateixa.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Prehistòria · El xamà',
                    'right_label'  => 'Egipte · El metge reial',
                    'left_points'  => [
                        'Cura i ritual indissociables',
                        'La imatge és l\'eina terapèutica',
                        'No hi ha distinció metge/sacerdot',
                        'La malaltia és ruptura amb el cosmos',
                    ],
                    'right_points' => [
                        'Tractament racional + màgia combinats',
                        'La imatge és el registre i la identitat',
                        'Comença l\'especialització mèdica',
                        'La malaltia és un desequilibri que es pot tractar',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'El Papir Ebers barreja tractaments racionals i invocacions màgiques. Creieu que avui la medicina i el pensament màgic conviuen? On els veieu?'],
                    ['question' => 'Per a qué creus que Hesy-Ra volia que el recordessin com a metge? Qué diu sobre la seva societat que els metges mereixessin tombes elaborades?'],
                    ['question' => 'Si haguessis de representar visualment "la medicina actual" en una sola imatge, quina seria? Per qué?'],
                ],

                'exercise' => [
                    'title'     => 'Llegir una imatge mèdica',
                    'duration'  => '25–30 min',
                    'statement' => 'Tria una de les tres obres d\'avui i observa-la en silenci durant dos minuts. Llavors respon per escrit a les preguntes de l\'exercici.',
                    'examples'  => [
                        'Pas 1 — Descriu el que veus sense interpretar res. Només el que hi ha: formes, línies, materials, colors. No diguis "és un metge" — digues "hi ha una figura humana dreta amb un objecte a la mà".',
                        'Pas 2 — Ara interpreta: qui creus que és? Qué creus que fa? Per qué ho creus? Raona a partir del que has observat al pas 1.',
                        'Pas 3 — Pregunta\'t: qué em genera aquesta imatge? Confiança? Distància? Respecte? Por? No hi ha resposta correcta. L\'important és identificar la teva reacció i preguntar-te d\'on ve.',
                        'Pas 4 — Escriu una sola frase que resumeixi el que creus que l\'autor de la imatge volia comunicar. Màxim 20 paraules. La limitació t\'obliga a triar el que és essencial.',
                    ],
                    'tips' => [
                        'Mirar una obra lentament és una habilitat que es practica.',
                        'La majoria de persones mirem una imatge menys de 10 segons. Avui provem de mirar-la almenys 2 minuts sense fer res més.',
                        'Sovint, el que descobrim en el segon minut és més interessant que el que veiem en el primer.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],

                'questions' => [
                    [
                        'index' => 0, 'type' => 'open_text', 'block' => 'reflection',
                        'text'  => 'El Papir Ebers barreja tractaments racionals i invocacions màgiques. Creieu que avui la medicina i el pensament màgic conviuen? On els veieu?',
                        'required' => false, 'points' => 0,
                    ],
                    [
                        'index' => 1, 'type' => 'yes_no', 'block' => 'quiz',
                        'text'  => 'El xamà de les societats prehistòriques era la primera figura de professional de la salut del grup?',
                        'correct_answer' => true, 'required' => true, 'points' => 1,
                    ],
                    [
                        'index' => 2, 'type' => 'choice_one', 'block' => 'quiz',
                        'text'  => 'El Papir Ebers data aproximadament de...',
                        'options'        => ['~3.500 aC', '~1.550 aC', '~500 aC', '~100 dC'],
                        'correct_answer' => '~1.550 aC',
                        'required' => true, 'points' => 2,
                    ],
                    [
                        'index' => 3, 'type' => 'yes_no', 'block' => 'quiz',
                        'text'  => 'La medicina egípcia del Papir Ebers separava clarament els tractaments racionals dels rituals màgics?',
                        'correct_answer' => false, 'required' => true, 'points' => 1,
                    ],
                    [
                        'index' => 4, 'type' => 'select_from_examples', 'block' => 'exercise',
                        'text'  => 'Quina de les tres obres d\'avui has triat per a l\'exercici d\'observació?',
                        'options' => [
                            'Pintures rupestres amb figures xamàniques',
                            'El Papir Ebers (~1550 aC)',
                            'Panells de fusta de la tomba d\'Hesy-Ra',
                        ],
                        'allow_custom' => false, 'required' => false, 'points' => 0,
                    ],
                ],
            ]
        );

        // ══════════════════════════════════════════════════════════════════════
        // SESSIÓ 2 — Asclepi, Hipòcrates i la medicina racional
        // ══════════════════════════════════════════════════════════════════════
        $s2 = LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 2],
            [
                'title'      => 'Asclepi, Hipòcrates i la medicina racional',
                'subtitle'   => 'Grècia i Roma: el moment en què la malaltia deixa de ser un càstig diví.',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 2,

                'quote_text'   => "Primer, no fer mal.\nPrimum non nocere.",
                'quote_author' => 'Hipòcrates de Cos',
                'quote_work'   => '«pare de la medicina occidental» (~460–370 aC)',

                'intro_text' => 'Quatre paraules. Però darrere hi ha una revolució: la idea que el metge ha de pensar abans d\'actuar. Que la seva responsabilitat no és vers els déus, sinó vers el pacient. Que la medicina és, en primer lloc, un acte ètic.

La sessió d\'avui transcorre entre dos mons. El primer és el d\'Asclepi — el déu de la medicina, els seus temples-hospital, els rituals de curació. El segon és el d\'Hipòcrates — l\'observació, el registre, el diagnòstic. Tots dos van conviure durant segles. I les imatges que van deixar ens ho expliquen millor que qualsevol text.',

                'topic_text' => 'La gran aportació de la Grècia clàssica a la medicina no va ser tècnica — va ser conceptual. Hipòcrates i la seva escola van proposar alguna cosa radical: que les malalties tenien causes naturals, no divines. Que podien ser observades, descrites i tractades de manera racional. Que el cos funcionava amb les seves pròpies lleis.

Però aquesta transició no va ser immediata ni total. Durant segles, el temple d\'Asclepi i la consulta d\'Hipòcrates van coexistir. Les obres d\'art ens mostren exactament on estava la línia — i com de borrosa era.

Context cronològic: ~1200 aC — Asclepi apareix als texts grecs com a fill d\'Apol·lo i metge mític. Les seves filles Higièia (salut preventiva) i Panacea (curació) donen nom a paraules que usem avui. ~500–300 aC — Els Asclepeions (temples-hospital) s\'estenen per tot el món grec. El d\'Epidaure era el més famós: els malalts hi dormien esperant que el déu els curàs en somnis. ~460–370 aC — Hipòcrates a l\'illa de Cos: observació sistemàtica, primers casos clínics documentats, jurament mèdic. ~I–II dC — Roma hereta els dos sistemes alhora: temples d\'Asclepi i metges professionals amb instruments quirúrgics sofisticats.

El jurament hipocràtic: "Usaré el tractament per ajudar els malalts en la mesura del meu poder i judici, i mai no ho faré per causar dany o mal. No donaré cap droga letal a ningú si me\'n demanen, ni suggeriré tal consell." El jurament hipocràtic continua sent avui la base de l\'ètica mèdica occidental. Però fixeu-vos en el que diu: no parla de com curar, sinó de com comportar-se. Primer l\'ètica, després la tècnica.',

                'concepts' => [
                    [
                        'icon'        => '🐍',
                        'title'       => 'Medicina racional',
                        'description' => 'El pas de la medicina ritual a la medicina basada en l\'observació. Hipòcrates va proposar que la malaltia tenia causes naturals (dieta, clima, estil de vida) i podia estudiar-se sistemàticament. El seu mètode: observar el pacient, registrar els símptomes, identificar patrons. No màgia — però tampoc cap als nostres estàndards actuals.',
                    ],
                    [
                        'icon'        => '⚕️',
                        'title'       => 'El bastó d\'Asclepi',
                        'description' => 'El bastó amb la serp és avui el símbol de la medicina a tot el món. La serp no era un animal de mal averany: a la Grècia antiga, la seva capacitat per mudar la pell la convertia en símbol de renovació i curació. Els temples d\'Asclepi tenien serps reals que caminaven lliurement entre els malalts.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'reference',
                        'title'    => 'Estatua d\'Asclepi (tipus Giustini)',
                        'author'   => 'Còpia romana d\'un original grec · ~II dC · Museu Arqueològic Nacional de Nàpols',
                        'year'     => null,
                        'extract'  => 'Asclepi amb el bastó i la serp · Símbol de la medicina fins avui · Museu Arqueològic Nacional de Nàpols',
                        'analysis' => 'Asclepi es representa gairebé sempre de la mateixa manera: un home madur, de peu, amb una túnica que deixa el pit descobert, i un bastó al voltant del qual s\'enrosca una serp. Aquesta figura tan consistent no és casual — és una marca. Un símbol reconeixible que deia, sense paraules: aquí hi ha curació.

Però el que fa interessant aquesta escultura des del punt de vista mèdic no és la serp — és la postura. Asclepi no actua. No toca cap pacient. Simplement és, amb una presència tranquil·la i segura. La curació ve de la seva presència, no de les seves mans. Hipòcrates ho canviarà: les mans del metge hauran d\'aprendre a tocar, examinar i operar.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Bust d\'Hipòcrates de Cos',
                        'author'   => 'Còpia romana d\'un original grec · ~I–II dC · Museu Pushkin / Museu Britànic',
                        'year'     => null,
                        'extract'  => 'Bust d\'Hipòcrates · Còpia romana d\'un original grec del s. IV aC · Diversos museus europeus',
                        'analysis' => 'Els bustos d\'Hipòcrates que han arribat fins a nosaltres no van ser fets en vida seva — són còpies romanes fetes segles després de la seva mort. No sabem com era Hipòcrates realment. El que veiem en el bust és una construcció cultural: la imatge d\'un savi que una societat volia recordar d\'una manera determinada.

És un home gran, calb i serè. No hi ha instruments, no hi ha malalts, no hi ha acció. Simplement una cara que pensa. Aquí rau la diferència fonamental amb Asclepi: mentre el déu curava amb la seva presència, Hipòcrates curava amb el seu pensament. La seva aportació fonamental va ser el pronòstic: l\'intent de predir l\'evolució d\'una malaltia basant-se en l\'observació de casos anteriors. Que la natura era previsible. Que la raó humana podia comprendre el cos.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Instruments quirúrgics de Pompeia',
                        'author'   => '~I dC · Trobats a la Casa del Cirurgià, Pompeia · Museu Arqueològic Nacional de Nàpols',
                        'year'     => null,
                        'extract'  => 'Instruments quirúrgics romans · Casa del Cirurgià, Pompeia · ~I dC · Més de 150 eines de bronze i ferro',
                        'analysis' => 'L\'any 79 dC, el Vesuvi sepultà Pompeia sota les cendres. I entre les ruïnes va quedar perfectament conservada la casa d\'un cirurgià romà amb el seu instrumental complet: més de 150 eines de bronze i ferro. Escalpels, forceps, agulles de sutura, espèculums, sondes, ganivets de tots els mides.

Quan els arqueòlegs van estudiar aquells instruments al segle XIX, van quedar impressionats per una cosa: molts eren gairebé idèntics als que s\'usaven als quiròfans europeus del seu temps, divuit segles després. Formes pensades per a la mà humana, dissenyades per a una funció concreta. El cos humà havia deixat de ser sagrat — i podia ser obert, reparat i tancat.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Asclepi · Medicina divina',
                    'right_label'  => 'Hipòcrates · Medicina racional',
                    'left_points'  => [
                        'La curació ve de la presència del déu',
                        'El malalt dorm al temple (incubació)',
                        'Medicina i religió indissociables',
                        'El metge és un intermediari sagrat',
                        'La serp com a símbol de renovació',
                    ],
                    'right_points' => [
                        'La curació ve de l\'observació i la raó',
                        'El metge examina, diagnostica, tracta',
                        'Medicina i ètica indissociables',
                        'El metge és un professional laic',
                        'L\'instrument com a símbol de la tècnica',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'El bastó amb la serp d\'Asclepi és avui el símbol de la medicina mundial. Per qué creieu que un símbol de fa 2.500 anys continua vigent? Qué comunica?'],
                    ['question' => 'El jurament hipocràtic diu «primer no fer mal». Creieu que aquest principi és sempre fàcil de seguir en la medicina actual? Podeu pensar en situacions on sigui complicat?'],
                    ['question' => 'Els instruments quirúrgics romans i els actuals s\'assemblen molt. Per qué creieu que algunes formes no han canviat en 2.000 anys? Qué explica sobre el cos humà?'],
                ],

                'exercise' => [
                    'title'     => 'Dos metges, dos segles, una mirada',
                    'duration'  => '25–30 min',
                    'statement' => 'Busca o recorda una imatge d\'un metge o espai mèdic actual (una consulta, un quiròfan, una ambulància, una vacunació). Compara-la mentalment amb l\'estatua d\'Asclepi o el bust d\'Hipòcrates. Respon les preguntes.',
                    'examples'  => [
                        'Pas 1 — Descriu la imatge actual que has triat: qué hi veus? Qui hi és? Qué fan? Quins objectes hi ha? Sense interpretar encara — només descriure.',
                        'Pas 2 — Ara compara amb l\'obra antiga: qué ha canviat? Qué és sorprenentment igual? Pensa en la postura, els símbols, la relació entre qui cuida i qui és cuidat.',
                        'Pas 3 — Quina de les dues imatges et transmet més confiança? Per qué? Qué diu sobre com construïm la idea de "metge"? No hi ha resposta correcta. Tots dos sistemes han generat confiança en milions de persones.',
                        'Pas 4 — Escriu un títol breu per a la imatge actual, a l\'estil dels títols que podria tenir l\'escultura d\'Asclepi. Exemple: «Metgessa amb estetoscopi» / «Figura amb màscara i guants». Qué canvia si el titules així?',
                    ],
                    'tips' => [
                        'El títol d\'una obra d\'art canvia com la mirem.',
                        '"The Doctor" de Luke Fildes (1891) — que veurem a la Sessió 5 — és bàsicament el mateix que faries al pas 4: convertir una escena mèdica en una obra que fa pensar.',
                        'Titulant la imatge actual ets, durant un moment, l\'artista.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],

                'questions' => [
                    [
                        'index' => 0, 'type' => 'open_text', 'block' => 'reflection',
                        'text'  => 'El bastó amb la serp d\'Asclepi és avui el símbol de la medicina mundial. Per qué creieu que un símbol de fa 2.500 anys continua vigent? Qué comunica?',
                        'required' => false, 'points' => 0,
                    ],
                    [
                        'index' => 1, 'type' => 'yes_no', 'block' => 'quiz',
                        'text'  => 'Hipòcrates creia que les malalties tenien causes naturals, no divines?',
                        'correct_answer' => true, 'required' => true, 'points' => 1,
                    ],
                    [
                        'index' => 2, 'type' => 'choice_one', 'block' => 'quiz',
                        'text'  => 'El jurament hipocràtic parla principalment de...',
                        'options'        => ['Com diagnosticar malalties', 'Com comportar-se èticament', 'Quines herbes medicinals usar', 'Com practicar la cirurgia'],
                        'correct_answer' => 'Com comportar-se èticament',
                        'required' => true, 'points' => 2,
                    ],
                    [
                        'index' => 3, 'type' => 'choice_many', 'block' => 'quiz',
                        'text'  => 'Quins elements o símbols apareixen associats a la figura d\'Asclepi?',
                        'options'         => ['Un bastó', 'Una serp', 'Un escalpel', 'Un temple', 'Un papir'],
                        'correct_answers' => ['Un bastó', 'Una serp', 'Un temple'],
                        'required' => false, 'points' => 2,
                    ],
                    [
                        'index' => 4, 'type' => 'select_from_examples', 'block' => 'exercise',
                        'text'  => 'Quina imatge mèdica actual has triat per comparar amb les obres antigues?',
                        'options' => [
                            'Una consulta mèdica actual',
                            'Un quiròfan en funcionament',
                            'Una escena de vacunació',
                            'Una ambulància o emergència',
                        ],
                        'allow_custom' => true, 'required' => false, 'points' => 0,
                    ],
                ],
            ]
        );

        // ══════════════════════════════════════════════════════════════════════
        // SESSIONS 3–8 — Contingut bàsic des de l'índex
        // ══════════════════════════════════════════════════════════════════════

        $sessions = [
            3 => [
                'title'       => 'La pesta i la mort: l\'art davant l\'epidèmia',
                'subtitle'    => 'Edat Mitjana: quan la malaltia ho omple tot i l\'art en dóna testimoni.',
                'badge'       => 'Antiguitat',
                'quote_text'  => 'No hi ha mirall que reflecteixi millor la por d\'una societat que l\'art que crea quan es troba davant la mort.',
                'quote_author'=> 'Dita de taller · curs Art i Medicina',
                'quote_work'  => null,
                'intro_text'  => 'La pesta negra va matar entre un terç i la meitat de la població europea entre 1347 i 1351. L\'art medieval en va quedar marcat per sempre: esqueletes que ballen, processons de morts, metges emmascats. Quan la mort es fa omnipresent, l\'art la representa de totes les maneres possibles.',
                'topic_text'  => 'L\'Edat Mitjana europea va viure l\'epidèmia com un càstig diví — i la va representar com a tal. Les "Danses de la Mort" (Danse Macabre) mostraven esquelets que s\'emportaven reis, bisbes i pagesos sense distinció. L\'art es va convertir en un instrument de comprensió col·lectiva d\'allò incomprensible. El metge de la pesta, amb la seva màscara de bec, els seus ulls de vidre i el seu bastonet, és un dels primers símbols visuals d\'equip de protecció individual de la historia.',
                'obra1_title' => 'Dances de la mort (Danse Macabre)',
                'obra1_autor' => 'Diverses representacions · s. XIV–XV · Domini públic',
                'obra1_text'  => 'Les dances de la mort medievals mostraven esquelets ballant amb representants de tots els estaments socials: el papa, el rei, el bisbe, el mercader, el pagès. La mort no distingeix. El missatge era teològic — davant la mort, tothom és igual — però també epidèmic: la pesta no tenia pietat.',
                'obra2_title' => 'El metge de la pesta (màscara de bec, s. XVII)',
                'obra2_autor' => 'Gravat de Paul Fürst · 1656 · Domini públic',
                'obra2_text'  => 'La màscara de bec del metge de la pesta —amb els seus ulls de vidre i el bec ple d\'herbes aromàtiques— és un dels primers dissenys d\'equip de protecció individual documentats. No funcionava (la pesta es transmetia per puces, no per l\'aire), però la figura visual va quedar gravada en la cultura europea com el retrat de la medicina en temps de crisi.',
                'reflection'  => [
                    'Creieu que la Covid-19 generarà un art similar al de la pesta medieval? Ja n\'heu vist exemples?',
                    'La màscara del metge de la pesta protegia poc —però comunicava molt. Qué comunica la bata blanca d\'un metge actual?',
                    'Per qué creieu que l\'art medieval representava tant la mort? Era por, resignació o alguna cosa més?',
                ],
            ],
            4 => [
                'title'       => 'Leonardo, Vesalius i el cos descobert',
                'subtitle'    => 'Renaixement: la dissecció com a revolució. Art i ciència, inseparables.',
                'badge'       => 'Renaixement',
                'quote_text'  => 'L\'ull, que es diu finestra de l\'ànima, és el principal mitjà pel qual la raó pot contemplar de manera abundant i magnífica les obres infinites de la natura.',
                'quote_author'=> 'Leonardo da Vinci',
                'quote_work'  => 'Quaderns anatòmics, ~1489–1513',
                'intro_text'  => 'Al Renaixement, algú va decidir que per entendre el cos calia obrir-lo. La dissecció de cadàvers, prohibida durant segles, es va convertir en acte científic i artístic alhora. Leonardo da Vinci va dibuixar el que veia. Andreas Vesalius ho va publicar. I la medicina mai va ser la mateixa.',
                'topic_text'  => 'Els dibuixos anatòmics de Leonardo da Vinci (1452–1519) representen el primer intent sistemàtic de documentar el cos humà a través de l\'observació directa. Leonardo va assistir a disseccions, va prendre notes, va girar els cadàvers, va seccionar membres. El resultat: uns 200 folis amb representacions del cor, el cervell, el fetus, les extremitats, que no es publicarien fins al s. XIX. Andreas Vesalius (1514–1564) va fer el pas definitiu: el 1543 va publicar "De humani corporis fabrica", el primer atlas anatòmic basat en disseccions sistemàtiques. Les il·lustracions eren obres d\'art en si mateixes.',
                'obra1_title' => 'Dibuixos anatòmics de Leonardo da Vinci',
                'obra1_autor' => 'Leonardo da Vinci · ~1489–1513 · Royal Collection Trust, Windsor · Domini públic',
                'obra1_text'  => 'Els quaderns anatòmics de Leonardo mostren el cos humà des d\'angles i perspectives que cap artista havia intentat. No eren il·lustracions científiques al servei d\'un text — eren documents visuals autònoms, on el dibuix era l\'instrument de coneixement. Leonardo va dibuixar el que veia, no el que li havien dit que hi havia.',
                'obra2_title' => 'De humani corporis fabrica (Vesalius, 1543)',
                'obra2_autor' => 'Andreas Vesalius · Basilea, 1543 · Il·lustracions atribuïdes al taller de Tizià · Domini públic',
                'obra2_text'  => 'La "Fabrica" de Vesalius és un dels llibres més influents de la historia de la medicina. Les il·lustracions mostren músculs, ossos i òrgans amb una precisió i una bellesa sense precedents. Els esquelets estan representats en postures reflexives, gairebé pensatives — com si la mort fos un estat de contemplació, no de fi. Era, alhora, un llibre científic i una obra d\'art.',
                'reflection'  => [
                    'Leonardo va dibuixar el que veia, no el que li havien ensenyat. Com creus que va canviar la seva manera de veure els seus contemporanis?',
                    'Els esquelets de Vesalius semblen pensatius o melancòlics. Per qué creus que l\'artista els va representar així?',
                    'Avui les imatges mèdiques (radiografies, TAC, ressonàncies) les fan les màquines. Hem perdut alguna cosa?',
                ],
            ],
            5 => [
                'title'       => 'El metge i el pacient: una relació pintada',
                'subtitle'    => 'Segles XIX–XX: l\'hospital entra als museus. La cura com a acte humà.',
                'badge'       => 'Moderna',
                'quote_text'  => 'El bon metge tracta la malaltia. El gran metge tracta el pacient que té la malaltia.',
                'quote_author'=> 'Sir William Osler',
                'quote_work'  => 'metge i humanista canadenc (1849–1919)',
                'intro_text'  => 'Al segle XIX, els pintors van entrar als hospitals. I el que van trobar allà — el metge vetllant el malalt, l\'operació quirúrgica, la sala d\'espera — es va convertir en tema pictòric de primer ordre. Per primera vegada, la relació entre qui cuida i qui és cuidat va quedar fixada en tela.',
                'topic_text'  => 'Dues obres defineixen aquest moment. "The Doctor" de Luke Fildes (1891) mostra un metge de camp que vetlla un nen malalt a la llum d\'una llàntia, mentre els pares esperen al fons. Era la imatge d\'una vocació. "The Gross Clinic" de Thomas Eakins (1875) mostra el cirurgià Samuel Gross operant davant d\'estudiants: les mans ensangonades, l\'expressió concentrada. Era la imatge d\'una tècnica. Juntes, representen les dues ànimes de la medicina del segle XIX.',
                'obra1_title' => 'The Doctor (Luke Fildes, 1891)',
                'obra1_autor' => 'Sir Samuel Luke Fildes · 1891 · Tate Britain, Londres · Domini públic',
                'obra1_text'  => 'Fildes va pintar "The Doctor" en memòria del seu fill mort de tifus la nit de Nadal de 1877. El metge del quadre és una figura silenciosa, concentrada, impotent davant la malaltia però present davant el pacient. Va ser una de les imatges més reproduïdes de la seva època — i la preferida dels estudiants de medicina del segle XX.',
                'obra2_title' => 'The Gross Clinic (Thomas Eakins, 1875)',
                'obra2_autor' => 'Thomas Eakins · 1875 · Philadelphia Museum of Art · Domini públic',
                'obra2_text'  => '"The Gross Clinic" va ser rebutjada per l\'exposició centennial de Filadèlfia per ser "massa cruenta". El cirurgià Gross apareix al centre, ensangontat, explicant una operació a estudiants que prenen notes. Era una obra sobre el poder del coneixement — però la sang la feia incòmoda. Avui s\'considera una de les obres mestres de l\'art nord-americà.',
                'reflection'  => [
                    '"The Doctor" mostra impotència i presència. "The Gross Clinic" mostra poder i tècnica. Quina de les dues imatges és més propera al que esperaries d\'un metge avui?',
                    'Fildes va pintar el quadre per la mort del seu fill. Com creus que el context personal canvia una obra d\'art?',
                    'Si haguessis de pintar la medicina del segle XXI, quina escena triaries? Qui hi hauria i on?',
                ],
            ],
            6 => [
                'title'       => 'El dolor representat: Goya, Van Gogh, Munch',
                'subtitle'    => 'Del dolor físic al dolor de l\'ànima. Quan la malaltia mental entra a l\'art.',
                'badge'       => 'Moderna',
                'quote_text'  => 'L\'art és una ferida convertida en llum.',
                'quote_author'=> 'Georges Braque',
                'quote_work'  => 'pintor cubista francès (1882–1963)',
                'intro_text'  => 'Goya va quedar sord als 46 anys. Van Gogh va passar temporades en un hospital psiquiàtric. Munch va viure tota la vida entre la malaltia i la mort de la família. Els tres van convertir el dolor en art — i a través d\'ells podem veure com la malaltia transforma la mirada.',
                'topic_text'  => 'El dolor no es pot fotografiar directament. Però es pot pintar. Goya, en els seus "pinturas negras" del final de la vida, va mostrar la degradació física i mental sense misericòrdia. Van Gogh va pintar des de l\'interior de la malaltia: les espirals de "Nit estrellada", els autoretrats amb l\'orella emmarcada. Munch va condensar en "El crit" una experiència que qualsevol amb un atac de pànic reconeixeria: la naturalesa que es retorç, l\'aire que vibra, la figura que xiscla en silenci. L\'art com a diagnòstic i com a testimoni.',
                'obra1_title' => 'Saturn devorant el seu fill (Francisco de Goya, ~1821–1823)',
                'obra1_autor' => 'Francisco de Goya · ~1821–23 · Museu del Prado, Madrid · Domini públic',
                'obra1_text'  => '"Saturn devorant el seu fill" forma part de les "pinturas negras" que Goya va pintar directament sobre les parets de la seva casa, la "Quinta del Sordo", en els últims anys de la seva vida. Era sord, ancià i vivia en un país devastat. La violència de la imatge és la violència del seu món interior.',
                'obra2_title' => 'El crit (Edvard Munch, 1893)',
                'obra2_autor' => 'Edvard Munch · 1893 · Galeria Nacional, Oslo · Domini públic',
                'obra2_text'  => 'Munch va escriure al seu diari que estava caminant quan "vaig sentir el crit infinit de la natura". La figura del quadre no crida — rep el crit. El cel en flames, el fiord distorsionat, la figura sense gènere ni edat. "El crit" és avui el quadre que més sovint es cita quan es parla d\'ansietat, por o crisi existencial. Va precedir el diagnòstic modern de trastorn d\'ansietat en unes quantes dècades.',
                'reflection'  => [
                    'Podeu pensar en un artista contemporani que, com Goya o Munch, hagi convertit la seva malaltia o dolor en obra?',
                    '"El crit" es descriu sovint com el "quadre de l\'ansietat". Creieu que l\'art pot diagnosticar estats psicològics que la medicina no sabria nomenar?',
                    'Mirant "Saturn devorant el seu fill", qué us genera? Fàstic, compassió, por? Qué diu de vosaltres la vostra reacció?',
                ],
            ],
            7 => [
                'title'       => 'Frida Kahlo: el cos com a autobiografia',
                'subtitle'    => 'El dolor crònic, la cirurgia i la identitat. Pintar per sobreviure.',
                'badge'       => 'Contemporani',
                'quote_text'  => 'Vaig patir dos accidents greus en la meva vida. Un en el qual un tramvia em va atropellar... L\'altre accident és Diego.',
                'quote_author'=> 'Frida Kahlo',
                'quote_work'  => 'pintora mexicana (1907–1954)',
                'intro_text'  => 'Frida Kahlo va sobreviure un accident de tramvia als divuit anys que li va trencar la columna, la pelvis i múltiples ossos. Va passar la vida entre operacions, cotilles de guix i dolor crònic. I va pintar tot això — el cos obert, la columna trencada, els fetus perduts, les cicatrius — amb una precisió clínica i una intensitat emotiva que no té precedents en la historia de l\'art.',
                'topic_text'  => 'Kahlo va convertir el cos malalt en territori d\'exploració artística. A "La columna trencada" (1944), es representa a si mateixa amb el pit obert i una columna jònica trencada al lloc de la columna vertebral, mentre llàgrimes li rellisquen per la cara. A "Sense esperança" (1945), és alimentada a la força mentre es troba immobilitzada. No són quadres sobre la malaltia en abstracte — són documents d\'una experiència viscuda, amb la precisió d\'un informe mèdic i la intensitat d\'un crit.',
                'obra1_title' => 'La columna trencada (Frida Kahlo, 1944)',
                'obra1_autor' => 'Frida Kahlo · 1944 · Museu Dolores Olmedo, Mèxic · © Banco de México / Fundació Kahlo',
                'obra1_text'  => '"La columna trencada" va ser pintada just després que Kahlo fos operada de columna i hagués de portar un cotilla de metall. La figura central —ella mateixa— té el cos obert com un llibre, la columna substituïda per una ruïna arquitectònica. El fons és un desert àrid. Les claus que li travessen el cos recorden les ungles de l\'acupuntura, però també els claus de la crucifixió.',
                'obra2_title' => 'Sense esperança (Frida Kahlo, 1945)',
                'obra2_autor' => 'Frida Kahlo · 1945 · Museu Dolores Olmedo, Mèxic · © Banco de México / Fundació Kahlo',
                'obra2_text'  => 'El 1945, Kahlo estava tan malalta que no podia estar asseguda. Va penjar el llenç del sostre i va pintar des del llit. "Sense esperança" la mostra immobilitzada mentre un embut li introdueix a la força un caos de carn, ossos i animals. Va escriure: "No em queda cap esperança... Tot es mou al compàs d\'allò que conté el ventre."',
                'reflection'  => [
                    'Kahlo va dir que pintava la seva pròpia realitat perquè era el que coneixia millor. Hi ha alguna cosa que tu coneixes tan profundament que sentiries la necessitat de representar-la?',
                    'Com creus que hauria estat rebuda Kahlo si hagués viscut avui, amb les xarxes socials i la cultura del selfie?',
                    'La medicina actual tractaria el dolor de Kahlo de manera molt diferent. Creus que el seu art hauria existit igualment?',
                ],
            ],
            8 => [
                'title'       => 'Art-teràpia: quan crear és curar',
                'subtitle'    => 'Avui: hospitals, teràpies creatives, art i neurociència. El taller final.',
                'badge'       => 'Teràpia',
                'quote_text'  => 'La creativitat és la intel·ligència que s\'alegra.',
                'quote_author'=> 'Atribuït a Albert Einstein',
                'quote_work'  => 'físic i humanista (1879–1955)',
                'intro_text'  => 'Avui els hospitals contracten artistes. Els oncòlegs prescriuen tallers de pintura. Els neurocientífics estudien com el procés creatiu activa zones del cervell relacionades amb el benestar. L\'art-teràpia no és marginal ni alternativa — és una disciplina reconeguda amb protocols, evidència i professionals formats. I hem passat vuit sessions preparant-nos per entendre per qué.',
                'topic_text'  => 'L\'art-teràpia és l\'ús terapèutic del procés creatiu per millorar el benestar físic, mental i emocional. No cal ser artista — cal expressar. La neurociència ha demostrat que crear activa el sistema de recompensa, redueix el cortisol (hormona de l\'estrès) i pot generar nous camins neuronals. Hospitals com el Massachusetts General de Boston o el Vall d\'Hebron de Barcelona integren l\'art-teràpia en protocols oncològics, de salut mental i de rehabilitació. En aquest taller final, tu et converteixes en subjecte: usaràs el que has après al llarg del curs per crear alguna cosa teva.',
                'obra1_title' => 'Art-teràpia en contextos hospitalaris',
                'obra1_autor' => 'Pràctica clínica contemporània · Diversos hospitals i centres de salut',
                'obra1_text'  => 'L\'art-teràpia en entorns clínics ha demostrat efectivitat per a la reducció d\'ansietat en pacients oncològics, la millora de l\'estat d\'ànim en persones amb depressió, la comunicació en pacients amb demència avançada i el processament del trauma. La clau no és el resultat artístic — és el procés.',
                'obra2_title' => 'Neurociència de la creativitat',
                'obra2_autor' => 'Investigació contemporània · Dècades 2000–2020',
                'obra2_text'  => 'Estudis d\'imatge cerebral mostren que crear art activa simultàniament el lòbul frontal (planificació), el sistema límbic (emoció) i el còrtex visual. El "flow" creatiu —aquell estat de concentració total— s\'associa a una reducció de l\'activitat de la xarxa de mode per defecte, la mateixa que s\'associa a la ruminació i l\'ansietat. Crear és, literalment, una pausa del cervell preocupat.',
                'reflection'  => [
                    'Al llarg del curs hem vist art que representa la malaltia. Avui l\'art és el tractament. Com has canviat la teva manera de mirar una obra d\'art al llarg d\'aquestes sessions?',
                    'Si poguessis dissenyar un programa d\'art-teràpia per a un col·lectiu específic (gent gran, adolescents, pacients crònics...), on posaries el focus? Per qué?',
                    'De totes les obres que hem vist al llarg del curs — des del Papir Ebers fins a Frida Kahlo —, quina t\'ha quedat gravada? Per qué creus que és aquesta i no una altra?',
                ],
            ],
        ];

        foreach ($sessions as $num => $data) {
            LmsLesson::updateOrCreate(
                ['course_id' => $course->id, 'session_number' => $num],
                [
                    'title'      => $data['title'],
                    'subtitle'   => $data['subtitle'],
                    'duration'   => '45–60 min',
                    'status'     => 'published',
                    'sort_order' => $num,

                    'quote_text'   => $data['quote_text'],
                    'quote_author' => $data['quote_author'],
                    'quote_work'   => $data['quote_work'],
                    'intro_text'   => $data['intro_text'],
                    'topic_text'   => $data['topic_text'],

                    'concepts' => [],

                    'text_cards' => [
                        [
                            'type'     => 'reference',
                            'title'    => $data['obra1_title'],
                            'author'   => $data['obra1_autor'],
                            'year'     => null,
                            'extract'  => '',
                            'analysis' => $data['obra1_text'],
                        ],
                        [
                            'type'     => 'reference',
                            'title'    => $data['obra2_title'],
                            'author'   => $data['obra2_autor'],
                            'year'     => null,
                            'extract'  => '',
                            'analysis' => $data['obra2_text'],
                        ],
                    ],

                    'reflection_questions' => array_map(
                        fn ($q) => ['question' => $q],
                        $data['reflection']
                    ),

                    'exercise' => [
                        'title'             => 'Exercici d\'observació i reflexió',
                        'duration'          => '25–30 min',
                        'statement'         => '',
                        'examples'          => [],
                        'tips'              => [],
                        'demo_first_person' => null,
                        'demo_third_person' => null,
                    ],

                    'questions' => [],
                ]
            );
        }

        $this->command->info('✅ LmsArtMedicinaSeeder: curs «' . $course->title . '» amb 8 sessions creat/verificat.');
    }
}
