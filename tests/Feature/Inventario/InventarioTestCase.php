<?php

namespace Tests\Feature\Inventario;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Entities\Dependencia;
use Modules\Inventario\Entities\HistorialModificacionBien;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;
use Tests\TestCase;

/**
 * Base para todos los tests del módulo Inventario.
 *
 * Usa DatabaseTransactions en lugar de RefreshDatabase para no destruir
 * la base de datos de desarrollo (sin .env.testing / SQLite configurado).
 * Cada test se envuelve en una transacción que se revierte al finalizar.
 */
abstract class InventarioTestCase extends TestCase
{
    use DatabaseTransactions;

    /**
     * Crea un usuario transaccional con el rol indicado.
     * El rol debe existir en la BD real (Administrador, Rector, Coordinador, etc.).
     */
    protected function crearUsuarioConRol(string $roleName): User
    {
        $role = Role::where('nombre', $roleName)->firstOrFail();

        return User::create([
            'nombres'   => 'Test',
            'apellidos' => 'Inventario',
            'userID'    => 'TST-' . uniqid(),
            'email'     => 'tst.' . uniqid() . '@inventario.test',
            'password'  => bcrypt('test-password'),
            'role_id'   => $role->id,
        ]);
    }

    protected function crearAdmin(): User
    {
        return $this->crearUsuarioConRol('Administrador');
    }

    /**
     * Crea un Bien con todos los campos opcionales (tabla bienes: todos nullable).
     */
    protected function crearBien(array $overrides = []): Bien
    {
        return Bien::create(array_merge([
            'nombre'   => 'Bien de Prueba ' . uniqid(),
            'serie'    => 'SER-' . uniqid(),
            'cantidad' => 1,
        ], $overrides));
    }

    /**
     * Crea un HistorialModificacionBien pendiente.
     * Requiere un Bien con dependencia_id válido (historial.dependencia_id es NOT NULL).
     */
    protected function crearModificacionPendiente(Bien $bien, string $campo = 'nombre', string $valorNuevo = 'Nombre Modificado'): HistorialModificacionBien
    {
        $dependenciaId = $bien->dependencia_id ?? Dependencia::value('id');

        return HistorialModificacionBien::create([
            'bien_id'        => $bien->id,
            'tipo_objeto'    => 'bien',
            'campo'          => $campo,
            'valor_anterior' => $bien->$campo,
            'valor_nuevo'    => $valorNuevo,
            'dependencia_id' => $dependenciaId,
            'estado'         => 'pendiente',
        ]);
    }

    /**
     * Crea un Bien con dependencia_id, necesario para tests de HMB.
     */
    protected function crearBienConDependencia(): Bien
    {
        $dependencia = Dependencia::first();
        $this->assertNotNull($dependencia, 'La BD de desarrollo no tiene dependencias. Ejecute los seeders primero.');

        return $this->crearBien(['dependencia_id' => $dependencia->id]);
    }
}
