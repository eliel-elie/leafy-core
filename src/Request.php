<?php

namespace LeafyTech\Core;

class Request
{
    private array $routeParams = [];

    public Header $headers;

    public function __construct()
    {
        $this->headers = new Header(get_request_headers());
    }

    public function getUrl(): string
    {
        $path     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $route    = substr($path, strlen($basePath));

        return '/' . ltrim($route, '/');
    }

    public function getCurrentRoute(): string
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        $route = substr($requestUri, strlen($basePath));

        return '/' . ltrim($route, '/');
    }

    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function getBody(): array
    {
        if ($this->isGet()) {
            return filter_input_array(INPUT_GET) ?? [];
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);

            return is_array($data) ? $data : [];
        }

        if ($this->isPost()) {
            return filter_input_array(INPUT_POST) ?? [];
        }

        if (in_array($this->getMethod(), ['PUT', 'PATCH', 'DELETE']) && !empty($_SERVER['CONTENT_LENGTH'])) {
            parse_str(file_get_contents('php://input', false, null, 0, $_SERVER['CONTENT_LENGTH']), $data);

            return is_array($data) ? $data : [];
        }

        return [];
    }

    public function setRouteParams($params): Request
    {
        $this->routeParams = $params;

        return $this;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function getRouteParam($param, $default = null)
    {
        return $this->routeParams[$param] ?? $default;
    }
}
