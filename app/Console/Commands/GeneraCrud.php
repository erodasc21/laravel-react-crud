<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GeneraCrud extends Command
{
    protected $signature = 'make:catalog {table : The table name (plural)}';
    protected $description = 'Generate a complete CRUD catalog from database table';

    private $table;
    private $modelName;
    private $columns;
    private $fillable;
    private $modelVariablePlural;

    public function handle()
    {
        $this->table = $this->argument('table');
        $this->modelName = Str::studly(Str::singular($this->table));
        $this->modelVariablePlural = Str::camel($this->table);

        // Verificar que la tabla existe
        if (!$this->tableExists()) {
            $this->error("La tabla '{$this->table}' no existe en la base de datos.");
            return 1;
        }

        $this->info("🚀 Generando catálogo para: {$this->table}");

        // Obtener columnas de la tabla
        $this->getTableColumns();

        // Generar archivos
        $this->generateModel();
        $this->generateController();
        $this->generateFormRequest();
        $this->generateRoutes();
        $this->generatePermissionsSeeder();
        $this->generateReactComponents();

        $this->info("\n✅ Catálogo generado exitosamente!");
        $this->info("\n📝 Próximos pasos:");
        $this->info("1. Ejecuta: php artisan db:seed --class={$this->modelName}PermissionsSeeder");
        $this->info("2. Asigna los permisos a tus roles");
        $this->info("3. Revisa y personaliza los componentes React generados");

        return 0;
    }

    private function tableExists(): bool
    {
        return DB::getSchemaBuilder()->hasTable($this->table);
    }

    private function getTableColumns(): void
    {
        $this->columns = DB::getSchemaBuilder()->getColumns($this->table);

        // Filtrar columnas para fillable (excluir id, timestamps, etc.)
        $this->fillable = collect($this->columns)
            ->pluck('name')
            ->reject(fn($col) => in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at']))
            ->values()
            ->toArray();
    }

    private function generateModel(): void
    {
        $stub = $this->getStub('model');
        $content = str_replace(
            ['{{modelName}}', '{{fillable}}', '{{table}}'],
            [$this->modelName, $this->formatArrayForStub($this->fillable), $this->table],
            $stub
        );

        $path = app_path("Models/{$this->modelName}.php");
        File::put($path, $content);

        $this->info("✓ Modelo creado: {$path}");
    }

    private function generateController(): void
    {
        $stub = $this->getStub('controller');
        $content = str_replace(
            ['{{modelName}}', '{{modelVariable}}', '{{tableName}}'],
            [$this->modelName, Str::camel($this->modelName), $this->table],
            $stub
        );

        $path = app_path("Http/Controllers/{$this->modelName}Controller.php");
        File::put($path, $content);

        $this->info("✓ Controlador creado: {$path}");
    }

    private function generateFormRequest(): void
    {
        $stub = $this->getStub('request');

        $rules = $this->generateValidationRules();

        $content = str_replace(
            ['{{modelName}}', '{{rules}}'],
            [$this->modelName, $rules],
            $stub
        );

        $path = app_path("Http/Requests/{$this->modelName}Request.php");
        File::put($path, $content);

        $this->info("✓ Form Request creado: {$path}");
    }

    private function generateValidationRules(): string
    {
        $rules = [];

        foreach ($this->columns as $column) {
            $columnName = $column['name'];

            // Saltar columnas automáticas
            if (in_array($columnName, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $validationRules = [];

            // Required si no es nullable
            if (!$column['nullable']) {
                $validationRules[] = 'required';
            } else {
                $validationRules[] = 'nullable';
            }

            // Tipo de dato
            $type = $this->getValidationType($column['type_name']);
            if ($type) {
                $validationRules[] = $type;
            }

            // Max length para strings
            if (in_array($column['type_name'], ['varchar', 'char', 'text']) && isset($column['length'])) {
                $validationRules[] = "max:{$column['length']}";
            }

            $rules[] = "            '{$columnName}' => '" . implode('|', $validationRules) . "',";
        }

        return implode("\n", $rules);
    }

    private function getValidationType($sqlType): ?string
    {
        return match ($sqlType) {
            'int', 'integer', 'bigint', 'smallint', 'tinyint' => 'integer',
            'decimal', 'float', 'double' => 'numeric',
            'boolean', 'bool' => 'boolean',
            'date' => 'date',
            'datetime', 'timestamp' => 'date',
            'json', 'jsonb' => 'json',
            'varchar', 'char', 'text' => 'string',
            default => 'string',
        };
    }

    private function generateRoutes(): void
    {
        $routeLine = "\nRoute::resource('{$this->table}', {$this->modelName}Controller::class);";

        $routesPath = base_path('routes/web.php');
        $routesContent = File::get($routesPath);

        // Verificar si la ruta ya existe
        if (strpos($routesContent, $routeLine) === false) {
            // Agregar import del controlador
            $controllerImport = "use App\\Http\\Controllers\\{$this->modelName}Controller;";

            if (strpos($routesContent, $controllerImport) === false) {
                // Buscar la última línea de use
                $lines = explode("\n", $routesContent);
                $lastUseLine = 0;

                foreach ($lines as $index => $line) {
                    if (strpos($line, 'use ') === 0 && strpos($line, ';') !== false) {
                        $lastUseLine = $index;
                    }
                }

                // Insertar el import después de la última línea use
                array_splice($lines, $lastUseLine + 1, 0, $controllerImport);
                $routesContent = implode("\n", $lines);
                File::put($routesPath, $routesContent);
            }

            // Agregar ruta al final
            File::append($routesPath, $routeLine);
            $this->info("✓ Ruta agregada a routes/web.php");
        } else {
            $this->warn("⚠ La ruta ya existe en routes/web.php");
        }
    }

    private function generatePermissionsSeeder(): void
    {
        $stub = $this->getStub('seeder');
        $permissions = [
            "view {$this->table}",
            "create {$this->table}",
            "edit {$this->table}",
            "delete {$this->table}",
        ];

        $content = str_replace(
            ['{{modelName}}', '{{permissions}}'],
            [$this->modelName, $this->formatPermissionsForSeeder($permissions)],
            $stub
        );

        $path = database_path("seeders/{$this->modelName}PermissionsSeeder.php");
        File::put($path, $content);

        $this->info("✓ Seeder de permisos creado: {$path}");
    }

    private function generateReactComponents(): void
    {
        $componentsPath = resource_path("js/Pages/{$this->modelName}");

        if (!File::exists($componentsPath)) {
            File::makeDirectory($componentsPath, 0755, true);
        }

        // Generar Index.tsx
        $this->generateIndexComponent($componentsPath);

        // Generar Create.tsx
        $this->generateCreateComponent($componentsPath);

        // Generar Edit.tsx
        $this->generateEditComponent($componentsPath);

        // Generar Form.tsx
        $this->generateFormComponent($componentsPath);
    }

    private function generateIndexComponent($path): void
    {
        $stub = $this->getStub('react/index');
        $content = str_replace(
            [
                '{{modelName}}',
                '{{modelVariable}}',
                '{{modelVariablePlural}}',
                '{{tableName}}',
                '{{columns}}',
                '{{interface}}',
            ],
            [
                $this->modelName,
                Str::camel($this->modelName),
                $this->modelVariablePlural,
                $this->table,
                $this->generateTableColumns(),
                $this->generateTypeScriptInterface(),
            ],
            $stub
        );

        File::put("{$path}/Index.tsx", $content);
        $this->info("✓ Componente Index.tsx creado");
    }

    private function generateCreateComponent($path): void
    {
        $stub = $this->getStub('react/create');
        $content = str_replace(
            ['{{modelName}}', '{{modelVariable}}', '{{modelVariablePlural}}', '{{tableName}}'],
            [$this->modelName, Str::camel($this->modelName), $this->modelVariablePlural, $this->table],
            $stub
        );

        File::put("{$path}/Create.tsx", $content);
        $this->info("✓ Componente Create.tsx creado");
    }

    private function generateEditComponent($path): void
    {
        $stub = $this->getStub('react/edit');
        $content = str_replace(
            ['{{modelName}}', '{{modelVariable}}', '{{modelVariablePlural}}', '{{tableName}}', '{{interface}}'],
            [
                $this->modelName,
                Str::camel($this->modelName),
                $this->modelVariablePlural, // ✅ AGREGAR
                $this->table,
                $this->generateTypeScriptInterface()
            ],
            $stub
        );
        File::put("{$path}/Edit.tsx", $content);
        $this->info("✓ Componente Edit.tsx creado");
    }


    private function generateFormComponent($path): void
    {
        $stub = $this->getStub('react/form');
        $content = str_replace(
            [
                '{{modelName}}',
                '{{modelVariable}}',
                '{{formFields}}',
                '{{interface}}',
            ],
            [
                $this->modelName,
                Str::camel($this->modelName),
                $this->generateFormFields(),
                $this->generateTypeScriptInterface(),
            ],
            $stub
        );

        File::put("{$path}/Form.tsx", $content);
        $this->info("✓ Componente Form.tsx creado");
    }

    private function getStub($type): string
    {
        return File::get(base_path("stubs/catalog/{$type}.stub"));
    }

    private function formatArrayForStub(array $items): string
    {
        return "'" . implode("', '", $items) . "'";
    }

    private function formatPermissionsForSeeder(array $permissions): string
    {
        return "            '" . implode("',\n            '", $permissions) . "',";
    }

    private function generateTableColumns(): string
    {
        // Generar definición de columnas para TanStack Table
        $columns = [];

        foreach ($this->fillable as $column) {
            $columns[] = $this->generateColumnDefinition($column);
        }

        return implode(",\n        ", $columns);
    }

    private function generateColumnDefinition($columnName): string
    {
        $label = Str::title(str_replace('_', ' ', $columnName));

        return <<<EOT
{
            accessorKey: '{$columnName}',
            header: ({ column }) => {
                return (
                    <Button
                        variant="ghost"
                        onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    >
                        {$label}
                        <ArrowUpDown className="ml-2 h-4 w-4" />
                    </Button>
                );
            },
        }
EOT;
    }

    private function generateTypeScriptInterface(): string
    {
        $properties = [];

        foreach ($this->columns as $column) {
            $tsType = $this->mapSqlTypeToTypeScript($column['type_name']);
            $nullable = $column['nullable'] ? ' | null' : '';
            $properties[] = "    {$column['name']}: {$tsType}{$nullable};";
        }

        return implode("\n", $properties);
    }

    private function mapSqlTypeToTypeScript($sqlType): string
    {
        return match ($sqlType) {
            'int', 'integer', 'bigint', 'smallint', 'tinyint', 'decimal', 'float', 'double' => 'number',
            'boolean', 'bool' => 'boolean',
            'json', 'jsonb' => 'any',
            default => 'string',
        };
    }

    private function generateFormFields(): string
    {
        $fields = [];

        foreach ($this->fillable as $column) {
            $label = Str::title(str_replace('_', ' ', $column));
            $fields[] = $this->generateFormField($column, $label);
        }

        return implode("\n\n            ", $fields);
    }

    private function generateFormField($name, $label): string
    {
        return <<<EOT
<div>
                <Label htmlFor="{$name}">{$label}</Label>
                <Input
                    id="{$name}"
                    type="text"
                    value={data.{$name}}
                    onChange={(e) => setData('{$name}', e.target.value)}
                />
                {errors.{$name} && (
                    <p className="text-sm text-destructive">{errors.{$name}}</p>
                )}
            </div>
EOT;
    }
}
