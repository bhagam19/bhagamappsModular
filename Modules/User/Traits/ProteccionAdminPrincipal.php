<?php

namespace Modules\User\Traits;

use Modules\User\Entities\AuditoriaPassword;
use Modules\User\Entities\User;

trait ProteccionAdminPrincipal
{
    /**
     * Aborta si el usuario objetivo es el Administrador Principal.
     * Registra el intento en auditoría antes de abortar.
     */
    protected function verificarNoEsAdminPrincipal(User $target, string $accion = 'intento_modificar_admin_principal'): void
    {
        if (! $target->isAdminPrincipal()) {
            return;
        }

        AuditoriaPassword::create([
            'usuario_afectado_id' => $target->id,
            'administrador_id'    => auth()->id(),
            'accion'              => $accion,
            'fecha_hora'          => now(),
        ]);

        abort(403, 'El Administrador Principal no puede ser modificado.');
    }
}
