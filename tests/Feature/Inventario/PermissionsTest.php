<?php

namespace Tests\Feature\Inventario;

/**
 * Fase 1 — Authorization Tests
 *
 * Verifica que las capas de seguridad del módulo Inventario funcionen correctamente:
 *   - Middleware `auth` → redirige a login si no autenticado
 *   - Middleware `app.access:inventario` → 403 si el rol no tiene acceso al módulo
 *   - Middleware `permission:{slug}` → 403 si el usuario no tiene el permiso específico
 *
 * Roles con acceso a inventario: Administrador, Rector, Coordinador
 * Roles sin acceso a inventario: Docente, Auxiliar, Estudiante, Invitado
 */
class PermissionsTest extends InventarioTestCase
{
    // ─── auth middleware ──────────────────────────────────────────────────────

    public function test_invitado_es_redirigido_a_login_desde_bienes(): void
    {
        $this->get('/inventario/bienes')->assertRedirect('/login');
    }

    public function test_invitado_es_redirigido_a_login_desde_hmb(): void
    {
        $this->get('/inventario/hmb')->assertRedirect('/login');
    }

    public function test_invitado_es_redirigido_a_login_desde_heb(): void
    {
        $this->get('/inventario/heb')->assertRedirect('/login');
    }

    // ─── app.access:inventario ────────────────────────────────────────────────

    public function test_usuario_sin_acceso_inventario_obtiene_403_en_bienes(): void
    {
        $docente = $this->crearUsuarioConRol('Docente');

        $this->actingAs($docente)
             ->get('/inventario/bienes')
             ->assertForbidden();
    }

    public function test_usuario_sin_acceso_inventario_obtiene_403_en_hmb(): void
    {
        $docente = $this->crearUsuarioConRol('Docente');

        $this->actingAs($docente)
             ->get('/inventario/hmb')
             ->assertForbidden();
    }

    // ─── permission:ver-bienes ────────────────────────────────────────────────

    public function test_coordinador_puede_ver_bienes(): void
    {
        $coordinador = $this->crearUsuarioConRol('Coordinador');

        $this->actingAs($coordinador)
             ->get('/inventario/bienes')
             ->assertOk();
    }

    public function test_administrador_puede_ver_bienes(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
             ->get('/inventario/bienes')
             ->assertOk();
    }

    public function test_rector_puede_ver_bienes(): void
    {
        $rector = $this->crearUsuarioConRol('Rector');

        $this->actingAs($rector)
             ->get('/inventario/bienes')
             ->assertOk();
    }

    // ─── permission:gestionar-historial-modificaciones-bienes ─────────────────

    public function test_coordinador_no_puede_ver_hmb(): void
    {
        $coordinador = $this->crearUsuarioConRol('Coordinador');

        $this->actingAs($coordinador)
             ->get('/inventario/hmb')
             ->assertForbidden();
    }

    public function test_administrador_puede_ver_hmb(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
             ->get('/inventario/hmb')
             ->assertOk();
    }

    public function test_rector_puede_ver_hmb(): void
    {
        $rector = $this->crearUsuarioConRol('Rector');

        $this->actingAs($rector)
             ->get('/inventario/hmb')
             ->assertOk();
    }

    // ─── permission:gestionar-historial-eliminaciones-bienes ──────────────────

    public function test_coordinador_no_puede_ver_heb(): void
    {
        $coordinador = $this->crearUsuarioConRol('Coordinador');

        $this->actingAs($coordinador)
             ->get('/inventario/heb')
             ->assertForbidden();
    }

    public function test_administrador_puede_ver_heb(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
             ->get('/inventario/heb')
             ->assertOk();
    }

    // ─── permission:ver-responsables-bienes ───────────────────────────────────

    public function test_administrador_puede_ver_responsables(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
             ->get('/inventario/responsables')
             ->assertOk();
    }

    public function test_coordinador_puede_ver_responsables(): void
    {
        $coordinador = $this->crearUsuarioConRol('Coordinador');

        $this->actingAs($coordinador)
             ->get('/inventario/responsables')
             ->assertOk();
    }

    // ─── permission:ver-historial-ubicaciones-bienes ──────────────────────────

    public function test_administrador_puede_ver_historial_ubicaciones(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
             ->get('/inventario/ubicaciones/historial')
             ->assertOk();
    }

    public function test_coordinador_puede_ver_historial_ubicaciones(): void
    {
        $coordinador = $this->crearUsuarioConRol('Coordinador');

        $this->actingAs($coordinador)
             ->get('/inventario/ubicaciones/historial')
             ->assertOk();
    }
}
