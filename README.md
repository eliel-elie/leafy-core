# Leafy Core

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Leafy Core is a minimalistic and flexible PHP micro-framework designed for building web applications and APIs quickly. It leverages the powerful Eloquent ORM from Laravel for database interactions and provides a simple but effective routing system, a straightforward view layer, and a command-line interface for common tasks.

## Features

-   **Simple & Fast Routing**: A clean and easy-to-use router supporting parameters and controller bindings.
-   **Eloquent ORM Integration**: Harness the full power of Laravel's Eloquent for database operations, including relationships, migrations, and seeding. It comes with `yajra/laravel-oci8` for Oracle DB support.
-   **View Layer**: A simple template system that uses plain PHP files for views and layouts.
-   **Middleware Support**: Protect your routes and manage requests with middleware.
-   **CLI Commands**: Includes a console command runner for database migrations, key generation, and more.
-   **Centralized Configuration**: Easy environment-based configuration using `.env` files.
-   **Error Handling**: Pre-configured to capture and log all uncaught exceptions and errors.

## Installation

You can install Leafy Core via Composer.

```bash
composer require elielelie/leafy-core
```

## Configuration

### Environment File

Configuration is handled through an `.env` file in the root of your project. To get started, copy the `.env.example` file to `.env`.

```bash
cp .env.example .env
```

Then, update the variables in your new `.env` file to match your environment. A unique `APP_KEY` can be generated using the CLI command.

```bash
php console key:generate
```

### Key Environment Variables

-   `APP_NAME`: The name of your application.
-   `APP_KEY`: A unique, 32-character random string for encryption.
-   `APP_URL`: The base URL of your application.
-   `APP_FOLDER`: If your app is in a subfolder, specify it here (e.g., `/myapp`).
-   `APP_DEBUG`: Set to `true` for development, `false` for production.
-   `DB_DRIVER`: Database driver (e.g., `mysql`, `pgsql`, `oracle`).
-   `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Database credentials.

## Application Structure

Leafy Core is a library, but it's designed to be used within a project structure like this:

```
.
├── app/
│   ├── Controllers/
│   ├── Middlewares/
│   └── Models/
├── config/
├── public/
│   └── index.php
├── resources/
│   ├── views/
│   └── lang/
├── routes/
│   └── web.php
├── storage/
├── .env
└── composer.json
```

### Bootstrap

Your application's entry point is typically `public/index.php`. Here you will bootstrap the Leafy Core application.

```php
<?php

require_once __DIR__ . "/../vendor/autoload.php";

use LeafyTech\\Core\\Application;

// Define ROOT_DIR
define(\'ROOT_DIR\', dirname(__DIR__));

// Load environment variables
$dotenv = Dotenv\\Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();

$app = new Application(ROOT_DIR);

$app->boot()->run();
```

## Core Concepts

### Routing

Routes are defined in files within the `routes/` directory. The `RouteHelper` automatically includes all PHP files from this directory. You can define routes using the `LeafyTech\Core\Router` class, which is globally available.

**Basic Routes**

```php
// routes/web.php

use LeafyTech\Core\Router;

$router = new Router();

// Closure-based route
$router->get("/", function () {
    return "Hello, World!";
});

// Route to a controller method
$router->get("/users", [App\\Controllers\\UserController::class, "index"]);

// Route with parameters
$router->get("/users/{id}", [App\\Controllers\\UserController::class, "show"]);
```

**Available Router Methods**

```php
$router->get($uri, $callback);
$router->post($uri, $callback);
$router->put($uri, $callback);
$router->patch($uri, $callback);
$router->delete($uri, $callback);
```

### Controllers

Controllers are used to group related request-handling logic. They should extend the base `LeafyTech\Core\Controller`.

```php
<?php

namespace App\\Controllers;

use LeafyTech\\Core\\Controller;
use LeafyTech\\Core\\Request;

class UserController extends Controller
{
    public function index()
    {
        // Logic to fetch all users
        $users = UserModel::all();
        return $this->render('users/index', ['users' => $users]);
    }

    public function show(Request $request)
    {
        $params = $request->getRouteParams();
        $user = UserModel::find($params['id']);
        return $this->render('users/show', ['user' => $user]);
    }
}
```

### Request and Response

-   **Request**: The `LeafyTech\Core\Request` class provides methods to access the incoming request data, such as `getMethod()`, `getUrl()`, `getBody()`, and `getRouteParams()`.
-   **Response**: The `LeafyTech\Core\Response` class can be used to send responses, like redirects: `response()->redirect('/login')`. For JSON responses, you can simply return an array from your controller, and the framework will handle it (assuming proper headers are set).

### Views

Views are plain PHP files stored in the `resources/views` directory. You can pass data to views from your controllers.

The `render` method in a controller renders a view within a layout file. By default, it looks for `resources/templates/layout.php`. The layout file should contain `{{content}}` where the view will be injected.

**Example View (`resources/views/users/index.php`)**

```php
<h1>All Users</h1>
<ul>
    <?php foreach ($users as $user):
        ?>
        <li><?= htmlspecialchars($user->name) ?></li>
    <?php
    endforeach;
    ?>
</ul>
```

### Database & Eloquent ORM

Leafy Core uses `illuminate/database` (Eloquent). Once your database connection is configured in `.env`, you can use Eloquent models just like in Laravel.

**Example Model (`app/Models/User.php`)**

```php
<?php

namespace App\\Models;

use Illuminate\Database\\Eloquent\\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
}
```

**Querying the Database**

```php
// Find a user by ID
$user = User::find(1);

// Get all users
$users = User::all();

// A more complex query
$activeUsers = User::where('active', 1)->orderBy('name')->get();
```

### Middleware

Middleware provide a mechanism for filtering HTTP requests. To create a middleware, extend the `LeafyTech\Core\Middlewares\BaseMiddleware` class and implement the `execute` method.

**Example Middleware (`app/Middlewares/LogMiddleware.php`)**

```php
<?php

namespace App\\Middlewares;

use LeafyTech\\Core\\Middlewares\\BaseMiddleware;
use LeafyTech\\Core\\Support\\Log\\Log;

class LogMiddleware extends BaseMiddleware
{
    public function execute()
    {
        Log::info('Request to: ' . app()->request->getUrl());
    }
}
```

You can register middleware in your controller's constructor.

```php
// In a Controller
public function __construct()
{
    $this->registerMiddleware(new LogMiddleware());
}
```

### Command-Line Interface (CLI)

Leafy Core comes with a console application to run commands. You need a `console` file in your project root to run it.

**Example `console` file:**

```php
#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use LeafyTech\\Core\\Application;
use Symfony\\Component\\Console\\Application as ConsoleApplication;

define(\'ROOT_DIR\', __DIR__);

$dotenv = Dotenv\\Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();

$app = new Application(ROOT_DIR);

$console = new ConsoleApplication('Leafy Core Console');

// Register Commands
$console->add(new LeafyTech\\Core\\Commands\\KeyGenerateCommand());
$console->add(new LeafyTech\\Core\\Commands\\MigrateCommand());
// ... add other commands

$console->run();
```

**Available Commands:**

-   `key:generate`: Generates a new `APP_KEY`.
-   `make:migration`: Creates a new migration file.
-   `migrate`: Runs the database migrations.
-   `migrate:rollback`: Rolls back the last database migration.
-   `migrate:status`: Shows the status of each migration.
-   `app:up`: Brings the application out of maintenance mode.
-   `app:down`: Puts the application into maintenance mode.

### Error Handling

The framework automatically sets up a robust error handler that logs all `E_ERROR`, `E_PARSE`, uncaught exceptions, and other fatal errors using the built-in `Log` facade. In a production environment (`APP_DEBUG=false`), it will display a generic 500 error page to the user instead of exposing sensitive error details.

## Testing

To run the test suite, you can use PHPUnit.

```bash
./vendor/bin/phpunit
```

## License

Leafy Core is open-source software licensed under the [MIT License](LICENSE).
