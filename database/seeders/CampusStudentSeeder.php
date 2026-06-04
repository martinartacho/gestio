<?php

namespace Database\Seeders;

use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusPayment;
use App\Models\CampusStudent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CampusStudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            ['first_name' => 'Marc',    'last_name' => 'Puig',     'email' => 'marc.puig@test.cat',     'phone' => '611 001 001', 'city' => 'Barcelona'],
            ['first_name' => 'Laura',   'last_name' => 'Ferrer',   'email' => 'laura.ferrer@test.cat',  'phone' => '611 001 002', 'city' => 'Granollers'],
            ['first_name' => 'Jordi',   'last_name' => 'Vila',     'email' => 'jordi.vila@test.cat',    'phone' => '611 001 003', 'city' => 'Sabadell'],
            ['first_name' => 'Marta',   'last_name' => 'Soler',    'email' => 'marta.soler@test.cat',   'phone' => '611 001 004', 'city' => 'Terrassa'],
            ['first_name' => 'Pere',    'last_name' => 'Costa',    'email' => 'pere.costa@test.cat',    'phone' => '611 001 005', 'city' => 'Vic'],
            ['first_name' => 'Núria',   'last_name' => 'Molas',    'email' => 'nuria.molas@test.cat',   'phone' => '611 001 006', 'city' => 'Mataró'],
            ['first_name' => 'David',   'last_name' => 'Roca',     'email' => 'david.roca@test.cat',    'phone' => '611 001 007', 'city' => 'Manresa'],
            ['first_name' => 'Sílvia',  'last_name' => 'Prat',     'email' => 'silvia.prat@test.cat',   'phone' => '611 001 008', 'city' => 'Girona'],
            ['first_name' => 'Ricard',  'last_name' => 'Duran',    'email' => 'ricard.duran@test.cat',  'phone' => '611 001 009', 'city' => 'Lleida'],
            ['first_name' => 'Gemma',   'last_name' => 'Mas',      'email' => 'gemma.mas@test.cat',     'phone' => '611 001 010', 'city' => 'Tarragona'],
            ['first_name' => 'Arnau',   'last_name' => 'Gibert',   'email' => 'arnau.gibert@test.cat',  'phone' => '611 001 011', 'city' => 'Barcelona'],
            ['first_name' => 'Cristina','last_name' => 'Vidal',    'email' => 'cristina.vidal@test.cat','phone' => '611 001 012', 'city' => 'Badalona'],
        ];

        $password = Hash::make(
            config('seeder.student_password') ?? throw new \RuntimeException('SEEDER_STUDENT_PASSWORD no està definit al .env')
        );

        $createdStudents = [];
        foreach ($students as $data) {
            $createdStudents[] = CampusStudent::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['password' => $password, 'data_consent' => true])
            );
        }

        // Agafar alguns cursos actius per fer inscripcions
        $courses = CampusCourse::where('status', 'active')->where('is_public', true)->take(4)->get();

        if ($courses->isEmpty()) {
            $courses = CampusCourse::where('is_public', true)->take(4)->get();
        }

        if ($courses->isEmpty()) {
            return;
        }

        $this->seedEnrollments($createdStudents, $courses);
    }

    private function seedEnrollments(array $students, $courses): void
    {
        $course1 = $courses->get(0);
        $course2 = $courses->get(1);
        $course3 = $courses->get(2);
        $course4 = $courses->get(3);

        // [student_idx, course, status, paid_at, payment_method]
        // payment_method: 'stripe'|'transfer'|'cash'|'card'|null (pending/cancelled)
        $scenarios = [
            [0,  $course1,            'paid',      now()->subDays(10), 'transfer'],
            [1,  $course1,            'paid',      now()->subDays(9),  'cash'],
            [2,  $course1,            'paid',      now()->subDays(8),  'card'],
            [3,  $course1,            'pending',   null,               null],
            [4,  $course1,            'cancelled', null,               null],
            [5,  $course2 ?? $course1,'paid',      now()->subDays(7),  'transfer'],
            [6,  $course2 ?? $course1,'paid',      now()->subDays(6),  'stripe'],
            [7,  $course2 ?? $course1,'pending',   null,               null],
            [8,  $course3 ?? $course1,'paid',      now()->subDays(5),  'stripe'],
            [9,  $course3 ?? $course1,'refunded',  null,               'transfer'],
            [10, $course4 ?? $course1,'paid',      now()->subDays(3),  'cash'],
            [11, $course4 ?? $course1,'pending',   null,               null],
            // alumne inscrit a 2 cursos
            [0,  $course2 ?? $course1,'paid',      now()->subDays(4),  'stripe'],
            [1,  $course3 ?? $course1,'paid',      now()->subDays(2),  'card'],
        ];

        foreach ($scenarios as [$idx, $course, $status, $paidAt, $paymentMethod]) {
            if (! $course) {
                continue;
            }

            $student = $students[$idx];

            $existing = CampusEnrollment::where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->first();

            if ($existing) {
                continue;
            }

            $isStripe  = $paymentMethod === 'stripe';
            $isManual  = in_array($paymentMethod, ['transfer', 'cash', 'card', 'domiciliacio']);

            $enrollment = CampusEnrollment::create([
                'student_id'      => $student->id,
                'course_id'       => $course->id,
                'first_name'      => $student->first_name,
                'last_name'       => $student->last_name,
                'email'           => $student->email,
                'phone'           => $student->phone,
                'enrollment_date' => ($paidAt ?? now())->toDateString(),
                'status'          => $status,
                'amount'          => $course->price,
                'payment_method'  => $paymentMethod,
                'paid_at'         => in_array($status, ['paid', 'refunded']) ? $paidAt : null,
                'stripe_session_id'     => $isStripe && $status === 'paid' ? 'cs_test_seed_' . uniqid() : null,
                'stripe_payment_intent' => $isStripe && $status === 'paid' ? 'pi_test_seed_' . uniqid() : null,
            ]);

            // Crear campus_payment per pagaments manuals (no Stripe)
            if ($isManual && in_array($status, ['paid', 'refunded'])) {
                CampusPayment::create([
                    'enrollment_id' => $enrollment->id,
                    'amount'        => $course->price,
                    'method'        => $paymentMethod,
                    'status'        => $status === 'paid' ? 'completed' : 'refunded',
                    'payment_date'  => ($paidAt ?? now())->toDateString(),
                    'reference'     => strtoupper($paymentMethod) . '-' . strtoupper(uniqid()),
                ]);
            }

            // Inserir al pivot campus_course_student només si pagat
            if ($status === 'paid') {
                $course->students()->syncWithoutDetaching([
                    $student->id => [
                        'enrollment_id' => $enrollment->id,
                        'enrolled_at'   => $paidAt ?? now(),
                    ],
                ]);
            }
        }
    }
}
