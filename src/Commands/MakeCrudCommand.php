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
    protected string $modelName;
    protected string $tableName;
    protected array $fields = [];

    public function handle(): void
    {
        $this->modelName = $this->argument('name');
        if (!$this->modelName){
            $this->error('The name field is required.');
            return;
        }
        $this->tableName = Str::plural(Str::snake($this->modelName));
        $this->parseFields();
        if (!$this->fields){
            $this->error('The fields option is required.');
            return;
        }
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
            if ($this->option('factory')) {
                $this->generateFactory();
            }

            if ($this->option('test')) {
                $this->generateTests();
            }

            $this->info("CRUD for {$this->modelName} created successfully!");
        }
    }

    protected function parseFields(): void
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

    protected function generateModel(): void
    {
        $modelTemplate = $this->getStub('model');
        $modelTemplate = str_replace(['{{modelName}}', '{{tableName}}', '{{fillable}}'], [$this->modelName, $this->tableName, $this->getFillableString()], $modelTemplate);

        $path = app_path("Models/{$this->modelName}.php");
        $this->createFile($path, $modelTemplate);
    }

    protected function generateMigration(): void
    {
        $migrationTemplate = $this->getStub('migration');
        $migrationTemplate = str_replace(['{{tableName}}', '{{fields}}'], [$this->tableName, $this->getMigrationFields()], $migrationTemplate);

        $fileName = date('Y_m_d_His') . "_create_{$this->tableName}_table.php";
        $path = database_path("migrations/{$fileName}");
        $this->createFile($path, $migrationTemplate);
    }

    protected function generateController(): void
    {
        $controllerName = "{$this->modelName}Controller";
        $controllerTemplate = $this->getStub($this->option('api') ? 'controller.api' : 'controller');
        $controllerTemplate = str_replace(['{{modelName}}', '{{modelNameLowerCase}}', '{{controllerName}}'], [$this->modelName, strtolower($this->modelName), $controllerName], $controllerTemplate);

        $path = app_path("Http/Controllers/{$controllerName}.php");
        $this->createFile($path, $controllerTemplate);
    }

    protected function generateViews(): void
    {
        if (!$this->option('api')) {
            $views = ['index', 'create', 'edit', 'show'];
            foreach ($views as $view) {
                $viewTemplate = $this->getStub("views.{$view}");
                $viewTemplate = str_replace(['{{modelName}}', '{{modelNameLowerCase}}', '{{fields}}'], [$this->modelName, strtolower($this->modelName), $this->getViewFields($view)], $viewTemplate);

                $path = resource_path("views/" . Str::snake(Str::plural($this->modelName)) . "/{$view}.blade.php");
                $this->createFile($path, $viewTemplate);
            }
        }
    }

    protected function generateRoutes(): void
    {
        $routeTemplate = $this->getStub($this->option('api') ? 'routes.api' : 'routes.web');
        $routeTemplate = str_replace(['{{modelName}}', '{{modelNameLowerCase}}'], [$this->modelName, strtolower($this->modelName)], $routeTemplate);

        $this->info("Add the following routes to your routes file:");
        $this->info($routeTemplate);
    }

    protected function generateFactory(): void
    {
        $factoryTemplate = $this->getStub('factory');
        $factoryTemplate = str_replace(['{{modelName}}', '{{fields}}'], [$this->modelName, $this->getFactoryFields()], $factoryTemplate);
        $path = database_path("factories/{$this->modelName}Factory.php");
        $this->createFile($path, $factoryTemplate);
    }

    protected function generateSeeder(): void
    {
        $seederTemplate = $this->getStub('seeder');
        $seederTemplate = str_replace(['{{modelName}}', '{{fields}}'], [$this->modelName, $this->getSeederFields()], $seederTemplate);

        $path = database_path("seeders/{$this->modelName}Seeder.php");
        $this->createFile($path, $seederTemplate);
//        TODO : Ajouter la ligne suivante dans le fichier DatabaseSeeder.php a faire dans la prochaine version
//        $this->addSeederToSeederFile($path);
    }

    protected function generatePolicy(): void
    {
        $policyTemplate = $this->getStub('policy');
        $policyTemplate = str_replace(['{{modelName}}', '{{modelNameLowerCase}}'], [$this->modelName, strtolower($this->modelName)], $policyTemplate);

        $path = app_path("Policies/{$this->modelName}Policy.php");
        $this->createFile($path, $policyTemplate);
    }

    protected function generateFormRequests(): void
    {
        $requests = ['Store', 'Update'];
        foreach ($requests as $action) {
            $requestTemplate = $this->getStub('request');
            $requestTemplate = str_replace(['{{modelName}}', '{{action}}', '{{rules}}'], [$this->modelName, $action, $this->getRequestRules()], $requestTemplate);

            $path = app_path("Http/Requests/{$action}{$this->modelName}Request.php");
            $this->createFile($path, $requestTemplate);
        }
    }

    protected function generateResource(): void
    {
        $resourceTemplate = $this->getStub('resource');
        $resourceTemplate = str_replace(['{{modelName}}', '{{fields}}'], [$this->modelName, $this->getResourceFields()], $resourceTemplate);

        $path = app_path("Http/Resources/{$this->modelName}Resource.php");
        $this->createFile($path, $resourceTemplate);
    }

    protected function generateTests(): void
    {
        $testTemplate = $this->getStub($this->option('api') ? 'test.api' : 'test.feature');
        $testTemplate = str_replace(['{{modelName}}', '{{modelNameLowerCase}}'], [$this->modelName, strtolower($this->modelName)], $testTemplate);

        $path = base_path("tests/" . ($this->option('api') ? "Unit" : "Feature") . "/{$this->modelName}Test.php");
        $this->createFile($path, $testTemplate);
    }

    protected function getStub($type): bool|string
    {
        $stubPath = __DIR__ . '/stubs/' . $type . '.stub';

        if (!file_exists($stubPath)) {
            throw new \RuntimeException("Stub file not found: {$stubPath}");
        }

        return file_get_contents($stubPath);
    }

    protected function createFile($path, $content): void
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

    protected function getFillableString(): string
    {
        return "'" . implode("', '", array_keys($this->fields)) . "'";
    }

    protected function getMigrationFields(): string
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

    protected function getViewFields($view): string
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

    protected function generateFormField($name, $type): string
    {
        $label = ucfirst(str_replace('_', ' ', $name));
        $value = $this->option('api') ? '' : '{{ $' . strtolower($this->modelName) . '->' . $name . ' }}';

        return match ($type) {
            'text' => "<div class=\"mb-4\">
                <label for=\"{$name}\" class=\"block text-sm font-medium text-gray-700\">{$label}</label>
                <textarea name=\"{$name}\" id=\"{$name}\" rows=\"3\" class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50\">{$value}</textarea>
            </div>\n",
            'boolean' => "<div class=\"mb-4\">
                <label for=\"{$name}\" class=\"inline-flex items-center\">
                    <input type=\"checkbox\" name=\"{$name}\" id=\"{$name}\" class=\"rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50\" {$value} ? 'checked' : ''>
                    <span class=\"ml-2\">{$label}</span>
                </label>
            </div>\n",
            default => "<div class=\"mb-4\">
                <label for=\"{$name}\" class=\"block text-sm font-medium text-gray-700\">{$label}</label>
                <input type=\"text\" name=\"{$name}\" id=\"{$name}\" value=\"{$value}\" class=\"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50\">
            </div>\n",
        };
    }

    protected function getSeederFields(): string
    {
        $fields = [];
        foreach ($this->fields as $name => $type) {
            $fields[] = match ($type) {
                'string' => "'{$name}' => \$this->faker->sentence",
                'text' => "'{$name}' => \$this->faker->paragraph",
                'integer' => "'{$name}' => \$this->faker->numberBetween(1, 100)",
                'boolean' => "'{$name}' => \$this->faker->boolean",
                'date' => "'{$name}' => \$this->faker->date",
                'datetime' => "'{$name}' => \$this->faker->dateTime",
                'decimal' => "'{$name}' => \$this->faker->randomFloat(2, 0, 999)",
                default => "'{$name}' => \$this->faker->word",
            };
        }
        return implode(",\n            ", $fields);
    }

    protected function getFactoryFields(): string
    {
        $fiels = [];
        foreach ($this->fields as $name => $type) {
            $fiels[] = match ($type) {
                'text' => "'{$name}' => \$this->faker->text",
                'boolean' => "'{$name}' => \$this->faker->boolean",
                'email'=> "'{$name}' => \$this->faker->unique()->safeEmail",
                'integer'=>"'{$name}' => \$this->faker->numberBetween(1, 100)",
                default => "'{$name}' => \$this->faker->word",
            };
        }
        return implode(",\n            ", $fiels);
    }

    protected function getRequestRules(): string
    {
        $rules = [];
        foreach ($this->fields as $name => $type) {
            $rules[] = match ($type) {
                'string' => "'{$name}' => 'required|string|max:255'",
                'email' => "'{$name}' => 'required|string|email|max:255|unique:{$this->tableName}',",
                'text' => "'{$name}' => 'required|string'",
                'integer' => "'{$name}' => 'required|integer'",
                'boolean' => "'{$name}' => 'boolean'",
                'date' => "'{$name}' => 'required|date'",
                'datetime' => "'{$name}' => 'required|date_format:Y-m-d H:i:s'",
                'decimal' => "'{$name}' => 'required|numeric'",
                default => "'{$name}' => 'required'",
            };
        }
        return implode(",\n            ", $rules);
    }

    protected function getResourceFields(): string
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