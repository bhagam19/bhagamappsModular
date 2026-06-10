<?php

namespace App\Http\Requests\Inventario;

use App\Auth\Capacidad;
use App\DTOs\Inventario\AsignarResponsableData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreResponsableBienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows(Capacidad::InventarioResponsablesAsignar->value);
    }

    public function rules(): array
    {
        return [
            'user_id'          => ['required', 'integer', 'exists:users,id'],
            'fecha_asignacion' => ['required', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id'          => 'responsable',
            'fecha_asignacion' => 'fecha de asignación',
        ];
    }

    public function toData(): AsignarResponsableData
    {
        $v = $this->validated();

        return new AsignarResponsableData(
            user_id:          (int) $v['user_id'],
            fecha_asignacion: $v['fecha_asignacion'],
        );
    }
}
