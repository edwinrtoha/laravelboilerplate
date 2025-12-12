<?php
namespace Edwinrtoha\Laravelboilerplate;

use Edwinrtoha\Laravelboilerplate\Http\Controllers\ApiController;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Route;

class Router
{
    public string $prefix = '';
    public string $controller = ApiController::class;

    public function generate(string $method, string $path, string $controller, string $func, array $middleware = [], bool $login = false)
    {
        $router = Route::$method($path, [$controller, $func])->middleware($middleware);
        if ($login) {
            $router = $router->middleware('auth:sanctum');
        }

        return $router;
    }

    public function generateGroup(string $prefix = '', string $controller, array $login = [], callable $before = null, callable $after = null, callable $custom = null)
    {
        $this->prefix = $prefix;
        $this->controller = $controller;
        Route::group(['prefix' => $this->prefix], function () use ($login, $before, $after, $custom) {
            if ($before != null) {
                $before($this);
            }
            if ($custom == null) {
                $this->generate('get', '/', $this->controller, 'index', login: in_array('index', $login) || in_array('*', $login));
                $this->generate('post', '/', $this->controller, 'store', login: in_array('store', $login) || in_array('*', $login));
                
                $controllerInstance = new $this->controller;
                // Use class_uses_recursive to check for SoftDeletes trait in parent classes as well
                if (in_array(SoftDeletes::class, class_uses_recursive($controllerInstance->model))) {
                    Route::group(['prefix' => 'trash'], function () use ($login) {
                        $this->generate('get', '/', $this->controller, 'trashed', login: in_array('trashed', $login) || in_array('*', $login));
                        $this->generate('get', '/{id}/restore', $this->controller, 'restore', login: in_array('restore', $login) || in_array('*', $login));
                        $this->generate('delete', '/{id}', $this->controller, 'forceDelete', login: in_array('forceDelete', $login) || in_array('*', $login));
                    });
                }

                $this->generate('get', '/{id}', $this->controller, 'show', login: in_array('show', $login) || in_array('*', $login));
                $this->generate('post', '/{id}', $this->controller, 'update', login: in_array('update', $login) || in_array('*', $login));
                $this->generate('delete', '/{id}', $this->controller, 'destroy', login: in_array('destroy', $login) || in_array('*', $login));

            } else {
                $custom($this);
            }
            if ($after != null) {
                $after($this);
            }
        });
    }
}