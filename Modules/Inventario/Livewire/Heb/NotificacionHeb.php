<?php

namespace Modules\Inventario\Livewire\Heb;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Inventario\Entities\{
    Dependencia,
    Categoria,
    HistorialEliminacionBien
};

class NotificacionHeb extends Notification
{
    public $solicitud;

    public function __construct(HistorialEliminacionBien $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    public function via($notifiable)
    {
        return ['mail']; // o solo 'database', según el caso
    }

    public function toMail($notifiable)
    {
        $dependencia = $this->solicitud->dependencia;

        $usuario = $dependencia?->usuario;
        $nombreUsuario = $usuario ? trim("{$usuario->nombres} {$usuario->apellidos}") : 'Usuario desconocido';
        $dependenciaNombre = $dependencia?->nombre ?? 'Dependencia no encontrada';

        $bien = $this->solicitud->bien;
        $bienId = $this->solicitud->bien?->id;
        $nombreBien = $bien?->nombre ?? 'Bien no identificado';

        return (new MailMessage)
            ->subject("Inventario: Eliminación pendiente de {$nombreUsuario}")
            ->greeting("Hola,")
            ->line("El usuario **{$nombreUsuario}** ha solicitado eliminar el bien **{$nombreBien}** con ID **{$bienId}**.")
            ->line("**Dependencia:** {$dependenciaNombre}")
            ->action('Revisar solicitud', url('/inventario/heb'))
            ->line('Por favor, ingrese al sistema para revisar y aprobar o rechazar el cambio.');
    }


    /*
    public function toDatabase($notifiable)
    {
        return [
            'bien_id' => $this->solicitud->bien_id,
            'campo' => $this->solicitud->campo,
            'valor_anterior' => $this->solicitud->valor_anterior,
            'valor_nuevo' => $this->solicitud->valor_nuevo,
            'usuario' => $this->solicitud->user->name,
        ];
    }
    */
}
