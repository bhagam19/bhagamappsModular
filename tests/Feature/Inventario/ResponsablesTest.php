<?php

namespace Tests\Feature\Inventario;

use Livewire\Livewire;
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Entities\BienResponsable;
use Modules\Inventario\Livewire\Responsables\ResponsablesIndex;
use Modules\User\Entities\User;

/**
 * Fase 2 — Critical Business Flows: Responsables
 *
 * Cubre:
 *   - Asignar responsable a un bien
 *   - Transferir responsable
 *   - Retirar responsable (fecha_retiro)
 */
class ResponsablesTest extends InventarioTestCase
{
    // ─── Render ───────────────────────────────────────────────────────────────

    public function test_responsables_index_renderiza_sin_errores(): void
    {
        $admin = $this->crearAdmin();

        Livewire::actingAs($admin)
                ->test(ResponsablesIndex::class)
                ->assertStatus(200);
    }

    // ─── Tabla bienes_responsables ────────────────────────────────────────────

    public function test_tabla_bienes_responsables_existe(): void
    {
        $this->assertTrue(
            \Schema::hasTable('bienes_responsables'),
            'La tabla bienes_responsables debe existir'
        );
    }

    public function test_tabla_bienes_responsables_tiene_columnas_requeridas(): void
    {
        $columnas = \Schema::getColumnListing('bienes_responsables');

        foreach (['bien_id', 'user_id', 'fecha_asignacion', 'fecha_retiro'] as $col) {
            $this->assertContains($col, $columnas, "Columna '$col' requerida en bienes_responsables");
        }
    }

    // ─── Asignar responsable ──────────────────────────────────────────────────

    public function test_asignar_responsable_crea_registro_con_fecha_asignacion(): void
    {
        $admin = $this->crearAdmin();
        $bien = $this->crearBien();

        $responsable = BienResponsable::create([
            'bien_id'          => $bien->id,
            'user_id'          => $admin->id,
            'fecha_asignacion' => today()->toDateString(),
        ]);

        $this->assertDatabaseHas('bienes_responsables', [
            'id'               => $responsable->id,
            'bien_id'          => $bien->id,
            'user_id'          => $admin->id,
            'fecha_asignacion' => today()->toDateString(),
            'fecha_retiro'     => null,
        ]);
    }

    public function test_asignar_responsable_via_livewire(): void
    {
        $admin = $this->crearAdmin();
        $bien = $this->crearBien();

        Livewire::actingAs($admin)
                ->test(ResponsablesIndex::class)
                ->call('iniciarAsignacion', $bien->id)
                ->set('nuevoUserId', $admin->id)
                ->call('confirmarAsignacion');

        $this->assertDatabaseHas('bienes_responsables', [
            'bien_id' => $bien->id,
            'user_id' => $admin->id,
        ]);
    }

    // ─── Retirar responsable ──────────────────────────────────────────────────

    public function test_retirar_responsable_registra_fecha_retiro(): void
    {
        $admin = $this->crearAdmin();
        $bien = $this->crearBien();

        $responsable = BienResponsable::create([
            'bien_id'          => $bien->id,
            'user_id'          => $admin->id,
            'fecha_asignacion' => today()->toDateString(),
        ]);

        $responsable->update(['fecha_retiro' => today()->toDateString()]);

        $this->assertDatabaseHas('bienes_responsables', [
            'id'           => $responsable->id,
            'fecha_retiro' => today()->toDateString(),
        ]);
    }

    // ─── Transferir responsable ───────────────────────────────────────────────

    public function test_transferir_responsable_retira_anterior_y_asigna_nuevo(): void
    {
        $admin = $this->crearAdmin();
        $nuevoResponsable = $this->crearUsuarioConRol('Coordinador');
        $bien = $this->crearBien();

        // Asignar primer responsable
        $anterior = BienResponsable::create([
            'bien_id'          => $bien->id,
            'user_id'          => $admin->id,
            'fecha_asignacion' => today()->toDateString(),
        ]);

        // Transferir: retirar al anterior y asignar al nuevo
        $anterior->update(['fecha_retiro' => today()->toDateString()]);

        $nuevo = BienResponsable::create([
            'bien_id'          => $bien->id,
            'user_id'          => $nuevoResponsable->id,
            'fecha_asignacion' => today()->toDateString(),
        ]);

        // Anterior tiene fecha_retiro
        $this->assertNotNull(
            BienResponsable::find($anterior->id)->fecha_retiro,
            'El responsable anterior debe tener fecha_retiro al transferir'
        );

        // Nuevo responsable está activo (sin fecha_retiro)
        $this->assertNull(
            BienResponsable::find($nuevo->id)->fecha_retiro,
            'El nuevo responsable debe estar activo (fecha_retiro null)'
        );
    }

    // ─── Sin responsable activo ───────────────────────────────────────────────

    public function test_bien_sin_responsable_activo_no_tiene_asignaciones_vigentes(): void
    {
        $bien = $this->crearBien();

        $activos = BienResponsable::where('bien_id', $bien->id)
                                  ->whereNull('fecha_retiro')
                                  ->count();

        $this->assertEquals(0, $activos, 'Bien recién creado no debe tener responsables activos');
    }
}
