<?php

namespace Modules\AdminSistema\Livewire\Backups;

use Livewire\Component;
use Illuminate\Contracts\View\View;
use Modules\AdminSistema\Services\BackupReaderService;

class BackupDetalle extends Component
{
    public string  $fecha    = '';
    public ?array  $meta     = null;
    public ?string $zipName  = null;
    public int     $zipSize  = 0;
    public bool    $existe   = false;

    public function mount(string $fecha): void
    {
        $this->fecha   = $fecha;
        $this->meta    = BackupReaderService::leerMetadata($fecha);
        $zipPath       = base_path("backups/IEE-{$fecha}.zip");
        $this->zipName = "IEE-{$fecha}.zip";
        $this->existe  = file_exists($zipPath);
        $this->zipSize = $this->existe ? filesize($zipPath) : 0;
    }

    public function formatSize(int $bytes): string
    {
        return BackupReaderService::formatSize($bytes);
    }

    public function render(): View
    {
        return view('adminsistema::livewire.backups.backup-detalle');
    }
}
