<?php

use LeafyTech\Core\Application;
use LeafyTech\Core\Helpers\Env;
use LeafyTech\Core\Helpers\FlashMessages;
use LeafyTech\Core\Response;
use LeafyTech\Core\Session;

if (!function_exists('get_request_headers')) {
    function get_request_headers(): array
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

if (!function_exists('response')) {
    function response($content = '', $data = [], $status = 200, $headers = []): Response
    {
        return new Response($content, $data, $status, $headers);
    }
}
if (! function_exists('app')) {
    function app(): Application
    {
        return Application::$app;
    }
}

if (! function_exists('event')) {
    function event(...$args): ?array
    {
        return Application::$app->events->dispatch(...$args);
    }
}

if (! function_exists('env')) {
    function env($key, $default = null)
    {
        return Env::get($key, $default);
    }
}

if (! function_exists('flashMessage')) {
    function flashMessage(): FlashMessages
    {
        return Application::$app->session->flashMessage();
    }
}

if (! function_exists('session')) {
    function session(): Session
    {
        return Application::$app->session;
    }
}
if (! function_exists('fileVersion')) {
    function fileVersion($file, $type = 'js', $dirRoot = 'templates')
    {
        $fileNameWithPath = Application::$ROOT_DIR . '/' . $dirRoot . '/' . $file . '.' . $type;
        if (!file_exists($fileNameWithPath)) return $file;
        return filemtime($fileNameWithPath);;
    }
}

if (! function_exists('now')) {
    function now(): DateTime
    {
        return new DateTime();
    }
}

if (! function_exists('base_path')) {
    function base_path($path = ''): string
    {
        return app()->basePath($path);
    }
}