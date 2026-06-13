<?php

namespace Modules\AdminSistema\Livewire\Backups;

use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Modules\AdminSistema\Jobs\GenerarBackupJob;
use Modules\AdminSistema\Services\BackupReaderService;

class BackupDashboard extends Component
{
    public array  $backups       = [];
    public ?array $ultimoBackup  = null;
    public string $alerta        = 'verde';
    public string $proximaEjec   = '';
    public bool   $generando     = false;
    public string $mensaje       = '';
    public string $estadoMensaje = '';

    public function mount(): void
    {
        $this->cargarDatos();
    }

    public function generarBackup(): void
    {
        if (!Auth::user()->hasPermission('generar-backups')) {
            abort(403);
        }

        $this->generando     = true;
        $this->mensaje       = '';
        $this->estadoMensaje = '';

        try {
            dispatch(new GenerarBackupJob());
            $this->mensaje       = 'Respaldo generado exitosamente.';
            $this->estadoMensaje = 'success';
        } catch (\Throwable $e) {
            $this->mensaje       = 'Error al generar el respaldo: ' . $e->getMessage();
            $this->estadoMensaje = 'danger';
        } finally {
            $this->generando = false;
            $this->cargarDatos();
        }
    }

    public function cargarDatos(): void
    {
        $this->backups      = BackupReaderService::listar();
        $this->ultimoBackup = BackupReaderService::ultimoBackup();
        $this->alerta       = BackupReaderService::estadoAlerta($this->ultimoBackup);
        $this->proximaEjec  = BackupReaderService::proximaEjecucion();
    }

    public function formatSize(int $bytes): string
    {
        return BackupReaderService::formatSize($bytes);
    }

    public function render(): View
    {
        return view('adminsistema::livewire.backups.backup-dashboard');
    }
}
