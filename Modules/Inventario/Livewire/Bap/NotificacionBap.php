<?php

namespace Modules\Inventario\Livewire\Bap;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Inventario\Entities\BienAprobacionPendiente;

class NotificacionBap extends Notification
{
    public $aprobacion;

    public function __construct(BienAprobacionPendiente $aprobacion)
    {
        $this->aprobacion = $aprobacion;
    }

    public function via($notifiable)
    {
        return ['mail']; // o solo 'database', según el caso
    }

    public function toMail($notifiable)
    {
        $usuario = $this->aprobacion->usuarioResponsable();

        $nombreUsuario = $usuario ? trim("{$usuario->nombres} {$usuario->apellidos}") : 'desconocido';

        return (new MailMessage)
            ->subject("Inventario: Aprobación pendiente. Usuario: {$nombreUsuario} ")
            ->line("El usuario {$nombreUsuario} ha solicitado un cambio en un bien.")
            ->action('Revisar', 'https://bhagamapps.com/inventario/bap');
    }
    /*
    public function toDatabase($notifiable)
    {
        return [
            'bien_id' => $this->aprobacion->bien_id,
            'campo' => $this->aprobacion->campo,
            'valor_anterior' => $this->aprobacion->valor_anterior,
            'valor_nuevo' => $this->aprobacion->valor_nuevo,
            'usuario' => $this->aprobacion->user->name,
        ];
    }
    */
}
