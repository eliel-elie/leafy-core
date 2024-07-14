<?php

namespace LeafyTech\Core;

class Config
{
    protected array $config = [];

    public function __construct(array $env)
    {
        $this->config = [
            'app'    => [
                'id'         => $env['APP_ID'],
                'key'        => $env['APP_KEY'],
                'name'       => $env['APP_NAME'],
                'url'        => $env['APP_URL'],
                'folder'     => $env['APP_FOLDER'] ?? null,
                'debug'      => $env['APP_DEBUG'],
                'auth'       => $env['APP_AUTH'],
                'prefix'     => $env['APP_PREFIX'] ?? null,
                'devPrefix'  => $env['APP_DEV_PREFIX'] ?? null,
            ],
            'db'     => [
                'driver'   => $env['DB_DRIVER'],
                'host'     => $env['DB_HOST'],
                'port'     => $env['DB_PORT'],
                'database' => $env['DB_DATABASE'],
                'username' => $env['DB_USERNAME'],
                'password' => $env['DB_PASSWORD'],
                'charset'  => $env['DB_CHARSET'],
                'prefix'   => $env['DB_PREFIX'],
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

    public function __get(string $name)
    {
        return $this->config[$name] ?? null;
    }
}