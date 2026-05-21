<?php

namespace Database\Seeders;

use App\Models\CampusCourse;
use App\Models\LmsLesson;
use Illuminate\Database\Seeder;

class LmsLessonSeeder extends Seeder
{
    public function run(): void
    {
        $course = CampusCourse::where('status', 'active')->first();

        if (! $course) {
            $this->command->warn('LmsLessonSeeder: cap curs actiu trobat, s\'ometen les lliçons.');
            return;
        }

        // ── Sessió 1: Narrativa curta ─────────────────────────────────────────
        LmsLesson::firstOrCreate(
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
                    'demo_first_person'  => null,
                    'demo_third_person'  => null,
                ],
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
                        'analysis' => 'El narrador parla en primera persona i des del primer moment es revela: és algú que no vol sentir-se vell, que té por de perdre alguna cosa, que es justifica davant d\'ell mateix. No ens diu "tinc por" — ens mostra com pensa, com mira la noia de la clínica, com signa sense llegir. Aquí la veu és el personatge. Tota la seva psicologia queda exposada sense que ell s\'adoni que l\'exposem.',
                    ],
                    [
                        'type'     => 'reference',
                        'title'    => 'La mosca · The Fly',
                        'author'   => 'Katherine Mansfield',
                        'year'     => '1922',
                        'extract'  => 'La mosca va sortir del tinter i va netejar les ales. L\'home la va mirar. Llavors va agafar la ploma i hi va tornar a deixar caure una gota. Mansfield no explica per qué. No cal.',
                        'analysis' => 'Un home de negocis rep un vell empleat al seu despatx. Tots dos han perdut fills a la Gran Guerra. Quan el visitant marxa, l\'home troba una mosca ofegant-se en el seu tinter. Mansfield utilitza la tercera persona, però s\'acosta tant al personatge que quasi sentim els seus pensaments des de dins. No hi ha cap judici, cap explicació. Simplement mostra. I precisament per això la crueltat resulta tan pertorbadora.',
                    ],
                ],

                'comparison' => [
                    'left_label'   => 'Súper Phono · 1a persona',
                    'right_label'  => 'Mansfield · 3a persona',
                    'left_points'  => ['Narrador dins de la historia', 'Veu íntima i subjectiva', 'Es justifica i dubta', 'El lector el veu més del que ell es veu'],
                    'right_points' => ['Narrador fora de la historia', 'Veu continguda i propera', 'Mostra sense jutjar mai', 'El lector decideix el sentit'],
                ],

                'reflection_questions' => [
                    ['question' => 'En Súper Phono, el narrador diu "Algunos gastos no son gastos, son decisiones sobre quién quieres seguir siendo." Creieu que ell és conscient del que realment li passa? Qué veieu vosaltres que ell no veu?'],
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
                    'demo_first_person'  => 'Arribo a la parada i el bus ja se\'n va. Miro el rellotge. Queden vint minuts per al proper. Penso que podria haver sortit abans, però no és veritat del tot...',
                    'demo_third_person'  => 'Ella arriba a la parada just quan el bus tanca les portes. Mira el rellotge. Queda quieta uns segons, els llavis apretats. Llavors treu el mòbil i fa veure que hi mira alguna cosa.',
                ],
            ]
        );

        $this->command->info("✅ LmsLessonSeeder: 2 lliçons creades per al curs «{$course->title}».");
    }
}
