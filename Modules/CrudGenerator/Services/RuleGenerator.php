<?php

namespace Modules\CrudGenerator\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RuleGenerator
{
    public function generate(string $module, string $name): string
    {
        $modelClass = "\\Modules\\{$module}\\Entities\\{$name}";

        if (!class_exists($modelClass)) {
            return "// Modelo {$modelClass} no encontrado\n";
        }

        $model = new $modelClass;
        $table = $model->getTable();

        if (!Schema::hasTable($table)) {
            return "// Tabla '{$table}' no existe\n";
        }

        $rules = [];

        foreach ($model->getFillable() as $field) {
            try {
                $type = Schema::getColumnType($table, $field);

                switch ($type) {
                    case 'bigint':
                        $rules[] = "'form.{$field}' => 'required|integer'";
                        break;
                    case 'boolean':
                        $rules[] = "'form.{$field}' => 'required|boolean'";
                        break;
                    case 'date':
                    case 'datetime':
                        $rules[] = "'form.{$field}' => 'required|date'";
                        break;
                    case 'text':
                        $rules[] = "'form.{$field}' => 'required|string'";
                        break;
                    default:
                        $rules[] = "'form.{$field}' => 'required|string|max:255'";
                        break;
                }
            } catch (\Throwable $e) {
                $rules[] = "// Error con el campo {$field}: " . $e->getMessage();
            }
        }

        return implode(",\n        ", $rules);
    }
}
