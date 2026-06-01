<?php

namespace Database\Seeders;

use App\Models\AssociatMember;
use Illuminate\Database\Seeder;

class AssociatMemberSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('seeder.member_password')
            ?? throw new \RuntimeException('SEEDER_MEMBER_PASSWORD no està definit al .env');

        $members = [
            // Actius amb mandat SEPA configurat
            [
                'number'  => '1947', 'first' => 'Josep',   'last' => 'Pla Verdaguer',
                'email'   => 'membre.1947@demo.test',       'status' => 'active',
                'joined'  => '1990-09-01',
                'iban'    => 'ES9121000418450200051332',
                'holder'  => 'Josep Pla Verdaguer',
                'mandate' => 'MAND-1947-001', 'mandate_date' => '2024-01-15', 'sequence' => 'RCUR',
            ],
            [
                'number'  => '2001', 'first' => 'Maria',   'last' => 'Casals Fontanills',
                'email'   => 'membre.2001@demo.test',       'status' => 'active',
                'joined'  => '2001-01-15',
                'iban'    => 'ES2100490001912036432879',
                'holder'  => 'Maria Casals Fontanills',
                'mandate' => 'MAND-2001-001', 'mandate_date' => '2023-09-01', 'sequence' => 'RCUR',
            ],
            [
                'number'  => '2015', 'first' => 'Carles',  'last' => 'Roca Ferrer',
                'email'   => 'membre.2015@demo.test',       'status' => 'active',
                'joined'  => '2015-03-10',
                'iban'    => 'ES4400750899600667907624',
                'holder'  => 'Carles Roca Ferrer',
                'mandate' => 'MAND-2015-001', 'mandate_date' => '2024-03-10', 'sequence' => 'FRST',
            ],
            // Actiu sense mandat SEPA
            [
                'number'  => '2023', 'first' => 'Núria',   'last' => 'Soler Mas',
                'email'   => 'membre.2023@demo.test',       'status' => 'active',
                'joined'  => '2023-10-05',
                'iban'    => null, 'holder' => null, 'mandate' => null,
                'mandate_date' => null, 'sequence' => null,
            ],
            // Pendent d'activació
            [
                'number'  => '2026', 'first' => 'Andreu',  'last' => 'Vidal Pujol',
                'email'   => 'membre.2026@demo.test',       'status' => 'pending',
                'joined'  => null,
                'iban'    => null, 'holder' => null, 'mandate' => null,
                'mandate_date' => null, 'sequence' => null,
            ],
            // Cancel·lat
            [
                'number'  => '1985', 'first' => 'Antoni',  'last' => 'Bosch Vila',
                'email'   => 'membre.1985@demo.test',       'status' => 'cancelled',
                'joined'  => '1985-06-01',
                'iban'    => null, 'holder' => null, 'mandate' => null,
                'mandate_date' => null, 'sequence' => null,
            ],
        ];

        foreach ($members as $d) {
            AssociatMember::firstOrCreate(['member_number' => $d['number']], [
                'first_name'         => $d['first'],
                'last_name'          => $d['last'],
                'email'              => $d['email'],
                'password'           => bcrypt($password),
                'status'             => $d['status'],
                'joined_at'          => $d['joined'],
                'cancelled_at'       => $d['status'] === 'cancelled' ? '2020-12-31' : null,
                'data_consent'       => true,
                'city'               => 'Granollers',
                'bank_iban'          => $d['iban'],
                'bank_holder'        => $d['holder'],
                'mandate_reference'  => $d['mandate'],
                'mandate_signed_at'  => $d['mandate_date'],
                'mandate_sequence'   => $d['sequence'],
                'mandate_method'     => $d['mandate'] ? 'paper' : null,
            ]);
        }

        $this->command->info('✅ Socis creats: ' . count($members));
    }
}
