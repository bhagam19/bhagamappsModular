<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Catálogo institucional de orígenes
    private array $catalog = [
        ['nombre' => 'Sin origen',    'descripcion' => 'Bienes sin clasificar o con origen desconocido'],
        ['nombre' => 'Institucional', 'descripcion' => 'Adquiridos con recursos propios de la institución'],
        ['nombre' => 'Municipio',     'descripcion' => 'Aportados por el municipio de Entrerríos'],
        ['nombre' => 'SEDUCA',        'descripcion' => 'Provenientes de la Secretaría de Educación'],
        ['nombre' => 'MEN',           'descripcion' => 'Provenientes del Ministerio de Educación Nacional'],
        ['nombre' => 'Donación',      'descripcion' => 'Recibidos como donación de cualquier entidad o persona'],
        ['nombre' => 'Comodato',      'descripcion' => 'Bienes en préstamo o de propiedad de terceros'],
        ['nombre' => 'Compra',        'descripcion' => 'Adquiridos por compra directa'],
        ['nombre' => 'Proyecto',      'descripcion' => 'Obtenidos mediante proyectos institucionales'],
        ['nombre' => 'Transferencia', 'descripcion' => 'Transferidos de otra entidad educativa o pública'],
        ['nombre' => 'Otro',          'descripcion' => 'Origen no categorizable en las opciones anteriores'],
    ];

    // Mapeo: valor literal de bienes.origen → nombre en catálogo
    private array $mapping = [
        '-'                          => 'Sin origen',
        'Institucional'              => 'Institucional',
        'Donación'                   => 'Donación',
        'Seduca'                     => 'SEDUCA',
        'Municipal'                  => 'Municipio',
        'Donación Colanta'           => 'Donación',
        'Propiedad De Don Miguel'    => 'Comodato',
        'Donación Prom 2018'         => 'Donación',
        'Comodato Madena'            => 'Comodato',
        'Comodato Fritolay'          => 'Comodato',
        'Donación 2023'              => 'Donación',
        'Colanta'                    => 'Donación',
        'Comodato Cocacola'          => 'Comodato',
        'Comodato'                   => 'Comodato',
        'Compra'                     => 'Compra',
        'Donacion Governacion'       => 'Donación',
        'Donación Bonanza'           => 'Donación',
        'Men'                        => 'MEN',
        'Compra 2023'                => 'Compra',
        'Donacion Acueducto La Beta' => 'Donación',
        'Propiedad De Fritolay'      => 'Comodato',
        'Donación Coopecrédito'      => 'Donación',
        'Mineducación'               => 'MEN',
        'Parque Explora'             => 'Donación',
        'Comodato Cremhelado'        => 'Comodato',
        'Comodato Postobón'          => 'Comodato',
        'Propiedad De Postobon'      => 'Comodato',
    ];

    public function up(): void
    {
        $now = now();

        // 1. Insertar catálogo (insertOrIgnore por idempotencia)
        foreach ($this->catalog as $entry) {
            DB::table('origenes')->insertOrIgnore([
                'nombre'      => $entry['nombre'],
                'descripcion' => $entry['descripcion'],
                'activo'      => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        // 2. Construir índice nombre → id
        $catalogIds = DB::table('origenes')
            ->pluck('id', 'nombre')
            ->toArray();

        $sinOrigenId = $catalogIds['Sin origen'] ?? null;
        $otroId      = $catalogIds['Otro']       ?? null;

        if (! $sinOrigenId || ! $otroId) {
            throw new \RuntimeException('Catálogo de orígenes no insertado correctamente.');
        }

        // 3. Mapear bienes con origen string conocido
        foreach ($this->mapping as $origenString => $catalogNombre) {
            $targetId = $catalogIds[$catalogNombre] ?? $otroId;
            DB::table('bienes')
                ->whereNull('deleted_at')
                ->whereNull('origen_id')
                ->where('origen', $origenString)
                ->update(['origen_id' => $targetId]);
        }

        // 4. Bienes con origen NULL o vacío → Sin origen
        DB::table('bienes')
            ->whereNull('deleted_at')
            ->whereNull('origen_id')
            ->where(function ($q) {
                $q->whereNull('origen')->orWhere('origen', '');
            })
            ->update(['origen_id' => $sinOrigenId]);

        // 5. Cualquier valor restante no mapeado → Otro
        DB::table('bienes')
            ->whereNull('deleted_at')
            ->whereNull('origen_id')
            ->update(['origen_id' => $otroId]);
    }

    public function down(): void
    {
        DB::table('bienes')->update(['origen_id' => null]);
        DB::table('origenes')->truncate();
    }
};
