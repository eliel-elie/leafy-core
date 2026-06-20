<?php

use LeafyTech\Core\Request;

beforeEach(function () {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/submit?foo=bar';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
});

test('it determines request method and url', function () {
    $request = new Request();

    expect($request->getMethod())->toBe('POST')
        ->and($request->isPost())->toBeTrue()
        ->and($request->isGet())->toBeFalse()
        ->and($request->getUrl())->toBe('/submit');
});

test('it handles route parameters', function () {
    $request = new Request();
    $request->setRouteParams(['id' => '100']);

    expect($request->getRouteParams())->toBe(['id' => '100'])
        ->and($request->getRouteParam('id'))->toBe('100')
        ->and($request->getRouteParam('non_existent', 'default'))->toBe('default');
});
