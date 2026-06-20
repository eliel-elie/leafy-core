<?php

use LeafyTech\Core\Config;

test('it can be instantiated with environment variables', function () {
    $env    = [
        'APP_ID'      => 'test-id',
        'APP_KEY'     => 'test-key',
        'APP_NAME'    => 'Test App',
        'APP_URL'     => 'http://localhost',
        'APP_DEBUG'   => 'true',
        'DB_DRIVER'   => 'mysql',
        'DB_HOST'     => '127.0.0.1',
        'DB_PORT'     => '3306',
        'DB_DATABASE' => 'test_db',
        'DB_USERNAME' => 'root',
        'DB_PASSWORD' => 'password',
        'DB_CHARSET'  => 'utf8mb4',
        'DB_PREFIX'   => 'tbl_',
    ];

    $config = new Config($env);

    expect($config->get('app'))->toBeArray()
        ->and($config->get('app')['id'])->toBe('test-id')
        ->and($config->get('app')['name'])->toBe('Test App')
        ->and($config->get('connections')['default']['driver'])->toBe('mysql')
        ->and($config->get('connections')['default']['database'])->toBe('test_db');
});

test('it allows dynamic property access via __get', function () {
    $env    = [
        'APP_KEY'     => 'test-key',
        'APP_NAME'    => 'Test App',
        'APP_URL'     => 'http://localhost',
        'APP_DEBUG'   => 'true',
        'DB_DRIVER'   => 'mysql',
        'DB_HOST'     => '127.0.0.1',
        'DB_PORT'     => '3306',
        'DB_DATABASE' => 'test_db',
        'DB_USERNAME' => 'root',
        'DB_PASSWORD' => 'password',
        'DB_CHARSET'  => 'utf8mb4',
        'DB_PREFIX'   => 'tbl_',
    ];

    $config = new Config($env);

    expect($config->app)->toBeArray()
        ->and($config->app['name'])->toBe('Test App')
        ->and($config->connections)->toBeArray()
        ->and($config->connections['default']['driver'])->toBe('mysql');
});

test('it can set and merge configurations', function () {
    $env    = [
        'APP_KEY'     => 'test-key',
        'APP_NAME'    => 'Test App',
        'APP_URL'     => 'http://localhost',
        'APP_DEBUG'   => 'true',
        'DB_DRIVER'   => 'mysql',
        'DB_HOST'     => '127.0.0.1',
        'DB_PORT'     => '3306',
        'DB_DATABASE' => 'test_db',
        'DB_USERNAME' => 'root',
        'DB_PASSWORD' => 'password',
        'DB_CHARSET'  => 'utf8mb4',
        'DB_PREFIX'   => 'tbl_',
    ];

    $config = new Config($env);

    // Test setting new key
    $config->set('custom', ['key' => 'value']);
    expect($config->get('custom'))->toBe(['key' => 'value']);

    // Test merging existing key
    $config->set('custom', ['new_key' => 'new_value']);
    expect($config->get('custom'))->toBe([
        'key'     => 'value',
        'new_key' => 'new_value',
    ]);
});

test('it can load configuration files', function () {
    $env      = [
        'APP_KEY'     => 'test-key',
        'APP_NAME'    => 'Test App',
        'APP_URL'     => 'http://localhost',
        'APP_DEBUG'   => 'true',
        'DB_DRIVER'   => 'mysql',
        'DB_HOST'     => '127.0.0.1',
        'DB_PORT'     => '3306',
        'DB_DATABASE' => 'test_db',
        'DB_USERNAME' => 'root',
        'DB_PASSWORD' => 'password',
        'DB_CHARSET'  => 'utf8mb4',
        'DB_PREFIX'   => 'tbl_',
    ];

    $config   = new Config($env);

    // Create a temporary configuration file
    $tempFile = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($tempFile, '<?php return ["foo" => "bar"];');

    try {
        $config->loadConfigFile('loaded', $tempFile);
        expect($config->get('loaded'))->toBeArray()
            ->and($config->get('loaded')['foo'])->toBe('bar');
    } finally {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }
});

test('it throws exception when config file is not readable', function () {
    $config = new Config([
        'APP_KEY'     => 'test-key',
        'APP_NAME'    => 'Test App',
        'APP_URL'     => 'http://localhost',
        'APP_DEBUG'   => 'true',
        'DB_DRIVER'   => 'mysql',
        'DB_HOST'     => '127.0.0.1',
        'DB_PORT'     => '3306',
        'DB_DATABASE' => 'test_db',
        'DB_USERNAME' => 'root',
        'DB_PASSWORD' => 'password',
        'DB_CHARSET'  => 'utf8mb4',
        'DB_PREFIX'   => 'tbl_',
    ]);

    $config->loadConfigFile('loaded', 'non-existent-file.php');
})->throws(InvalidArgumentException::class, 'Configuration file not found or not readable');

test('it throws exception when config file does not return array', function () {
    $config   = new Config([
        'APP_KEY'     => 'test-key',
        'APP_NAME'    => 'Test App',
        'APP_URL'     => 'http://localhost',
        'APP_DEBUG'   => 'true',
        'DB_DRIVER'   => 'mysql',
        'DB_HOST'     => '127.0.0.1',
        'DB_PORT'     => '3306',
        'DB_DATABASE' => 'test_db',
        'DB_USERNAME' => 'root',
        'DB_PASSWORD' => 'password',
        'DB_CHARSET'  => 'utf8mb4',
        'DB_PREFIX'   => 'tbl_',
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($tempFile, '<?php return "not an array";');

    try {
        $config->loadConfigFile('loaded', $tempFile);
    } finally {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }
})->throws(RuntimeException::class, 'Configuration file must return an array');
