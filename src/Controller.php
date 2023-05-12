<?php

namespace LeafyTech\Core;

use LeafyTech\Core\Middlewares\BaseMiddleware;

class Controller
{
    public string $layout = 'layout';
    public string $action = '';

    protected array $middlewares = [];

    public function setLayout($layout): void
    {
        $this->layout = $layout;
    }

    public function render($view, $params = [], $script = [], $css = []): string
    {
        return Application::$app->router->renderView($view, $params, $css, $script);
    }

    public function registerMiddleware(BaseMiddleware $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}