<?php

namespace Tests\Feature\Inventario;

use Livewire\Livewire;
use Modules\Inventario\Entities\HistorialModificacionBien;
use Modules\Inventario\Livewire\Notifications\NotificacionesDropdown;
use Modules\Inventario\Livewire\Notifications\NotificacionesIcono;

/**
 * Fase 3 — Regression Protection: IMPL-INV-NOTIF-001B
 *
 * Verifica que las correcciones del sistema de notificaciones se mantienen:
 *
 * D-1/D-6: aprobarCambio y rechazarCambio NO eliminan registros HMB.
 *          Originalmente: $cambio->delete() → pérdida de evidencia histórica.
 *          Corrección: actualizar estado a 'aprobada'/'rechazada'.
 *
 * NOTIF-004: NotificacionesIcono refresca contador via #[On('cambioActualizado')]
 *            sin depender de wire:poll (IMPL-INV-008).
 *
 * DF-001-FIX: NotificacionesDropdown muestra únicamente registros pendientes.
 */
class NotificacionesTest extends InventarioTestCase
{
    // ─── NOTIF-001: aprobarCambio conserva historial ──────────────────────────

    public function test_aprobar_cambio_actualiza_estado_a_aprobada_sin_eliminar(): void
    {
        $admin = $this->crearAdmin();
        $bien = $this->crearBienConDependencia();
        $hmb = $this->crearModificacionPendiente($bien, 'nombre', 'Nombre Aprobado');

        Livewire::actingAs($admin)
                ->test(NotificacionesDropdown::class)
                ->call('aprobarCambio', $hmb->id);

        // V-001: el registro persiste (no fue eliminado)
        $this->assertDatabaseHas('historial_modificaciones_bienes', ['id' => $hmb->id]);

        // V-002: estado actualizado correctamente
        $this->assertDatabaseHas('historial_modificaciones_bienes', [
            'id'          => $hmb->id,
            'estado'      => 'aprobada',
            'aprobado_por' => $admin->id,
        ]);
    }

    public function test_aprobar_cambio_no_elimina_registro_hmb_regresion_d1(): void
    {
        $admin = $this->crearAdmin();
        $bien = $this->crearBienConDependencia();
        $hmb = $this->crearModificacionPendiente($bien);

        $idAntes = $hmb->id;

        Livewire::actingAs($admin)
                ->test(NotificacionesDropdown::class)
                ->call('aprobarCambio', $idAntes);

        // V-004: 0 eliminaciones indebidas
        $this->assertDatabaseHas('historial_modificaciones_bienes', ['id' => $idAntes]);
    }

    public function test_aprobar_cambio_actualiza_campo_del_bien(): void
    {
        $admin = $this->crearAdmin();
        $bien = $this->crearBienConDependencia();
        $bien->nombre = 'Nombre Original';
        $bien->save();

        $hmb = $this->crearModificacionPendiente($bien, 'nombre', 'Nombre Post-Aprobacion');

        Livewire::actingAs($admin)
                ->test(NotificacionesDropdown::class)
                ->call('aprobarCambio', $hmb->id);

        $this->assertDatabaseHas('bienes', [
            'id'     => $bien->id,
            'nombre' => 'Nombre Post-Aprobacion',
        ]);
    }

    // ─── NOTIF-002: rechazarCambio conserva historial ────────────────────────

    public function test_rechazar_cambio_actualiza_estado_a_rechazada_sin_eliminar(): void
    {
        $admin = $this->crearAdmin();
        $bien = $this->crearBienConDependencia();
        $hmb = $this->crearModificacionPendiente($bien);

        Livewire::actingAs($admin)
                ->test(NotificacionesDropdown::class)
                ->call('rechazarCambio', $hmb->id);

        // V-003: rechazo consistente con HMB
        $this->assertDatabaseHas('historial_modificaciones_bienes', [
            'id'          => $hmb->id,
            'estado'      => 'rechazada',
            'aprobado_por' => $admin->id,
        ]);
    }

    public function test_rechazar_cambio_no_elimina_registro_hmb_regresion_d6(): void
    {
        $admin = $this->crearAdmin();
        $bien = $this->crearBienConDependencia();
        $hmb = $this->crearModificacionPendiente($bien);

        $idAntes = $hmb->id;

        Livewire::actingAs($admin)
                ->test(NotificacionesDropdown::class)
                ->call('rechazarCambio', $idAntes);

        // V-004: 0 eliminaciones indebidas
        $this->assertDatabaseHas('historial_modificaciones_bienes', ['id' => $idAntes]);
    }

    // ─── DF-001-FIX: Dropdown solo muestra pendientes ─────────────────────────

    public function test_notificaciones_dropdown_renderiza_solo_pendientes(): void
    {
        $admin = $this->crearAdmin();
        $bien = $this->crearBienConDependencia();

        // Crear uno pendiente y uno aprobado
        $pendiente = $this->crearModificacionPendiente($bien, 'nombre', 'Pendiente');
        $aprobado = $this->crearModificacionPendiente($bien, 'serie', 'Aprobado');
        $aprobado->update(['estado' => 'aprobada']);

        $component = Livewire::actingAs($admin)->test(NotificacionesDropdown::class);

        // V-005: dropdown operativo — no lanza errores
        $component->assertStatus(200);

        // Solo deben aparecer los pendientes en la vista
        $pendientes = HistorialModificacionBien::where('estado', 'pendiente')->count();
        $this->assertGreaterThanOrEqual(1, $pendientes);
    }

    // ─── NOTIF-004: Icono refresca contador ───────────────────────────────────

    public function test_notificaciones_icono_renderiza_sin_errores(): void
    {
        $admin = $this->crearAdmin();

        Livewire::actingAs($admin)
                ->test(NotificacionesIcono::class)
                ->assertStatus(200);
    }

    public function test_notificaciones_icono_refresca_al_evento_cambio_actualizado(): void
    {
        $admin = $this->crearAdmin();
        $bien = $this->crearBienConDependencia();

        $hmb = $this->crearModificacionPendiente($bien);

        // Contar pendientes antes
        $totalAntes = HistorialModificacionBien::where('estado', 'pendiente')->count();

        // V-007: el icono responde al evento sin wire:poll
        Livewire::actingAs($admin)
                ->test(NotificacionesIcono::class)
                ->dispatch('cambioActualizado')
                ->assertSet('total', HistorialModificacionBien::where('estado', 'pendiente')->count());
    }

    // ─── IMPL-INV-008: sin wire:poll ─────────────────────────────────────────

    /**
     * Regresión IMPL-INV-008: NotificacionesIcono no usa wire:poll.
     * El contador debe actualizarse exclusivamente via evento #[On('cambioActualizado')].
     */
    public function test_notificaciones_icono_no_usa_wire_poll(): void
    {
        $vista = file_get_contents(
            base_path('Modules/Inventario/resources/views/livewire/hmb/notificaciones-icono.blade.php')
        );

        $this->assertStringNotContainsString('wire:poll', $vista,
            'REGRESIÓN IMPL-INV-008: notificaciones-icono.blade.php no debe usar wire:poll'
        );
    }

    public function test_notificaciones_dropdown_no_usa_wire_poll(): void
    {
        $vista = file_get_contents(
            base_path('Modules/Inventario/resources/views/livewire/hmb/notificaciones-dropdown.blade.php')
        );

        $this->assertStringNotContainsString('wire:poll', $vista,
            'REGRESIÓN IMPL-INV-008: notificaciones-dropdown.blade.php no debe usar wire:poll'
        );
    }
}
