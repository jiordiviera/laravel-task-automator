<?php

namespace JiordiViera\LaravelTaskAutomator\Tests\Feature;

use JiordiViera\LaravelTaskAutomator\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class TaskAutomatorCommandTest extends TestCase
{
    protected string $modelName = 'TestModel';
    protected $fields = 'name:string,email:string:unique,age:integer,is_active:boolean';

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        parent::tearDown();
    }

    protected function cleanUp(): void
    {
        File::deleteDirectory(app_path("Models"));
        File::deleteDirectory(app_path("Http/Controllers"));
        File::deleteDirectory(database_path("migrations"));
        File::deleteDirectory(resource_path("views"));
        File::deleteDirectory(app_path("Http/Requests"));
        File::deleteDirectory(app_path("Policies"));
        File::deleteDirectory(database_path("factories"));
        File::deleteDirectory(database_path("seeders"));
        File::deleteDirectory(app_path("Http/Resources"));
    }

    /** @test */
    public function it_generates_basic_crud_files()
    {
        $this->artisan('make:crud', [
            'name' => $this->modelName,
            '--fields' => $this->fields,
            '--force' => true
        ])
            ->expectsOutput("CRUD for {$this->modelName} created successfully!")
            ->assertExitCode(0);

        $this->assertModelCreated();
        $this->assertControllerCreated();
        $this->assertMigrationCreated();
//        $this->assertViewsCreated();
    }

    /** @test */
    public function it_generates_api_crud_files()
    {
        $this->artisan('make:crud', [
            'name' => $this->modelName,
            '--fields' => $this->fields,
            '--api' => true,
            '--force' => true
        ])
            ->expectsOutput("CRUD for {$this->modelName} created successfully!")
            ->assertExitCode(0);

        $this->assertModelCreated();
        $this->assertApiControllerCreated();
        $this->assertMigrationCreated();
        $this->assertViewsNotCreated();
    }

    /** @test */
    public function it_generates_crud_with_all_options()
    {
        $this->artisan('make:crud', [
            'name' => $this->modelName,
            '--fields' => $this->fields,
            '--api' => true,
            '--seed' => true,
            '--factory' => true,
            '--policy' => true,
            '--requests' => true,
            '--resource' => true,
            '--force' => true
        ])
            ->expectsOutput("CRUD for {$this->modelName} created successfully!")
            ->assertExitCode(0);

        $this->assertModelCreated();
        $this->assertApiControllerCreated();
        $this->assertMigrationCreated();
//        $this->assertSeederCreated();
//        $this->assertFactoryCreated();
//        $this->assertPolicyCreated();
//        $this->assertRequestsCreated();
//        $this->assertResourceCreated();
    }

    /** @test */
    /*public function it_validates_input()
    {
        $this->artisan('make:crud', [
            'name' => '',
            '--fields' => $this->fields
        ])
            ->expectsOutput('The name field is required.')
            ->assertExitCode(1);

        $this->artisan('make:crud', [
            'name' => $this->modelName,
            '--fields' => ''
        ])
            ->expectsOutput('The fields option is required.')
            ->assertExitCode(1);
    }*/

    protected function assertModelCreated(): void
    {
        $modelPath = app_path("Models/{$this->modelName}.php");
        $this->assertFileExists($modelPath);
        $modelContent = file_get_contents($modelPath);
        $this->assertStringContainsString("class {$this->modelName} extends Model", $modelContent);
        $this->assertStringContainsString("protected \$fillable = ['name', 'email', 'age', 'is_active'];", $modelContent);
    }

    protected function assertControllerCreated(): void
    {
        $controllerPath = app_path("Http/Controllers/{$this->modelName}Controller.php");
        $this->assertFileExists($controllerPath);
        $controllerContent = file_get_contents($controllerPath);
        $this->assertStringContainsString("class {$this->modelName}Controller extends Controller", $controllerContent);
        $this->assertStringContainsString('public function index()', $controllerContent);
        $this->assertStringContainsString('public function create()', $controllerContent);
        $this->assertStringContainsString('public function store(Request $request)', $controllerContent);
        $this->assertStringContainsString('public function show(', $controllerContent);
        $this->assertStringContainsString('public function edit(', $controllerContent);
        $this->assertStringContainsString('public function update(Request $request,', $controllerContent);
        $this->assertStringContainsString('public function destroy(', $controllerContent);
    }

    protected function assertApiControllerCreated(): void
    {
        $controllerPath = app_path("Http/Controllers/{$this->modelName}Controller.php");
        $this->assertFileExists($controllerPath);
        $controllerContent = file_get_contents($controllerPath);
        $this->assertStringContainsString("class {$this->modelName}Controller extends Controller", $controllerContent);
        $this->assertStringContainsString('public function index()', $controllerContent);
        $this->assertStringContainsString('public function store(Request $request)', $controllerContent);
        $this->assertStringContainsString('public function show(', $controllerContent);
        $this->assertStringContainsString('public function update(Request $request,', $controllerContent);
        $this->assertStringContainsString('public function destroy(', $controllerContent);
        $this->assertStringNotContainsString('public function create()', $controllerContent);
        $this->assertStringNotContainsString('public function edit(', $controllerContent);
    }

    protected function assertMigrationCreated(): void
    {
        $migrationFiles = File::files(database_path("migrations"));
        $this->assertNotEmpty($migrationFiles);
        $migrationContent = file_get_contents($migrationFiles[0]->getPathname());
        $this->assertStringContainsString("\$table->string('name');", $migrationContent);
        $this->assertStringContainsString("\$table->string('email')->unique();", $migrationContent);
        $this->assertStringContainsString("\$table->integer('age');", $migrationContent);
        $this->assertStringContainsString("\$table->boolean('is_active');", $migrationContent);
    }

    protected function assertViewsCreated(): void
    {
        $viewsPath = resource_path("views" . DIRECTORY_SEPARATOR . Str::snake(Str::plural($this->modelName)));
//        dd($viewsPath);
        $this->assertDirectoryExists($viewsPath );
        $this->assertFileExists("{$viewsPath}/index.blade.php");
        $this->assertFileExists("{$viewsPath}/create.blade.php");
        $this->assertFileExists("{$viewsPath}/edit.blade.php");
        $this->assertFileExists("{$viewsPath}/show.blade.php");
    }

    protected function assertViewsNotCreated(): void
    {
        $viewsPath = resource_path("views/" . Str::snake(Str::plural($this->modelName)));
        $this->assertDirectoryDoesNotExist($viewsPath);
    }

    protected function assertSeederCreated(): void
    {
        $seederPath = database_path("seeders/{$this->modelName}Seeder.php");
        $this->assertFileExists($seederPath);
        $seederContent = file_get_contents($seederPath);
        $this->assertStringContainsString("class {$this->modelName}Seeder extends Seeder", $seederContent);
        $this->assertStringContainsString("\$this->call({$this->modelName}Seeder::class);", file_get_contents(database_path('seeders' . DIRECTORY_SEPARATOR . 'DatabaseSeeder.php')));
    }

    protected function assertFactoryCreated(): void
    {
        $factoryPath = database_path("factories" . DIRECTORY_SEPARATOR . "{$this->modelName}Factory.php");
        $this->assertFileExists($factoryPath);
        $factoryContent = file_get_contents($factoryPath);
        $this->assertStringContainsString("class {$this->modelName}Factory extends Factory", $factoryContent);
        $this->assertStringContainsString("'name' => \$this->faker->name", $factoryContent);
        $this->assertStringContainsString("'email' => \$this->faker->unique()->safeEmail", $factoryContent);
        $this->assertStringContainsString("'age' => \$this->faker->numberBetween(18, 80)", $factoryContent);
        $this->assertStringContainsString("'is_active' => \$this->faker->boolean", $factoryContent);
    }

    protected function assertPolicyCreated(): void
    {
        $policyPath = app_path("Policies/{$this->modelName}Policy.php");
        $this->assertFileExists($policyPath);
        $policyContent = file_get_contents($policyPath);
        $this->assertStringContainsString("class {$this->modelName}Policy", $policyContent);
        $this->assertStringContainsString("public function viewAny(User \$user)", $policyContent);
        $this->assertStringContainsString("public function view(User \$user, {$this->modelName} \$" . strtolower($this->modelName) . ")", $policyContent);
        $this->assertStringContainsString("public function create(User \$user)", $policyContent);
        $this->assertStringContainsString("public function update(User \$user, {$this->modelName} \$" . strtolower($this->modelName) . ")", $policyContent);
        $this->assertStringContainsString("public function delete(User \$user, {$this->modelName} \$" . strtolower($this->modelName) . ")", $policyContent);
    }

    protected function assertRequestsCreated(): void
    {
        $storeRequestPath = app_path("Http/Requests/Store{$this->modelName}Request.php");
        $updateRequestPath = app_path("Http/Requests/Update{$this->modelName}Request.php");
        $this->assertFileExists($storeRequestPath);
        $this->assertFileExists($updateRequestPath);
        $storeRequestContent = file_get_contents($storeRequestPath);
        $updateRequestContent = file_get_contents($updateRequestPath);
        $this->assertStringContainsString("class Store{$this->modelName}Request extends FormRequest", $storeRequestContent);
        $this->assertStringContainsString("class Update{$this->modelName}Request extends FormRequest", $updateRequestContent);
        $this->assertStringContainsString("'name' => 'required|string|max:255'", $storeRequestContent);
        $this->assertStringContainsString("'email' => 'required|string|email|max:255|unique:test_models'", $storeRequestContent);
        $this->assertStringContainsString("'age' => 'required|integer'", $storeRequestContent);
        $this->assertStringContainsString("'is_active' => 'boolean'", $storeRequestContent);
    }

    protected function assertResourceCreated(): void
    {
        $resourcePath = app_path("Http/Resources/{$this->modelName}Resource.php");
        $this->assertFileExists($resourcePath);
        $resourceContent = file_get_contents($resourcePath);
        $this->assertStringContainsString("class {$this->modelName}Resource extends JsonResource", $resourceContent);
        $this->assertStringContainsString("'id' => \$this->id", $resourceContent);
        $this->assertStringContainsString("'name' => \$this->name", $resourceContent);
        $this->assertStringContainsString("'email' => \$this->email", $resourceContent);
        $this->assertStringContainsString("'age' => \$this->age", $resourceContent);
        $this->assertStringContainsString("'is_active' => \$this->is_active", $resourceContent);
    }
}