<?php

namespace Modules\AdminSistema\Livewire\Backups;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\ActivityLog\Services\ActivityLogger;
use Modules\AdminSistema\Services\BackupReaderService;

class RestaurarBackup extends Component
{
    // ── Listado ──────────────────────────────────────────────────────────────
    public array $backups = [];

    // ── Snapshot seleccionado ─────────────────────────────────────────────
    public string $fechaSeleccionada = '';
    public ?array $metaSeleccionada  = null;
    public int    $zipSize           = 0;

    // ── Confirmación (RESTORE-WEB-004) ────────────────────────────────────
    public string $confirmacion = '';

    // ── Resultado (RESTORE-WEB-008) ───────────────────────────────────────
    public bool   $exito        = false;
    public string $outputComando = '';

    // ── Máquina de estado ─────────────────────────────────────────────────
    // listado | vista-previa | confirmar | resultado
    public string $estado = 'listado';

    public function mount(): void
    {
        $this->autorizar();
        $this->cargarBackups();
    }

    // ── Acciones ──────────────────────────────────────────────────────────

    public function seleccionar(string $fecha): void
    {
        $this->autorizar();

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return;
        }

        $zipPath = base_path("backups/IEE-{$fecha}.zip");
        if (!file_exists($zipPath)) {
            return;
        }

        $this->fechaSeleccionada = $fecha;
        $this->metaSeleccionada  = BackupReaderService::leerMetadata($fecha);
        $this->zipSize           = (int) filesize($zipPath);
        $this->confirmacion      = '';
        $this->estado            = 'vista-previa';
    }

    public function irAConfirmar(): void
    {
        $this->autorizar();
        $this->confirmacion = '';
        $this->estado       = 'confirmar';
    }

    public function cancelar(): void
    {
        $this->fechaSeleccionada = '';
        $this->metaSeleccionada  = null;
        $this->zipSize           = 0;
        $this->confirmacion      = '';
        $this->estado            = 'listado';
    }

    public function ejecutarRestauracion(): void
    {
        $this->autorizar();

        if (trim($this->confirmacion) !== 'RESTAURAR') {
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->fechaSeleccionada)) {
            return;
        }

        $zipPath = base_path("backups/IEE-{$this->fechaSeleccionada}.zip");
        if (!file_exists($zipPath)) {
            $this->exito        = false;
            $this->outputComando = 'El archivo ZIP no existe en el servidor.';
            $this->estado       = 'resultado';
            $this->registrarAuditoria(false, 'ZIP no encontrado');
            return;
        }

        $exitCode = Artisan::call('backup:restore-from-zip', [
            '--file'  => "backups/IEE-{$this->fechaSeleccionada}.zip",
            '--force' => true,
        ]);

        $this->exito         = ($exitCode === 0);
        $this->outputComando = Artisan::output();
        $this->estado        = 'resultado';

        $this->registrarAuditoria(
            $this->exito,
            $this->exito
                ? 'EXITOSA desde CAB (exit=0)'
                : "FALLIDA desde CAB (exit={$exitCode})"
        );
        ActivityLogger::log(
            modulo:      'Backups',
            accion:      'restaurar',
            descripcion: ($this->exito ? 'Restauración exitosa' : 'Restauración fallida') . ": IEE-{$this->fechaSeleccionada}.zip",
            tipoObjeto:  'Snapshot',
            datosNuevos: ['exito' => $this->exito, 'origen' => 'CAB-WEB'],
        );
    }

    public function resetear(): void
    {
        $this->fechaSeleccionada = '';
        $this->metaSeleccionada  = null;
        $this->zipSize           = 0;
        $this->confirmacion      = '';
        $this->exito             = false;
        $this->outputComando     = '';
        $this->estado            = 'listado';
        $this->cargarBackups();
    }

    public function render(): View
    {
        return view('adminsistema::livewire.backups.restaurar-backup');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function formatSize(int $bytes): string
    {
        return BackupReaderService::formatSize($bytes);
    }

    private function cargarBackups(): void
    {
        $this->backups = BackupReaderService::listar();
    }

    // RESTORE-WEB-005: permiso + es_principal obligatorio
    private function autorizar(): void
    {
        $user = Auth::user();
        if (!$user
            || !$user->hasPermission('restaurar-backups')
            || !$user->isAdminPrincipal()
        ) {
            abort(403, 'Solo el Administrador Principal puede restaurar respaldos.');
        }
    }

    // RESTORE-WEB-007: auditoría con usuario, snapshot y resultado
    private function registrarAuditoria(bool $exito, string $detalle): void
    {
        $user = Auth::user();

        $entrada = json_encode([
            'fecha'      => now()->format('Y-m-d H:i:s'),
            'origen'     => 'CAB-WEB',
            'usuario_id' => $user?->id,
            'usuario'    => $user ? trim("{$user->nombres} {$user->apellidos}") : 'desconocido',
            'backup'     => "IEE-{$this->fechaSeleccionada}.zip",
            'resultado'  => $exito ? 'EXITOSA' : 'FALLIDA',
            'detalle'    => $detalle,
        ], JSON_UNESCAPED_UNICODE);

        file_put_contents(
            storage_path('logs/restore.log'),
            $entrada . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}
