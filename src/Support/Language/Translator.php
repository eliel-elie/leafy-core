<?php

namespace LeafyTech\Core\Support\Language;

class Translator
{
    private string $locale         = 'en';

    private string $fallbackLocale = 'en';

    private string $translationsPath;

    private array $translations    = [];

    private array $loadedFiles     = [];

    public function __construct(string $translationsPath = 'lang')
    {
        $this->translationsPath = base_path($translationsPath);
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setFallbackLocale(string $locale): void
    {
        $this->fallbackLocale = $locale;
    }

    private function loadTranslationFile(string $locale, string $group): bool
    {
        $fileKey                             = "{$locale}.{$group}";

        if (isset($this->loadedFiles[$fileKey])) {
            return true;
        }

        $filePath                            = "{$this->translationsPath}/{$locale}/{$group}.php";

        if (!file_exists($filePath)) {
            return false;
        }

        $translations                        = include $filePath;

        if (!is_array($translations)) {
            return false;
        }

        $this->translations[$locale][$group] = $translations;
        $this->loadedFiles[$fileKey]         = true;

        return true;
    }

    private function getTranslationFromArray(array $array, string $key): mixed
    {
        $keys  = explode('.', $key);
        $value = $array;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale         = $locale ?? $this->locale;

        $parts          = explode('.', $key, 2);

        if (count($parts) < 2) {
            return $key;
        }

        [$group, $item] = $parts;

        if (! $this->loadTranslationFile($locale, $group)) {
            if ($locale !== $this->fallbackLocale) {
                return $this->get($key, $replace, $this->fallbackLocale);
            }

            return $key;
        }

        $translation    = $this->getTranslationFromArray(
            $this->translations[$locale][$group] ?? [],
            $item
        );

        if ($translation === null && $locale !== $this->fallbackLocale) {
            return $this->get($key, $replace, $this->fallbackLocale);
        }

        if ($translation === null) {
            return $key;
        }

        return $this->makeReplacements($translation, $replace);
    }

    public function has(string $key, ?string $locale = null): bool
    {
        $locale         = $locale ?? $this->locale;
        $parts          = explode('.', $key, 2);

        if (count($parts) < 2) {
            return false;
        }

        [$group, $item] = $parts;

        if (!$this->loadTranslationFile($locale, $group)) {
            if ($locale !== $this->fallbackLocale) {
                return $this->has($key, $this->fallbackLocale);
            }

            return false;
        }

        $translation    = $this->getTranslationFromArray(
            $this->translations[$locale][$group] ?? [],
            $item
        );

        return $translation !== null;
    }

    private function makeReplacements(string $line, array $replace): string
    {
        if (empty($replace)) {
            return $line;
        }

        foreach ($replace as $key => $value) {
            $line = str_replace(
                [':' . $key, ':' . strtoupper($key), ':' . ucfirst($key)],
                [$value, strtoupper($value), ucfirst($value)],
                $line
            );
        }

        return $line;
    }

    public function choice(string $key, int $number, array $replace = [], ?string $locale = null): string
    {
        $line             = $this->get($key, $replace, $locale);

        if (strpos($line, '|') !== false) {
            $segments = explode('|', $line);

            if ($number === 0 && isset($segments[0])) {
                $line = trim($segments[0]);
            } elseif ($number === 1 && isset($segments[1])) {
                $line = trim($segments[1]);
            } elseif ($number > 1 && isset($segments[2])) {
                $line = trim($segments[2]);
            } elseif (isset($segments[1])) {
                $line = trim($segments[1]);
            }
        }

        $replace['count'] = $number;

        return $this->makeReplacements($line, $replace);
    }

    public function addTranslation(string $locale, string $group, string $key, string $value): void
    {
        if (!isset($this->translations[$locale])) {
            $this->translations[$locale] = [];
        }

        if (!isset($this->translations[$locale][$group])) {
            $this->translations[$locale][$group] = [];
        }

        $keys  = explode('.', $key);
        $array = &$this->translations[$locale][$group];

        foreach ($keys as $segment) {
            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                $array[$segment] = [];
            }
            $array = &$array[$segment];
        }

        $array = $value;
    }
}
