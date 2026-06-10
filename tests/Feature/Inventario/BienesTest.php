<?php

namespace Tests\Feature\Inventario;

use Livewire\Livewire;
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Livewire\Bienes\BienesIndex;
use Modules\Inventario\Livewire\Bienes\EditarCampoBien;

/**
 * Fase 2 — Critical Business Flows: Bienes
 * Fase 3 — Regression Protection: GAP-001, GAP-002
 *
 * GAP-001: bienes no usa columna user_id  (no existe en tabla bienes)
 * GAP-002: bienes no usa columna ubicacion_id (no existe en tabla bienes)
 *          ubicacion se obtiene exclusivamente de historial_ubicaciones_bienes
 */
class BienesTest extends InventarioTestCase
{
    // ─── Render ───────────────────────────────────────────────────────────────

    public function test_bienes_index_renderiza_sin_errores(): void
    {
        $admin = $this->crearAdmin();

        Livewire::actingAs($admin)
                ->test(BienesIndex::class)
                ->assertStatus(200);
    }

    // ─── GAP-001: bienes no usa user_id ───────────────────────────────────────

    /**
     * Regresión GAP-001: la tabla bienes NO tiene columna user_id.
     * Si el código intenta filtrar por user_id, la consulta lanza un error de BD.
     * Este test verifica que BienesIndex renderiza sin error con bienes creados
     * sin user_id, demostrando que la columna no es requerida ni utilizada.
     */
    public function test_bienes_no_depende_de_columna_user_id_regresion_gap001(): void
    {
        $admin = $this->crearAdmin();

        $this->crearBien(['nombre' => 'Bien Sin User ID']);

        $this->assertFalse(
            in_array('user_id', \Schema::getColumnListing('bienes')),
            'GAP-001: La tabla bienes NO debe tener columna user_id'
        );

        Livewire::actingAs($admin)
                ->test(BienesIndex::class)
                ->assertStatus(200);
    }

    // ─── GAP-002: bienes no usa ubicacion_id ─────────────────────────────────

    /**
     * Regresión GAP-002: la tabla bienes NO tiene columna ubicacion_id.
     * La ubicación de un bien se deriva exclusivamente de historial_ubicaciones_bienes.
     */
    public function test_bienes_no_depende_de_columna_ubicacion_id_regresion_gap002(): void
    {
        $this->assertFalse(
            in_array('ubicacion_id', \Schema::getColumnListing('bienes')),
            'GAP-002: La tabla bienes NO debe tener columna ubicacion_id'
        );
    }

    // ─── Crear bien ───────────────────────────────────────────────────────────

    public function test_bien_puede_crearse_con_nombre_y_serie(): void
    {
        $admin = $this->crearAdmin();
        $nombre = 'Computador de Prueba ' . uniqid();

        Livewire::actingAs($admin)
                ->test(BienesIndex::class)
                ->set('nombreSeleccionado', $nombre)
                ->set('origenSeleccionado', 'Compra')
                ->set('serie', 'PC-TEST-001')
                ->set('cantidad', 1)
                ->call('store');

        $this->assertDatabaseHas('bienes', ['nombre' => $nombre]);
    }

    public function test_bien_creado_queda_en_tabla_bienes(): void
    {
        $bien = $this->crearBien(['nombre' => 'Bien Registrado Test']);

        $this->assertDatabaseHas('bienes', ['id' => $bien->id, 'nombre' => 'Bien Registrado Test']);
    }

    // ─── Editar bien ─────────────────────────────────────────────────────────

    public function test_bien_puede_editarse_campo_nombre(): void
    {
        $admin = $this->crearAdmin();
        $bien = $this->crearBien(['nombre' => 'Nombre Original']);

        // Admins/Rectores edit directly (no HMB created); mount requires bienId + campo
        Livewire::actingAs($admin)
                ->test(EditarCampoBien::class, ['bienId' => $bien->id, 'campo' => 'nombre'])
                ->set('valor', 'Nombre Actualizado')
                ->call('actualizar');

        $this->assertDatabaseHas('bienes', [
            'id'     => $bien->id,
            'nombre' => 'Nombre Actualizado',
        ]);
    }

    // ─── Solicitar modificación ───────────────────────────────────────────────

    public function test_solicitud_modificacion_crea_historial_pendiente(): void
    {
        $bien = $this->crearBienConDependencia();

        $hmb = $this->crearModificacionPendiente($bien, 'nombre', 'Nuevo Nombre');

        $this->assertEquals('pendiente', $hmb->estado);
        $this->assertDatabaseHas('historial_modificaciones_bienes', [
            'id'     => $hmb->id,
            'estado' => 'pendiente',
        ]);
    }
}
