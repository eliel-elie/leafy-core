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

    public function getUrl()
    {
        $path = $_SERVER['REQUEST_URI'];
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        return $path;
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
            return filter_input_array(INPUT_GET);
        }

        if ($this->isPost()) {
            return filter_input_array(INPUT_POST);
        }

        if (in_array($this->getMethod(), ['PUT', 'PATCH', 'DELETE']) && !empty($_SERVER['CONTENT_LENGTH'])) {
            parse_str(file_get_contents('php://input', false, null, 0, $_SERVER['CONTENT_LENGTH']), $data);
            return $data;
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