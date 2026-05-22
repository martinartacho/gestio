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
        $seederMail  = env('SEEDER_MAIL', 'gestio.test');
        $teacherPass = env('SEEDER_TEACHER_PASSWORD')
            ?? throw new \RuntimeException('SEEDER_TEACHER_PASSWORD no està definit al .env');
        $studentPass = env('SEEDER_STUDENT_PASSWORD')
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

        // ── Professor (mateix que LMS-01 o nou) ────────────────────────────────
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

        // ── Alumne de prova (mateix) ───────────────────────────────────────────
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
                        'index'    => 0,
                        'type'     => 'open_text',
                        'block'    => 'reflection',
                        'text'     => 'Per qué creus que els humans antics van sentir la necessitat de comptar? Qué necessitaven saber exactament?',
                        'required' => false,
                        'points'   => 0,
                    ],
                    [
                        'index'          => 1,
                        'type'           => 'yes_no',
                        'block'          => 'quiz',
                        'text'           => 'El zero és un nombre natural?',
                        'correct_answer' => true,
                        'required'       => true,
                        'points'         => 1,
                    ],
                    [
                        'index'          => 2,
                        'type'           => 'choice_one',
                        'block'          => 'quiz',
                        'text'           => 'En el nombre 3.705, el dígit 7 representa...',
                        'options'        => ['7 unitats', '70 desenes', '700 (set-centes)', '7.000 milers'],
                        'correct_answer' => '700 (set-centes)',
                        'required'       => true,
                        'points'         => 2,
                    ],
                    [
                        'index'           => 3,
                        'type'            => 'choice_many',
                        'block'           => 'quiz',
                        'text'            => 'Quins d\'aquests nombres SÓN enters però NO naturals?',
                        'options'         => ['−3', '0', '5', '−1', '100'],
                        'correct_answers' => ['−3', '−1'],
                        'required'        => false,
                        'points'          => 2,
                    ],
                    [
                        'index'       => 4,
                        'type'        => 'select_from_examples',
                        'block'       => 'exercise',
                        'text'        => 'Quin problema de l\'exercici t\'ha semblat més difícil?',
                        'options'     => [
                            'A) Escriure i descompondre 8.237',
                            'B) Ordenar nombres enters a la recta',
                            'C) Sumes amb temperatures negatives',
                            'D) El nombre més gran i més petit amb 5 dígits',
                        ],
                        'allow_custom' => true,
                        'required'    => false,
                        'points'      => 0,
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
        // SESSIONS 2–8 — Contingut bàsic des de l'índex
        // ══════════════════════════════════════════════════════════════════════

        $sessions = [
            2 => [
                'title'    => 'Fraccions: repartir el món',
                'subtitle' => 'Fraccions equivalents, comparació i operacions bàsiques. Quan la meitat no és sempre la meitat.',
                'quote_text'   => 'Les matemàtiques són la música de la raó.',
                'quote_author' => 'James Joseph Sylvester',
                'quote_work'   => 'matemàtic anglès (1814–1897)',
                'intro_text'   => 'Imagina que has de repartir una pizza entre tres amics de manera justa. O que has de calcular quant d\'un pastís li correspon a cadascú. Les fraccions neixen exactament aquí: de la necessitat de partir allò que no es pot comptar sencer.',
                'topic_text'   => 'Una fracció expressa una part d\'un tot. El numerador diu quantes parts tenim i el denominador diu en quantes parts iguals hem dividit el tot. 3/4 vol dir: hem dividit en 4 parts, i en tenim 3. Dues fraccions són equivalents si representen la mateixa part del tot: 1/2 = 2/4 = 3/6.',
                'problem'      => 'Repartir una pizza entre amics de manera justa',
                'reference'    => 'El papir de Rhind: fraccions a l\'Egipte antic',
                'ref_analysis' => 'El papir de Rhind (~1650 aC) conté 87 problemes matemàtics. La majoria usen fraccions unitàries (1/2, 1/3, 1/4...). Els egipcis no escrivien 2/5 — descomponien qualsevol fracció en suma de fraccions unitàries. Una restricció curiosa que els va portar a descobrir propietats fascinants de les fraccions.',
            ],
            3 => [
                'title'    => 'Decimals i percentatges',
                'subtitle' => 'Nombres decimals, conversió fracció–decimal i percentatges. Els nombres del supermercat.',
                'quote_text'   => 'En matemàtiques no hi ha símbols per a conceptes confusos.',
                'quote_author' => 'Henri Poincaré',
                'quote_work'   => 'matemàtic i físic francès (1854–1912)',
                'intro_text'   => 'Entres a una botiga i veus: "20% de descompte". Al rebut llegeixes: "preu final: 15,99 €". Al mòbil: "bateria al 37%". Els decimals i percentatges estan a tot arreu — i entendre\'ls t\'estalvia diners i malentesos.',
                'topic_text'   => 'Un nombre decimal representa una fracció el denominador de la qual és una potència de 10. Cada posició a la dreta de la coma val deu vegades menys: dècimes, centèsimes, mil·lèsimes... Un percentatge és una fracció amb denominador 100: 25% = 25/100 = 0,25.',
                'problem'      => 'Calcular descomptes i preus finals en rebaixes',
                'reference'    => 'Simon Stevin i la invenció dels decimals (s. XVI)',
                'ref_analysis' => 'Simon Stevin (1548–1620) va publicar el 1585 "La Disme", el primer tractat sistemàtic sobre nombres decimals. Volia simplificar els càlculs dels comerciants i enginyers. Abans de Stevin, tothom treballava amb fraccions, que eren molt més lentes de calcular. La seva proposta va trigar generacions a imposar-se, però avui és la base de tot el sistema monetari i científic modern.',
            ],
            4 => [
                'title'    => 'La proporcionalitat',
                'subtitle' => 'Raons, proporcions directes i inverses. Escales, receptes i velocitats.',
                'quote_text'   => 'La natura parla el llenguatge de les matemàtiques.',
                'quote_author' => 'Galileo Galilei',
                'quote_work'   => 'físic i astrònom italià (1564–1642)',
                'intro_text'   => 'Si una recepta per a 4 persones necessita 200 g de farina, quanta en necessitem per a 10? Si un cotxe tarda 2 hores a fer 120 km, quant tarda a fer 300 km a la mateixa velocitat? La proporcionalitat és l\'eina matemàtica per respondre aquestes preguntes.',
                'topic_text'   => 'Dues magnituds són directament proporcionals si quan una es multiplica per un factor, l\'altra es multiplica pel mateix factor. Són inversament proporcionals si quan una es multiplica, l\'altra es divideix. La regla de tres és el procediment per calcular un valor desconegut en una proporció.',
                'problem'      => 'Adaptar una recepta per a més persones',
                'reference'    => 'Tales de Milet i l\'alçada de les piràmides',
                'ref_analysis' => 'Tales de Milet (~624–546 aC) va mesurar l\'alçada de la piràmide de Gizeh sense escalar-la. Va esperar el moment del dia en que la seva ombra tenia la mateixa longitud que la seva alçada. En aquell moment, l\'ombra de la piràmide igualava la seva alçada. Proporcionalitat pura, aplicada a un problema que semblava impossible. Era el 600 aC i les piràmides tenien ja dos mil anys.',
            ],
            5 => [
                'title'    => 'Figures planes: àrea i perímetre',
                'subtitle' => 'Triangles, quadrilàters i cercles. Calcular l\'espai que ocupa una cosa plana.',
                'quote_text'   => 'Eureka!',
                'quote_author' => 'Arquímedes de Siracusa',
                'quote_work'   => 'matemàtic grec (287–212 aC)',
                'intro_text'   => 'Has d\'enrajolar una cuina. O posar gespa a un jardí triangular. O calcular quanta pintura necessites per pintar una paret circular. Totes aquestes situacions requereixen saber calcular àrea i perímetre. No per memoritzar fórmules — per entendre qué mesuren.',
                'topic_text'   => 'El perímetre és la longitud del contorn d\'una figura (la suma de tots els seus costats). L\'àrea és la superfície que ocupa la figura — quantes unitats quadrades hi caben dins. Les fórmules principals: rectangle (b × h), triangle (b × h / 2), cercle (π × r²). El nombre π ≈ 3,14159... és la relació entre la circumferència i el diàmetre de qualsevol cercle.',
                'problem'      => 'Pintar la paret d\'una habitació sense malgastar pintura',
                'reference'    => 'Arquímedes i el càlcul del nombre π',
                'ref_analysis' => 'Arquímedes (287–212 aC) va aproximar π inscrivint i circumscrivint polígons de fins a 96 costats dins i fora d\'un cercle. Va demostrar que 3 + 10/71 < π < 3 + 1/7. Sense ordinadors, sense calculadora — només geometria i raonament. La seva aproximació va ser suficientment precisa per a qualsevol càlcul pràctic durant segles.',
            ],
            6 => [
                'title'    => 'Cossos geomètrics: el volum',
                'subtitle' => 'Cubs, prismes i cilindres. Quan necessitem saber quant cap dins d\'una cosa.',
                'quote_text'   => 'Dona\'m un punt d\'on recolzar-me i mouré la Terra.',
                'quote_author' => 'Arquímedes de Siracusa',
                'quote_work'   => 'matemàtic grec (287–212 aC)',
                'intro_text'   => 'Una piscina, una caixa de cartró, una llauna de refresc. Per a omplir-los, enviar-los o fabricar-los necessitem saber quant d\'espai ocupen per dins. Aquí és on entra el volum.',
                'topic_text'   => 'El volum mesura l\'espai tridimensional que ocupa un cos. S\'expressa en unitats cúbiques (cm³, m³, litres). Les fórmules principals: cub (a³), prisma rectangular (l × a × h), cilindre (π × r² × h). 1 litre = 1 dm³ = 1.000 cm³.',
                'problem'      => 'Quanta aigua cap en una piscina del barri?',
                'reference'    => 'Arquímedes i la corona del rei Hieró',
                'ref_analysis' => 'El rei Hieró II de Siracusa sospitava que el seu orfebre havia mesclat plata amb l\'or de la seva corona. Va encarregar a Arquímedes que ho descobrís sense fondre-la. Arquímedes, entrant a la banyera, va observar que el nivell de l\'aigua pujava. Va entendre que qualsevol cos submergit desplaça un volum d\'aigua igual al seu propi volum. Comparant el desplaçament de la corona amb el d\'una peça d\'or pur del mateix pes, va demostrar el frau.',
            ],
            7 => [
                'title'    => 'Estadística: llegir els nombres del món',
                'subtitle' => 'Recollir dades, fer gràfics i calcular la mitjana. Quan els nombres expliquen una historia.',
                'quote_text'   => 'Les estadístiques són com un biquini: el que mostren és suggerent, però el que amaguen és essencial.',
                'quote_author' => 'Aaron Levenstein',
                'quote_work'   => 'professor de negocis, EUA (1911–1986)',
                'intro_text'   => 'Les notes de la classe, els vots d\'una elecció, la temperatura de cada dia del mes. Tenim dades a tot arreu. Però sense eines per analitzar-les, les dades són soroll. L\'estadística és el conjunt d\'eines que ens permet veure el que hi ha darrere dels nombres.',
                'topic_text'   => 'L\'estadística descriptiva organitza i resumeix dades. Els conceptes clau: la mitjana aritmètica (suma de tots els valors dividida entre el nombre de valors), la mediana (el valor central quan s\'ordenen els dades) i la moda (el valor que apareix més vegades). Els gràfics de barres, de línies i circulars ajuden a visualitzar les dades de manera que el cervell les entengui d\'un sol cop.',
                'problem'      => 'Analitzar les notes de classe per millorar-les',
                'reference'    => 'Florence Nightingale i el poder dels gràfics (s. XIX)',
                'ref_analysis' => 'Florence Nightingale (1820–1910) és coneguda com la fundadora de la infermeria moderna, però era també una estadística brillant. Durant la guerra de Crimea, va recollir dades sobre les causes de mort dels soldats i va crear el "diagrama de rosa" — un gràfic polar que mostrava que la majoria de morts eren per malalties infeccioses evitables, no per ferides de guerra. El gràfic va convèncer el govern britànic de millorar les condicions sanitàries. Les dades, ben presentades, van salvar milers de vides.',
            ],
            8 => [
                'title'    => 'Taller de problemes: tot connectat',
                'subtitle' => 'Repàs actiu a través de problemes reals que combinen els conceptes del curs. Treballem en equip.',
                'quote_text'   => 'Si no pots resoldre un problema, és que hi ha un problema més fàcil que sí que pots resoldre. Troba\'l.',
                'quote_author' => 'George Pólya',
                'quote_work'   => 'matemàtic hongarès (1887–1985)',
                'intro_text'   => 'Durant set sessions hem parlat de nombres, fraccions, decimals, proporcionalitat, geometria i estadística. Avui no fem tema nou — posem en pràctica tot el que hem après. Els problemes reals no vénen etiquetats amb el tema que cal usar. Aquí tampoc.',
                'topic_text'   => 'La resolució de problemes té quatre fases, segons George Pólya: (1) Entendre el problema — qué et demana exactament? Qué dades tens? (2) Fer un pla — quin concepte o operació necessites? (3) Executar el pla — càlculs, passos, raonament escrit. (4) Revisar — té sentit la resposta? És possible?',
                'problem'      => 'Treball en equip: 3 reptes progressius que combinen tots els blocs del curs',
                'reference'    => 'George Pólya i l\'art de resoldre problemes',
                'ref_analysis' => 'George Pólya va publicar el 1945 "How to Solve It", un dels llibres de matemàtiques més venuts de la historia. El seu argument central: la resolució de problemes és una habilitat que s\'aprèn, no un talent innat. El mètode de les quatre fases (entendre, planificar, executar, revisar) és avui la base de l\'educació matemàtica a tot el món.',
            ],
        ];

        foreach ($sessions as $num => $data) {
            LmsLesson::firstOrCreate(
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
                            'type'     => 'project',
                            'title'    => 'Problema del dia',
                            'author'   => $data['problem'],
                            'year'     => null,
                            'extract'  => '',
                            'analysis' => '',
                        ],
                        [
                            'type'     => 'reference',
                            'title'    => $data['reference'],
                            'author'   => '',
                            'year'     => null,
                            'extract'  => '',
                            'analysis' => $data['ref_analysis'],
                        ],
                    ],

                    'reflection_questions' => [],
                    'exercise'             => [
                        'title'             => 'Exercici pràctic',
                        'duration'          => '20 min',
                        'statement'         => '',
                        'examples'          => [],
                        'tips'              => [],
                        'demo_first_person' => null,
                        'demo_third_person' => null,
                    ],
                ]
            );
        }

        $this->command->info('✅ LmsMathSeeder: curs «' . $course->title . '» amb 8 sessions creat/verificat.');
    }
}
