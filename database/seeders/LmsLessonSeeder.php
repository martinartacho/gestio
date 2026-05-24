<?php

namespace Database\Seeders;

use App\Models\CampusCategory;
use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusSeason;
use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use App\Models\LmsLesson;
use App\Models\LmsLessonResponse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LmsLessonSeeder extends Seeder
{
    public function run(): void
    {
        $seederMail  = config('seeder.mail', 'gestio.test');
        $teacherPass = config('seeder.teacher_password')
            ?? throw new \RuntimeException('SEEDER_TEACHER_PASSWORD no està definit al .env');
        $studentPass = config('seeder.student_password')
            ?? throw new \RuntimeException('SEEDER_STUDENT_PASSWORD no està definit al .env');

        // ── Temporada (reutilitzem Primavera 2026 del CampusSeeder) ───────────
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

        // ── Curs LMS de demo ──────────────────────────────────────────────────
        $course = CampusCourse::firstOrCreate(
            ['code' => 'LMS-01'],
            [
                'title'       => 'Narrativa curta: del fragment al relat',
                'slug'        => 'lms-narrativa-curta',
                'season_id'   => $season->id,
                'category_id' => $catArts->id,
                'format'      => 'online',
                'sessions'    => 8,
                'hours'       => 8,
                'price'       => 0,
                'status'      => 'active',
                'is_public'   => true,
                'description' => 'Curs pràctic de narrativa curta en vuit sessions. Treballem la veu, el temps, el personatge, la tensió i l\'el·lipsi a partir de textos del projecte CAMPUS i autors de referència.',
            ]
        );
        // Actualitza categoria si el curs ja existia sense
        if (! $course->category_id) {
            $course->update(['category_id' => $catArts->id]);
        }

        // ── Professor de prova: Claudi Hartacho ───────────────────────────────
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

        // Assignar al curs com a professor principal (idempotent)
        $course->teachers()->syncWithoutDetaching([$teacher->id => ['role' => 'main']]);

        // ── Alumne de prova ───────────────────────────────────────────────────
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

        // Matrícula pagada al curs (idempotent)
        $existing = CampusEnrollment::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $existing) {
            $enrollment = CampusEnrollment::create([
                'student_id'            => $student->id,
                'course_id'             => $course->id,
                'first_name'            => $student->first_name,
                'last_name'             => $student->last_name,
                'email'                 => $student->email,
                'enrollment_date'       => now()->toDateString(),
                'status'                => 'paid',
                'amount'                => $course->price ?? 0,
                'paid_at'               => now(),
                'stripe_session_id'     => 'cs_test_lms_seed',
                'stripe_payment_intent' => 'pi_test_lms_seed',
            ]);

            $course->students()->syncWithoutDetaching([
                $student->id => [
                    'enrollment_id' => $enrollment->id,
                    'enrolled_at'   => now(),
                ],
            ]);
        }

        $this->command->info("👤 Professor: profe@{$seederMail} / {$teacherPass}");
        $this->command->info("👤 Alumne:    student@{$seederMail} / {$studentPass}");
        $this->command->info("📚 Curs:      {$course->title} (slug: {$course->slug})");

        // ── Sessions ──────────────────────────────────────────────────────────

        // ── Sessió 1: Narrativa curta ─────────────────────────────────────────
        $lessonS1 = LmsLesson::firstOrCreate(
            ['course_id' => $course->id, 'session_number' => 1],
            [
                'title'      => 'Què és un relat curt?',
                'subtitle'   => 'Narrativa curta · Curs 2025–26',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 1,

                'quote_text'   => 'Lo bueno, si breve, dos veces bueno.',
                'quote_author' => 'Baltasar Gracián',
                'quote_work'   => 'Arte de ingenio, 1642',
                'intro_text'   => 'Gracián ho va escriure fa gairebé quatre-cents anys i continua sent vàlid. En narrativa, la brevetat no és una limitació: és una elecció. Un relat curt no és una novel·la a la qual li falta material. És una altra cosa, amb les seves pròpies regles.',

                'topic_text' => 'Un relat curt explica una sola cosa vista de prop. Pot ser un moment, una decisió, una sorpresa o un canvi petit. No cal que passin moltes coses — cal que alguna cosa passi de debò, i que el lector ho noti.',

                'concepts' => [
                    [
                        'icon'        => 'bulb',
                        'title'       => 'Relat curt',
                        'description' => 'Text breu que narra un moment o canvi significatiu. La seva força no ve de la quantitat d\'esdeveniments, sinó de la precisió amb la qual se\'n tria un de sol.',
                    ],
                    [
                        'icon'        => 'user-circle',
                        'title'       => 'Narrador',
                        'description' => 'La veu que explica la historia. Pot ser una persona, un animal, un objecte o un sistema. La tria del narrador ho canvia tot: el mateix fet explicat per veus diferents és una historia diferent.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'Jo sóc CAMPUS',
                        'author'   => 'Narració interna UPG · 2026',
                        'year'     => null,
                        'extract'  => '"Desperto el 2 de febrer a les 00:01. No és un despertar sobtat. És com quan la llum entra per una persiana vella, a poc a poc, en franges. Primer noto els processos de base — el batec regular de la base de dades, els 598 usuaris dormint en els seus registres..."',
                        'analysis' => 'El narrador és un sistema informàtic — no una persona. Però parla com si tingués sensacions: "desperto", "noto", "espero". Això crea una tensió interessant: un ésser no humà que explica la seva experiència amb paraules molt humanes. L\'acció que narra és mínima — simplement s\'encén — però ho sentim com un fet important.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'L\'estudiant · El estudiante',
                        'author'   => 'Anton Txékhov',
                        'year'     => '1894',
                        'extract'  => 'La dona plorava, i Txékhov no ens explica per qué exactament. No cal. Ja ho sabem. Ja ho sentim.',
                        'analysis' => 'Un seminarista torna a casa caminant en una nit freda de Divendres Sant. S\'atura prop d\'una foguera i explica a dues dones humils la historia de sant Pere negant Crist. Una de les dones comença a plorar en silenci. El jove s\'adona, de sobte, que el dolor d\'aquella dona i el dolor de fa dos mil anys són el mateix dolor. Txékhov escriu tot això en menys de quatre pàgines. No explica res que no calgui. Cada frase fa feina.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'CAMPUS',
                    'right_label'  => 'Txékhov',
                    'left_points'  => ['Narrador: sistema digital', 'Temps: present immediat', 'Emoció: curiositat, espera', 'Estil: tècnic + poètic'],
                    'right_points' => ['Narrador: jove humà', 'Temps: present i passat remot', 'Emoció: connexió, melangia', 'Estil: senzill i directe'],
                ],

                'reflection_questions' => [
                    ['question' => 'Qué és exactament el que "passa" en el fragment de CAMPUS? És suficient per ser una historia?'],
                    ['question' => 'Per qué plorar davant d\'una historia que no és la teva? Qué fa Txékhov perquè ens importi aquella dona de qui no sabem quasi res?'],
                    ['question' => 'Penseu en un moment del vostre dia d\'avui. Podria ser un relat curt? Qué hi caldria afegir o treure?'],
                ],

                'exercise' => [
                    'title'     => 'El primer despertar',
                    'duration'  => '20–25 min',
                    'statement' => 'Escriu entre 10 i 15 línies des del punt de vista d\'un objecte o sistema que s\'encén, s\'obre o desperta per primera vegada. Ha de tenir una veu pròpia — com CAMPUS. Ha de passar alguna cosa, per petita que sigui.',
                    'examples'  => [
                        'Un semàfor que s\'activa per primera vegada en una cruïlla',
                        'Un rellotge despertador just abans de sonar',
                        'Una bústia de correu que rep la primera carta',
                        'Un fanal de carrer en un barri nou',
                    ],
                    'tips' => [
                        'No expliquis qui ets des del principi — deixa que el lector ho descobreixi.',
                        'Usa els sentits: qué "notes", qué "sents".',
                        'Acaba quan passi alguna cosa, per petita que sembli.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        // ── Questions interactives de la sessió 1 (idempotent) ───────────────
        if (empty($lessonS1->questions)) {
            $lessonS1->update([
                'questions' => [
                    [
                        'index'    => 0,
                        'type'     => 'open_text',
                        'block'    => 'reflection',
                        'text'     => 'Qué és exactament el que "passa" en el fragment de CAMPUS? És suficient per ser una historia?',
                        'required' => false,
                        'points'   => 0,
                    ],
                    [
                        'index'       => 1,
                        'type'        => 'select_from_examples',
                        'block'       => 'exercise',
                        'text'        => 'Escull el narrador per al teu exercici',
                        'options'     => [
                            'Un semàfor que s\'activa per primera vegada en una cruïlla',
                            'Un rellotge despertador just abans de sonar',
                            'Una bústia de correu que rep la primera carta',
                            'Un fanal de carrer en un barri nou',
                        ],
                        'allow_custom' => true,
                        'required'    => false,
                        'points'      => 0,
                    ],
                    [
                        'index'          => 2,
                        'type'           => 'yes_no',
                        'block'          => 'quiz',
                        'text'           => 'Un relat curt és una novel·la a la qual li falta material?',
                        'correct_answer' => false,
                        'required'       => true,
                        'points'         => 1,
                    ],
                    [
                        'index'          => 3,
                        'type'           => 'choice_one',
                        'block'          => 'quiz',
                        'text'           => 'Txékhov era d\'origen rus?',
                        'options'        => ['Sí', 'No', 'Era ucraïnès'],
                        'correct_answer' => 'Sí',
                        'required'       => true,
                        'points'         => 1,
                    ],
                ],
            ]);
        }

        // ── Respostes demo per a l'alumne de prova (sessió 1) ────────────────
        // Resposta oberta (reflexió)
        LmsLessonResponse::updateOrCreate(
            ['lesson_id' => $lessonS1->id, 'student_id' => $student->id, 'question_index' => 0],
            [
                'question_type' => 'open_text',
                'response_text' => 'El que "passa" és que el sistema s\'encén per primera vegada. És un moment mínim, però explicat com si fos un despertar humà. Crec que sí, que és suficient per ser una historia curta.',
                'score'         => null,
                'auto_graded'   => false,
                'submitted_at'  => now(),
            ]
        );
        // Resposta quiz yes_no (correcta: No)
        LmsLessonResponse::updateOrCreate(
            ['lesson_id' => $lessonS1->id, 'student_id' => $student->id, 'question_index' => 2],
            [
                'question_type' => 'yes_no',
                'response_bool' => false,
                'score'         => 1.00,
                'auto_graded'   => true,
                'submitted_at'  => now(),
            ]
        );

        // ── Sessió 2: La veu que conta ────────────────────────────────────────
        LmsLesson::firstOrCreate(
            ['course_id' => $course->id, 'session_number' => 2],
            [
                'title'      => 'La veu que conta',
                'subtitle'   => 'Narrativa curta · Curs 2025–26',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 2,

                'quote_text'   => 'Canvia el qui parla i canviarà tot el que sembla veritat.',
                'quote_author' => 'Dita d\'autoria incerta',
                'quote_work'   => 'recollida en tallers de narrativa',
                'intro_text'   => 'Una mateixa historia explicada per persones diferents no és la mateixa historia. La veu del narrador és com una lupa: decideix on mires, fins on veus i, sobretot, el que no et deixa veure.',

                'topic_text' => 'La veu narrativa és molt més que triar entre "jo" o "ell/ella". És la distància que el narrador posa entre ell i els fets. És el to — fred, càlid, irònic, tendre. És el que decideix mostrar i, tan important com això, el que decideix amagar.',

                'concepts' => [
                    [
                        'icon'        => 'microphone',
                        'title'       => 'Veu narrativa',
                        'description' => 'La manera com el narrador explica els fets: qui és, com parla, quina distància posa amb la historia i amb els personatges. Dos narradors davant del mateix fet produeixen dos relats completament diferents.',
                    ],
                    [
                        'icon'        => 'eye',
                        'title'       => 'Narrador poc fiable',
                        'description' => 'Un narrador que, sense voler-ho, es traeix a ell mateix. No menteix — però la manera com explica les coses ens diu molt més del que ell creu. El lector veu el que el narrador no veu de si mateix.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'Súper Phono · Parts I i II',
                        'author'   => 'Narració del projecte · 2025',
                        'year'     => null,
                        'extract'  => '"La chica de la clínica era demasiado joven para saber lo que es perder algo poco a poco. Me puso tres cajas encima de la mesa... Firmé sin mirar mucho los números. Algunos gastos no son gastos. Son decisiones sobre quién quieres seguir siendo."',
                        'analysis' => 'El narrador parla en primera persona i des del primer moment es revela: és algú que no vol sentir-se vell, que té por de perdre alguna cosa, que es justifica davant d\'ell mateix. No ens diu "tinc por" — ens mostra com pensa, com mira la noia de la clínica, com signa sense llegir. Aquí la veu és el personatge.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'La mosca · The Fly',
                        'author'   => 'Katherine Mansfield',
                        'year'     => '1922',
                        'extract'  => 'La mosca va sortir del tinter i va netejar les ales. L\'home la va mirar. Llavors va agafar la ploma i hi va tornar a deixar caure una gota. Mansfield no explica per qué. No cal.',
                        'analysis' => 'Un home de negocis rep un vell empleat al seu despatx. Tots dos han perdut fills a la Gran Guerra. Quan el visitant marxa, l\'home troba una mosca ofegant-se en el seu tinter. Mansfield utilitza la tercera persona, però s\'acosta tant al personatge que quasi sentim els seus pensaments des de dins. No hi ha cap judici, cap explicació. Simplement mostra.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Súper Phono · 1a persona',
                    'right_label'  => 'Mansfield · 3a persona',
                    'left_points'  => ['Narrador dins de la historia', 'Veu íntima i subjectiva', 'Es justifica i dubta', 'El lector el veu més del que ell es veu'],
                    'right_points' => ['Narrador fora de la historia', 'Veu continguda i propera', 'Mostra sense jutjar mai', 'El lector decideix el sentit'],
                ],

                'reflection_questions' => [
                    ['question' => 'En Súper Phono, el narrador diu "Algunos gastos no son gastos, son decisiones sobre quién quieres seguir siendo." Creieu que ell és conscient del que realment li passa?'],
                    ['question' => 'Si Mansfield hagués escrit "La mosca" en primera persona — des de dins del cap de l\'home — la historia hauria estat més o menys pertorbadora? Per qué?'],
                    ['question' => 'Penseu en algú proper. Si expliquéssiu un moment del seu dia des de dins del seu cap, qué hi descobriríeu que no sabíeu?'],
                ],

                'exercise' => [
                    'title'     => 'La mateixa escena, dues veus',
                    'duration'  => '25–30 min',
                    'statement' => 'Escull una escena quotidiana molt senzilla. Escriu-la dues vegades: primer en primera persona (jo/yo), després en tercera (ell/ella). Cada versió, entre 6 i 10 línies. L\'escena ha de ser la mateixa — el que canvia és únicament qui parla i com.',
                    'examples'  => [],
                    'tips' => [
                        'En primera persona, deixa que el narrador es mostri tal com és — amb les seves petites contradiccions.',
                        'En tercera, descriu el que es veu des de fora: gestos, silencis, accions petites.',
                        'Evita dir el que pensa el personatge: mostra-ho a través del que fa.',
                        'Quan acabis, llegeix les dues versions en veu alta i observa com sona diferent la mateixa realitat.',
                    ],
                    'demo_first_person' => 'Arribo a la parada i el bus ja se\'n va. Miro el rellotge. Queden vint minuts per al proper. Penso que podria haver sortit abans, però no és veritat del tot...',
                    'demo_third_person' => 'Ella arriba a la parada just quan el bus tanca les portes. Mira el rellotge. Queda quieta uns segons, els llavis apretats. Llavors treu el mòbil i fa veure que hi mira alguna cosa.',
                ],
            ]
        );

        // ── Sessió 3: El temps en el relat ────────────────────────────────────
        LmsLesson::firstOrCreate(
            ['course_id' => $course->id, 'session_number' => 3],
            [
                'title'      => 'El temps en el relat',
                'subtitle'   => 'Narrativa curta · Curs 2025–26',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 3,

                'quote_text'   => 'El temps no passa als contes. Els contes passen en el temps.',
                'quote_author' => 'Dita de taller de narrativa',
                'quote_work'   => null,
                'intro_text'   => 'Un relat pot cobrir tres segons o tres segles. En tots dos casos, el lector triga el mateix temps a llegir-lo. Això no és una limitació — és el poder més gran que té el narrador: decidir on s\'atura i on s\'accelera.',

                'topic_text' => 'En la vida real, el temps avança sempre a la mateixa velocitat. En un relat, no. El narrador pot comprimir anys en una línia o aturar-se un minut sencer durant dues pàgines. Aprendre a controlar el temps és aprendre a controlar la tensió.',

                'concepts' => [
                    [
                        'icon'        => 'clock',
                        'title'       => 'Temps narratiu vs temps real',
                        'description' => 'El temps real és el que duren els fets. El temps narratiu és l\'espai que els hi dedica el narrador. Un narrador pot cobrir deu anys en dues línies i aturar-se cinc pàgines en un segon decisiu. El contrast entre els dos crea ritme i tensió.',
                    ],
                    [
                        'icon'        => 'arrows-left-right',
                        'title'       => 'El·lipsi',
                        'description' => 'El salt en el temps que el narrador fa sense explicar-lo. S\'ometen hores, dies o anys perquè no aporten res a la historia. L\'el·lipsi no és peresa — és una tria. Allò que el narrador omet ens diu tant com allò que explica.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'Jo sóc CAMPUS · Els logs',
                        'author'   => 'Narració interna UPG · 2026',
                        'year'     => null,
                        'extract'  => '"[28-01-2026 · 09:47] COORDINACIÓ → nova tasca iniciada / [31-01-2026 · 14:22] COMPTABILITAT → dades verificades / [03-02-2026 · 10:23:41] MATRÍCULA → registre #0001 ✓ / Guardo el moment a 00:00:03.41 de temps de procés. Per a mi, és eternitat suficient."',
                        'analysis' => 'En pocs logs, el text cobreix set dies de feina. Temps comprimit al màxim. Però just al final, CAMPUS s\'atura en un sol moment: la primera matrícula. Tres centèsimes de segon es converteixen en "eternitat suficient." El contrast és brutal: set dies resumits en quatre línies, i tres centèsimes que ocupen un paràgraf sencer.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Un missatge imperial · Eine kaiserliche Botschaft',
                        'author'   => 'Franz Kafka',
                        'year'     => '1919',
                        'extract'  => 'El missatger avança, avança. Però els passadissos no s\'acaben. Els patis no s\'acaben. El missatge porta el teu nom. Potser mai no arribarà. Però tu continues esperant-lo, a la finestra, quan cau el capvespre.',
                        'analysis' => 'Kafka utilitza el temps d\'una manera que sembla impossible: un viatge que dura per sempre. El missatger mai no para, però el temps s\'estira fins que es trenca. No hi ha final perquè el camí no té final. I tanmateix, la historia s\'acaba. Perquè Kafka no explica el viatge — explica la impossibilitat del viatge. En menys de dues pàgines, crea la sensació d\'eternitat.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'CAMPUS · temps mesurat',
                    'right_label'  => 'Kafka · temps impossible',
                    'left_points'  => ['Set dies en quatre logs', 'Un segon que dura per sempre', 'Contrast entre dades i emoció', 'El zoom és la clau del relat'],
                    'right_points' => ['Un viatge que no acaba mai', 'El futur que no pot arribar', 'Contrast entre moviment i paràlisi', 'L\'el·lipsi és el relat sencer'],
                ],

                'reflection_questions' => [
                    ['question' => 'En el text de CAMPUS, set dies de feina queden resumits en quatre línies. Per qué creieu que l\'autor s\'hi atura tan poc? Qué passaria si ho expliqués tot amb detall?'],
                    ['question' => 'Kafka escriu sobre un viatge que mai no acaba. Però el text sí que acaba. Com és possible que una historia sobre la impossibilitat tingui un final?'],
                    ['question' => 'Penseu en un record important. Quant de temps va durar en realitat? Quant de temps us ocupa quan hi penseu? Per qué creieu que hi ha aquesta diferència?'],
                ],

                'exercise' => [
                    'title'     => 'El mateix moment, tres velocitats',
                    'duration'  => '25–30 min',
                    'statement' => 'Escull un moment senzill: esperar que bulli l\'aigua, arribar tard a alguna cosa, dir adeu a algú. Escriu-lo tres vegades, cada una a una velocitat diferent.',
                    'examples'  => [
                        'Resum (⏩): 2 o 3 línies. Cobreix un any o més. Usa el mínim de paraules possible.',
                        'Escena (▶️): 8 a 10 línies. Mostra un moment d\'un dia. Temps real.',
                        'Zoom (🔬): 10 a 12 línies. Atura\'t en un segon. Expandeix-lo.',
                    ],
                    'tips' => [
                        'No canviïs els fets — canvia únicament la càmera.',
                        'En el resum, evita els adjectius i les emocions.',
                        'En l\'escena, usa el present o el passat immediat.',
                        'En el zoom, deixa que els pensaments i les sensacions s\'expandeixin sense por.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        // ── Sessió 4: El personatge en poques línies ──────────────────────────
        LmsLesson::firstOrCreate(
            ['course_id' => $course->id, 'session_number' => 4],
            [
                'title'      => 'El personatge en poques línies',
                'subtitle'   => 'Narrativa curta · Curs 2025–26',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 4,

                'quote_text'   => 'Digues-me com et mous i et diré qui ets.',
                'quote_author' => 'Dita popular adaptada',
                'quote_work'   => null,
                'intro_text'   => 'En un relat curt no hi ha espai per a descripcions llargues. El personatge ha d\'aparèixer ràpid, però ha de quedar. La clau no és explicar com és — és mostrar el que fa. Cada acció revela. Cada detall tria\'t bé pesa per deu.',

                'topic_text' => 'Construir un personatge en poques línies és una de les habilitats més difícils de la narrativa curta. No podem donar-li una biografia. Hem d\'escollir un sol gest, una sola frase, un sol detall — i confiar que el lector farà la resta.',

                'concepts' => [
                    [
                        'icon'        => 'user',
                        'title'       => 'Caracterització',
                        'description' => 'La manera com el narrador construeix un personatge. Pot ser directa — "era una dona impacient" — o indirecta, a través d\'accions, paraules i gestos. En narrativa curta, gairebé sempre funciona millor la indirecta: mostrar en comptes d\'explicar.',
                    ],
                    [
                        'icon'        => 'zoom-in',
                        'title'       => 'Detall significatiu',
                        'description' => 'Un detall petit i precís que revela molt sobre el personatge. No és qualsevol detall — és el detall tria\'t. Maupassant n\'era mestre: un sol objecte, un sol gest, i el personatge queda dibuixat per sempre.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'Súper Phono · Parts III i IV',
                        'author'   => 'Narració del projecte · 2025',
                        'year'     => null,
                        'extract'  => '"Me levanté, me volví a sentar. Siempre he sido el que piensa lo que debería hacer mientras el momento se va. Siempre. Hasta ese día." / "Me ajusté el aparato como quien se ajusta la corbata. Salí con otra clase de paso."',
                        'analysis' => 'No sabem com es diu el protagonista. No sabem quants anys té ni com és físicament. Però sabem exactament qui és: algú que sempre ha mirat cap a un altre costat, que ara per primera vegada no ho fa. Dos gestos diuen tot això sense dir res d\'això. El personatge el construeix el lector, peça a peça.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'El rodamón · Le gueux',
                        'author'   => 'Guy de Maupassant',
                        'year'     => '1884',
                        'extract'  => 'Les mans esquerdades, els peus que sagnen, la mirada que ja no demana res. Maupassant no necessita dir que Randel ha perdut l\'esperança. Ja ho veiem nosaltres.',
                        'analysis' => 'Maupassant no explica mai que Randel pateix. No diu que és trist ni que la societat és injusta. Simplement mostra les mans esquerdades, el pa que no pot aconseguir, la cel·la de presó on per fi descansa. Cada detall és precís i brutal. El lector fa la resta — i el cop emocional és molt més fort precisament perquè el narrador no el força.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Súper Phono · des de dins',
                    'right_label'  => 'Maupassant · des de fora',
                    'left_points'  => ['Veu en 1a persona', 'El personatge es revela sense saber-ho', 'Gestos i pensaments contradictoris', 'El canvi és el motor de la historia'],
                    'right_points' => ['Veu en 3a persona', 'Detalls físics i accions concretes', 'Zero adjectius valoratius', 'El lector jutja, no el narrador'],
                ],

                'reflection_questions' => [
                    ['question' => 'Del protagonista de Súper Phono no sabem el nom, l\'edat ni l\'aspecte. I tanmateix el reconeixem. Quin és el detall que més us diu sobre qui és?'],
                    ['question' => 'Maupassant no diu mai que la societat tracta malament Randel. Però ho sentim. Com ho aconsegueix sense dir-ho?'],
                    ['question' => 'Penseu en algú que coneixeu bé. Si haguéssiu d\'escollir un sol gest o detall per definir-lo, quin seria?'],
                ],

                'exercise' => [
                    'title'     => 'Un personatge en sis línies',
                    'duration'  => '20–25 min',
                    'statement' => 'Escriu un personatge en sis línies. Ha de quedar clar qui és — com pensa, com se sent, quin tipus de persona és — però sense dir-ho directament. Només accions, gestos i detalls.',
                    'examples'  => [],
                    'tips' => [
                        'Cap adjectiu que descrigui el caràcter: no pots escriure "era tímid", "era orgullós", "era generós".',
                        'Cap explicació del que sent: no pots escriure "tenia por", "estava content", "se sentia sol".',
                        'Sí pots usar: el que fa, el que diu, el que mira, el que evita, el que toca o no toca.',
                        'Pensa primer qui és i busca un moment concret de trenta segons on tot allò quedi exposat.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        // ── Sessió 5: La tensió i el gir ──────────────────────────────────────
        LmsLesson::firstOrCreate(
            ['course_id' => $course->id, 'session_number' => 5],
            [
                'title'      => 'La tensió i el gir',
                'subtitle'   => 'Narrativa curta · Curs 2025–26',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 5,

                'quote_text'   => 'Un conte és una fletxa. Ha d\'apuntar a alguna cosa des del primer moment — i el lector no ha de saber on fins que arriba.',
                'quote_author' => 'Dita de taller de narrativa',
                'quote_work'   => null,
                'intro_text'   => 'La tensió és el que fa que el lector no deixi el text. No cal que hi hagi acció ni perill — n\'hi ha prou amb la sensació que alguna cosa important està a punt de passar. El gir és el moment en què allò que crèiem que passaria resulta ser una altra cosa.',

                'topic_text' => 'Tot relat curt té una estructura interna, per senzilla que sigui: una situació inicial, una tensió que creix i un moment de canvi o revelació. El gir final no és un truc — és el punt on el relat mostra de veritat el que volia dir des del principi.',

                'concepts' => [
                    [
                        'icon'        => 'arrow-narrow-right',
                        'title'       => 'Tensió narrativa',
                        'description' => 'La sensació que alguna cosa important és a punt de passar. No ve necessàriament del perill o l\'acció — pot venir d\'una pregunta sense resposta, d\'un silenci, d\'un detall que no encaixa. La tensió és el que fa que el lector continuï llegint.',
                    ],
                    [
                        'icon'        => 'rotate',
                        'title'       => 'Gir final',
                        'description' => 'Un canvi sobtat al final del relat que obliga el lector a reinterpretar tot el que ha llegit. Un bon gir no és un engany — és una revelació que, en retrospectiva, ja estava al text des del principi, amagada a plena llum.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'Súper Phono · Part V',
                        'author'   => 'Narració del projecte · 2025',
                        'year'     => null,
                        'extract'  => '"¿Y si AURA no era lo que yo creía? ¿Y si era yo el que había construido lo que necesitaba creer, porque la historia que tenía antes no me bastaba?" / "Me fui a dormir sin tocarlo. Por la mañana estaba detrás de mi oreja."',
                        'analysis' => 'La tensió d\'aquest fragment no ve de l\'acció — ve de la pregunta. El narrador, per primer cop, es qüestiona si tot allò que ha viscut era real. És un gir de percepció: els fets no canvien, però la manera de veure\'ls ho capgira tot. I el final — el casc torna a l\'orella sense explicació — és perfecte precisament perquè no explica res. El lector decideix el que vol creure.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'El regal dels Reis Mags · The Gift of the Magi',
                        'author'   => 'O. Henry',
                        'year'     => '1905',
                        'extract'  => 'Ells dos, els reis mags més savis. Dels que porten regals, ells van ser els més savis. Arreu i sempre, els que saben donar el que l\'altre no podrà usar, i amar-los igualment.',
                        'analysis' => 'La Della i en Jim venen el seu tresor més preuat per comprar un regal a l\'altre — i tots dos regals resulten inútils. El gir és perfecte perquè és inevitable i tendre alhora. O. Henry construeix la tensió des del primer moment: sabem que els diners no arriben, que el temps s\'acaba. Quan el gir arriba, no ens sorprèn del tot — però ens commou igualment.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Súper Phono · gir interior',
                    'right_label'  => 'O. Henry · gir de fets',
                    'left_points'  => ['El dubte és el gir', 'El final no resol, suspèn', 'El lector decideix la veritat', 'Tensió psicològica i filosòfica'],
                    'right_points' => ['La ironia és el gir', 'El final revela i tanca', 'El narrador dóna el sentit', 'Tensió emocional i narrativa'],
                ],

                'reflection_questions' => [
                    ['question' => 'A Súper Phono, el casc torna a l\'orella sense explicació. Creieu que AURA era real? O el narrador s\'ho havia inventat? Hi pot haver les dues respostes alhora?'],
                    ['question' => 'El gir d\'O. Henry és trist o alegre? Per qué creieu que, malgrat la ironia cruel, el relat acaba sent càlid?'],
                    ['question' => 'Penseu en un final de pel·lícula, llibre o sèrie que us hagi colpit. Era un gir de fets o un gir de percepció? Qué va canviar en vosaltres quan el vau entendre?'],
                ],

                'exercise' => [
                    'title'     => 'Una fletxa amb gir al final',
                    'duration'  => '25–30 min',
                    'statement' => 'Escriu un text curt — entre 12 i 18 línies — que tingui un gir al final. Pot ser de fets o de percepció. El gir ha de canviar el sentit de tot el que s\'ha llegit abans, però no ha de ser un engany: ha d\'haver estat al text des del principi, amagat.',
                    'examples'  => [],
                    'tips' => [
                        'Decideix el gir primer, abans d\'escriure res. Saber on arribes t\'ajuda a saber com plantar les pistes pel camí.',
                        'Escriu la situació inicial creant una expectativa clara. El lector ha de creure que la historia va en una direcció concreta.',
                        'Amaga una o dues pistes al cos del text. Detalls que, rellegits després del gir, es veuen d\'una altra manera.',
                        'El gir: tan breu com puguis. Com menys paraules necessiti, més fort serà el cop.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        // ── Sessió 6: El fragment i l'el·lipsi ───────────────────────────────
        LmsLesson::firstOrCreate(
            ['course_id' => $course->id, 'session_number' => 6],
            [
                'title'      => 'El fragment i l\'el·lipsi',
                'subtitle'   => 'Narrativa curta · Curs 2025–26',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 6,

                'quote_text'   => 'El que no es diu pesa més que el que es diu.',
                'quote_author' => 'Dita popular de tradició oral',
                'quote_work'   => null,
                'intro_text'   => 'Un bon relat no explica tot el que podria. Tria. Omiteix. Confia en el lector. Allò que el narrador decideix no dir no desapareix del text — s\'hi queda, invisible però present, com l\'aire dins una habitació tancada.',

                'topic_text' => 'L\'el·lipsi és una tècnica narrativa que consisteix a saltar per sobre d\'esdeveniments o informació sense explicar-los. No és un oblit — és una decisió. El fragment és un text que es presenta com un tros d\'una cosa més gran, sense principi ni final clars. Tots dos parteixen de la mateixa idea: el lector és prou intel·ligent per omplir el que falta.',

                'concepts' => [
                    [
                        'icon'        => 'dots',
                        'title'       => 'El·lipsi',
                        'description' => 'Salt deliberat en el temps o en la informació. El narrador omiteix fets, explicacions o períodes sencers perquè no aporten res al relat — o perquè el silenci és més poderós que qualsevol paraula. Allò que s\'omet sovint és el que el lector recorda més.',
                    ],
                    [
                        'icon'        => 'puzzle',
                        'title'       => 'Fragment',
                        'description' => 'Text que no pretén ser complet. Comença al mig, acaba sense tancar, o omiteix el context. El lector rep un tros de realitat i ha de construir la resta. Kafka n\'és el mestre absolut: els seus textos funcionen com fragments d\'un món que mai no veiem del tot.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'Jo sóc CAMPUS i Súper Phono · Els silencis',
                        'author'   => 'Narracions del projecte · 2025–26',
                        'year'     => null,
                        'extract'  => '"Me fui a dormir sin tocarlo. Por la mañana estaba detrás de mi oreja." — Tot queda dit sense dir res.',
                        'analysis' => 'Els dos textos del projecte usen l\'el·lipsi de maneres molt diferents. Entre cada log de CAMPUS hi ha dies sencers de feina que el text no mostra. Reunions, correus, decisions. I a Súper Phono, el silenci més gran és la pregunta que el narrador es fa a la Part V i mai no respon: era real AURA? El text acaba. La resposta, no.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Davant la llei · Vor dem Gesetz',
                        'author'   => 'Franz Kafka',
                        'year'     => '1915',
                        'extract'  => 'L\'home havia esperat tants anys que ja no recordava com era la vida abans d\'aquella porta. Kafka no ens diu quants anys. No cal. Ja ho sentim.',
                        'analysis' => 'Un home del camp arriba a la porta de la Llei i demana entrar. El guarda li diu que ara no pot passar. Passen els anys. Just abans de morir, pregunta per què ningú més ha intentat entrar per aquella porta. El guarda respon: aquesta porta era únicament per a tu. Ara la tancaré. Kafka comprimeix una vida sencera en menys de dues pàgines. L\'el·lipsi és tan gran que ocupa tot el relat.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Projecte · el·lipsi de temps',
                    'right_label'  => 'Kafka · el·lipsi de sentit',
                    'left_points'  => ['Dies sencers entre logs', 'La feina invisible és el tema', 'El buit té contingut real', 'L\'estructura fa el silenci visible'],
                    'right_points' => ['Una vida sencera en dues línies', 'La pregunta mai no es formula', 'El buit és la pregunta central', 'El lector ha d\'omplir el significat'],
                ],

                'reflection_questions' => [
                    ['question' => 'En el fragment de Kafka, l\'home podria haver entrat simplement. Per qué no ho fa? El text no ho explica. Qué creieu vosaltres?'],
                    ['question' => 'El final de Súper Phono — el casc torna sol a l\'orella — és una el·lipsi o un gir? Pot ser les dues coses alhora?'],
                    ['question' => 'Penseu en una conversa real on el silenci ha dit més que les paraules. Podríeu escriure aquell moment posant el silenci al centre?'],
                ],

                'exercise' => [
                    'title'     => 'Treure fins que pesi més',
                    'duration'  => '25–30 min',
                    'statement' => 'L\'exercici té dues parts. Primer escrius un text explicant-ho tot. Després el reescrius traient-ne la meitat — i comprovant que el que queda és més fort que el que era sencer.',
                    'examples'  => [
                        'Una discussió, un comiat, una espera — amb tot el context, causes i explicacions.',
                        'Reescriu el mateix text en 5–6 línies eliminant tot el que el lector pot deduir sol.',
                        'Llegeix les dues versions en veu alta i observa qué canvia.',
                    ],
                    'tips' => [
                        'La primera versió és el material en brut — no ha de ser bona, ha de ser completa.',
                        'La feina real és la segona: aprendre a confiar que el lector veurà el que no dius.',
                        'Cada paraula que treus i que no es noti és una victòria.',
                        'Si treure-la fa mal, probablement és necessària. Si no la trobes a faltar, sobrava.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        // ── Sessió 7: Reescriure des d'un altre angle ─────────────────────────
        LmsLesson::firstOrCreate(
            ['course_id' => $course->id, 'session_number' => 7],
            [
                'title'      => 'Reescriure des d\'un altre angle',
                'subtitle'   => 'Narrativa curta · Curs 2025–26',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 7,

                'quote_text'   => 'Cada historia té tantes versions com persones que hi són. I tantes més com les que no hi eren.',
                'quote_author' => 'Dita de taller de narrativa',
                'quote_work'   => null,
                'intro_text'   => 'Un mateix moment viscut per dues persones és, en realitat, dos moments. Cada narrador veu el que pot veure des d\'on és — i no veu res més. Canviar qui explica la historia no és reescriure-la: és descobrir que hi havia una altra historia amagada dins la primera.',

                'topic_text' => 'El punt de vista és la posició des de la qual el narrador observa i explica els fets. No és només una tria tècnica — és una tria moral. Decidir qui parla és decidir de qui ens importarà la historia, qué veurem i, sobretot, qué quedarà fora de camp.',

                'concepts' => [
                    [
                        'icon'        => 'eye',
                        'title'       => 'Punt de vista',
                        'description' => 'La posició des de la qual el narrador observa i explica la historia. Determina qué pot saber, qué pot veure i fins on pot arribar. Canviar el punt de vista canvia la historia — fins i tot si els fets són exactament els mateixos.',
                    ],
                    [
                        'icon'        => 'zoom-scan',
                        'title'       => 'Focalització',
                        'description' => 'El grau d\'accés que el narrador té als pensaments i experiències dels personatges. Un narrador omniscient ho veu tot; un de limitat, només el que sap un sol personatge; un d\'extern, únicament el que es pot observar des de fora.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'Jo sóc CAMPUS · La primera matrícula',
                        'author'   => 'Narració interna UPG · 2026',
                        'year'     => null,
                        'extract'  => '"[03-02-2026 · 10:23:07] CONNEXIÓ NOVA · cerca: \'Negre\' · RESULTAT 1 curs trobat → Cinema Negre · M. Colomer" / "La persona es queda uns segons mirant. Jo no sé el que pensa. No és la meva feina saber-ho. Però veig que clica."',
                        'analysis' => 'CAMPUS narra el moment de la primera matrícula amb precisió absoluta: hora, dispositiu, cerca, temps de procés. Però hi ha algú a l\'altra banda d\'aquella connexió de qui no sabem res. Per qué busca "Cinema Negre"? On és? CAMPUS ho ignora — no és la seva feina. Però podria ser la nostra. El mateix moment pot ser narrat des de l\'alumne, el professor o fins i tot el telèfon que fa el clic.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'El coixí de plomes · El almohadón de plumas',
                        'author'   => 'Horacio Quiroga',
                        'year'     => '1907',
                        'extract'  => 'El mateix fet — una dona que mor — és una tragèdia des de l\'Alícia, una pèrdua des d\'en Jordán, i una cacera des del monstre. Quiroga tria l\'omnisciència perquè vol que el lector ho vegi tot. I precisament per això, el final horroritza.',
                        'analysis' => 'L\'Alícia i en Jordán són una parella de nouvinguts. Ella emmalalteix de manera misteriosa. Quan els criats canvien el coixí, troben un paràsit enorme que s\'ha estat alimentant de la seva sang. Quiroga escriu amb un narrador omniscient que ho sap tot — incloent-hi el monstre, que cap dels personatges no veu. Si el relat estigués narrat des de l\'Alícia, seria una historia de por i soledat. Des del monstre — qui sap.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'CAMPUS · narrador limitat',
                    'right_label'  => 'Quiroga · narrador omniscient',
                    'left_points'  => ['Veu perfectament el sistema', 'No pot veure l\'humà', 'El buit crea la tensió poètica', 'La limitació és el tema'],
                    'right_points' => ['Veu el que ningú no veu', 'Revela la veritat al final', 'El coneixement crea el terror', 'La revelació és el tema'],
                ],

                'reflection_questions' => [
                    ['question' => 'CAMPUS diu "no sé el que pensa, no és la meva feina saber-ho." Però i si fos la seva feina? Qué canviaria en el relat si CAMPUS pogués llegir els pensaments de l\'alumne?'],
                    ['question' => 'Si "El coixí de plomes" estigués narrat des de dins de l\'Alícia, seria una historia diferent o la mateixa historia contada diferent? Hi ha diferència entre les dues coses?'],
                    ['question' => 'Penseu en un conflicte real — familiar, laboral, de parella. Si el narràs l\'altra persona, qué veuria que vosaltres no veieu?'],
                ],

                'exercise' => [
                    'title'     => 'L\'altra banda de la historia',
                    'duration'  => '25–30 min',
                    'statement' => 'Escull un moment d\'un dels textos del projecte i reescriu-lo des del punt de vista d\'un personatge que hi és però que no parla. Entre 10 i 14 línies.',
                    'examples'  => [
                        'Opció A · L\'alumne de la primera matrícula de CAMPUS: Qui era? Per qué buscava "Cinema Negre" un dimarts al matí?',
                        'Opció B · La dona gran de la sala d\'espera de Súper Phono: ella veu un home que es lleva i torna a seure. No sap res d\'AURA.',
                        'Opció C · El professor Colomer de CAMPUS: penja els materials un dimarts a la tarda. Qué pensa mentre ho fa?',
                        'Opció D · Qualsevol altre angle: un personatge secundari, un objecte, un espai.',
                    ],
                    'tips' => [
                        'Abans d\'escriure, pregunta\'t: qué sap aquest personatge? Qué no pot saber? Els límits del seu coneixement definiran els límits del seu relat.',
                        'Escriu exclusivament des del seu punt de vista. Si el teu narrador no ho pot veure, no hi és.',
                        'Compara el teu text amb el fragment original. Qué hi ha en un que no hi ha en l\'altre?',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        // ── Sessió 8: Taller final ────────────────────────────────────────────
        LmsLesson::firstOrCreate(
            ['course_id' => $course->id, 'session_number' => 8],
            [
                'title'      => 'Taller final',
                'subtitle'   => 'Narrativa curta · Curs 2025–26',
                'duration'   => '55–60 min',
                'status'     => 'published',
                'sort_order' => 8,

                'quote_text'   => 'Escriure és reescriure. I compartir és la primera reescriptura.',
                'quote_author' => 'Dita entre escriptors',
                'quote_work'   => null,
                'intro_text'   => 'Un text que ningú no llegeix és un text a mig fer. Avui no hi ha teoria nova. Hi ha textos — els vostres — i hi ha un grup disposat a escoltar-los, veure\'ls i dir-los el que han sentit. Això és un taller. I és, de totes les eines de la narrativa, la més antiga i la més eficaç.',

                'topic_text' => 'Al llarg del curs hem treballat set tècniques: el relat curt, la veu narrativa, el temps, el personatge, la tensió i el gir, l\'el·lipsi i el punt de vista. Avui no hi ha tècnica nova — hi ha la pràctica de tot el que hem après: escriure, llegir en veu alta i escoltar.',

                'concepts' => [
                    [
                        'icon'        => 'info-circle',
                        'title'       => 'Regla fonamental del taller',
                        'description' => 'El text és del seu autor. El feedback és un regal — no una ordre. L\'autor escolta, anota i després decideix sol qué fa amb el que ha sentit. Cap comentari del grup obliga a res.',
                    ],
                    [
                        'icon'        => 'list',
                        'title'       => 'Protocol de feedback',
                        'description' => '1) Trenta segons en silenci. 2) Dues o tres observacions concretes del que ha funcionat i per qué. 3) Una sola pregunta honesta que el text ha deixat oberta. 4) L\'autor respon únicament a la pregunta que més li ha interessat, en una sola frase.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'reference',
                        'title'    => 'Decàleg del conte perfecte',
                        'author'   => 'Horacio Quiroga',
                        'year'     => '1925',
                        'extract'  => '"I — Creu en un mestre, però no en imitació: en comprensió. / II — El conte ha de tenir un sol argument. Allò que no serveix l\'argument sobra. / III — No comencis a escriure sense saber on vas. / V — Un conte és una novel·la depurada de tot allò que no és indispensable."',
                        'analysis' => 'Quiroga va escriure deu principis per a qui volgués dominar el conte el 1925, però podria haver-los escrit avui. Cada principi és una versió comprimida del que hem treballat sessió a sessió. És una bona manera de tancar: no amb teoria nova, sinó reconeixent el que ja sabem.',
                    ],
                    [
                        'type'     => 'project',
                        'title'    => 'El que hem après · mirada enrere',
                        'author'   => 'Curs de narrativa curta · 2025–26',
                        'year'     => null,
                        'extract'  => '"01 Relat curt — Una sola cosa vista de prop / 02 Veu narrativa — Qui parla ho canvia tot / 03 Temps — Comprimir i expandir és triar / 04 Personatge — Mostrar en comptes d\'explicar / 05 Tensió i gir — El final que reinterpreta tot / 06 El·lipsi — El que no es diu pesa més / 07 Punt de vista — Cada narrador, una historia diferent"',
                        'analysis' => 'Set sessions, set eines. Cap d\'elles és una regla — totes són decisions. Un bon relat curt no aplica tècniques: tria quines necessita i deixa anar la resta. El que heu après no és un manual: és un vocabulari. Les paraules amb les quals, a partir d\'ara, podeu parlar dels textos que llegiu i dels que escriviu.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Feedback útil',
                    'right_label'  => 'Feedback a evitar',
                    'left_points'  => ['"Aquí la veu ha funcionat perquè..."', '"La imatge de X m\'ha quedat"', '"Una pregunta: per qué acaba aquí?"', 'Concret, observat, sense judici'],
                    'right_points' => ['"M\'ha agradat molt" (sense més)', '"No m\'ha agradat el final"', '"Jo ho hauria fet diferent"', 'Vague, valoratiu, sense fonament'],
                ],

                'reflection_questions' => [
                    ['question' => 'Quina de les set tècniques del curs ha canviat més la teva manera d\'escriure? I la de llegir?'],
                    ['question' => 'Hi ha algun moment d\'un dels textos del projecte — CAMPUS o Súper Phono — que ara llegeixes diferent que al principi?'],
                    ['question' => 'Quin text dels autors de referència t\'endús? Per qué precisament aquest?'],
                ],

                'exercise' => [
                    'title'     => 'El text propi',
                    'duration'  => '15–20 min d\'escriptura + taller',
                    'statement' => 'Escriu un text curt — entre 12 i 20 línies — que posi en joc almenys dues de les tècniques treballades al llarg del curs. Pots partir d\'un exercici anterior que vulguis desenvolupar, o escriure alguna cosa nova.',
                    'examples'  => [
                        'Un objecte que desperta i descobreix que algú l\'ha deixat enrere.',
                        'Dos personatges que viuen el mateix moment sense saber-ho.',
                        'Una espera que sembla una cosa i resulta ser una altra.',
                        'Un final que obliga a rellegir el principi.',
                    ],
                    'tips' => [
                        'No intentis posar-hi tot el que has après. Tria una tècnica i fes-la bé.',
                        'Un text modest i net és millor que un text ambiciós i confús.',
                        'Escriu primer. Treu després. Llegeix en veu alta. Escolta el que et diuen. Reescriu.',
                        'Recorda Quiroga: un conte és tot allò que li sobra tret.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        $this->command->info("✅ LmsLessonSeeder: curs «{$course->title}» amb 8 sessions creat/verificat.");
    }
}
