<?php

namespace Modules\AdminSistema\Livewire\Backups;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use ZipArchive;

class ImportarSnapshot extends Component
{
    use WithFileUploads;

    // SNAP-003: archivo cargado por el usuario
    public $zipFile = null;
    public string $errorUpload = '';

    // Ruta persistida del ZIP en storage/app/imported-snapshots/ (SNAP-008)
    public string $rutaImportada = '';
    public string $nombreArchivo = '';
    public int    $tamanoBytes   = 0;

    // SNAP-005: metadata leída del ZIP
    public ?array $meta = null;

    // SNAP-006: confirmación obligatoria
    public string $confirmacion = '';

    // Resultado (SNAP-009)
    public bool   $exito         = false;
    public string $outputComando = '';

    // Estado: subir | vista-previa | confirmar | resultado
    public string $estado = 'subir';

    // CSVs institucionales mínimos que debe contener un Snapshot válido (SNAP-004)
    private const CSV_MINIMOS = [
        'users.csv',
        'bienes.csv',
        'categorias.csv',
        'dependencias.csv',
        'permissions.csv',
    ];

    public function mount(): void
    {
        $this->autorizar();
        $this->limpiarImportacionesAntiguas();
    }

    // ── SNAP-003/004/005: Carga, validación estructural y vista previa ────────

    public function cargarYValidar(): void
    {
        $this->autorizar();
        $this->errorUpload = '';

        $this->validate([
            'zipFile' => ['required', 'file', 'mimes:zip', 'max:51200'],
        ], [
            'zipFile.required' => 'Debes seleccionar un archivo ZIP.',
            'zipFile.mimes'    => 'Solo se permiten archivos con extensión .zip.',
            'zipFile.max'      => 'El archivo supera el límite máximo permitido.',
        ]);

        // SNAP-008: persistir en storage/app/imported-snapshots/
        $importDir = storage_path('app/imported-snapshots');
        if (!is_dir($importDir)) {
            mkdir($importDir, 0755, true);
        }

        $nombreTemp = 'SNAP-' . now()->format('Ymd-His') . '-' . Str::random(6) . '.zip';
        $storedRel  = $this->zipFile->storeAs('imported-snapshots', $nombreTemp, 'local');

        if (!$storedRel) {
            $this->errorUpload = 'Error al guardar el archivo en el servidor. Verifica permisos de storage.';
            return;
        }

        $rutaAbs = storage_path('app/' . $storedRel);

        // SNAP-004: validación estructural del ZIP
        $errorEstructural = $this->validarEstructuraZip($rutaAbs);
        if ($errorEstructural !== null) {
            @unlink($rutaAbs);
            $this->errorUpload = $errorEstructural;
            return;
        }

        // SNAP-005: leer metadata.json
        $meta = $this->leerMetadataDesdeZip($rutaAbs);
        if ($meta === null) {
            @unlink($rutaAbs);
            $this->errorUpload = 'No se pudo leer metadata.json del Snapshot. El archivo puede estar dañado.';
            return;
        }

        // Renombrar con nombre institucional basado en fecha del snapshot
        $fechaSnap   = $meta['fecha'] ?? now()->format('Y-m-d');
        $nombreFinal = "IEE-{$fechaSnap}-imported.zip";
        $rutaFinal   = "{$importDir}/{$nombreFinal}";

        if (file_exists($rutaFinal)) {
            unlink($rutaFinal);
        }

        rename($rutaAbs, $rutaFinal);

        $this->meta          = $meta;
        $this->rutaImportada = $rutaFinal;
        $this->nombreArchivo = $nombreFinal;
        $this->tamanoBytes   = (int) filesize($rutaFinal);
        $this->estado        = 'vista-previa';
    }

    public function irAConfirmar(): void
    {
        $this->autorizar();
        $this->confirmacion = '';
        $this->estado       = 'confirmar';
    }

    public function cancelar(): void
    {
        $this->limpiarImportada();
        $this->zipFile     = null;
        $this->errorUpload = '';
        $this->meta        = null;
        $this->confirmacion = '';
        $this->estado      = 'subir';
    }

    // SNAP-007: reutiliza backup:restore-from-zip como único motor oficial

    public function ejecutarRestauracion(): void
    {
        $this->autorizar();

        if (trim($this->confirmacion) !== 'RESTAURAR') {
            return;
        }

        if (!$this->rutaImportada || !file_exists($this->rutaImportada)) {
            $this->exito         = false;
            $this->outputComando = 'El archivo importado ya no existe en el servidor. Importa el Snapshot nuevamente.';
            $this->estado        = 'resultado';
            $this->registrarAuditoria(false, 'Archivo importado no encontrado al ejecutar');
            return;
        }

        $exitCode = Artisan::call('backup:restore-from-zip', [
            '--file'  => $this->rutaImportada,
            '--force' => true,
        ]);

        $this->exito         = ($exitCode === 0);
        $this->outputComando = Artisan::output();
        $this->estado        = 'resultado';

        $this->registrarAuditoria(
            $this->exito,
            $this->exito
                ? 'EXITOSA desde CAB — snapshot importado externo (exit=0)'
                : "FALLIDA desde CAB — snapshot importado externo (exit={$exitCode})"
        );

        // SNAP-008: eliminar el archivo importado tras la restauración
        $this->limpiarImportada();
    }

    public function resetear(): void
    {
        $this->limpiarImportada();
        $this->zipFile       = null;
        $this->errorUpload   = '';
        $this->meta          = null;
        $this->confirmacion  = '';
        $this->exito         = false;
        $this->outputComando = '';
        $this->estado        = 'subir';
    }

    public function render(): View
    {
        return view('adminsistema::livewire.backups.importar-snapshot');
    }

    // ── SNAP-004: Validación estructural ──────────────────────────────────────

    private function validarEstructuraZip(string $zipPath): ?string
    {
        $zip    = new ZipArchive();
        $result = $zip->open($zipPath, ZipArchive::RDONLY);

        if ($result !== true) {
            return "El archivo ZIP no es válido o está corrupto (código ZipArchive: {$result}).";
        }

        if ($zip->locateName('metadata.json') === false) {
            $zip->close();
            return 'El archivo no contiene metadata.json. No es un Snapshot Institucional IEE válido.';
        }

        $faltantes = [];
        foreach (self::CSV_MINIMOS as $csv) {
            if ($zip->locateName($csv) === false) {
                $faltantes[] = $csv;
            }
        }

        $totalArchivos = $zip->numFiles;
        $zip->close();

        if (!empty($faltantes)) {
            return 'Faltan archivos requeridos en el Snapshot: ' . implode(', ', $faltantes)
                 . '. El archivo puede estar incompleto o no ser un Snapshot Institucional.';
        }

        if ($totalArchivos < 5) {
            return "El Snapshot contiene solo {$totalArchivos} archivo(s). Se esperan al menos 5. Snapshot incompleto.";
        }

        return null;
    }

    private function leerMetadataDesdeZip(string $zipPath): ?array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::RDONLY) !== true) {
            return null;
        }

        $json = $zip->getFromName('metadata.json');
        $zip->close();

        if ($json === false) {
            return null;
        }

        $data = json_decode($json, true);

        if (!is_array($data) || !isset($data['conteos'])) {
            return null;
        }

        return $data;
    }

    // ── Limpieza (SNAP-008) ───────────────────────────────────────────────────

    private function limpiarImportada(): void
    {
        if ($this->rutaImportada && file_exists($this->rutaImportada)) {
            @unlink($this->rutaImportada);
        }
        $this->rutaImportada = '';
        $this->nombreArchivo = '';
        $this->tamanoBytes   = 0;
    }

    private function limpiarImportacionesAntiguas(): void
    {
        $importDir = storage_path('app/imported-snapshots');
        if (!is_dir($importDir)) {
            return;
        }

        $corte = now()->subHours(24)->getTimestamp();
        foreach (glob("{$importDir}/*.zip") ?: [] as $archivo) {
            if (filemtime($archivo) < $corte) {
                @unlink($archivo);
            }
        }
    }

    // ── SNAP-002/SNAP-010: Autorización ──────────────────────────────────────

    private function autorizar(): void
    {
        $user = Auth::user();
        if (!$user
            || !$user->hasPermission('importar-snapshot-backup')
            || !$user->isAdminPrincipal()
        ) {
            abort(403, 'Solo el Administrador Principal puede importar Snapshots institucionales.');
        }
    }

    // ── SNAP-009: Auditoría ───────────────────────────────────────────────────

    private function registrarAuditoria(bool $exito, string $detalle): void
    {
        $user = Auth::user();

        $entrada = json_encode([
            'fecha'           => now()->format('Y-m-d H:i:s'),
            'origen'          => 'CAB-WEB-IMPORT',
            'usuario_id'      => $user?->id,
            'usuario'         => $user ? trim("{$user->nombres} {$user->apellidos}") : 'desconocido',
            'backup'          => $this->nombreArchivo ?: 'snapshot-importado',
            'version_iee'     => $this->meta['version_iee'] ?? 'n/a',
            'total_registros' => $this->meta['total_registros'] ?? 0,
            'tamano_bytes'    => $this->tamanoBytes,
            'resultado'       => $exito ? 'EXITOSA' : 'FALLIDA',
            'detalle'         => $detalle,
        ], JSON_UNESCAPED_UNICODE);

        file_put_contents(
            storage_path('logs/restore.log'),
            $entrada . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    public function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
