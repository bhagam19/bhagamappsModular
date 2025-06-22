<?php

namespace Modules\Inventario\Livewire\Hmb;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Inventario\Entities\{
    Dependencia,
    Categoria,
    HistorialModificacionBien
};

class NotificacionHmb extends Notification
{
    public $modificacion;

    public function __construct(HistorialModificacionBien $modificacion)
    {
        $this->modificacion = $modificacion;
    }

    public function via($notifiable)
    {
        return ['mail']; // o solo 'database', según el caso
    }

    public function toMail($notifiable)
    {
        $dependencia = $this->modificacion->dependencia;

        $usuario = $dependencia?->usuario;
        $nombreUsuario = $usuario ? trim("{$usuario->nombres} {$usuario->apellidos}") : 'Usuario desconocido';
        $dependenciaNombre = $dependencia?->nombre ?? 'Dependencia no encontrada';

        $bien = $this->modificacion->bien;
        $bienId = $this->modificacion->bien?->id;
        $nombreBien = $bien?->nombre ?? 'Bien no identificado';

        $detallesCambios = '';

        $selectCampos = [
            'categoria_id' => Categoria::class,
            'dependencia_id' => Dependencia::class,
        ];

        if ($this->modificacion->tipo_objeto === 'detalle') {
            // Cambios en detalles (varios campos)
            $valorAnterior = json_decode($this->modificacion->valor_anterior, true) ?? [];
            $valorNuevo = json_decode($this->modificacion->valor_nuevo, true) ?? [];

            foreach ($valorNuevo as $campo => $valor) {
                $anterior = $valorAnterior[$campo] ?? 'null';
                $nuevo = is_array($valor) ? ($valor['nuevo'] ?? 'null') : $valor;

                $detallesCambios .= "- **" . ucfirst(str_replace('_', ' ', $campo)) . "**: {$anterior} → {$nuevo}\n";
            }
        } elseif ($this->modificacion->tipo_objeto === 'bien') {
            $campo = $this->modificacion->campo;
            $nombreCampo = ucfirst(str_replace('_', ' ', $campo));

            $anterior = $this->modificacion->valor_anterior ?? 'null';
            $nuevo = $this->modificacion->valor_nuevo ?? 'null';

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
            ->line("El usuario **{$nombreUsuario}** ha solicitado un cambio en el bien **{$nombreBien}** con ID **{$bienId}**.")
            ->line("**Dependencia:** {$dependenciaNombre}")
            ->line("**Cambios solicitados:**")
            ->line($detallesCambios ?: '- Sin cambios detectados')
            ->action('Revisar solicitud', url('/inventario/h'))
            ->line('Por favor, ingrese al sistema para revisar y aprobar o rechazar el cambio.');
    }


    /*
    public function toDatabase($notifiable)
    {
        return [
            'bien_id' => $this->modificacion->bien_id,
            'campo' => $this->modificacion->campo,
            'valor_anterior' => $this->modificacion->valor_anterior,
            'valor_nuevo' => $this->modificacion->valor_nuevo,
            'usuario' => $this->modificacion->user->name,
        ];
    }
    */
}
