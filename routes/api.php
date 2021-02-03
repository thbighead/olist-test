<?php

use Illuminate\Support\Facades\Route;

defined('API_CONTROLLERS_PATH_FROM_APP_DIRECTORY')
    ?: define('API_CONTROLLERS_PATH_FROM_APP_DIRECTORY', 'Http/Controllers/API');

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
|--------------------------------------------------------------------------
| Automatically loading Laravel API Rest Resource Controller's methods
| routes.
|--------------------------------------------------------------------------
|
| Please remember to use php artisan route:cache command when preparing
| this project for non-development environments to avoid this code being
| executed everytime a route is asked by Laravel.
|
*/
$api_versions_controllers_path = app_path(API_CONTROLLERS_PATH_FROM_APP_DIRECTORY);

foreach (scandir($api_versions_controllers_path) as $api_version) { // Reading API versions available
    if (!preg_match('/^V\d+\D*/', $api_version)) {
        continue; // skipping paths which aren't API versions folders for this project
    }

    Route::prefix(strtolower($api_version))->group(function () use ($api_version) {
        $api_controllers_path = app_path(API_CONTROLLERS_PATH_FROM_APP_DIRECTORY . "/{$api_version}");
        foreach (scandir($api_controllers_path) as $controller_filename) {
            if ($controller_filename === 'Controller.php' || !Str::endsWith($controller_filename, '.php')) {
                continue; // skipping paths which aren't Controllers developed for this project
            }

            $controller_name = substr($controller_filename, 0, -4); // removing '.php' from filename
            $controller_namespace = "\\App\\Http\\Controllers\\API\\$api_version\\$controller_name";

            Route::apiResource(
                Str::snake(Str::before($controller_name, 'Controller')),
                $controller_namespace
            )->only(get_class_methods($controller_namespace));
        }
    });
}
