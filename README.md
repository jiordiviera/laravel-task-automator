# Laravel Task Automator 📦✨
![Laravel Version](https://img.shields.io/badge/Laravel-7.x%20to%2011.x-red)
![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.0-blue)
![GitHub License](https://img.shields.io/github/license/jiordiviera/laravel-task-automator)
<p align="center">
    <a href="https://packagist.org/packages/mckenziearts/laravel-notify"><img src="https://poser.pugx.org/mckenziearts/laravel-notify/d/total.svg" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/mckenziearts/laravel-notify"><img src="https://poser.pugx.org/mckenziearts/laravel-notify/v/stable.svg" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/mckenziearts/laravel-notify"><img src="https://poser.pugx.org/mckenziearts/laravel-notify/license.svg" alt="License"></a>
</p>

Laravel Task Automator is a powerful Laravel package designed to supercharge your workflow by automating repetitive tasks like CRUD generation, authentication setup, seeder creation, API configuration, and more. Save time and focus on what truly matters—building awesome applications. 🚀

## ✨ Features
- **CRUD Generation**: Quickly generate CRUD scaffolding with models, controllers, migrations, views, and more.
- **API & Web Ready**: Supports both web-based and API-based controllers.
- **Advanced Options**: Includes seeding, form requests, policies, and testing capabilities.
- **Developer Productivity**: Generate files effortlessly and keep your application organized.

## 🚀 Installation

First, add Laravel Task Automator to your Laravel project using Composer:

```bash
composer require jiordiviera/laravel-task-automator:dev-main
```

Once installed, you can use the `make:crud` command to generate various components for your application.

## 📜 Usage

Laravel Task Automator makes it easy to generate a complete CRUD setup with just a single command:

```bash
php artisan make:crud {name} {--fields=} {--api} {--force} {--seed} {--policy} {--requests} {--resource} {--test}
```

### Arguments & Options:

- **`name`** (required): The name of the model. Example: `Post`, `User`.
- **`--fields`**: Fields for the migration in the format: `name:type,name:type,...`. Example: `title:string,content:text`.
- **`--api`**: Generate API-based controller and routes instead of web.
- **`--force`**: Force overwrite existing files if they already exist.
- **`--seed`**: Generate a seeder for the model.
- **`--policy`**: Generate a policy for the model.
- **`--requests`**: Generate form request classes for validation.
- **`--resource`**: Generate an API resource.
- **`--test`**: Generate feature or unit tests.

### Example Command:

```bash
php artisan make:crud Post --fields="title:string,content:text,is_published:boolean" --seed --policy --api
```

This command will generate:
- A **Post** model.
- A **migration** for the `posts` table with the specified fields.
- An **API Controller** (`PostController`) and corresponding **routes**.
- **Views** (if `--api` is not specified).
- A **Seeder** (`PostSeeder`).
- A **Policy** (`PostPolicy`).
- **Form Request** validation classes (`StorePostRequest`, `UpdatePostRequest`).
- An **API Resource** (`PostResource`).
- Feature or unit **tests**.

## 🛠️ Generated Files
Laravel Task Automator generates a complete set of files, customized to your application. Here’s a quick look at the generated components:

### 1. **Model** 🗂️
Path: `app/Models/Post.php`

The model is equipped with `fillable` properties based on the fields you specify.

### 2. **Migration** 📅
Path: `database/migrations/YYYY_MM_DD_HHMMSS_create_posts_table.php`

The migration is automatically set up with all specified fields.

### 3. **Controller** 🖇️
Path: `app/Http/Controllers/PostController.php`

Depending on the `--api` flag, either a web or API-based controller is generated with all CRUD methods.

### 4. **Views** (Optional) 👁️
Paths:
- `resources/views/post/index.blade.php`
- `resources/views/post/create.blade.php`
- `resources/views/post/edit.blade.php`
- `resources/views/post/show.blade.php`

Only generated if the `--api` flag is not set.

### 5. **Routes** 🚦
You’ll be prompted to add the generated routes to your application. You can add them to either the web or API routes file.

### 6. **Seeder** 🌱
Path: `database/seeders/PostSeeder.php`

This seeder uses Faker to populate your database with random but sensible data.

### 7. **Policy** 🔒
Path: `app/Policies/PostPolicy.php`

Generated to handle authorization for your model’s actions.

### 8. **Form Requests** 📜
Paths:
- `app/Http/Requests/StorePostRequest.php`
- `app/Http/Requests/UpdatePostRequest.php`

Handles validation logic for storing and updating the model.

### 9. **API Resource** 🌐
Path: `app/Http/Resources/PostResource.php`

Generated for transforming your model’s data in API responses.

### 10. **Tests** 🧪
Paths:
- `tests/Feature/PostTest.php` (or `tests/Unit/PostTest.php` for API)

Automatically generated feature or unit tests, making it easy to validate your generated CRUD.

## 📝 Advanced Usage

### Using Stubs
You can customize your own stubs for more control over the generated code. Laravel Task Automator uses stub files located in `stubs/`. Feel free to modify these to match your project’s style and standards.

To publish the stubs for customization:

```bash
php artisan vendor:publish --tag=laravel-task-automator-stubs
```

Edit the stubs to suit your needs, and Laravel Task Automator will use your versions for code generation.

## ⚙️ Configuration

Laravel Task Automator is designed to work out-of-the-box, but there are a few things you might want to adjust for a more tailored experience.

### Customizing Routes
You can modify the generated route template as per your app’s structure. You’ll find the routes in the stub files under `stubs/routes/`.

### Form Requests
Validation rules are generated automatically based on the provided fields, but you can edit the form request classes to add custom validation rules, messages, or other logic.

## 🛠️ Contributing
Contributions are welcome! If you have ideas, bug fixes, or improvements, please feel free to submit a pull request or open an issue on GitHub.

[GitHub Repository](https://github.com/jiordiviera/laravel-task-automator)

## 📝 License
Laravel Task Automator is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🫶 Acknowledgments
Special thanks to the Laravel community for creating an amazing framework that makes building web applications a joyful experience.

---

Feel free to enhance your development journey with Laravel Task Automator! Happy coding! 🎉🚀
