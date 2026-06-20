<?php

use LeafyTech\Core\Application;
use LeafyTech\Core\View;

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'leafy_view_test_' . uniqid();
    mkdir($this->tempDir);
    mkdir($this->tempDir . DIRECTORY_SEPARATOR . 'views');
    mkdir($this->tempDir . DIRECTORY_SEPARATOR . 'templates');

    file_put_contents(
        $this->tempDir . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'test-view.php',
        '<?php echo "view content " . $foo; ?>'
    );

    file_put_contents(
        $this->tempDir . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'test-view-layout.php',
        '<?php echo "view content " . $foo; ?>'
    );

    file_put_contents(
        $this->tempDir . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'layout.php',
        'layout start {{content}} layout end'
    );

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

    new Application($this->tempDir);
});

afterEach(function () {
    // Clean up temporary files
    $files = [
        $this->tempDir . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'test-view.php',
        $this->tempDir . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'test-view-layout.php',
        $this->tempDir . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'layout.php',
    ];
    foreach ($files as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
    @rmdir($this->tempDir . DIRECTORY_SEPARATOR . 'views');
    @rmdir($this->tempDir . DIRECTORY_SEPARATOR . 'templates');
    @rmdir($this->tempDir);

    restore_error_handler();
    restore_exception_handler();
});

test('it renders views with parameters', function () {
    $view = new View();

    $contentOnly = $view->renderViewOnly('test-view', ['foo' => 'bar']);
    expect($contentOnly)->toBe('view content bar');

    $fullView = $view->renderView('test-view-layout', ['foo' => 'bar']);
    expect($fullView)->toBe('layout start view content bar layout end');
});
