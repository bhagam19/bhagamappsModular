<?php

namespace Tests\Feature\Inventario;

use Livewire\Livewire;
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Entities\HistorialUbicacionBien;
use Modules\Inventario\Entities\Ubicacion;
use Modules\Inventario\Livewire\Ubicaciones\HistorialUbicacionesBien;

/**
 * Fase 2 — Critical Business Flows: Historial de Ubicaciones
 * Fase 3 — Regression Protection: IMPL-INV-005, GAP-002
 *
 * IMPL-INV-005: La ubicación actual de un bien se deriva del historial
 *               (historial_ubicaciones_bienes), no de una FK directa en bienes.
 *
 * GAP-002: bienes no tiene columna ubicacion_id.
 */
class HistorialUbicacionesTest extends InventarioTestCase
{
    // ─── GAP-002 en profundidad ───────────────────────────────────────────────

    public function test_tabla_bienes_no_tiene_columna_ubicacion_id_regresion_gap002(): void
    {
        $this->assertFalse(
            in_array('ubicacion_id', \Schema::getColumnListing('bienes')),
            'GAP-002: bienes no debe tener columna ubicacion_id'
        );
    }

    // ─── IMPL-INV-005: ubicacion derivada del historial ──────────────────────

    public function test_bien_sin_historial_ubicacion_retorna_ubicacion_actual_null(): void
    {
        $bien = $this->crearBien();

        $this->assertNull(
            $bien->ubicacionActual,
            'Un bien sin historial de ubicaciones debe retornar null'
        );
    }

    public function test_bien_con_historial_retorna_ultima_ubicacion_destino(): void
    {
        $ubicacion = Ubicacion::first();

        if (! $ubicacion) {
            $this->markTestSkipped('No hay ubicaciones en la BD. Ejecute UbicacionesSeeder.');
        }

        $bien = $this->crearBien();

        HistorialUbicacionBien::create([
            'bien_id'             => $bien->id,
            'ubicacion_destino_id' => $ubicacion->id,
        ]);

        $ubicacionActual = $bien->fresh()->ubicacionActual;

        $this->assertNotNull($ubicacionActual);
        $this->assertEquals($ubicacion->id, $ubicacionActual->ubicacion_destino_id);
    }

    public function test_ultima_ubicacion_es_la_mas_reciente(): void
    {
        $ubicaciones = Ubicacion::take(2)->get();

        if ($ubicaciones->count() < 2) {
            $this->markTestSkipped('Se necesitan al menos 2 ubicaciones. Ejecute UbicacionesSeeder.');
        }

        $bien = $this->crearBien();

        HistorialUbicacionBien::create([
            'bien_id'              => $bien->id,
            'ubicacion_destino_id' => $ubicaciones[0]->id,
            'fecha_movimiento'     => now()->subMinute(),
        ]);

        HistorialUbicacionBien::create([
            'bien_id'              => $bien->id,
            'ubicacion_destino_id' => $ubicaciones[1]->id,
            'fecha_movimiento'     => now(),
        ]);

        $this->assertEquals(
            $ubicaciones[1]->id,
            $bien->fresh()->ubicacionActual->ubicacion_destino_id,
            'ubicacionActual debe retornar la ubicación del registro más reciente'
        );
    }

    // ─── Cambiar ubicación ────────────────────────────────────────────────────

    public function test_cambio_ubicacion_registra_registro_en_historial(): void
    {
        $ubicacion = Ubicacion::first();

        if (! $ubicacion) {
            $this->markTestSkipped('No hay ubicaciones en la BD. Ejecute UbicacionesSeeder.');
        }

        $admin = $this->crearAdmin();
        $bien = $this->crearBien();

        $conteoBefore = HistorialUbicacionBien::where('bien_id', $bien->id)->count();

        HistorialUbicacionBien::create([
            'bien_id'             => $bien->id,
            'ubicacion_destino_id' => $ubicacion->id,
        ]);

        $this->assertEquals(
            $conteoBefore + 1,
            HistorialUbicacionBien::where('bien_id', $bien->id)->count(),
            'Cambio de ubicación debe crear un registro en historial_ubicaciones_bienes'
        );
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function test_historial_ubicaciones_bien_renderiza_sin_errores(): void
    {
        $admin = $this->crearAdmin();

        Livewire::actingAs($admin)
                ->test(HistorialUbicacionesBien::class)
                ->assertStatus(200);
    }

    // ─── Tabla historial_ubicaciones_bienes existe ────────────────────────────

    public function test_tabla_historial_ubicaciones_bienes_existe(): void
    {
        $this->assertTrue(
            \Schema::hasTable('historial_ubicaciones_bienes'),
            'La tabla historial_ubicaciones_bienes debe existir (IMPL-INV-005)'
        );
    }

    public function test_tabla_historial_ubicaciones_tiene_columnas_requeridas(): void
    {
        $columnas = \Schema::getColumnListing('historial_ubicaciones_bienes');

        foreach (['bien_id', 'ubicacion_destino_id', 'fecha_movimiento'] as $col) {
            $this->assertContains($col, $columnas, "Columna '$col' requerida en historial_ubicaciones_bienes");
        }
    }
}
