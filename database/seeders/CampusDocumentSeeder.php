<?php

namespace Database\Seeders;

use App\Models\CampusCourse;
use App\Models\CampusDocument;
use App\Models\CampusTeacher;
use Illuminate\Database\Seeder;

class CampusDocumentSeeder extends Seeder
{
    public function run(): void
    {
        // Busquem algun curs actiu
        $course = CampusCourse::where('status', 'active')->first();
        $teacher = CampusTeacher::where('status', 'active')->first();

        if (! $course || ! $teacher) {
            $this->command->warn('⚠ No s\'han trobat cursos/professors actius. Omitint documents de prova.');
            return;
        }

        $documents = [
            [
                'title'       => 'Programa del curs',
                'description' => 'Programa detallat amb objectius, continguts i metodologia.',
                'type'        => 'url',
                'url'         => 'https://example.com/programa.pdf',
                'visibility'  => 'public',
                'sort_order'  => 1,
                'status'      => 'active',
            ],
            [
                'title'       => 'Material de la sessió 1',
                'description' => 'Presentació i apunts de la primera sessió.',
                'type'        => 'url',
                'url'         => 'https://docs.google.com/presentation/d/example',
                'visibility'  => 'enrolled',
                'sort_order'  => 2,
                'status'      => 'active',
            ],
            [
                'title'       => 'Exercicis pràctics',
                'description' => 'Full d\'exercicis per als alumnes matriculats.',
                'type'        => 'url',
                'url'         => 'https://example.com/exercicis.pdf',
                'visibility'  => 'enrolled',
                'sort_order'  => 3,
                'status'      => 'active',
            ],
            [
                'title'       => 'Notes del professor/a (privat)',
                'description' => 'Notes internes per al professorat.',
                'type'        => 'url',
                'url'         => 'https://example.com/notes-professor.pdf',
                'visibility'  => 'private',
                'sort_order'  => 10,
                'status'      => 'active',
            ],
        ];

        foreach ($documents as $def) {
            CampusDocument::firstOrCreate(
                [
                    'title'     => $def['title'],
                    'course_id' => $course->id,
                ],
                array_merge($def, [
                    'course_id'  => $course->id,
                    'teacher_id' => $teacher->id,
                    'created_by' => null,
                ])
            );
        }

        $this->command->info("✅ Documents de prova creats per al curs [{$course->code}] {$course->title}");
    }
}
