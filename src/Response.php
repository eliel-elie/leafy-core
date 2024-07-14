<?php

namespace LeafyTech\Core;

class Response
{
    private string $message;

    private array $data;

    private bool $reload    = false;

    private string $url     = '';

    private int $statusCode = 200;

    private array $headers  = [];

    private bool $defaultOption = false;

    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    private ?string $wrap = 'data';

    /**
     * @param string $content
     * @param int $status
     * @param array $headers
     */
    public function __construct(string $content = '', $data = [], int $status = 200, array $headers = [])
    {
        $this->message    = $content;
        $this->data       = $data;
        $this->statusCode = $status;
        $this->headers    = $headers;
    }

    /**
     * @param string $wrap
     * @return Response
     */
    public function setWrap(string $wrap): Response
    {
        $this->wrap = $wrap;
        return $this;
    }

    public function withoutWrapping(): Response
    {
        $this->wrap = null;
        return $this;
    }

    public function statusCode(int $code): Response
    {
        $this->statusCode = $code;
        http_response_code($code);
        return $this;
    }

    public function redirect($url): void
    {
        $url = $url === '/' ? env('APP_URL') : $url;
        header("Location: $url");
    }

    /**
     * @param bool $reload
     * @return Response
     */
    public function setPageReload(bool $reload): Response
    {
        $this->reload = $reload;
        return $this;
    }

    /**
     * @param mixed $url
     * @return Response
     */
    public function setUrl($url): Response
    {
        $this->url = $url;
        return $this;
    }

    public function success($message = '', $data = [])
    {
        if(!empty($message)) {
            $this->message = $message;
        }

        $this->sendHeaders();

        if(!empty($data)) {
            $stringJson = $this->getDefaultOptions();
            if(!is_null($this->wrap)) {
                $stringJson[$this->wrap] = $data;
            } else {
                $stringJson = array_merge($stringJson,$data);
            }
        } else {
            $stringJson = $this->getDefaultOptions();
        }

        return json_encode($stringJson);
    }

    public function error($message = '', $data = [])
    {
        if(!empty($message)) {
            $this->message = $message;
        }

        $this->sendHeaders();

        if(!empty($data)) {
            $stringJson = $this->getDefaultOptions( false);
            if(!is_null($this->wrap)) {
                $stringJson[$this->wrap] = $data;
            } else {
                $stringJson = array_merge($stringJson,$data);
            }
        } else {
            $stringJson = $this->getDefaultOptions(false);
        }

        return json_encode($stringJson);
    }

    public function header($key, $value, $replace = true)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function json($data = [], $options = null, $status = 200)
    {
        if($status !== 200) $this->statusCode($status);

        $this->sendHeaders();

        if($this->defaultOption && is_null($options)) {
            $stringJson = $this->getDefaultOptions();
            if(!is_null($this->wrap)) {
                $stringJson[$this->wrap] = empty($data) ? $this->data : $data;
            } else {
                $stringJson = array_merge($stringJson, empty($data) ? $this->data : $data);
            }
        } else {

            if(!is_null($this->wrap)) {
                $stringJson[$this->wrap] = empty($data) ? $this->data : $data;
            } else {
                $stringJson = empty($data) ? $this->data : $data;
            }

            if (!is_null($options)) $stringJson = array_merge($options, $stringJson);
        }

        return json_encode($stringJson);
    }

    private function sendHeaders()
    {
        if(empty($this->headers)) {
            header('Content-Type: Application/json', true, $this->statusCode);
        } else {
            foreach ($this->headers as $name => $value) {
                header($name . ': ' . $value, true, $this->statusCode);
            }
        }
    }

    private function getDefaultOptions($success = true)
    {
        if ($this->defaultOption) {
            return [
                'success'    => $success,
                'message'    => $this->message,
                'pageReload' => $this->reload,
                'link'       => $this->url
            ];
        } else {
            return [
                'success'    => $success,
                'message'    => $this->message,
            ];
        }
        return;
    }

}