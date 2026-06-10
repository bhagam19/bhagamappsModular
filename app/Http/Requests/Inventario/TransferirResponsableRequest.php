<?php

namespace App\Http\Requests\Inventario;

use App\Auth\Capacidad;
use App\DTOs\Inventario\TransferirResponsableData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class TransferirResponsableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows(Capacidad::InventarioResponsablesTransferir->value);
    }

    public function rules(): array
    {
        return [
            'user_id'               => ['required', 'integer', 'exists:users,id'],
            'fecha_asignacion'      => ['required', 'date'],
            'fecha_retiro_anterior' => ['required', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id'               => 'nuevo responsable',
            'fecha_asignacion'      => 'fecha de asignación',
            'fecha_retiro_anterior' => 'fecha de retiro',
        ];
    }

    public function toData(): TransferirResponsableData
    {
        $v = $this->validated();

        return new TransferirResponsableData(
            user_id:               (int) $v['user_id'],
            fecha_asignacion:      $v['fecha_asignacion'],
            fecha_retiro_anterior: $v['fecha_retiro_anterior'],
        );
    }
}
