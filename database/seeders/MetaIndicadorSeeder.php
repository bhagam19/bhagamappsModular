<?php

namespace Database\Seeders;

use App\Models\Indicador;
use App\Models\Meta;
use Illuminate\Database\Seeder;

class MetaIndicadorSeeder extends Seeder
{
    public function run(): void
    {
        // Relaciones según DDOM-GESTION-MAP-001
        $relaciones = [
            'META-GD01-001' => ['IND-DIR-001'],
            'META-GD01-002' => ['IND-DIR-002'],
            'META-GD02-001' => ['IND-DIR-002'],
            'META-GD02-002' => ['IND-DIR-002'],
            'META-GD03-001' => ['IND-DIR-003'],
            'META-GD03-002' => ['IND-DIR-003'],
            'META-GD04-001' => ['IND-DIR-004'],
            'META-GD04-002' => ['IND-DIR-004'],
            'META-GD05-001' => ['IND-DIR-004'],
            'META-GD05-002' => ['IND-DIR-004'],
            'META-GD06-001' => ['IND-DIR-005'],
            'META-GD06-002' => ['IND-DIR-005'],
            'META-GA01-001' => ['IND-ACA-001'],
            'META-GA01-002' => ['IND-ACA-001'],
            'META-GA02-001' => ['IND-ACA-002'],
            'META-GA02-002' => ['IND-ACA-002'],
            'META-GA03-001' => ['IND-ACA-003'],
            'META-GA03-002' => ['IND-ACA-003'],
            'META-GA04-001' => ['IND-ACA-004'],
            'META-GA04-002' => ['IND-ACA-005'],
            'META-GA04-003' => ['IND-ACA-006', 'IND-ACA-007'],
            'META-GAF01-001' => ['IND-ADM-001'],
            'META-GAF01-002' => ['IND-ADM-001'],
            'META-GAF02-001' => ['IND-ADM-002'],
            'META-GAF02-002' => ['IND-ADM-003'],
            'META-GAF02-003' => ['IND-ADM-004'],
            'META-GAF03-001' => ['IND-ADM-005'],
            'META-GAF03-002' => ['IND-ADM-005'],
            'META-GAF04-001' => ['IND-ADM-006'],
            'META-GAF04-002' => ['IND-ADM-006'],
            'META-GAF05-001' => ['IND-ADM-007'],
            'META-GAF05-002' => ['IND-ADM-007'],
            'META-GC01-001'  => ['IND-COM-001'],
            'META-GC01-002'  => ['IND-COM-002'],
            'META-GC02-001'  => ['IND-COM-003'],
            'META-GC02-002'  => ['IND-COM-003'],
            'META-GC03-001'  => ['IND-COM-004'],
            'META-GC03-002'  => ['IND-DIR-004', 'IND-COM-004'],
            'META-GC04-001'  => ['IND-COM-005'],
            'META-GC04-002'  => ['IND-COM-006'],
        ];

        $metas       = Meta::pluck('id', 'codigo');
        $indicadores = Indicador::pluck('id', 'codigo');

        foreach ($relaciones as $metaCodigo => $indCodigos) {
            $meta = $metas[$metaCodigo] ?? null;
            if (! $meta) {
                continue;
            }
            $metaModel = Meta::find($meta);
            $ids = collect($indCodigos)
                ->map(fn ($c) => $indicadores[$c] ?? null)
                ->filter()
                ->values()
                ->toArray();

            $metaModel->indicadores()->syncWithoutDetaching($ids);
        }
    }
}
