<?php

use LeafyTech\Core\Request;
use LeafyTech\Core\Response;
use LeafyTech\Core\Router;
use LeafyTech\Core\Exception\NotFoundException;

beforeEach(function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
});

test('it registers routes correctly', function () {
    $request = new Request();
    $response = new Response();
    $router = new Router($request, $response);

    $router->get('/home', fn() => 'home');
    $router->post('/submit', fn() => 'submit');

    $routes = $router->routes();

    expect($routes)->toHaveKey('GET')
        ->and($routes)->toHaveKey('POST')
        ->and($routes['GET'])->toHaveKey('/home')
        ->and($routes['POST'])->toHaveKey('/submit');
});

test('it resolves basic routes', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/about';

    $request = new Request();
    $response = new Response();
    $router = new Router($request, $response);

    $router->get('/about', fn() => 'about page');

    $result = $router->resolve();
    expect($result)->toBe('about page');
});

test('it resolves dynamic route parameters', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/users/42';

    $request = new Request();
    $response = new Response();
    $router = new Router($request, $response);

    $router->get('/users/{id}', function (Request $req) {
        return 'user ' . $req->getRouteParam('id');
    });

    $callback = $router->getCallback();
    expect($callback)->toBeCallable();

    $result = $router->resolve();
    expect($result)->toBe('user 42');
});

test('it throws NotFoundException for unregistered routes', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/non-existent';

    $request = new Request();
    $response = new Response();
    $router = new Router($request, $response);

    $router->get('/about', fn() => 'about');

    $router->resolve();
})->throws(NotFoundException::class);
