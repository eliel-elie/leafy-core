<?php

use LeafyTech\Core\Application;
use LeafyTech\Core\Helpers\FlashMessages;

/**
 * @runInSeparateProcess
 * @preserveGlobalState disabled
 */
test('it adds and displays flash messages', function () {
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

    if (!isset($_SESSION)) {
        $_SESSION = [];
    }

    $flash = new FlashMessages();
    $flash->info('Hello world');

    expect($_SESSION['flash_messages'])->toBeArray()
        ->and($_SESSION['flash_messages'])->toHaveCount(1);
});

afterEach(function () {
    restore_error_handler();
    restore_exception_handler();
});
