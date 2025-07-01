<?php

use LeafyTech\Core\Application;
use LeafyTech\Core\Helpers\Env;
use LeafyTech\Core\Helpers\FlashMessages;
use LeafyTech\Core\Request;
use LeafyTech\Core\Response;
use LeafyTech\Core\Session;
use LeafyTech\Core\Support\Language\Translator;

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

if (!function_exists('resource_path')) {
    function resource_path($path = ''): bool|string
    {
        $basePath = dirname(__DIR__, 2);

        $resourcePath = $basePath . DIRECTORY_SEPARATOR . 'resources';

        if ($path) {
            $resourcePath .= DIRECTORY_SEPARATOR . ltrim($path, '/\\');
        }

        return realpath($resourcePath) ?: $resourcePath;
    }
}

if (!function_exists('response')) {
    function response($content = '', $data = [], $status = 200, $headers = []): Response
    {
        return new Response($content, $data, $status, $headers);
    }
}

if (!function_exists('request')) {
    function request(): Request
    {
        return Application::$app->router->getRequest();
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
        $fileNameWithPath = app()->basePath( $dirRoot.DIRECTORY_SEPARATOR. $file . '.' . $type);
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

if (!function_exists('__')) {
    function __($key, $replace = [], $locale = null): string
    {
        if (!isset(app()->translator) || !(app()->translator instanceof Translator)) {
            throw new Exception('Translator is not initialized. Make sure to create an instance of Translator before using the helper functions.');
        }

        return app()->translator->get($key, $replace, $locale);
    }
}

if (!function_exists('trans')) {
    function trans($key, $replace = [], $locale = null): string
    {
        return __($key, $replace, $locale);
    }
}

if (!function_exists('trans_choice')) {
    function trans_choice($key, $number, $replace = [], $locale = null): string
    {
        if (!isset(app()->translator) || !(app()->translator instanceof Translator)) {
            throw new Exception('Translator is not initialized. Make sure to create an instance of Translator before using the helper functions.');
        }

        return app()->translator->choice($key, $number, $replace, $locale);
    }
}

if (!function_exists('set_locale')) {
    function set_locale($locale): void
    {
        if (!isset(app()->translator) || !(app()->translator instanceof Translator)) {
            throw new Exception('Translator is not initialized..');
        }

        app()->translator->setLocale($locale);
    }
}

if (!function_exists('get_locale')) {
    function get_locale(): string
    {
        if (!isset(app()->translator) || !(app()->translator instanceof Translator)) {
            throw new Exception('Translator is not initialized..');
        }

        return app()->translator->getLocale();
    }
}

if (!function_exists('trans_exists')) {
    function trans_exists($key, $locale = null): bool
    {
        if (!isset(app()->translator) || !(app()->translator instanceof Translator)) {
            throw new Exception('Translator is not initialized.');
        }

        return app()->translator->has($key, $locale);
    }
}

if (!function_exists('add_translation')) {
    function add_translation($locale, $group, $key, $value): void
    {
        if (!isset(app()->translator) || !(app()->translator instanceof Translator)) {
            throw new Exception('Translator is not initialized.');
        }

        app()->translator->addTranslation($locale, $group, $key, $value);
    }
}