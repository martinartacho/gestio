<?php

namespace Database\Seeders;

use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusSeason;
use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use App\Models\LmsLesson;
use App\Models\LmsLessonResponse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LmsMathSeeder extends Seeder
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

        // ── Curs MAT-01 ────────────────────────────────────────────────────────
        $course = CampusCourse::firstOrCreate(
            ['code' => 'MAT-01'],
            [
                'title'       => 'Matemàtica elemental',
                'slug'        => 'mat-matematica-elemental',
                'season_id'   => $season->id,
                'format'      => 'online',
                'sessions'    => 8,
                'hours'       => 8,
                'price'       => 0,
                'status'      => 'active',
                'is_public'   => true,
                'description' => '6è de Primària · Curs de preparació per a l\'ESO · 8 sessions de 45–60 min. Un curs que repassa i consolida els conceptes clau de sisè abans de fer el salt a l\'ESO. Cada sessió parteix d\'una situació real i acaba amb un exercici pràctic sense calculadora.',
            ]
        );

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
                'stripe_session_id'     => 'cs_test_mat_seed',
                'stripe_payment_intent' => 'pi_test_mat_seed',
            ]);
            $course->students()->syncWithoutDetaching([
                $student->id => ['enrollment_id' => $enrollment->id, 'enrolled_at' => now()],
            ]);
        }

        $this->command->info("📚 Curs: {$course->title} (slug: {$course->slug})");

        // ══════════════════════════════════════════════════════════════════════
        // SESSIÓ 1 — Els nombres: de les pedres al mòbil
        // ══════════════════════════════════════════════════════════════════════
        $s1 = LmsLesson::firstOrCreate(
            ['course_id' => $course->id, 'session_number' => 1],
            [
                'title'      => 'Els nombres: de les pedres al mòbil',
                'subtitle'   => 'Nombres naturals, enters i el sistema decimal. D\'on venen els nombres i per a qué serveixen.',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 1,

                'quote_text'   => 'Els nombres governen el món.',
                'quote_author' => 'Pitàgores',
                'quote_work'   => 'matemàtic grec (570–495 aC)',

                'intro_text' => 'Abans d\'aprendre cap fórmula, val la pena preguntar-se: d\'on vénen els nombres? Comptar és una de les primeres coses que els humans van aprendre a fer. I ho van fer de la mateixa manera que ho faríem nosaltres avui si no tinguéssim paper: amb pedres, ossos i marques a la paret.',

                'topic_text' => 'Avui repassem els tres tipus de nombres que ja coneixes i el sistema que usem per escriure\'ls. No és teoria nova — és organitzar bé el que ja saps perquè et serveixi a l\'ESO.

Els nombres naturals (0, 1, 2, 3, 4...) serveixen per comptar coses. Els nombres enters amplien els naturals cap a l\'esquerra del zero: ..., −3, −2, −1, 0, 1, 2, 3... El sistema decimal usa deu símbols (0 al 9) i el valor de cada dígit depèn del lloc que ocupa — això s\'anomena valor posicional. El dígit 3 val 3.000 si és a la posició de milers, però val 3 si és a la posició d\'unitats.',

                'concepts' => [
                    [
                        'icon'        => 'bulb',
                        'title'       => 'Valor posicional',
                        'description' => 'En el sistema decimal, el valor d\'un dígit depèn del lloc que ocupa. El dígit 3 val coses molt diferents a 3, a 30, a 300 o a 3.000. La mateixa xifra, diferent pes segons on és. El nombre 3.705 = 3.000 + 700 + 0 + 5. Cada posició val deu vegades més que la de la dreta.',
                    ],
                    [
                        'icon'        => 'minus',
                        'title'       => 'Nombres negatius',
                        'description' => 'Els nombres negatius apareixen quan necessitem representar quelcom per sota d\'un punt de referència: temperatura sota zero, pisos per sota del carrer, saldo negatiu al banc. A la recta numèrica, viuen a l\'esquerra del zero.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'La guardiola de la Laia',
                        'author'   => 'Valor posicional · nombres naturals',
                        'year'     => null,
                        'extract'  => 'La Laia ha estat guardant monedes i bitllets durant un any. Avui buida la guardiola i troba: 1 bitllet de 50 €, 3 bitllets de 10 €, 7 monedes de 2 €, 9 monedes de 0,50 €. Quant té en total?',
                        'analysis' => 'Pas 1: calcula el valor de cada grup. 50×1 = 50 €, 10×3 = 30 €, 2×7 = 14 €, 0,50×9 = 4,50 €. Pas 2: suma tots els valors. 50 + 30 + 14 + 4,50 = 98,50 €. Pas 3: escriu el total en la taula de valor posicional. 9 desenes, 8 unitats, 5 dècimes → noranta-vuit euros i cinquanta cèntims.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'L\'os d\'Ishango · 20.000 anys comptant',
                        'author'   => 'Congo · ~18.000 aC',
                        'year'     => null,
                        'extract'  => 'El 1960, a la vora del llac Eduard al Congo, els arqueòlegs van trobar un os de babuí amb 168 marques gravades en tres columnes. Tenia més de vint mil anys.',
                        'analysis' => 'No sabem exactament per a qué servia — potser per comptar la lluna, potser per registrar caceres — però és el primer registre conegut d\'un ésser humà comptant de manera sistemàtica. Milers d\'anys després, Pitàgores va afirmar que els nombres no eren només eines per comptar, sinó l\'estructura invisible de tot el que existeix. El sistema que usem avui — deu dígits, valor posicional — va arribar de l\'Índia a través dels matemàtics àrabs fa uns mil anys. Sense ell, no existirien els ordinadors ni res digital.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Nombres naturals',
                    'right_label'  => 'Nombres enters',
                    'left_points'  => ['Comencen al 0', 'Sempre positius o zero', 'Comptar objectes reals', 'Exemple: 0, 1, 2, 3, 4...'],
                    'right_points' => ['Inclouen els negatius', 'A esquerra i dreta del 0', 'Temperatura, deutes, pisos', 'Exemple: ..., −3, −2, −1, 0, 1, 2, 3...'],
                ],

                'reflection_questions' => [
                    ['question' => 'Per qué creus que els humans antics van sentir la necessitat de comptar? Qué necessitaven saber exactament?'],
                    ['question' => 'Posa un exemple de la teva vida on usis nombres negatius sense adonar-te\'n. La temperatura? Els pisos d\'un aparcament? El saldo del mòbil?'],
                    ['question' => 'Si no existís el zero, qué canviaria? Podríem tenir el sistema decimal sense el zero?'],
                ],

                'exercise' => [
                    'title'     => 'Llegir, escriure i ordenar nombres',
                    'duration'  => '20–25 min',
                    'statement' => 'Resol aquests quatre reptes sense calculadora. Raona cada resposta — no n\'hi ha prou amb el resultat. A l\'ESO et demanaran sempre el procés, no només la solució.',
                    'examples'  => [
                        'A) Escriu en xifres: "vuit mil dos-cents trenta-set". Després, descompon-lo en milers, centenes, desenes i unitats.',
                        'B) Ordena de menor a major: −8, 3, −1, 0, 7, −4, 2. Situa\'ls a la recta numèrica.',
                        'C) La temperatura a Puigcerdà era de −3 °C al matí. A migdia havia pujat 11 graus. Quina temperatura feia a migdia? I si a la nit baixa 8 graus més?',
                        'D) Quin és el nombre més gran que pots escriure amb els dígits 5, 0, 3, 8 i 1, usant cada dígit una sola vegada? I el més petit? Per qué?',
                    ],
                    'tips' => [
                        'No vagis directe al resultat. Llegeix cada problema sencer i identifica qué et demana.',
                        'Escriu els passos — un resultat sense raonament és com una resposta sense pregunta.',
                        'Per als nombres negatius, usa la recta numèrica per no perdre\'t.',
                        'Pensa en quin lloc val més cada dígit quan ordenis xifres.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],
            ]
        );

        // ── Preguntes interactives sessió 1 ───────────────────────────────────
        if (empty($s1->questions)) {
            $s1->update([
                'questions' => [
                    [
                        'index' => 0, 'type' => 'open_text', 'block' => 'reflection',
                        'text'  => 'Per qué creus que els humans antics van sentir la necessitat de comptar? Qué necessitaven saber exactament?',
                        'required' => false, 'points' => 0,
                    ],
                    [
                        'index' => 1, 'type' => 'yes_no', 'block' => 'quiz',
                        'text'  => 'El zero és un nombre natural?',
                        'correct_answer' => true, 'required' => true, 'points' => 1,
                    ],
                    [
                        'index' => 2, 'type' => 'choice_one', 'block' => 'quiz',
                        'text'  => 'En el nombre 3.705, el dígit 7 representa...',
                        'options'        => ['7 unitats', '70 desenes', '700 (set-centes)', '7.000 milers'],
                        'correct_answer' => '700 (set-centes)',
                        'required' => true, 'points' => 2,
                    ],
                    [
                        'index' => 3, 'type' => 'choice_many', 'block' => 'quiz',
                        'text'  => 'Quins d\'aquests nombres SÓN enters però NO naturals?',
                        'options'         => ['−3', '0', '5', '−1', '100'],
                        'correct_answers' => ['−3', '−1'],
                        'required' => false, 'points' => 2,
                    ],
                    [
                        'index' => 4, 'type' => 'select_from_examples', 'block' => 'exercise',
                        'text'  => 'Quin problema de l\'exercici t\'ha semblat més difícil?',
                        'options' => [
                            'A) Escriure i descompondre 8.237',
                            'B) Ordenar nombres enters a la recta',
                            'C) Sumes amb temperatures negatives',
                            'D) El nombre més gran i més petit amb 5 dígits',
                        ],
                        'allow_custom' => true, 'required' => false, 'points' => 0,
                    ],
                ],
            ]);
        }

        // ── Respostes demo alumne (sessió 1) ──────────────────────────────────
        LmsLessonResponse::updateOrCreate(
            ['lesson_id' => $s1->id, 'student_id' => $student->id, 'question_index' => 0],
            ['question_type' => 'open_text', 'response_text' => 'Crec que necessitaven comptar per saber quanta menjar tenien, quants animals havien caçat, o quants dies feia que plovia.', 'score' => null, 'auto_graded' => false, 'submitted_at' => now()]
        );
        LmsLessonResponse::updateOrCreate(
            ['lesson_id' => $s1->id, 'student_id' => $student->id, 'question_index' => 1],
            ['question_type' => 'yes_no', 'response_bool' => true, 'score' => 1.00, 'auto_graded' => true, 'submitted_at' => now()]
        );
        LmsLessonResponse::updateOrCreate(
            ['lesson_id' => $s1->id, 'student_id' => $student->id, 'question_index' => 2],
            ['question_type' => 'choice_one', 'response_choices' => ['700 (set-centes)'], 'score' => 2.00, 'auto_graded' => true, 'submitted_at' => now()]
        );

        // ══════════════════════════════════════════════════════════════════════
        // SESSIÓ 2 — Fraccions: repartir el món
        // ══════════════════════════════════════════════════════════════════════
        $s2 = LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 2],
            [
                'title'      => 'Fraccions: repartir el món',
                'subtitle'   => 'Fraccions equivalents, comparació i operacions bàsiques. Quan la meitat no és sempre la meitat.',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 2,

                'quote_text'   => 'Una fracció no és un nombre trencat. És un nombre que diu exactament quant.',
                'quote_author' => 'Dita de taller de matemàtiques',
                'quote_work'   => null,

                'intro_text' => 'Cada vegada que talles un pa, reparteixes temps o divideixes qualsevol cosa, estàs usant fraccions. Existeixen des de fa milers d\'anys perquè la vida real mai no és sencera.',

                'topic_text' => 'El numerador (dalt) diu quantes parts tenim. El denominador (baix) diu en quantes parts iguals hem dividit el tot. 3/8 = 3 parts de 8 possibles.

Dues fraccions són equivalents si representen la mateixa part del tot: ½ = 2/4 = 3/6 = 4/8. Per simplificar, dividim numerador i denominador pel màxim comú divisor.

Operacions: si el denominador és el mateix, sumem o restem els numeradors directament. Si és diferent, primer busquem el mínim comú denominador. Per multiplicar: numerador × numerador i denominador × denominador. Per dividir: multipliquem per la fracció inversa.',

                'concepts' => [
                    [
                        'icon'        => 'bulb',
                        'title'       => 'Concepte clau: fracció',
                        'description' => 'Expressió que representa una part d\'un tot. El numerador (dalt) diu quantes parts tenim. El denominador (baix) diu en quantes parts iguals hem dividit el tot. Exemple: 3/8 = tres parts de vuit.',
                    ],
                    [
                        'icon'        => 'equals',
                        'title'       => 'Fraccions equivalents',
                        'description' => 'Fraccions amb valor igual però escrites diferent. ½ = 2/4 = 3/6 = 4/8. Per simplificar, dividim numerador i denominador pel màxim comú divisor (MCD) fins que no es pugui més.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'La pizza del divendres',
                        'author'   => 'Fraccions · operacions bàsiques',
                        'year'     => null,
                        'extract'  => 'Quatre amics comparteixen una pizza de 8 porcions iguals. La Mar en menja 3, en Pau en menja 2, la Laia en menja 1 i en Jordi en menja 1. Queda alguna porció?',
                        'analysis' => "Pas 1: Escriu el que menja cadascú com a fracció. Mar: 3/8 · Pau: 2/8 · Laia: 1/8 · Jordi: 1/8\nPas 2: Suma totes (mateix denominador, suma numeradors). 3/8 + 2/8 + 1/8 + 1/8 = 7/8\nPas 3: Resta del total. 8/8 − 7/8 = 1/8 → queda 1 porció de 8",
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'El papir de Rhind · Egipte, ~1650 aC',
                        'author'   => 'El primer manual de matemàtiques conegut',
                        'year'     => null,
                        'extract'  => '3.700 anys de matemàtiques i el problema de base segueix sent el mateix: com es reparteix alguna cosa de manera exacta i justa.',
                        'analysis' => 'El papir de Rhind és un document egipci de 3.700 anys. Conté 87 problemes matemàtics, la majoria sobre fraccions. Però els egipcis tenien una limitació curiosa: només usaven fraccions unitàries (½, ⅓, ¼...). Per a ells, 3/4 havia de ser ½ + ¼. Necessitaven taules senceres per descompondre qualsevol fracció. El nostre sistema actual és molt més potent — però el problema de base era exactament el mateix: repartir de manera precisa.',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'Si talles una pizza en 8 parts i en menges 4, és el mateix que menjar-ne la meitat? Per qué?'],
                    ['question' => 'Per qué creus que els egipcis usaven només fraccions amb numerador 1? Era una limitació o una tria?'],
                    ['question' => 'Posa un exemple de la teva vida on hagis usat fraccions sense adonar-te\'n.'],
                ],

                'exercise' => [
                    'title'     => 'Fraccions en acció',
                    'duration'  => '20–25 min',
                    'statement' => 'Resol els quatre reptes sense calculadora. Escriu sempre el denominador comú clarament abans d\'operar. Comprova que la fracció final és la més simplificada possible.',
                    'examples'  => [
                        'A) Simplifica al màxim: 12/16, 9/27, 15/25. Quina fracció equivalent més senzilla dona cada una? Pista: busca el nombre més gran que divideix numerador i denominador alhora.',
                        'B) Suma: 1/4 + 2/4 + 1/4. Ara suma: 1/3 + 1/6. Quin pas extra cal fer en el segon cas? Pista: el segon necessita mínim comú denominador.',
                        'C) En Manel té 3/4 d\'un pastís. En regala 1/3 a la seva germana. Quant li queda? Pista: resta fraccions amb diferent denominador.',
                        'D) Ordena de menor a major: 3/4, 2/3, 5/8, 7/12. Justifica la resposta. Pista: redueix totes al mateix denominador per comparar-les.',
                    ],
                    'tips' => [
                        'Escriu sempre el denominador comú clarament abans d\'operar.',
                        'Un error de denominador arrossega tots els càlculs.',
                        'Comprova que la fracció final és la més simplificada possible.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],

                'questions' => [
                    [
                        'index' => 0, 'type' => 'open_text', 'block' => 'reflection',
                        'text'  => 'Si talles una pizza en 8 parts i en menges 4, és el mateix que menjar-ne la meitat? Per qué?',
                        'required' => false, 'points' => 0,
                    ],
                    [
                        'index' => 1, 'type' => 'choice_one', 'block' => 'quiz',
                        'text'  => 'Quina és la fracció simplificada de 12/16?',
                        'options'        => ['1/2', '3/4', '2/3', '4/5'],
                        'correct_answer' => '3/4',
                        'required' => true, 'points' => 2,
                    ],
                    [
                        'index' => 2, 'type' => 'yes_no', 'block' => 'quiz',
                        'text'  => '½ i 2/4 representen la mateixa quantitat?',
                        'correct_answer' => true, 'required' => true, 'points' => 1,
                    ],
                    [
                        'index' => 3, 'type' => 'select_from_examples', 'block' => 'exercise',
                        'text'  => 'Quin problema de l\'exercici t\'ha semblat més difícil?',
                        'options' => [
                            'A) Simplificar 12/16, 9/27, 15/25',
                            'B) Sumar fraccions amb diferent denominador',
                            'C) Restar 3/4 − 1/3',
                            'D) Ordenar 3/4, 2/3, 5/8, 7/12',
                        ],
                        'allow_custom' => true, 'required' => false, 'points' => 0,
                    ],
                ],
            ]
        );

        // ══════════════════════════════════════════════════════════════════════
        // SESSIÓ 3 — Decimals i percentatges
        // ══════════════════════════════════════════════════════════════════════
        $s3 = LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 3],
            [
                'title'      => 'Decimals i percentatges',
                'subtitle'   => 'Nombres decimals, conversió fracció–decimal i percentatges. Els nombres del supermercat.',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 3,

                'quote_text'   => 'El tant per cent és la llengua dels diners, de les notes i de les estadístiques.',
                'quote_author' => 'Dita de taller de matemàtiques',
                'quote_work'   => null,

                'intro_text' => 'Al supermercat, a la nota del trimestre, a les enquestes del diari — els percentatges hi són a tot arreu. Saber llegir-los i calcular-los és una de les matemàtiques més útils de la vida real.',

                'topic_text' => 'Un nombre decimal té una part entera i una part fraccionària separades per una coma. Cada posició a la dreta de la coma val 10 vegades menys: dècimes (0,1), centèsimes (0,01), mil·lèsimes (0,001).

Equivalències: 3/4 = 0,75 = 75%. Per passar fracció a decimal: divideix numerador entre denominador. Per passar decimal a percentatge: multiplica per 100.

"Per cent" vol dir "de cada cent". Per calcular el X% d\'una quantitat: quantitat × X ÷ 100. El 30% de 200 = 200 × 30 ÷ 100 = 60.',

                'concepts' => [
                    [
                        'icon'        => 'decimal',
                        'title'       => 'Nombres decimals',
                        'description' => 'Nombre que té una part entera i una part fraccionària separades per una coma. Cada posició a la dreta de la coma val 10 vegades menys: dècimes (0,1), centèsimes (0,01), mil·lèsimes (0,001). Exemple: 3/4 = 0,75.',
                    ],
                    [
                        'icon'        => 'percent',
                        'title'       => 'Percentatges',
                        'description' => '"Per cent" vol dir "de cada cent". El 30% d\'una quantitat és 30 de cada 100 unitats. Per calcular-lo: quantitat × percentatge ÷ 100. Per exemple, el 30% de 200 = 200 × 30 ÷ 100 = 60.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'Les rebaixes de gener',
                        'author'   => 'Percentatges · descomptes i preus finals',
                        'year'     => null,
                        'extract'  => 'Una jaqueta costa 80 €. Està rebaixada un 25%. Una sabata costa 60 € i té un descompte del 30%. Quant costa cada peça? Quin estalvi és major en euros?',
                        'analysis' => "Pas 1: Descompte de la jaqueta: 25% de 80 € = 80 × 25 ÷ 100 = 20 € → preu final: 80 − 20 = 60 €\nPas 2: Descompte de les sabates: 30% de 60 € = 60 × 30 ÷ 100 = 18 € → preu final: 60 − 18 = 42 €\nPas 3: Compara els estalvis: 20 € vs 18 € → La jaqueta estalvia més en euros, tot i que el percentatge és menor.",
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Simon Stevin · El matemàtic dels decimals',
                        'author'   => 'Flandes, 1548–1620',
                        'year'     => null,
                        'extract'  => 'Stevin va prometre que els decimals simplificarien la vida de tothom. Tenia raó — però va trigar dos segles a tenir-la.',
                        'analysis' => 'Fins al segle XVI, a Europa es treballava amb fraccions per a tot. El matemàtic flamenc Simon Stevin va publicar el 1585 un libret titulat "La Disme" on proposava usar el sistema decimal per a tots els càlculs. La seva idea era radical: eliminar les fraccions de la comptabilitat, l\'enginyeria i el comerç. Trigaria dos segles a imposar-se completament. Avui el nostre sistema monetari és decimal precisament per la seva influència. Cada cop que pagues amb cèntims, uses la invenció de Stevin.',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'Un 50% de descompte és millor que dos descomptes del 25% seguits? Proveu-ho amb 100 € i sorpreneu-vos.'],
                    ['question' => 'Quan veieu "3 per 2" en un supermercat, quin percentatge de descompte real és? Com ho calculeu?'],
                    ['question' => 'Per qué creieu que Stevin va trigar tant a convèncer tothom si la seva idea era millor?'],
                ],

                'exercise' => [
                    'title'     => 'Decimals i percentatges en acció',
                    'duration'  => '20–25 min',
                    'statement' => 'Resol els quatre reptes sense calculadora. Aplica cada descompte sobre el preu que queda, no sobre l\'original. Comprova que el resultat té sentit.',
                    'examples'  => [
                        'A) Converteix: 3/5 a decimal i a percentatge. Després: 0,45 a fracció simplificada i a percentatge. Pista: per passar fracció a decimal, divideix numerador entre denominador.',
                        'B) Una samarreta costa 24 € amb un descompte del 15%. Quant costa? I si a sobre apliquen un 10% addicional? Pista: aplica cada descompte sobre el preu que queda, no sobre l\'original.',
                        'C) En una classe de 25 alumnes, el 60% han aprovat. Quants alumnes han aprovat? Quants no? Pista: 60% de 25 = 25 × 60 ÷ 100.',
                        'D) Un producte ha pujat de 40 € a 50 €. Quin percentatge ha pujat? I si hagués baixat de 50 € a 40 €, quin percentatge hauria baixat? Són el mateix? Pista: augment ÷ preu original × 100. Atenció: no és simètric!',
                    ],
                    'tips' => [
                        'El percentatge d\'augment i de baixada sobre el mateix canvi no és igual — la base canvia.',
                        'Comprovar si el resultat té sentit és tan important com calcular-lo.',
                        'Per passar de fracció a decimal: divideix numerador entre denominador.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],

                'questions' => [
                    [
                        'index' => 0, 'type' => 'open_text', 'block' => 'reflection',
                        'text'  => 'Un 50% de descompte és millor que dos descomptes del 25% seguits? Raona la resposta provant-ho amb 100 €.',
                        'required' => false, 'points' => 0,
                    ],
                    [
                        'index' => 1, 'type' => 'choice_one', 'block' => 'quiz',
                        'text'  => 'Quin és el decimal equivalent a 3/4?',
                        'options'        => ['0,34', '0,75', '0,43', '0,50'],
                        'correct_answer' => '0,75',
                        'required' => true, 'points' => 2,
                    ],
                    [
                        'index' => 2, 'type' => 'yes_no', 'block' => 'quiz',
                        'text'  => 'El 30% de 200 és 60?',
                        'correct_answer' => true, 'required' => true, 'points' => 1,
                    ],
                    [
                        'index' => 3, 'type' => 'select_from_examples', 'block' => 'exercise',
                        'text'  => 'Quin problema de l\'exercici t\'ha semblat més útil per a la vida real?',
                        'options' => [
                            'A) Conversions fracció–decimal–percentatge',
                            'B) Descomptes encadenats en roba',
                            'C) Percentatge d\'aprovats a classe',
                            'D) Pujades i baixades de preu',
                        ],
                        'allow_custom' => true, 'required' => false, 'points' => 0,
                    ],
                ],
            ]
        );

        // ══════════════════════════════════════════════════════════════════════
        // SESSIÓ 4 — La proporcionalitat
        // ══════════════════════════════════════════════════════════════════════
        $s4 = LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 4],
            [
                'title'      => 'La proporcionalitat',
                'subtitle'   => 'Raons, proporcions directes i inverses. Escales, receptes i velocitats.',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 4,

                'quote_text'   => 'Si saps com és una part, pots saber com és el tot. Això és la proporcionalitat.',
                'quote_author' => 'Dita de taller de matemàtiques',
                'quote_work'   => null,

                'intro_text' => 'Quan doblesses els ingredients d\'una recepta, quan mires un mapa o quan calcules a quina hora arribaràs caminant — estàs usant proporcionalitat. És la matemàtica de les relacions.',

                'topic_text' => 'Una raó és la comparació entre dues quantitats (3:4). Una proporció és la igualtat entre dues raons: si 2/4 = 3/6, les quatre quantitats formen una proporció. El producte dels extrems és igual al producte dels mitjans.

Proporcionalitat directa: si A augmenta, B augmenta (més hores treballades → més sou). Proporcionalitat inversa: si A augmenta, B baixa (més operaris → menys dies per acabar).

Regla de tres: Si 3 llapis costen 1,50 €, quant costen 7 llapis? → 7 × 1,50 ÷ 3 = 3,50 €. Directa: mateixa proporció. Inversa: proporció creuada.',

                'concepts' => [
                    [
                        'icon'        => 'scale',
                        'title'       => 'Raó i proporció',
                        'description' => 'Una raó és la comparació entre dues quantitats (3:4, o 3/4). Una proporció és la igualtat entre dues raons: si 2/4 = 3/6, les quatre quantitats formen una proporció. El producte dels extrems és igual al producte dels mitjans.',
                    ],
                    [
                        'icon'        => 'arrows',
                        'title'       => 'Directa vs Inversa',
                        'description' => 'Directa: si A augmenta, B augmenta. Exemple: més hores treballades, més sou guanyat. Inversa: si A augmenta, B baixa. Exemple: més operaris, menys dies per acabar la feina.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'La recepta del pa',
                        'author'   => 'Proporcionalitat directa · regla de tres',
                        'year'     => null,
                        'extract'  => 'Una recepta de pa per a 4 persones necessita: 500 g de farina, 300 ml d\'aigua, 10 g de sal i 7 g de llevat. Com cal adaptar-la per a 10 persones?',
                        'analysis' => "Pas 1: Identifica que és proporcionalitat directa. Factor d'escala: 10 ÷ 4 = 2,5\nPas 2: Multiplica cada ingredient pel factor. Farina: 500 × 2,5 = 1.250 g · Aigua: 300 × 2,5 = 750 ml\nPas 3: Continua amb la resta. Sal: 10 × 2,5 = 25 g · Llevat: 7 × 2,5 = 17,5 g",
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Tales de Milet · L\'alçada de les piràmides',
                        'author'   => 'Grècia, ~624–546 aC',
                        'year'     => null,
                        'extract'  => 'Tales no va escalar la piràmide. Va usar les matemàtiques per no haver-ho de fer. Això és el poder de la proporcionalitat.',
                        'analysis' => 'El faraó va desafiar Tales: "Mesura l\'alçada de la gran piràmide." Escalar-la era impossible. Tales va clavar un pal al terra i va esperar el moment del dia en què la seva ombra era igual a la seva alçada. En aquell moment exacte, mesurar l\'ombra de la piràmide era el mateix que mesurar la seva alçada. Va usar la proporcionalitat sense instruments sofisticats. Avui les escales dels mapes funcionen exactament igual.',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'En un mapa, 1 cm representa 5 km. Si dues ciutats estan a 3,5 cm al mapa, quina distància real hi ha? Com ho calcules?'],
                    ['question' => '5 operaris construeixen una paret en 8 dies. Quants dies trigaran 10 operaris? És proporcionalitat directa o inversa?'],
                    ['question' => 'Pensa en una situació de la teva vida que sigui proporcionalitat inversa. Comparteix-la amb el grup.'],
                ],

                'exercise' => [
                    'title'     => 'Proporcions a la vida real',
                    'duration'  => '20–25 min',
                    'statement' => 'Resol els quatre reptes sense calculadora. Abans de calcular, identifica sempre si la relació és directa o inversa. Un error aquí ho canvia tot.',
                    'examples'  => [
                        'A) Un cotxe recorre 180 km en 2 hores. A la mateixa velocitat, quants km recorre en 5 hores? I en 45 minuts? Pista: calcula primer la velocitat per hora.',
                        'B) Un mapa té escala 1:50.000. Dues ciutats estan a 4,5 cm al mapa. Quina distància real hi ha en km? Pista: 1 cm al mapa = 50.000 cm al terreny.',
                        'C) 6 pintors pinten una casa en 10 dies. Quants dies trigarien 4 pintors? I 15 pintors? Pista: és proporcionalitat inversa. Comprova que els dos resultats tenen sentit.',
                        'D) En una recepta de galetes per a 12 unitats calen 200 g de farina i 150 g de mantega. Quants grams de cada ingredient calen per fer-ne 30? Pista: factor d\'escala = 30 ÷ 12.',
                    ],
                    'tips' => [
                        'Abans de calcular, identifica sempre si la relació és directa o inversa.',
                        'Un error aquí ho canvia tot. Llegeix l\'enunciat dues vegades.',
                        'Pregunta\'t: si A puja, B puja o baixa?',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],

                'questions' => [
                    [
                        'index' => 0, 'type' => 'open_text', 'block' => 'reflection',
                        'text'  => '5 operaris construeixen una paret en 8 dies. 10 operaris, en quants dies la construiran? És directa o inversa? Raona-ho.',
                        'required' => false, 'points' => 0,
                    ],
                    [
                        'index' => 1, 'type' => 'yes_no', 'block' => 'quiz',
                        'text'  => 'Si A augmenta i B decreix, és proporcionalitat directa?',
                        'correct_answer' => false, 'required' => true, 'points' => 1,
                    ],
                    [
                        'index' => 2, 'type' => 'choice_one', 'block' => 'quiz',
                        'text'  => 'Per adaptar una recepta de 4 a 10 persones, quin factor d\'escala cal aplicar?',
                        'options'        => ['2', '2,5', '3', '4'],
                        'correct_answer' => '2,5',
                        'required' => true, 'points' => 2,
                    ],
                    [
                        'index' => 3, 'type' => 'select_from_examples', 'block' => 'exercise',
                        'text'  => 'Quin problema de proporcionalitat t\'ha semblat més difícil?',
                        'options' => [
                            'A) Velocitat i distància (cotxe)',
                            'B) Escala del mapa',
                            'C) Pintors i dies (inversa)',
                            'D) Ingredients de galetes',
                        ],
                        'allow_custom' => true, 'required' => false, 'points' => 0,
                    ],
                ],
            ]
        );

        // ══════════════════════════════════════════════════════════════════════
        // SESSIÓ 5 — Figures planes: àrea i perímetre
        // ══════════════════════════════════════════════════════════════════════
        $s5 = LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 5],
            [
                'title'      => 'Figures planes: àrea i perímetre',
                'subtitle'   => 'Triangles, quadrilàters i cercles. Calcular l\'espai que ocupa una cosa plana.',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 5,

                'quote_text'   => 'Doneu-me un punt d\'apuntalament i mouré el món.',
                'quote_author' => 'Arquímedes',
                'quote_work'   => 'matemàtic i inventor grec (287–212 aC)',

                'intro_text' => 'Cada vegada que tiles un bany, cerques una catifa o calcules quanta pintura necessites, estàs usant àrees i perímetres. La geometria plana és la matemàtica de les superfícies.',

                'topic_text' => 'El perímetre és la distància del contorn d\'una figura: la suma de tots els costats. Es mesura en unitats lineals (cm, m, km). L\'àrea és la superfície interior: l\'espai que ocupa la figura. Es mesura en unitats quadrades (cm², m²).

Fórmules clau: Quadrat: A = l² · Rectangle: A = b × h · Triangle: A = (b × h) ÷ 2 · Cercle: A = π × r² · Perímetre del cercle: P = 2 × π × r.

El nombre π (pi): la relació entre el perímetre d\'un cercle i el seu diàmetre és sempre la mateixa (π ≈ 3,14159...), sigui quin sigui el cercle. Arquímedes va ser el primer a calcular-la amb precisió, fa 2.200 anys, usant polígons de fins a 96 costats.',

                'concepts' => [
                    [
                        'icon'        => 'border',
                        'title'       => 'Perímetre vs Àrea',
                        'description' => 'Perímetre: la distància del contorn. Suma de tots els costats. Es mesura en unitats lineals: cm, m. Àrea: l\'espai que ocupa la figura per dins. Es mesura en unitats quadrades: cm², m².',
                    ],
                    [
                        'icon'        => 'circle',
                        'title'       => 'El nombre π (pi)',
                        'description' => 'La relació entre el perímetre d\'un cercle i el seu diàmetre és sempre la mateixa, sigui quin sigui el cercle. Aquesta constant s\'anomena π ≈ 3,14159... Arquímedes va ser el primer a calcular-la amb precisió fa 2.200 anys.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'Pintar l\'habitació',
                        'author'   => 'Àrees · figures compostes',
                        'year'     => null,
                        'extract'  => 'Una habitació fa 4 m × 3 m i té alçada 2,5 m. Té una finestra de 1,2 m × 1 m i una porta de 0,9 m × 2 m. Un pot de pintura cobreix 6 m². Quants pots calen per pintar les 4 parets?',
                        'analysis' => "Pas 1: Àrea total de les 4 parets. 2×(4×2,5) + 2×(3×2,5) = 20 + 15 = 35 m²\nPas 2: Resta finestra i porta. 35 − (1,2×1) − (0,9×2) = 35 − 1,2 − 1,8 = 32 m²\nPas 3: Pots necessaris. 32 ÷ 6 = 5,33 → calen 6 pots (sempre s'arrodoneix amunt)",
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Arquímedes i el càlcul de π',
                        'author'   => 'Siracusa, 287–212 aC',
                        'year'     => null,
                        'extract'  => 'Arquímedes no tenia ordinador. Tenia paciència, un compàs i la geometria. Amb això n\'hi va haver prou.',
                        'analysis' => 'Arquímedes va voler calcular π sense instruments de mesura. La seva estratègia va ser ingeniosament senzilla: inscriure i circumscriure polígons regulars dins i fora d\'un cercle. Com més costats tenia el polígon, més s\'assemblava al cercle. Amb polígons de 96 costats va aconseguir demostrar que π estava entre 3,1408 i 3,1428. Avui, amb ordinadors, π s\'ha calculat fins a bilions de decimals. Però la idea bàsica d\'Arquímedes segueix sent vàlida.',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'Si doblen el costat d\'un quadrat, l\'àrea es dobla? Proveu-ho i expliqueu el resultat.'],
                    ['question' => 'Per qué π és sempre el mateix per a qualsevol cercle, gran o petit? Té algun sentit intuitiu?'],
                    ['question' => 'Penseu en una situació real on sigui més important el perímetre que l\'àrea, i una altra on sigui el contrari.'],
                ],

                'exercise' => [
                    'title'     => 'Àrees i perímetres en acció',
                    'duration'  => '20–25 min',
                    'statement' => 'Resol els quatre reptes sense calculadora. Dibuixa sempre un esquema de la figura amb les mides anotades. Recorda: l\'àrea sempre va en unitats quadrades (m², cm²).',
                    'examples'  => [
                        'A) Calcula l\'àrea i el perímetre d\'un triangle de base 6 cm, alçada 4 cm i costats de 5, 5 i 6 cm. Pista: àrea = base × alçada ÷ 2. Perímetre = suma dels tres costats.',
                        'B) Una piscina circular té radi 3,5 m. Quant val el seu perímetre i la seva àrea? (usa π ≈ 3,14). Pista: P = 2πr · A = πr².',
                        'C) Un jardí rectangular fa 12 m × 8 m. Dins hi ha un camí de 1 m d\'amplada al voltant de tot el perímetre interior. Quina és l\'àrea verda que queda? Pista: calcula l\'àrea del rectangle gran menys el petit interior.',
                        'D) Vols enrajolar un terra de 5 m × 3,6 m amb rajoles quadrades de 30 cm de costat. Quantes rajoles calen? I si un 10% es trenquen, quantes has de comprar? Pista: converteix tot a la mateixa unitat primer.',
                    ],
                    'tips' => [
                        'Dibuixa sempre un esquema de la figura amb les mides anotades.',
                        'Un dibuix evita errors de confusió entre dimensions.',
                        'L\'àrea sempre va en unitats quadrades (m², cm²). El perímetre en lineals (m, cm).',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],

                'questions' => [
                    [
                        'index' => 0, 'type' => 'open_text', 'block' => 'reflection',
                        'text'  => 'Si doblen el costat d\'un quadrat, l\'àrea es dobla? Raona-ho i comprova-ho amb un exemple numèric.',
                        'required' => false, 'points' => 0,
                    ],
                    [
                        'index' => 1, 'type' => 'choice_one', 'block' => 'quiz',
                        'text'  => 'Quina és l\'àrea d\'un cercle de radi 5 cm? (π ≈ 3,14)',
                        'options'        => ['31,4 cm²', '78,5 cm²', '15,7 cm²', '157 cm²'],
                        'correct_answer' => '78,5 cm²',
                        'required' => true, 'points' => 2,
                    ],
                    [
                        'index' => 2, 'type' => 'yes_no', 'block' => 'quiz',
                        'text'  => 'El perímetre d\'una figura es mesura en unitats quadrades (cm², m²)?',
                        'correct_answer' => false, 'required' => true, 'points' => 1,
                    ],
                    [
                        'index' => 3, 'type' => 'select_from_examples', 'block' => 'exercise',
                        'text'  => 'Quin problema d\'àrees t\'ha costat més de resoldre?',
                        'options' => [
                            'A) Triangle: àrea i perímetre',
                            'B) Piscina circular amb π',
                            'C) Jardí amb camí interior',
                            'D) Enrajolar i calcular trencades',
                        ],
                        'allow_custom' => true, 'required' => false, 'points' => 0,
                    ],
                ],
            ]
        );

        // ══════════════════════════════════════════════════════════════════════
        // SESSIÓ 6 — Cossos geomètrics: el volum
        // ══════════════════════════════════════════════════════════════════════
        $s6 = LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 6],
            [
                'title'      => 'Cossos geomètrics: el volum',
                'subtitle'   => 'Cubs, prismes i cilindres. Quan necessitem saber quant cap dins d\'una cosa.',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 6,

                'quote_text'   => 'Eureka! — Ho he trobat!',
                'quote_author' => 'Arquímedes',
                'quote_work'   => 'en descobrir com calcular el volum per desplaçament (287–212 aC)',

                'intro_text' => 'Quan poses glaçons en un got i el líquid puja, estàs veient el principi d\'Arquímedes en acció. El volum és la quantitat d\'espai que ocupa un objecte — i calcular-lo va canviar la historia.',

                'topic_text' => 'Un cos geomètric és una figura tridimensional que ocupa un espai. Té cares (superfícies planes o corbes), arestes (línies on es troben dues cares) i vèrtexs (punts on es troben arestes). A diferència de les figures planes, existeix en les tres dimensions: llarg, ample i alt.

Fórmules de volum: Cub: V = a³ · Prisma rectangular: V = l × a × h · Cilindre: V = π × r² × h · Piràmide: V = (B × h) ÷ 3.

Volum i capacitat: El volum es mesura en unitats cúbiques (cm³, m³). La capacitat en litres. La relació: 1 litre = 1 dm³ = 1.000 cm³. Una ampolla d\'1,5 litres conté 1.500 cm³.',

                'concepts' => [
                    [
                        'icon'        => 'cube',
                        'title'       => 'Cossos geomètrics',
                        'description' => 'Figures tridimensionals amb llarg, ample i alt. Tenen cares (superfícies), arestes (línies on es troben dues cares) i vèrtexs (punts on es troben arestes). Exemples: cub, prisma, cilindre, piràmide.',
                    ],
                    [
                        'icon'        => 'water',
                        'title'       => 'Volum i capacitat',
                        'description' => 'El volum mesura l\'espai en unitats cúbiques (cm³, m³). La capacitat en litres. Relació: 1 litre = 1 dm³ = 1.000 cm³. Una ampolla d\'1,5 litres conté 1.500 cm³ de líquid.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'La piscina del barri',
                        'author'   => 'Volum · conversió d\'unitats',
                        'year'     => null,
                        'extract'  => 'La piscina municipal fa 25 m de llarg, 12,5 m d\'ample i 1,8 m de profunditat. Quants litres d\'aigua conté quan és plena? Si cada hora entra 5.000 litres, quantes hores triga a omplir-se?',
                        'analysis' => "Pas 1: Volum en metres cúbics. V = 25 × 12,5 × 1,8 = 562,5 m³\nPas 2: Converteix a litres (1 m³ = 1.000 litres). 562,5 × 1.000 = 562.500 litres\nPas 3: Temps per omplir-la. 562.500 ÷ 5.000 = 112,5 hores ≈ 4 dies i 16,5 hores",
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Arquímedes i la corona del rei Hieró',
                        'author'   => 'Siracusa, ~250 aC',
                        'year'     => null,
                        'extract'  => 'Arquímedes va resoldre un problema de frau amb una banyera i un descobriment. La física i la matemàtica al servei de la justícia.',
                        'analysis' => 'El rei Hieró II encomanà una corona d\'or pur. Quan la rebé, sospità que l\'orfebre havia barrejat plata amb l\'or. Encarregà a Arquímedes que ho comprovés sense fondre la corona. Arquímedes estava en un bany quan va veure que el seu cos feia pujar l\'aigua. Va córrer al carrer cridant "Eureka!": havia entès que un objecte submergit desplaça un volum d\'aigua igual al seu propi volum. Posant la corona i un lingot d\'or pur del mateix pes en aigua, va mesurar quin desplaçava més líquid. La corona desplaçava més — era menys densa que l\'or pur. L\'orfebre havia estat deshonest.',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'Si doblen totes les dimensions d\'un cub, el volum es dobla? Comproveu-ho amb nombres i expliqueu el resultat.'],
                    ['question' => 'Com podríeu mesurar el volum d\'una pedra irregular sense cap fórmula? Quins materials necessitaríeu?'],
                    ['question' => 'Un got cilíndric i un got prismàtic semblen tenir la mateixa mida. Com comprovaríeu quin en té més capacitat?'],
                ],

                'exercise' => [
                    'title'     => 'Volums en acció',
                    'duration'  => '20–25 min',
                    'statement' => 'Resol els quatre reptes sense calculadora. Calcula sempre el volum primer i converteix les unitats al final, no al mig del càlcul. Recorda: 1 dm³ = 1 litre = 1.000 cm³.',
                    'examples'  => [
                        'A) Una caixa de cartró fa 40 cm × 30 cm × 20 cm. Quants litres cap a dins? I quantes ampolles de 0,5 litres hi caben? Pista: V en cm³ → converteix a litres dividint per 1.000.',
                        'B) Un pot de iogurt és un cilindre de radi 4 cm i 10 cm d\'alçada. Quina és la seva capacitat en ml? (π ≈ 3,14). Pista: 1 cm³ = 1 ml.',
                        'C) Una piscina infantil rectangular (2 m × 1,5 m × 0,4 m) es buida a raó de 30 litres per minut. Quant triga a quedar buida? Pista: calcula primer el volum total en litres.',
                        'D) Dues caixes: una de 10 × 10 × 10 cm i una altra de 20 × 5 × 10 cm. Quina té més volum? Quina té més superfície exterior total? Pista: volum i superfície no sempre van de la mà.',
                    ],
                    'tips' => [
                        'El volum sempre en unitats cúbiques, la capacitat en litres.',
                        'Fes la conversió al final, no al mig del càlcul, per no confondre\'n les unitats.',
                        'Recorda: 1 dm³ = 1 litre = 1.000 cm³.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],

                'questions' => [
                    [
                        'index' => 0, 'type' => 'open_text', 'block' => 'reflection',
                        'text'  => 'Si doblen totes les dimensions d\'un cub, el volum es dobla? Comprova-ho amb nombres concrets i explica el resultat.',
                        'required' => false, 'points' => 0,
                    ],
                    [
                        'index' => 1, 'type' => 'yes_no', 'block' => 'quiz',
                        'text'  => '1 litre és igual a 1.000 cm³?',
                        'correct_answer' => true, 'required' => true, 'points' => 1,
                    ],
                    [
                        'index' => 2, 'type' => 'choice_one', 'block' => 'quiz',
                        'text'  => 'Quina és la fórmula del volum d\'un cilindre?',
                        'options'        => ['π × r × h', 'π × r² × h', 'l × a × h', 'a³'],
                        'correct_answer' => 'π × r² × h',
                        'required' => true, 'points' => 2,
                    ],
                    [
                        'index' => 3, 'type' => 'select_from_examples', 'block' => 'exercise',
                        'text'  => 'Quin exercici de volum t\'ha semblat més complex?',
                        'options' => [
                            'A) Caixa de cartró en litres',
                            'B) Cilindre del pot de iogurt',
                            'C) Piscina infantil buidant-se',
                            'D) Comparar dues caixes (volum vs superfície)',
                        ],
                        'allow_custom' => true, 'required' => false, 'points' => 0,
                    ],
                ],
            ]
        );

        // ══════════════════════════════════════════════════════════════════════
        // SESSIÓ 7 — Estadística: llegir els nombres del món
        // ══════════════════════════════════════════════════════════════════════
        $s7 = LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 7],
            [
                'title'      => 'Estadística: llegir els nombres del món',
                'subtitle'   => 'Recollir dades, fer gràfics i calcular la mitjana. Quan els nombres expliquen una historia.',
                'duration'   => '45–60 min',
                'status'     => 'published',
                'sort_order' => 7,

                'quote_text'   => 'Les xifres no menteixen. Però les xifres mal llegides poden enganyar tothom.',
                'quote_author' => 'Dita popular adaptada',
                'quote_work'   => null,

                'intro_text' => 'Cada vegada que veus un gràfic al diari, una enquesta a la televisió o les teves notes al butlletí, estàs veient estadística. Saber llegir-la és tan important com saber calcular-la.',

                'topic_text' => 'Tres maneres de resumir un conjunt de dades en un sol nombre. Cadascuna diu una cosa diferent:

Mitjana (x̄): suma de tots els valors dividida entre quants n\'hi ha. Sensible als valors extrems. Mediana (Me): el valor del mig quan les dades estan ordenades. Robusta davant valors extrems. Moda (Mo): el valor que apareix més vegades. Útil per a dades repetides o categories.

Gràfics: Un gràfic de barres compara categories. Un gràfic de línies mostra evolució en el temps. Un gràfic circular mostra proporcions. Triar el gràfic adequat és tan important com construir-lo bé.',

                'concepts' => [
                    [
                        'icon'        => 'chart',
                        'title'       => 'Mesures de tendència central',
                        'description' => 'Mitjana: suma ÷ nombre de valors. Sensible a extrems. Mediana: valor central quan s\'ordena. Robusta. Moda: valor que apareix més. Cap és "la millor" en tots els casos — depèn de les dades.',
                    ],
                    [
                        'icon'        => 'graph',
                        'title'       => 'Gràfics estadístics',
                        'description' => 'Gràfic de barres: compara categories. Gràfic de línies: mostra evolució temporal. Gràfic circular: mostra proporcions. Triar el gràfic adequat és tan important com construir-lo correctament.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'project',
                        'title'    => 'Les notes de la classe',
                        'author'   => 'Estadística descriptiva · mesures centrals',
                        'year'     => null,
                        'extract'  => 'Les notes de matemàtiques de 10 alumnes són: 6, 8, 5, 9, 7, 6, 10, 4, 6, 7. Calcula la mitjana, la mediana i la moda. Quin valor representa millor el grup?',
                        'analysis' => "Pas 1: Mitjana. 6+8+5+9+7+6+10+4+6+7 = 68 → 68÷10 = 6,8\nPas 2: Mediana. Ordena: 4, 5, 6, 6, 6, 7, 7, 8, 9, 10 → mig entre 6 i 7 → mediana = 6,5\nPas 3: Moda. El 6 apareix 3 vegades → moda = 6. Quin representa millor el grup? Depèn!",
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'Florence Nightingale · La matemàtica que va salvar vides',
                        'author'   => 'Anglaterra, 1820–1910',
                        'year'     => null,
                        'extract'  => 'Nightingale va entendre que les dades correctes, mal presentades, no serveixen. La forma en què expliques els nombres és tan important com els nombres mateixos.',
                        'analysis' => 'Florence Nightingale era infermera durant la Guerra de Crimea (1853–1856). Va observar que els soldats morien més per malalties i infeccions que per les ferides de batalla — però ningú ho veia perquè les dades estaven mal presentades. Va crear uns gràfics circulars innovadors on cada secció representava les causes de mort al llarg dels mesos. Quan els metges i polítics van veure els gràfics, el missatge va ser incontestable: calia millorar les condicions sanitàries. Les seves reformes van reduir la mortalitat de més del 40% a menys del 2%. Va salvar milers de vides amb matemàtiques i bons gràfics.',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'Si un equip de futbol té 10 jugadors que guanyen 1.000 € al mes i un que en guanya 100.000 €, quina és la mitjana salarial? Representa bé la realitat del grup?'],
                    ['question' => 'Quan és millor usar la mediana que la mitjana? Penseu en exemples de la vida real.'],
                    ['question' => 'Nightingale va canviar polítiques sanitàries amb gràfics. Creieu que avui els gràfics segueixen tenint aquest poder? Per qué?'],
                ],

                'exercise' => [
                    'title'     => 'Llegir i crear estadística',
                    'duration'  => '20–25 min',
                    'statement' => 'Resol els quatre reptes sense calculadora. Organitza les dades en una taula abans de calcular. Un error en la recollida de dades fa que tots els càlculs posteriors siguin incorrectes.',
                    'examples'  => [
                        'A) Les temperatures de 7 dies a Barcelona: 18, 22, 19, 25, 23, 21, 20 °C. Calcula la mitjana, la mediana i la moda. Quin valor representa millor la setmana? Pista: ordena les dades primer per trobar la mediana.',
                        'B) Les edats dels 8 membres d\'una família: 5, 8, 12, 38, 40, 65, 68, 70. Quina mesura central és la més representativa? Per qué? Pista: pensa en l\'efecte dels valors extrems sobre la mitjana.',
                        'C) Un gràfic de barres mostra: 2023 → 120 alumnes, 2024 → 105 alumnes, 2025 → 130 alumnes. Quin percentatge ha canviat entre 2023 i 2025? Pista: (130−120) ÷ 120 × 100.',
                        'D) Pregunta 10 persones del teu entorn quants germans tenen. Recull les dades, calcula la mitjana i la moda i dibuixa un gràfic de barres senzill. Pista: aquest exercici el pots fer a casa i portar-lo a la propera sessió.',
                    ],
                    'tips' => [
                        'Organitza les dades en una taula abans de calcular.',
                        'Un error en la recollida de dades fa que tots els càlculs posteriors siguin incorrectes.',
                        'Ordena sempre les dades primer per calcular la mediana correctament.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],

                'questions' => [
                    [
                        'index' => 0, 'type' => 'open_text', 'block' => 'reflection',
                        'text'  => 'Quan és millor usar la mediana que la mitjana? Posa un exemple concret de la vida real.',
                        'required' => false, 'points' => 0,
                    ],
                    [
                        'index' => 1, 'type' => 'choice_one', 'block' => 'quiz',
                        'text'  => 'Les notes d\'una classe: 4, 5, 6, 6, 6, 7, 7, 8, 9, 10. Quina és la moda?',
                        'options'        => ['4', '5', '6', '7'],
                        'correct_answer' => '6',
                        'required' => true, 'points' => 1,
                    ],
                    [
                        'index' => 2, 'type' => 'yes_no', 'block' => 'quiz',
                        'text'  => 'La mediana és la suma de tots els valors dividida entre quants n\'hi ha.',
                        'correct_answer' => false, 'required' => true, 'points' => 1,
                    ],
                    [
                        'index' => 3, 'type' => 'select_from_examples', 'block' => 'exercise',
                        'text'  => 'Quin exercici d\'estadística t\'ha semblat més interessant?',
                        'options' => [
                            'A) Temperatures setmanals a Barcelona',
                            'B) Edats familiars i valors extrems',
                            'C) Percentatge de canvi d\'alumnes',
                            'D) Enquesta sobre germans (exercici de camp)',
                        ],
                        'allow_custom' => true, 'required' => false, 'points' => 0,
                    ],
                ],
            ]
        );

        // ══════════════════════════════════════════════════════════════════════
        // SESSIÓ 8 — Taller de problemes: tot connectat
        // ══════════════════════════════════════════════════════════════════════
        $s8 = LmsLesson::updateOrCreate(
            ['course_id' => $course->id, 'session_number' => 8],
            [
                'title'      => 'Taller de problemes: tot connectat',
                'subtitle'   => 'Repàs actiu a través de problemes reals que combinen els conceptes del curs. Treballem en equip.',
                'duration'   => '55–60 min',
                'status'     => 'published',
                'sort_order' => 8,

                'quote_text'   => 'Un problema ben plantejat és un problema mig resolt.',
                'quote_author' => 'George Pólya',
                'quote_work'   => 'matemàtic i pedagog (1887–1985)',

                'intro_text' => 'Avui no hi ha teoria nova. Hi ha problemes reals que connecten tot el que hem après i hi ha un grup disposat a resoldre\'ls junts. Així és com funciona la matemàtica de debò: no per temes separats, sinó tots alhora.',

                'topic_text' => 'El mètode Pólya — 4 passos per a qualsevol problema:

Pas 1 · Entendre: Llegeix el problema dues vegades. Identifica el que saps i el que et demanen. "Qué em donen? Qué he de trobar?"

Pas 2 · Planificar: Decideix quina estratègia usar: fórmula, dibuix, taula, regla de tres... "Com puc atacar aquest problema?"

Pas 3 · Executar: Aplica l\'estratègia pas a pas. Anota cada operació clarament. "Estic seguint el pla? Els càlculs quadren?"

Pas 4 · Revisar: Comprova que la resposta té sentit. Podria haver-hi una altra manera de resoldre\'l? "El resultat és raonable? Com ho verifico?"',

                'concepts' => [
                    [
                        'icon'        => 'method',
                        'title'       => 'El mètode Pólya',
                        'description' => '4 passos: 1) Entendre el problema (qué et demana, quines dades tens). 2) Planificar (quina estratègia: fórmula, taula, dibuix...). 3) Executar (càlculs, passos clars). 4) Revisar (el resultat és raonable?). Aplicable a qualsevol problema, matemàtic o no.',
                    ],
                    [
                        'icon'        => 'recap',
                        'title'       => 'Tot el que hem après',
                        'description' => '01 Nombres: valor posicional, enters, sistema decimal · 02 Fraccions: equivalents, simplificació, operacions · 03 Decimals i %: conversions, càlcul · 04 Proporcionalitat: raó, regla de tres · 05 Figures planes: perímetre, àrea, π · 06 Cossos geomètrics: volum, capacitat · 07 Estadística: mitjana, mediana, moda, gràfics.',
                    ],
                ],

                'text_cards' => [
                    [
                        'type'     => 'reference',
                        'title'    => 'George Pólya · L\'art de resoldre problemes',
                        'author'   => 'Hongria–EUA, 1887–1985',
                        'year'     => null,
                        'extract'  => 'Pólya deia que un professor que et dóna la resposta t\'ha robat l\'oportunitat d\'aprendre. El camí és la lliçó.',
                        'analysis' => 'George Pólya va néixer a Budapest i va ser un dels matemàtics més influents del segle XX. Però el seu llegat més gran no és cap teorema, sinó un llibre: "How to Solve It" (1945). En ell proposava que la matemàtica no s\'aprèn memoritzant, sinó resolent problemes — i que hi ha una estratègia general que funciona per a qualsevol problema, matemàtic o no. El seu mètode de 4 passos s\'ensenya avui a tot el món. Va demostrar que el procés importa tant com el resultat, i que equivocar-se i tornar-ho a intentar és part essencial de l\'aprenentatge.',
                    ],
                ],

                'reflection_questions' => [
                    ['question' => 'Quin concepte del curs et resultava més difícil al principi i ara entens millor? Qué ha canviat?'],
                    ['question' => 'Pensa en un problema real de la teva vida que podries resoldre ara amb el que has après. Quin seria?'],
                    ['question' => 'De tots els matemàtics que hem vist — Arquímedes, Tales, Stevin, Nightingale, Pólya — quin et sembla més fascinant i per qué?'],
                ],

                'exercise' => [
                    'title'     => 'Els tres reptes del taller · En equip',
                    'duration'  => '35–40 min',
                    'statement' => 'Tres reptes progressius que combinen tots els blocs del curs. Aplica el mètode Pólya a cada repte: entendre → planificar → executar → revisar.',
                    'examples'  => [
                        'Repte 1 — La festa de fi de curs [Nombres · % · Proporcions]: Una classe de 24 alumnes organitza una festa. Pressupost: 120 €. Gasten el 40% en menjar, el 25% en begudes i la resta en decoració. El menjar s\'ha de repartir per a 30 persones. Si una recepta de sandvitxos per a 8 persones necessita 400 g de pa, 250 g de pernil i 200 g de formatge, quantes quantitats calen per a 30 persones? I quin és el cost de cada apartat del pressupost?',
                        'Repte 2 — El pati de l\'escola [Geometria · Àrees · Volum]: El pati és rectangular: 30 m × 20 m. Es vol pintar un cercle al mig de 4 m de radi. A un cantó s\'instal·la un contenidor cúbic d\'1,2 m de costat. Calcula: l\'àrea del pati sense el cercle, el perímetre del cercle pintat, el volum del contenidor en litres, i quin percentatge del pati ocupa el cercle. (π ≈ 3,14)',
                        'Repte 3 — El campionat de matemàtiques [Estadística · Tot connectat]: 5 equips amb puntuacions: A: 87, B: 73, C: 91, D: 68, E: 91. Calcula la mitjana, la mediana i la moda. L\'equip A rep un 15% de bonificació per fair play. Quina és la seva nova puntuació? Ordena els 5 equips de major a menor puntuació final i indica quin percentatge de la puntuació total representa l\'equip guanyador.',
                    ],
                    'tips' => [
                        'Aplica el mètode Pólya a cada repte: entendre → planificar → executar → revisar.',
                        'Llegeix cada enunciat dues vegades i identifica qué et demana exactament.',
                        'Els reptes combinen diversos conceptes del curs — identifica quin cal usar a cada pas.',
                    ],
                    'demo_first_person' => null,
                    'demo_third_person' => null,
                ],

                'questions' => [
                    [
                        'index' => 0, 'type' => 'open_text', 'block' => 'reflection',
                        'text'  => 'Quin concepte del curs t\'ha resultat més difícil al principi? Explica qué ha canviat en la teva comprensió.',
                        'required' => false, 'points' => 0,
                    ],
                    [
                        'index' => 1, 'type' => 'choice_one', 'block' => 'quiz',
                        'text'  => 'Quin és el primer pas del mètode Pólya?',
                        'options'        => ['Executar el pla', 'Planificar l\'estratègia', 'Entendre el problema', 'Revisar la resposta'],
                        'correct_answer' => 'Entendre el problema',
                        'required' => true, 'points' => 1,
                    ],
                    [
                        'index' => 2, 'type' => 'select_from_examples', 'block' => 'exercise',
                        'text'  => 'Quin repte del taller has triat per resoldre?',
                        'options' => [
                            'Repte 1: La festa de fi de curs (% i proporcions)',
                            'Repte 2: El pati de l\'escola (geometria i volum)',
                            'Repte 3: El campionat de matemàtiques (estadística)',
                        ],
                        'allow_custom' => false, 'required' => false, 'points' => 0,
                    ],
                ],
            ]
        );

        $this->command->info('✅ LmsMathSeeder: curs «' . $course->title . '» amb 8 sessions creat/verificat.');
    }
}
