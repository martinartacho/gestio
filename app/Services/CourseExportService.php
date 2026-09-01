<?php

namespace App\Services;

use App\Models\CampusCourse;

class CourseExportService
{
    private const HEADERS = [
        'Codi', 'Títol', 'Categoria', 'Format', 'Temporada',
        'Professorat', 'Espai', 'Places', 'Preu', 'Data inici', 'Data fi', 'Estat',
    ];

    public function generate(?int $seasonId, ?int $tenantId = null): string
    {
        $courses = CampusCourse::where('tenant_id', $tenantId)
            ->with(['category', 'season', 'space', 'teachers'])
            ->when($seasonId, fn ($q) => $q->where('season_id', $seasonId))
            ->where('status', '!=', 'draft')
            ->orderBy('start_date')
            ->get();

        $handle = fopen('php://memory', 'w');

        fwrite($handle, "\xEF\xBB\xBF"); // BOM per Excel
        fputcsv($handle, self::HEADERS);

        foreach ($courses as $course) {
            fputcsv($handle, [
                $course->code,
                $course->title,
                $course->category?->name,
                $course->format,
                $course->season?->name,
                $course->teachers->map(fn ($t) => $t->first_name . ' ' . $t->last_name)->join(', '),
                $course->space?->name,
                $course->max_students,
                $course->price,
                $course->start_date?->toDateString(),
                $course->end_date?->toDateString(),
                $course->status,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
