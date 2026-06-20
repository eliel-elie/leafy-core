<?php

use LeafyTech\Core\Application;
use LeafyTech\Core\Helpers\Token;

beforeEach(function () {
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

    // Mock Application to set Application::$app
    new Application(__DIR__);
});

afterEach(function () {
    restore_error_handler();
    restore_exception_handler();
});

test('it generates and validates JWT tokens', function () {
    $tokenHelper = new Token();
    $payload = [
        'iss' => 'test-issuer',
        'aud' => 'test-audience',
        'user' => 'eliel',
    ];

    $jwt = $tokenHelper->generate($payload);
    expect($jwt)->toBeString()
        ->and(explode('.', $jwt))->toHaveCount(3);

    // Verify validity
    expect($tokenHelper->isValid($jwt))->toBeTrue();

    // Verify invalid signature
    $invalidJwt = $jwt . 'adulterated';
    expect($tokenHelper->isValid($invalidJwt))->toBeFalse()
        ->and($tokenHelper->getMessage())->toBe('Invalid token');
});

test('it fails validation for expired tokens', function () {
    $tokenHelper = new Token();
    
    // Explicitly construct an expired token because generation uses static properties
    $headers = ['alg' => 'HS512', 'typ' => 'JWT'];
    $payload = [
        'iss' => 'test-issuer',
        'aud' => 'test-audience',
        'user' => 'eliel',
        'exp' => time() - 100, // expired!
    ];
    
    // Base64Url encode helpers (mimics Token::encode)
    $encode = function (string $str): string {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    };

    $base64_header = $encode(json_encode($headers));
    $base64_payload = $encode(json_encode($payload));
    
    $secret = Application::$app->config->app['key'];
    $signature = hash_hmac('SHA256', $base64_header . "." . $base64_payload, $secret, true);
    $base64_signature = $encode($signature);
    
    $jwt = "$base64_header.$base64_payload.$base64_signature";

    expect($tokenHelper->isValid($jwt))->toBeFalse()
        ->and($tokenHelper->getMessage())->toBe('Token expired');
});
