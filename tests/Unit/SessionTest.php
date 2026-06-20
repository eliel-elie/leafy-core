<?php

use LeafyTech\Core\Application;
use LeafyTech\Core\Session;

/**
 * @runInSeparateProcess
 * @preserveGlobalState disabled
 */
test('it starts session and sets/gets variables', function () {
    $_ENV['APP_ID'] = 'test-id';
    $_ENV['APP_KEY'] = 'test-key';
    $_ENV['APP_NAME'] = 'Test App';
    $_ENV['APP_URL'] = 'http://localhost';
    $_ENV['APP_DEBUG'] = 'true';
    $_ENV['DB_DRIVER'] = 'mysql';
    $_ENV['DB_HOST'] = '127.0.0.1';
    $_ENV['DB_PORT'] = '3306';
    $_ENV['DB_DATABASE'] = 'test_db';
    $_ENV['DB_USERNAME'] = 'root';
    $_ENV['DB_PASSWORD'] = 'password';
    $_ENV['DB_CHARSET'] = 'utf8mb4';
    $_ENV['DB_PREFIX'] = 'tbl_';

    new Application(__DIR__);

    $session = new Session();
    $session->set('test_var', 'hello');

    expect($session->get('test_var'))->toBe('hello')
        ->and($_SESSION['test_var'])->toBe('hello');

    $session->remove('test_var');
    expect($session->get('test_var'))->toBeFalse();
});

afterEach(function () {
    restore_error_handler();
    restore_exception_handler();
});
