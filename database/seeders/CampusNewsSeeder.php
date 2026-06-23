<?php

namespace Database\Seeders;

use App\Models\CampusNews;
use Illuminate\Database\Seeder;

class CampusNewsSeeder extends Seeder
{
    public function run(): void
    {
        $news = [

            [
                'title'        => 'Estrenem la plataforma de formació',
                'summary'      => 'Catàleg de cursos, portal d\'alumnes, portal de professors i continguts en línia integrats en un sol lloc.',
                'body'         => '<p>La plataforma ja és en marxa. Des d\'ara podeu consultar el catàleg de cursos, inscriure-us en línia i fer el seguiment de les vostres matrícules des del portal d\'alumnes.</p><p>Els professors disposen del seu propi portal per gestionar sessions, penjar material i consultar les liquidacions. Els cursos amb contingut en línia (LMS) permeten avançar al ritme de cadascú i accedir als materials en qualsevol moment.</p>',
                'labels'       => ['sistema', 'admin'],
                'version'      => 'v1.0.0',
                'published_at' => '2026-05-01 09:00:00',
            ],

            [
                'title'        => 'Paga com vulguis: targeta, Bizum, transferència o efectiu',
                'summary'      => 'Múltiples formes de pagament i control de places en temps real.',
                'body'         => '<p>Ja no cal pagar únicament amb targeta. Ara podeu triar entre Stripe, Bizum, transferència bancària, PayPal o efectiu. Cada mètode genera una referència única i l\'administrador confirma el pagament des del panell.</p><p>Les places pendents de pagament compten al límit de capacitat del curs, de manera que el sistema reflecteix en tot moment la disponibilitat real. Quan el curs s\'omple, el botó d\'inscripció desapareix automàticament del catàleg.</p>',
                'labels'       => ['campus', 'tresoreria'],
                'version'      => 'v1.1.0',
                'published_at' => '2026-05-08 09:00:00',
            ],

            [
                'title'        => 'Accés segur i cua d\'espera per als cursos més sol·licitats',
                'summary'      => 'Verificació per codi OTP i sistema de torns per garantir un accés ordenat.',
                'body'         => '<p>El registre i la recuperació de contrasenya ara funcionen amb un codi d\'un sol ús (OTP) enviat per correu. Sense contrasenyes temporals ni esperes: introduïu el codi de 6 dígits i accediu immediatament.</p><p>Per als cursos amb alta demanda, hem incorporat un sistema de cua amb torns. L\'alumne s\'apunta a la cua, tria una franja horària i rep un avís quan és el seu moment. El pagament es completa dins la finestra assignada, sense presses ni col·lapses.</p>',
                'labels'       => ['campus'],
                'version'      => 'v1.2.0',
                'published_at' => '2026-05-15 09:00:00',
            ],

            [
                'title'        => 'Aprèn al teu ritme i obté el teu certificat',
                'summary'      => 'Els cursos en línia incorporen preguntes de comprensió i emeten certificats en acabar.',
                'body'         => '<p>Els cursos LMS ara poden incloure preguntes de resposta única al final de cada sessió. L\'alumne les respon quan vol i pot revisar-les en qualsevol moment des del seu portal.</p><p>En completar totes les sessions d\'un curs, la plataforma emet automàticament un <strong>certificat de finalització</strong> personalitzat amb el nom de l\'alumne i la data de superació. Accessible des del portal en qualsevol moment posterior.</p>',
                'labels'       => ['campus'],
                'version'      => 'v1.3.0',
                'published_at' => '2026-05-22 09:00:00',
            ],

            [
                'title'        => 'Estrenem el Passaport Cultural Digital per a socis',
                'summary'      => 'Carnet digital amb QR únic per a cada soci. Primer pas de la digitalització.',
                'body'         => '<p>Les entitats culturals ja poden oferir als seus socis un <strong>carnet digital</strong> accessible des del mòbil. Cada soci accedeix al portal amb el seu correu i obté el carnet amb un codi QR únic que preserva el número de soci historic.</p><p>El mòdul de socis és completament independent del campus de formació i s\'activa des de la configuració. Cada entitat que usa la plataforma pot personalitzar el nom i l\'aparença del carnet.</p>',
                'labels'       => ['associats', 'secretaria'],
                'version'      => 'v1.4.0',
                'published_at' => '2026-05-29 09:00:00',
            ],

            [
                'title'        => 'Visió financera global: tota la tresoreria en una sola pàgina',
                'summary'      => 'Inscripcions, pagaments, liquidacions de professors i quotes de socis, tot centralitzat.',
                'body'         => '<p>El responsable de tresoreria disposa ara d\'un <strong>resum financer consolidat</strong> accessible des del seu menú. Mostra en temps real l\'estat de tots els fluxos econòmics de la plataforma: inscripcions, pagaments, liquidacions de professors i quotes de socis.</p><p>Les quotes i les remeses SEPA dels socis també es gestionen directament des de tresoreria, amb visió transversal de tots els registres i estat de cada remesa. Si un mòdul no està actiu, la seva secció desapareix automàticament del resum.</p>',
                'labels'       => ['tresoreria', 'sistema'],
                'version'      => 'v1.5.0',
                'published_at' => '2026-06-05 09:00:00',
            ],

            [
                'title'        => 'Nou curs de benvinguda a la plataforma',
                'summary'      => 'Sis sessions per descobrir totes les funcionalitats, des de la configuració fins als socis.',
                'body'         => '<p>Hem publicat un <strong>curs d\'introducció</strong> a la plataforma amb sis sessions que guien l\'administrador pas a pas: configuració inicial, catàleg de cursos, tresoreria, mòdul de socis i gestió de rols i usuaris. Accessible des del portal d\'alumnes.</p><p>A més, les sessions LMS ara admeten <strong>imatges múltiples</strong> per sessió, amb posicionament flexible i peu de foto opcional. Els professors també poden recuperar la contrasenya amb codi OTP, igual que els alumnes.</p>',
                'labels'       => ['campus', 'associats', 'admin'],
                'version'      => 'v1.7.0',
                'published_at' => '2026-06-19 09:00:00',
            ],

        ];

        foreach ($news as $item) {
            CampusNews::firstOrCreate(
                ['version' => $item['version']],
                $item
            );
        }

        $this->command->info('✅ Notícies per defecte creades (' . count($news) . ' registres).');
    }
}
