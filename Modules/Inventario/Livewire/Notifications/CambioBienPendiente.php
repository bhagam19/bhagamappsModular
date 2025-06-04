<?php

namespace Modules\Inventario\Livewire\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Modules\Inventario\Entities\BienAprobacionPendiente;
use Modules\Inventario\Entities\Bien;

class CambioBienPendiente extends Notification
{
    use Queueable;

    protected BienAprobacionPendiente $cambio;

    public function __construct(BienAprobacionPendiente $cambio)
    {
        $this->cambio = $cambio;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $bien = Bien::find($this->cambio->bien_id);

        return [
            'titulo'        => 'Nuevo cambio pendiente por aprobar',
            'bien_id'       => $this->cambio->bien_id,
            'bien_nombre'   => $bien?->nombre ?? 'Bien desconocido',
            'campo'         => $this->cambio->campo,
            'valor_nuevo'   => $this->cambio->valor_nuevo,
            'valor_anterior'=> $this->cambio->valor_anterior,
            'usuario'       => $this->cambio->user->nombres ?? 'Usuario desconocido',
            'cambio_id'     => $this->cambio->id,
            'url'           => route('inventario.cambios-pendientes'), // Ajusta si ya tienes una vista
        ];
    }
}
