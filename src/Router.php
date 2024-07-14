<?php

namespace LeafyTech\Core;

use LeafyTech\Core\Exception\NotFoundException;

class Router
{
    private Request $request;
    private Response $response;
    protected array $routes = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response= $response;
    }

    public function register(string $requestMethod, string $route, $callback): self
    {
        if(env('APP_FOLDER') !== null && !empty(env('APP_FOLDER'))) {
            $this->routes[$requestMethod][env('APP_FOLDER').$route] = $callback;
        } else {
            $this->routes[$requestMethod][$route] = $callback;
        }
        return $this;
    }

    public function routes(): array
    {
        return $this->routes;
    }

    public function get($route, $callback): Router
    {
        return $this->register('GET', $route, $callback);
    }

    public function post($route, $callback): Router
    {
        return $this->register('POST', $route, $callback);
    }

    public function put($route, $callback): Router
    {
        return $this->register('PUT', $route, $callback);
    }

    public function patch($route, $callback): Router
    {
        return $this->register('PATCH', $route, $callback);
    }

    public function delete($route, $callback): Router
    {
        return $this->register('DELETE', $route, $callback);
    }

    public function resolve()
    {
        $method   = $this->request->getMethod();
        $url      = $this->request->getUrl();
        $callback = $this->routes[$method][$url] ?? false;

        if (!$callback) {

            $callback = $this->getCallback();

            if ($callback === false) {
                throw new NotFoundException();
            }

        }

        if (is_string($callback)) {
            return $this->renderView($callback);
        }

        if (is_array($callback)) {

            /**
             * @var $controller Controller
             */
            $controller         = new $callback[0];
            $controller->action = $callback[1];

            Application::$app->controller = $controller;

            $middlewares = $controller->getMiddlewares();

            foreach ($middlewares as $middleware) {
                $middleware->execute();
            }

            $callback[0] = $controller;
        }

        return call_user_func($callback, $this->request, $this->response);

    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getRouteMap($method): array
    {
        return $this->routes[$method] ?? [];
    }

    public function getCallback()
    {
        $method = $this->request->getMethod();
        $url    = $this->request->getUrl();
        $url    = trim($url, '/');

        $routes = $this->getRouteMap($method);

        $routeParams = false;

        foreach ($routes as $route => $callback) {

            $route      = trim($route, '/');
            $routeNames = [];

            if (!$route) continue;

            if (preg_match_all('/\{(\w+)(:[^}]+)?}/', $route, $matches)) {
                $routeNames = $matches[1];
            }

            $routeRegex = "@^" . preg_replace_callback('/\{\w+(:([^}]+))?}/', fn($m) => isset($m[2]) ? "({$m[2]})" : '(\w+)', $route) . "$@";

            if (preg_match_all($routeRegex, $url, $valueMatches)) {
                $values = [];
                for ($i = 1; $i < count($valueMatches); $i++) {
                    $values[] = $valueMatches[$i][0];
                }
                $routeParams = array_combine($routeNames, $values);

                $this->request->setRouteParams($routeParams);
                return $callback;
            }
        }

        return false;
    }

    public function renderView($view, $params = [], $css = [], $script = [])
    {
        return Application::$app->view->renderView($view, $params, $css, $script);
    }

    public function renderViewOnly($view, $params = [])
    {
        return Application::$app->view->renderViewOnly($view, $params);
    }

}