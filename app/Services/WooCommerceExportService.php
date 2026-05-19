<?php

namespace App\Services;

use App\Models\CampusCourse;

class WooCommerceExportService
{
    private const HEADERS = [
        'Identificador', 'Tipus', 'SKU', 'GTIN, UPC, EAN, o ISBN', 'Nom', 'Publicat',
        'Està destacat?', 'Visibilitat en el catàleg', 'Descripció breu', 'Descripció',
        'Dia en què comença el preu rebaixat', 'Dia en què acaba el preu rebaixat',
        "Estat de l'impost", "Classe d'impost", 'En estoc?', 'Estoc',
        "Quantitat d'estoc baixa", 'Voleu permetre reserves de productes esgotats?',
        "Es ven individualment?", 'Pes (kg)', 'Longitud (cm)', 'Amplada (cm)', 'Alçada (cm)',
        'Voleu permetre les ressenyes de clients?', 'Nota de compra', 'Preu rebaixat',
        'Preu habitual', 'Categories', 'Etiquetes', "Classe d'enviament", 'Imatges',
        'Limit de baixades', 'Dies de caducitat de la baixada', 'Pare', 'Productes agrupats',
        'Vendes dirigides', 'Vendes creuades', 'URL extern', 'Text del botó', 'Posició',
        'Marques', "Nom de l'atribut 1", "Valor(s) de l'atribut 1", 'Atribut visible 1',
        'Atribut global 1',
    ];

    public function generate(?int $seasonId): string
    {
        $courses = CampusCourse::with(['category', 'children' => fn($q) => $q->when($seasonId, fn($q) => $q->where('season_id', $seasonId))])
            ->whereNull('parent_id')
            ->when($seasonId, fn($q) => $q->where('season_id', $seasonId))
            ->where('status', '!=', 'draft')
            ->orderBy('id')
            ->get();

        $handle = fopen('php://memory', 'w');

        fwrite($handle, "\xEF\xBB\xBF"); // BOM per Excel
        fputcsv($handle, self::HEADERS);

        foreach ($courses as $position => $course) {
            $children = $course->children->where('status', '!=', 'draft')->values();

            if ($children->isNotEmpty()) {
                $allFormats = $children
                    ->map(fn($c) => CampusCourse::FORMATS[$c->format] ?? $c->format)
                    ->unique()->join(', ');

                fputcsv($handle, $this->buildRow($course, 'variable', null, $allFormats, $position + 1));

                foreach ($children as $i => $child) {
                    $formatLabel = CampusCourse::FORMATS[$child->format] ?? $child->format;
                    fputcsv($handle, $this->buildRow($child, 'variation, virtual', $course->id, $formatLabel, $i + 1));
                }
            } else {
                $formatLabel = CampusCourse::FORMATS[$course->format] ?? $course->format;
                fputcsv($handle, $this->buildRow($course, 'simple, virtual', null, $formatLabel, $position + 1));
            }
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    private function buildRow(
        CampusCourse $course,
        string $type,
        ?int $parentId,
        string $formatLabel,
        int $position = 0,
    ): array {
        $isVariable  = $type === 'variable';
        $isVariation = str_contains($type, 'variation');
        $inStock     = $course->max_students === null || $course->max_students > 0;

        return [
            $course->id,
            $type,
            $course->code ?? '',
            '',
            $course->title,
            $course->is_active ? 1 : 0,
            0,
            'visible',
            $course->requirements ?? '',
            $course->description ?? '',
            '', '',
            'taxable',
            $isVariation ? 'parent' : '',
            $inStock ? 1 : 0,
            $isVariable ? '' : ($course->max_students ?? ''),
            '', 0,
            1,
            '', '', '', '',
            0,
            '',
            '',
            $isVariable ? '' : number_format((float) ($course->price ?? 0), 2, '.', ''),
            $course->category?->name ?? '',
            '', '', '',
            '', '',
            $parentId ? "id:{$parentId}" : '',
            '', '', '', '', '',
            $position,
            '',
            'Format',
            $formatLabel,
            $isVariable ? 1 : '',
            1,
        ];
    }
}
