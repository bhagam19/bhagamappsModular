<?php

namespace App\Http\Controllers\Inventario;

use App\Actions\Inventario\AsignarResponsableBienAction;
use App\Actions\Inventario\TransferirResponsableBienAction;
use App\Auth\Capacidad;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventario\StoreResponsableBienRequest;
use App\Http\Requests\Inventario\TransferirResponsableRequest;
use App\Models\Inventario\Bien;
use App\Models\User;
use App\ReadServices\Inventario\BienResponsableReadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use LogicException;

class BienResponsableController extends Controller
{
    public function __construct(
        private readonly BienResponsableReadService $readService,
        private readonly AsignarResponsableBienAction $asignar,
        private readonly TransferirResponsableBienAction $transferir,
    ) {
    }

    public function historial(Bien $bien): View
    {
        Gate::authorize(Capacidad::InventarioResponsablesVer->value);

        return view('inventario.responsables.historial', [
            'bien'       => $bien->load(['categoria', 'ubicacion']),
            'historial'  => $this->readService->historialPorBien($bien),
            'actual'     => $this->readService->responsableActual($bien),
        ]);
    }

    public function asignarForm(Bien $bien): View
    {
        Gate::authorize(Capacidad::InventarioResponsablesAsignar->value);

        if ($bien->responsableActual()->exists()) {
            abort(422, 'El bien ya tiene un responsable vigente.');
        }

        return view('inventario.responsables.asignar', [
            'bien'     => $bien->load(['categoria', 'ubicacion']),
            'usuarios' => $this->readService->usuariosParaSelector(),
        ]);
    }

    public function store(StoreResponsableBienRequest $request, Bien $bien): RedirectResponse
    {
        try {
            $this->asignar->execute($bien, $request->toData());
        } catch (LogicException $e) {
            return back()->withErrors(['user_id' => $e->getMessage()]);
        }

        return redirect()->route('inventario.bienes.responsable.historial', $bien)
            ->with('status', 'responsable-asignado');
    }

    public function transferirForm(Bien $bien): View
    {
        Gate::authorize(Capacidad::InventarioResponsablesTransferir->value);

        return view('inventario.responsables.transferir', [
            'bien'     => $bien->load(['categoria', 'ubicacion']),
            'actual'   => $this->readService->responsableActual($bien),
            'usuarios' => $this->readService->usuariosParaSelector(),
        ]);
    }

    public function transferirStore(TransferirResponsableRequest $request, Bien $bien): RedirectResponse
    {
        try {
            $this->transferir->execute($bien, $request->toData());
        } catch (LogicException $e) {
            return back()->withErrors(['user_id' => $e->getMessage()]);
        }

        return redirect()->route('inventario.bienes.responsable.historial', $bien)
            ->with('status', 'responsable-transferido');
    }

    public function porUsuario(User $usuario): View
    {
        Gate::authorize(Capacidad::InventarioResponsablesVer->value);

        return view('inventario.responsables.por-usuario', [
            'usuario' => $usuario,
            'bienes'  => $this->readService->bienesAsignados($usuario),
        ]);
    }
}
