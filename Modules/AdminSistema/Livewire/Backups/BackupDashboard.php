<?php

namespace Modules\AdminSistema\Livewire\Backups;

use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Modules\AdminSistema\Jobs\GenerarBackupJob;
use Modules\AdminSistema\Jobs\SincronizarDriveJob;
use Modules\AdminSistema\Services\BackupReaderService;
use Modules\AdminSistema\Services\DriveService;

class BackupDashboard extends Component
{
    // ── Backup local ─────────────────────────────────────────────────────────
    public array  $backups       = [];
    public ?array $ultimoBackup  = null;
    public string $alerta        = 'verde';
    public string $proximaEjec   = '';
    public bool   $generando     = false;
    public string $mensaje       = '';
    public string $estadoMensaje = '';

    // ── Google Drive (DRIVE-003/004/007/008/010) ──────────────────────────
    public array  $estadoDrive        = [];
    public ?array $ultimaSync         = null;
    public string $alertaDrive        = 'rojo';
    public array  $historialDrive     = [];
    public int    $conteoBackupsDrive = 0;
    public bool   $sincronizando      = false;
    public string $mensajeDrive       = '';
    public string $estadoMensajeDrive = '';

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
        // Backup local
        $this->backups      = BackupReaderService::listar();
        $this->ultimoBackup = BackupReaderService::ultimoBackup();
        $this->alerta       = BackupReaderService::estadoAlerta($this->ultimoBackup);
        $this->proximaEjec  = BackupReaderService::proximaEjecucion();

        // Google Drive
        $this->estadoDrive        = DriveService::estadoConexion();
        $this->ultimaSync         = DriveService::ultimaSync();
        $this->alertaDrive        = DriveService::estadoAlerta($this->ultimaSync);
        $this->historialDrive     = DriveService::historial(5);
        $this->conteoBackupsDrive = DriveService::conteoBackupsDrive();
    }

    // ── DRIVE-005: Sincronización manual ─────────────────────────────────────

    public function sincronizarDrive(): void
    {
        if (!Auth::user()->hasPermission('sincronizar-backup-drive')) {
            abort(403);
        }

        $this->sincronizando      = true;
        $this->mensajeDrive       = '';
        $this->estadoMensajeDrive = '';

        try {
            dispatch(new SincronizarDriveJob());
            $this->mensajeDrive       = 'Sincronización con Google Drive completada.';
            $this->estadoMensajeDrive = 'success';
        } catch (\Throwable $e) {
            $this->mensajeDrive       = 'Error al sincronizar: ' . $e->getMessage();
            $this->estadoMensajeDrive = 'danger';
        } finally {
            $this->sincronizando = false;
            $this->cargarDatos();
        }
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
