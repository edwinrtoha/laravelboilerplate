<?php
namespace Edwinrtoha\Laravelboilerplate;

use Edwinrtoha\Laravelboilerplate\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

class Router
{
    var $prefix = '';
    var $controller = ApiController::class;

    public function generate($method, $path, $controller, $func, $middleware = [], $login = false)
    {
        $router = Route::$method($path, [$controller, $func])->middleware($middleware);
        if ($login) {
            $router = $router->middleware('auth:sanctum');
        }

        return $router;
    }
    public function generateGroup($prefix = '', $controller, $login = [], callable $before = null, callable $after = null, callable $custom = null)
    {
        $this->prefix = $prefix;
        $this->controller = $controller;
        Route::group(['prefix' => $this->prefix], function () use ($login, $before, $after, $custom) {
            if ($before != null) {
                $before();
            }
            if ($custom == null) {
                $this->generate('get', '/', $this->controller, 'index', login: in_array('index', $login) || in_array('*', $login));
                $this->generate('post', '/', $this->controller, 'store', login: in_array('store', $login) || in_array('*', $login));
                
                $controllerInstance = new $this->controller;
                if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($controllerInstance->model))) {
                    Route::group(['prefix' => 'trash'], function () use ($login) {
                        $this->generate('get', '/', $this->controller, 'trashed', login: in_array('trashed', $login) || in_array('*', $login));
                        $this->generate('get', '/{id}/restore', $this->controller, 'restore', login: in_array('restore', $login) || in_array('*', $login));
                        $this->generate('delete', '/{id}', $this->controller, 'forceDelete', login: in_array('forceDelete', $login) || in_array('*', $login));
                    });
                }

                $this->generate('get', '/{id}', $this->controller, 'show', login: in_array('show', $login) || in_array('*', $login));
                $this->generate('post', '/{id}', $this->controller, 'update', login: in_array('update', $login) || in_array('*', $login));
                $this->generate('delete', '/{id}', $this->controller, 'destroy', login: in_array('destroy', $login) || in_array('*', $login));

            }
            if ($after != null) {
                $after();
            }
        });
    }
}