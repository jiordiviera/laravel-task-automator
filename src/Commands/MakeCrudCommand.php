<?php

namespace JiordiViera\LaravelTaskAutomator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeCrudCommand extends Command
{
    protected $signature = 'make:crud {name} {--fields=} {--api} {--force} {--seed} {--policy} {--requests} {--resource} {--test} {--factory}';
    protected $description = 'Generate a complete CRUD setup for the specified model with advanced options';
    public $output;
    protected $modelName;
    protected $tableName;
    protected $fields = [];

    public function handle()
    {
        $this->modelName = $this->argument('name');
        $this->tableName = Str::plural(Str::snake($this->modelName));
        $this->parseFields();
        if ($this->option('force') || $this->confirm("This will create multiple files. Do you wish to continue?")) {
            $this->generateModel();
            $this->generateMigration();
            $this->generateController();
            $this->generateViews();
            $this->generateRoutes();

            if ($this->option('seed')) {
                $this->generateSeeder();
            }

            if ($this->option('policy')) {
                $this->generatePolicy();
            }

            if ($this->option('requests')) {
                $this->generateFormRequests();
            }

            if ($this->option('resource')) {
                $this->generateResource();
            }

            if ($this->option('test')) {
                $this->generateTests();
            }

            $this->info("CRUD for {$this->modelName} created successfully!");
        }
    }

    protected function parseFields()
    {
        $fields = $this->option('fields');
        if ($fields) {
            $fieldArray = explode(',', $fields);
            foreach ($fieldArray as $field) {
                $parts = explode(':', $field);
                $this->fields[$parts[0]] = $parts[1] ?? 'string';
            }
        }
    }

    protected function generateModel()
    {
        $modelTemplate = $this->getStub('model');
        $modelTemplate = str_replace(
            ['{{modelName}}', '{{tableName}}', '{{fillable}}'],
            [$this->modelName, $this->tableName, $this->getFillableString()],
            $modelTemplate
        );

        $path = app_path("Models/{$this->modelName}.php");
        $this->createFile($path, $modelTemplate);
    }

    protected function generateMigration()
    {
        $migrationTemplate = $this->getStub('migration');
        $migrationTemplate = str_replace(
            ['{{tableName}}', '{{fields}}'],
            [$this->tableName, $this->getMigrationFields()],
            $migrationTemplate
        );

        $fileName = date('Y_m_d_His') . "_create_{$this->tableName}_table.php";
        $path = database_path("migrations/{$fileName}");
        $this->createFile($path, $migrationTemplate);
    }

    protected function generateController()
    {
        $controllerName = "{$this->modelName}Controller";
        $controllerTemplate = $this->getStub($this->option('api') ? 'controller.api' : 'controller');
        $controllerTemplate = str_replace(
            ['{{modelName}}', '{{modelNameLowerCase}}', '{{controllerName}}'],
            [$this->modelName, strtolower($this->modelName), $controllerName],
            $controllerTemplate
        );

        $path = app_path("Http/Controllers/{$controllerName}.php");
        $this->createFile($path, $controllerTemplate);
    }

    protected function generateViews()
    {
        if (!$this->option('api')) {
            $views = ['index', 'create', 'edit', 'show'];
            foreach ($views as $view) {
                $viewTemplate = $this->getStub("views.{$view}");
                $viewTemplate = str_replace(
                    ['{{modelName}}', '{{modelNameLowerCase}}', '{{fields}}'],
                    [$this->modelName, strtolower($this->modelName), $this->getViewFields($view)],
                    $viewTemplate
                );

                $path = resource_path("views/" . strtolower($this->modelName) . "/{$view}.blade.php");
                $this->createFile($path, $viewTemplate);
            }
        }
    }

    protected function generateRoutes()
    {
        $routeTemplate = $this->getStub($this->option('api') ? 'routes.api' : 'routes.web');
        $routeTemplate = str_replace(
            ['{{modelName}}', '{{modelNameLowerCase}}'],
            [$this->modelName, strtolower($this->modelName)],
            $routeTemplate
        );

        $this->info("Add the following routes to your routes file:");
        $this->info($routeTemplate);
    }

    protected function generateSeeder()
    {
        $seederTemplate = $this->getStub('seeder');
        $seederTemplate = str_replace(
            ['{{modelName}}', '{{fields}}'],
            [$this->modelName, $this->getSeederFields()],
            $seederTemplate
        );

        $path = database_path("seeders/{$this->modelName}Seeder.php");
        $this->createFile($path, $seederTemplate);
    }

    protected function generatePolicy()
    {
        $policyTemplate = $this->getStub('policy');
        $policyTemplate = str_replace(['{{modelName}}', '{{modelNameLowerCase}}'], [$this->modelName, strtolower($this->modelName)], $policyTemplate);

        $path = app_path("Policies/{$this->modelName}Policy.php");
        $this->createFile($path, $policyTemplate);
    }

    protected function generateFormRequests()
    {
        $requests = ['Store', 'Update'];
        foreach ($requests as $action) {
            $requestTemplate = $this->getStub('request');
            $requestTemplate = str_replace(
                ['{{modelName}}', '{{action}}', '{{rules}}'],
                [$this->modelName, $action, $this->getRequestRules()],
                $requestTemplate
            );

            $path = app_path("Http/Requests/{$action}{$this->modelName}Request.php");
            $this->createFile($path, $requestTemplate);
        }
    }

    protected function generateResource()
    {
        $resourceTemplate = $this->getStub('resource');
        $resourceTemplate = str_replace(
            ['{{modelName}}', '{{fields}}'],
            [$this->modelName, $this->getResourceFields()],
            $resourceTemplate
        );

        $path = app_path("Http/Resources/{$this->modelName}Resource.php");
        $this->createFile($path, $resourceTemplate);
    }

    protected function generateTests()
    {
        $testTemplate = $this->getStub($this->option('api') ? 'test.api' : 'test.feature');
        $testTemplate = str_replace(
            ['{{modelName}}', '{{modelNameLowerCase}}'],
            [$this->modelName, strtolower($this->modelName)],
            $testTemplate
        );

        $path = base_path("tests/" . ($this->option('api') ? "Unit" : "Feature") . "/{$this->modelName}Test.php");
        $this->createFile($path, $testTemplate);
    }

    protected function getStub($type)
    {
        $stubPath = __DIR__ . '/stubs/' . $type . '.stub';

        if (!file_exists($stubPath)) {
            throw new \RuntimeException("Stub file not found: {$stubPath}");
        }

        return file_get_contents($stubPath);
    }

    protected function createFile($path, $content)
    {
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        if (!file_exists($path) || $this->option('force')) {
            file_put_contents($path, $content);
            $this->info("Created : {$path}");
        } else {
            $this->error("File : {$path} already exists. Use --force to overwrite.");
        }
    }

    protected function getFillableString()
    {
        return "'" . implode("', '", array_keys($this->fields)) . "'";
    }

    protected function getMigrationFields()
    {
        $fields = '';
        foreach ($this->fields as $name => $type) {
            if ($name === 'email') {
                $fields .= "\$table->string('{$name}')->unique();\n            ";
            } else {
                $fields .= "\$table->{$type}('{$name}');\n            ";
            }
        }
        return $fields;
    }

    protected function getViewFields($view)
    {
        $fields = '';
        foreach ($this->fields as $name => $type) {
            switch ($view) {
                case 'index':
                case 'show':
                    $fields .= "<td>{{ \$item->{$name} }}</td>\n";
                    break;
                case 'create':
                case 'edit':
                    $fields .= $this->generateFormField($name, $type);
                    break;
            }
        }
        return $fields;
    }

    protected function generateFormField($name, $type)
    {
        $label = ucfirst(str_replace('_', ' ', $name));
        $value = $this->option('api') ? '' : '{{ $' . strtolower($this->modelName) . '->' . $name . ' }}';

        switch ($type) {
            case 'text':
                return "<div class=\"mb-4\">
                <label for=\"{$name}\" class=\"block text-sm font-medium text-gray-700\">{$label}</label>
                <textarea name=\"{$name}\" id=\"{$name}\" rows=\"3\" class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50\">{$value}</textarea>
            </div>\n";
            case 'boolean':
                return "<div class=\"mb-4\">
                <label for=\"{$name}\" class=\"inline-flex items-center\">
                    <input type=\"checkbox\" name=\"{$name}\" id=\"{$name}\" class=\"rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50\" {$value} ? 'checked' : ''>
                    <span class=\"ml-2\">{$label}</span>
                </label>
            </div>\n";
            default:
                return "<div class=\"mb-4\">
                <label for=\"{$name}\" class=\"block text-sm font-medium text-gray-700\">{$label}</label>
                <input type=\"text\" name=\"{$name}\" id=\"{$name}\" value=\"{$value}\" class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50\">
            </div>\n";
        }
    }

    protected function getSeederFields()
    {
        $fields = [];
        foreach ($this->fields as $name => $type) {
            switch ($type) {
                case 'string':
                    $fields[] = "'{$name}' => \$this->faker->sentence";
                    break;
                case 'text':
                    $fields[] = "'{$name}' => \$this->faker->paragraph";
                    break;
                case 'integer':
                    $fields[] = "'{$name}' => \$this->faker->numberBetween(1, 100)";
                    break;
                case 'boolean':
                    $fields[] = "'{$name}' => \$this->faker->boolean";
                    break;
                case 'date':
                    $fields[] = "'{$name}' => \$this->faker->date";
                    break;
                case 'datetime':
                    $fields[] = "'{$name}' => \$this->faker->dateTime";
                    break;
                case 'decimal':
                    $fields[] = "'{$name}' => \$this->faker->randomFloat(2, 0, 999)";
                    break;
                default:
                    $fields[] = "'{$name}' => \$this->faker->word";
            }
        }
        return implode(",\n            ", $fields);
    }

    protected function getRequestRules()
    {
        $rules = [];
        foreach ($this->fields as $name => $type) {
            switch ($type) {
                case 'string':
                    $rules[] = "'{$name}' => 'required|string|max:255'";
                    break;
                case 'text':
                    $rules[] = "'{$name}' => 'required|string'";
                    break;
                case 'integer':
                    $rules[] = "'{$name}' => 'required|integer'";
                    break;
                case 'boolean':
                    $rules[] = "'{$name}' => 'boolean'";
                    break;
                case 'date':
                    $rules[] = "'{$name}' => 'required|date'";
                    break;
                case 'datetime':
                    $rules[] = "'{$name}' => 'required|date_format:Y-m-d H:i:s'";
                    break;
                case 'decimal':
                    $rules[] = "'{$name}' => 'required|numeric'";
                    break;
                default:
                    $rules[] = "'{$name}' => 'required'";
            }
        }
        return implode(",\n            ", $rules);
    }

    protected function getResourceFields()
    {
        $fields = [];
        foreach ($this->fields as $name => $type) {
            $fields[] = "'{$name}' => \$this->{$name}";
        }
        $fieldsStr = implode(",\n            ", $fields);
        return "return [
            'id' => \$this->id,
            {$fieldsStr},
            'created_at' => \$this->created_at,
            'updated_at' => \$this->updated_at,
        ];";
    }
}