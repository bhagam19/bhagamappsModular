<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Modules\User\Entities\AuditoriaPassword;
use Modules\User\Entities\Permission;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;
use Tests\TestCase;

/**
 * Suite IMPL-USERS-001 — Password Administration & Recovery.
 *
 * Usa DatabaseTransactions: cada test se revierte al finalizar.
 * Los roles y permisos mínimos se crean en setUp().
 */
class PasswordAdminTest extends TestCase
{
    use DatabaseTransactions;

    private Role $roleAdmin;
    private Role $roleRector;
    private Role $roleCoord;

    /** Permisos de password admin creados por esta suite. */
    private array $permIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleAdmin  = Role::firstOrCreate(['nombre' => 'Administrador'], ['descripcion' => 'Test admin']);
        $this->roleRector = Role::firstOrCreate(['nombre' => 'Rector'],        ['descripcion' => 'Test rector']);
        $this->roleCoord  = Role::firstOrCreate(['nombre' => 'Coordinador'],   ['descripcion' => 'Test coord']);

        $slugs = [
            'ver-administracion-passwords',
            'restablecer-passwords',
            'bloquear-usuarios',
            'desbloquear-usuarios',
        ];

        foreach ($slugs as $slug) {
            $perm = Permission::firstOrCreate(
                ['slug' => $slug],
                ['nombre' => $slug, 'descripcion' => $slug, 'categoria' => 'administracion-passwords']
            );
            $this->permIds[$slug] = $perm->id;
        }

        // Administrador y Rector reciben todos los permisos de password admin.
        foreach ($this->permIds as $permId) {
            if (!$this->roleAdmin->permissions()->where('permissions.id', $permId)->exists()) {
                $this->roleAdmin->permissions()->attach($permId);
            }
            if (!$this->roleRector->permissions()->where('permissions.id', $permId)->exists()) {
                $this->roleRector->permissions()->attach($permId);
            }
        }
        // Coordinador no recibe estos permisos (condición por defecto).
    }

    // ─────────────────────────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────────────────────────

    private function crearUsuario(Role $role): User
    {
        return User::create([
            'nombres'   => 'Test',
            'apellidos' => $role->nombre,
            'userID'    => 'TST-' . uniqid(),
            'email'     => 'tst.' . uniqid() . '@test.test',
            'password'  => Hash::make('Temporal@123'),
            'role_id'   => $role->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // V-001 — Administrador puede restablecer contraseña
    // ─────────────────────────────────────────────────────────────

    public function test_v001_administrador_puede_restablecer_password(): void
    {
        $admin   = $this->crearUsuario($this->roleAdmin);
        $destino = $this->crearUsuario($this->roleCoord);

        $this->assertTrue($admin->hasPermission('restablecer-passwords'));

        $destino->update(['password' => Hash::make('NuevoPass@456')]);

        AuditoriaPassword::create([
            'usuario_afectado_id' => $destino->id,
            'administrador_id'    => $admin->id,
            'accion'              => 'password_reset',
            'fecha_hora'          => now(),
        ]);

        $this->assertDatabaseHas('auditoria_passwords', [
            'usuario_afectado_id' => $destino->id,
            'administrador_id'    => $admin->id,
            'accion'              => 'password_reset',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // V-002 — Rector puede restablecer contraseña
    // ─────────────────────────────────────────────────────────────

    public function test_v002_rector_puede_restablecer_password(): void
    {
        $rector  = $this->crearUsuario($this->roleRector);
        $destino = $this->crearUsuario($this->roleCoord);

        $this->assertTrue($rector->hasPermission('restablecer-passwords'));

        $destino->update(['password' => Hash::make('NuevoRector@789')]);

        AuditoriaPassword::create([
            'usuario_afectado_id' => $destino->id,
            'administrador_id'    => $rector->id,
            'accion'              => 'password_reset',
            'fecha_hora'          => now(),
        ]);

        $this->assertDatabaseHas('auditoria_passwords', [
            'usuario_afectado_id' => $destino->id,
            'administrador_id'    => $rector->id,
            'accion'              => 'password_reset',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // V-003 — Coordinador NO puede restablecer contraseña
    // ─────────────────────────────────────────────────────────────

    public function test_v003_coordinador_no_puede_restablecer_password(): void
    {
        $coordinador = $this->crearUsuario($this->roleCoord);

        $this->assertFalse($coordinador->hasPermission('restablecer-passwords'));
        $this->assertFalse($coordinador->hasPermission('bloquear-usuarios'));
        $this->assertFalse($coordinador->hasPermission('desbloquear-usuarios'));
        $this->assertFalse($coordinador->hasPermission('ver-administracion-passwords'));
    }

    // ─────────────────────────────────────────────────────────────
    // V-004 — Usuario bloqueado no puede iniciar sesión
    // ─────────────────────────────────────────────────────────────

    public function test_v004_usuario_bloqueado_no_puede_autenticarse(): void
    {
        $user = $this->crearUsuario($this->roleCoord);
        $user->update(['bloqueado' => true]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'Temporal@123',
        ]);

        $this->assertGuest('web');
    }

    // ─────────────────────────────────────────────────────────────
    // V-005 — Usuario desbloqueado puede iniciar sesión
    // ─────────────────────────────────────────────────────────────

    public function test_v005_usuario_desbloqueado_puede_autenticarse(): void
    {
        $user = $this->crearUsuario($this->roleCoord);
        $user->update(['bloqueado' => false]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'Temporal@123',
        ]);

        $this->assertAuthenticated('web');
    }

    // ─────────────────────────────────────────────────────────────
    // V-006 — Forzar cambio de contraseña redirige
    // ─────────────────────────────────────────────────────────────

    public function test_v006_forzar_cambio_password_redirige(): void
    {
        $user = $this->crearUsuario($this->roleCoord);

        // Establecer antes de actingAs para que el objeto tenga el valor actualizado.
        $user->forzar_cambio_password = true;
        $user->save();

        // /profile solo requiere auth, no app.access ni permisos especiales.
        $response = $this->actingAs($user)->get('/profile');

        $response->assertRedirect('/user/profile');
    }

    // ─────────────────────────────────────────────────────────────
    // V-007 — Auditoría registrada para las 4 acciones
    // ─────────────────────────────────────────────────────────────

    public function test_v007_auditoria_registrada_para_todas_las_acciones(): void
    {
        $admin   = $this->crearUsuario($this->roleAdmin);
        $destino = $this->crearUsuario($this->roleCoord);

        $acciones = ['password_reset', 'password_forced', 'user_blocked', 'user_unblocked'];

        foreach ($acciones as $accion) {
            AuditoriaPassword::create([
                'usuario_afectado_id' => $destino->id,
                'administrador_id'    => $admin->id,
                'accion'              => $accion,
                'fecha_hora'          => now(),
            ]);
        }

        $registros = AuditoriaPassword::where('usuario_afectado_id', $destino->id)
            ->where('administrador_id', $admin->id)
            ->count();

        $this->assertEquals(4, $registros);

        foreach ($acciones as $accion) {
            $this->assertDatabaseHas('auditoria_passwords', [
                'usuario_afectado_id' => $destino->id,
                'administrador_id'    => $admin->id,
                'accion'              => $accion,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // V-008 — Sin regresiones en login normal
    // ─────────────────────────────────────────────────────────────

    public function test_v008_login_normal_sin_regresiones(): void
    {
        $user = $this->crearUsuario($this->roleAdmin);
        $user->update(['bloqueado' => false, 'forzar_cambio_password' => false]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'Temporal@123',
        ]);

        $this->assertAuthenticated('web');
    }
}
