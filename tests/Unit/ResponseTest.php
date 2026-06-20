<?php

use LeafyTech\Core\Response;

test('it can be instantiated with default values', function () {
    $response = new Response;
    expect($response)->toBeInstanceOf(Response::class);
});

test('it can set wrap and without wrapping options', function () {
    $response = new Response('Message');

    // Default wrap is 'data'
    // Let's test custom wrap
    $response->setWrap('payload');
    $json     = $response->success('Message', ['foo' => 'bar']);
    $data     = json_decode($json, true);
    expect($data)->toHaveKey('payload')
        ->and($data['payload'])->toBe(['foo' => 'bar']);

    // Test without wrapping
    $response->withoutWrapping();
    $json     = $response->success('Message', ['foo' => 'bar']);
    $data     = json_decode($json, true);
    expect($data)->not->toHaveKey('payload')
        ->and($data)->toHaveKey('foo')
        ->and($data['foo'])->toBe('bar');
});

test('it can set headers', function () {
    $response = new Response;
    $response->header('X-Custom-Header', 'custom-value');

    // We can't easily assert headers sent on CLI without checking internal state or output,
    // but we can verify the method returns the response instance (fluent interface)
    expect($response->header('X-Test', 'Value'))->toBe($response);
});

test('it outputs correct structure for success and error json responses', function () {
    // We run this test checking the structure of default responses
    $response    = new Response('Operation Successful');

    $jsonSuccess = $response->success('Operation Successful', ['id' => 123]);
    $dataSuccess = json_decode($jsonSuccess, true);

    expect($dataSuccess)->toHaveKey('success', true)
        ->and($dataSuccess)->toHaveKey('message', 'Operation Successful')
        ->and($dataSuccess['data'])->toBe(['id' => 123]);

    $jsonError   = $response->error('Operation Failed', ['error_code' => 500]);
    $dataError   = json_decode($jsonError, true);

    expect($dataError)->toHaveKey('success', false)
        ->and($dataError)->toHaveKey('message', 'Operation Failed')
        ->and($dataError['data'])->toBe(['error_code' => 500]);
});

test('it formats raw json output with custom data and options', function () {
    $response = new Response;
    $response->withoutWrapping();

    $json     = $response->json(['user' => 'user01'], ['meta' => 'test']);
    $data     = json_decode($json, true);

    expect($data)->toHaveKey('user', 'user01')
        ->and($data)->toHaveKey('meta', 'test');
});
