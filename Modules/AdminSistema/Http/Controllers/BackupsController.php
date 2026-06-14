<?php

namespace Modules\AdminSistema\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\ActivityLog\Services\ActivityLogger;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupsController extends Controller
{
    public function index(): \Illuminate\Contracts\View\View
    {
        return view('adminsistema::backups.index');
    }

    public function detalle(string $fecha): \Illuminate\Contracts\View\View
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            abort(404);
        }

        return view('adminsistema::backups.detalle', compact('fecha'));
    }

    public function descargar(string $fecha): BinaryFileResponse
    {
        if (!auth()->user()->hasPermission('descargar-backups')) {
            abort(403);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            abort(404);
        }

        $zipPath = base_path("backups/IEE-{$fecha}.zip");

        if (!file_exists($zipPath)) {
            abort(404, 'El respaldo no existe.');
        }

        ActivityLogger::log(
            modulo:      'Backups',
            accion:      'descargar',
            descripcion: "Snapshot descargado: IEE-{$fecha}.zip",
            tipoObjeto:  'Snapshot',
        );

        return response()->download($zipPath);
    }
}
