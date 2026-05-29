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
            ['number' => '1947', 'first' => 'Josep',   'last' => 'Martín Artacho', 'email' => 'pepe@acgranollers.cat',  'status' => 'active',  'joined' => '1990-09-01'],
            ['number' => '2001', 'first' => 'Maria',   'last' => 'Puig Sala',      'email' => 'maria@acgranollers.cat', 'status' => 'active',  'joined' => '2001-01-15'],
            ['number' => '2024', 'first' => 'Carles',  'last' => 'Roca Ferrer',    'email' => 'carles@acgranollers.cat','status' => 'active',  'joined' => '2024-03-10'],
            ['number' => '2025', 'first' => 'Núria',   'last' => 'Soler Mas',      'email' => 'nuria@acgranollers.cat', 'status' => 'pending', 'joined' => null],
            ['number' => '1985', 'first' => 'Antoni',  'last' => 'Bosch Vila',     'email' => 'antoni@acgranollers.cat','status' => 'cancelled','joined' => '1985-06-01'],
        ];

        foreach ($members as $data) {
            AssociatMember::firstOrCreate(['email' => $data['email']], [
                'member_number' => $data['number'],
                'first_name'    => $data['first'],
                'last_name'     => $data['last'],
                'password'      => bcrypt($password),
                'status'        => $data['status'],
                'joined_at'     => $data['joined'],
                'cancelled_at'  => $data['status'] === 'cancelled' ? now() : null,
                'data_consent'  => true,
                'city'          => 'Granollers',
            ]);
        }
    }
}
