<?php

namespace LeafyTech\Core\Support\Language;

use LeafyTech\Core\Application;

/**
 * @method static get(string $key, array $replace = [], ?string $locale = null)
 * @method static has(string $key, ?string $locale = null)
 */
class Lang
{
    private static ?Translator $instance = null;

    public static function getInstance(): ?Translator
    {
        if (self::$instance === null) {
            self::$instance = Application::$app->translator;
        }

        return self::$instance;
    }

    public static function setInstance(Translator $translator): void
    {
        self::$instance = $translator;
    }

    /**
     * Handle dynamic, static calls to the Translator instance.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        $instance = self::getInstance();

        return call_user_func_array([$instance, $method], $arguments);
    }
}
