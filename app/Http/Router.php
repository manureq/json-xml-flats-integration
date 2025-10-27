<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PropertyController;
use App\Support\Csrf;

final class Router
{
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

        if ($method === 'GET' && $uri === '/properties' && isset($_GET['id'])) {
            (new PropertyController())->showByQuery();
            return;
        }

        $routes = $this->routes();
        $key = $method . ':' . $uri;

        if (!isset($routes[$key])) {
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        $handler = $routes[$key];
        [$controller, $action] = $handler;
        $instance = new $controller();

        if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
            $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!Csrf::validate($token)) {
                http_response_code(419);
                echo 'Invalid CSRF token';
                return;
            }
            Csrf::regenerate();
        }

        $instance->$action();
    }

    /**
     * @return array<string, array{0: class-string, 1: string}>
     */
    private function routes(): array
    {
        return [
            'GET:/' => [DashboardController::class, 'index'],
            'GET:/properties/create' => [PropertyController::class, 'create'],
            'POST:/properties' => [PropertyController::class, 'store'],
            'GET:/properties' => [PropertyController::class, 'index'],
            'GET:/properties/' => [PropertyController::class, 'showByQuery'],
            'GET:/properties/edit' => [PropertyController::class, 'edit'],
            'POST:/properties/update' => [PropertyController::class, 'update'],
            'POST:/properties/delete' => [PropertyController::class, 'destroy'],
            'GET:/feeds/json' => [FeedController::class, 'json'],
            'GET:/feeds/xml' => [FeedController::class, 'xml'],
            'GET:/admin' => [AdminController::class, 'index'],
            'POST:/admin/settings' => [AdminController::class, 'updateSettings'],
            'POST:/admin/portals' => [AdminController::class, 'updatePortals'],
            'POST:/admin/import' => [AdminController::class, 'importXml'],
        ];
    }
}
