<?php

namespace Database\Seeders;

use App\Models\AssociatMember;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class AssociatMemberSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('seeder.member_password')
            ?? throw new \RuntimeException('SEEDER_MEMBER_PASSWORD no està definit al .env');

        $faker = Faker::create('es_ES');

        // Configuració dels socis de demo: número i estat fixos, dades personals via Faker
        $definitions = [
            ['number' => '1947', 'status' => 'active',    'joined' => '1990-09-01', 'sepa' => true,  'sequence' => 'RCUR'],
            ['number' => '2001', 'status' => 'active',    'joined' => '2001-01-15', 'sepa' => true,  'sequence' => 'RCUR'],
            ['number' => '2015', 'status' => 'active',    'joined' => '2015-03-10', 'sepa' => true,  'sequence' => 'FRST'],
            ['number' => '2023', 'status' => 'active',    'joined' => '2023-10-05', 'sepa' => false, 'sequence' => null],
            ['number' => '2026', 'status' => 'pending',   'joined' => null,         'sepa' => false, 'sequence' => null],
            ['number' => '1985', 'status' => 'cancelled', 'joined' => '1985-06-01', 'sepa' => false, 'sequence' => null],
        ];

        foreach ($definitions as $def) {
            $firstName = $faker->firstName();
            $lastName  = $faker->lastName() . ' ' . $faker->lastName();
            $num       = $def['number'];

            $iban   = $def['sepa'] ? $faker->iban('ES') : null;
            $holder = $iban ? "{$firstName} {$lastName}" : null;

            AssociatMember::firstOrCreate(['member_number' => $num], [
                'first_name'        => $firstName,
                'last_name'         => $lastName,
                'email'             => "membre.{$num}@demo.test",
                'password'          => bcrypt($password),
                'phone'             => $faker->phoneNumber(),
                'address'           => $faker->streetAddress(),
                'postal_code'       => $faker->postcode(),
                'city'              => $faker->city(),
                'status'            => $def['status'],
                'joined_at'         => $def['joined'],
                'cancelled_at'      => $def['status'] === 'cancelled' ? '2020-12-31' : null,
                'data_consent'      => true,
                'bank_iban'         => $iban,
                'bank_holder'       => $holder,
                'mandate_reference' => $iban ? "MAND-{$num}-001" : null,
                'mandate_signed_at' => $iban ? $def['joined'] : null,
                'mandate_sequence'  => $def['sequence'],
                'mandate_method'    => $iban ? 'paper' : null,
            ]);
        }

        $this->command->info('✅ Socis creats: ' . count($definitions));
    }
}
