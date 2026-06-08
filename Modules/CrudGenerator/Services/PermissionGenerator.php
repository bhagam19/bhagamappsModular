<?php

namespace Modules\CrudGenerator\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PermissionGenerator
{
    public function generate(string $name, string $module, Command $command): void
    {
        $nameLower = strtolower($name);
        $stubPath = module_path('CrudGenerator') . '/resources/stubs/permissions.stub';

        if (!File::exists($stubPath)) {
            $command->warn("❗ Stub permissions.stub no encontrado.");
            return;
        }

        $permissionsJson = file_get_contents($stubPath);

        $permissionsJson = str_replace(
            ['{{name}}', '{{nameLower}}', '{{module}}'],
            [$name, $nameLower, $module],
            $permissionsJson
        );

        $permissions = json_decode($permissionsJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $command->error("❗ Error al decodificar permissions.stub: " . json_last_error_msg());
            return;
        }

        foreach ($permissions as $permission) {
            $permission['created_at'] = now();
            $permission['updated_at'] = now();

            DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $command->info("✔ Permisos insertados en tabla 'permissions'");
    }
}
