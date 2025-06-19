<?php

namespace Modules\Inventario\Livewire\Bap;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Inventario\Entities\{
    Dependencia,
    Categoria,
    BienAprobacionPendiente
};

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
        $dependencia = $this->aprobacion->dependencia;

        $usuario = $dependencia?->usuario;
        $nombreUsuario = $usuario ? trim("{$usuario->nombres} {$usuario->apellidos}") : 'Usuario desconocido';
        $dependenciaNombre = $dependencia?->nombre ?? 'Dependencia no encontrada';

        $bien = $this->aprobacion->bien;
        $nombreBien = $bien?->nombre ?? 'Bien no identificado';

        $detallesCambios = '';

        $selectCampos = [
            'categoria_id' => Categoria::class,
            'dependencia_id' => Dependencia::class,
        ];

        if ($this->aprobacion->tipo_objeto === 'detalle') {
            // Cambios en detalles (varios campos)
            $valorAnterior = json_decode($this->aprobacion->valor_anterior, true) ?? [];
            $valorNuevo = json_decode($this->aprobacion->valor_nuevo, true) ?? [];

            foreach ($valorNuevo as $campo => $valor) {
                $anterior = $valorAnterior[$campo] ?? 'null';
                $nuevo = is_array($valor) ? ($valor['nuevo'] ?? 'null') : $valor;

                $detallesCambios .= "- **" . ucfirst(str_replace('_', ' ', $campo)) . "**: {$anterior} → {$nuevo}\n";
            }
        } elseif ($this->aprobacion->tipo_objeto === 'bien') {
            $campo = $this->aprobacion->campo;
            $nombreCampo = ucfirst(str_replace('_', ' ', $campo));

            $anterior = $this->aprobacion->valor_anterior ?? 'null';
            $nuevo = $this->aprobacion->valor_nuevo ?? 'null';

            // Si el campo es select, buscar el nombre correspondiente
            if (array_key_exists($campo, $selectCampos)) {
                $modelo = $selectCampos[$campo];

                $anteriorNombre = $anterior !== 'null' ? $modelo::find($anterior)?->nombre ?? "(ID: {$anterior})" : 'No asignado';
                $nuevoNombre = $nuevo !== 'null' ? $modelo::find($nuevo)?->nombre ?? "(ID: {$nuevo})" : 'No asignado';

                $anterior = $anteriorNombre;
                $nuevo = $nuevoNombre;
            }

            $detallesCambios = "- **{$nombreCampo}**: {$anterior} → {$nuevo}\n";
        }

        return (new MailMessage)
            ->subject("Inventario: Aprobación pendiente de {$nombreUsuario}")
            ->greeting("Hola,")
            ->line("El usuario **{$nombreUsuario}** ha solicitado un cambio en el bien **{$nombreBien}**.")
            ->line("**Dependencia:** {$dependenciaNombre}")
            ->line("**Cambios solicitados:**")
            ->line($detallesCambios ?: '- Sin cambios detectados')
            ->action('Revisar solicitud', url('/inventario/bap'))
            ->line('Por favor, ingrese al sistema para revisar y aprobar o rechazar el cambio.');
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
