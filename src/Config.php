<?php

namespace LeafyTech\Core;

use InvalidArgumentException;
use RuntimeException;

class Config
{
    protected array $config = [];

    public function __construct(array $env)
    {
        $this->config = [
            'app'    => [
                'id'         => $env['APP_ID']          ?? null,
                'key'        => $env['APP_KEY'],
                'name'       => $env['APP_NAME'],
                'url'        => $env['APP_URL'],
                'folder'     => $env['APP_FOLDER']      ?? null,
                'debug'      => $env['APP_DEBUG'],
                'auth'       => $env['APP_AUTH']        ?? null,
                'prefix'     => $env['APP_PREFIX']      ?? null,
                'devPrefix'  => $env['APP_DEV_PREFIX']  ?? null,
                'locale'     => $env['APP_LOCALE']      ?? 'en',
            ],
            'connections' => [
                'default'     => [
                    'driver'   => $env['DB_DRIVER'],
                    'host'     => $env['DB_HOST'],
                    'port'     => $env['DB_PORT'],
                    'database' => $env['DB_DATABASE'],
                    'username' => $env['DB_USERNAME'],
                    'password' => $env['DB_PASSWORD'],
                    'charset'  => $env['DB_CHARSET'],
                    'prefix'   => $env['DB_PREFIX'],
                ],
            ],
            'ldap' => [
                'host'          => $env['LDAP_HOST']            ?? '',
                'port'          => $env['LDAP_PORT']            ?? '',
                'baseDn'        => $env['LDAP_BASE_DN']         ?? '',
                'username'      => $env['LDAP_USERNAME']        ?? '',
                'password'      => $env['LDAP_PASSWORD']        ?? '',
                'accountPrefix' => $env['LDAP_ACCOUNT_PREFIX']  ?? '',
                'options'       => [
                    'version'         => $env['LDAP_OPTION_VERSION']           ?? '',
                    'followReferrals' => $env['LDAP_OPTION_FOLLOW_REFERRALS']  ?? '',
                ],
                'photoUrl'      => $env['LDAP_PHOTO_URL']       ?? '',
            ],
        ];
    }

    public function set(string $key, array $value): self
    {
        if (array_key_exists($key, $this->config)) {
            $this->config[$key] = array_merge($this->config[$key], $value);
        } else {
            $this->config[$key] = $value;
        }
        return $this;
    }

    /**
     * Loads a configuration file and stores its contents under the given key.
     *
     * @param  string  $key
     * @param  string  $filePath
     * @return mixed
     *
     * @throws InvalidArgumentException  If the file does not exist or is not readable.
     * @throws RuntimeException          If the file does not return a valid array.
     */
    public function loadConfigFile(string $key, string $filePath): mixed
    {
        if (! is_readable($filePath)) {
            throw new InvalidArgumentException("Configuration file not found or not readable: {$filePath}");
        }

        /** @var mixed $config */
        $config = require $filePath;

        if (! is_array($config)) {
            throw new RuntimeException("Configuration file must return an array: {$filePath}");
        }

        return $this->set($key, $config);
    }

    public function get(string $name)
    {
        return $this->config[$name] ?? null;
    }

    public function __get(string $name)
    {
        return $this->config[$name] ?? null;
    }
}