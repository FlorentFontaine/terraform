<?php

namespace Classes\Http;

class Request
{
    public array $getParams;
    public array $postParams;
    public array $cookies;
    public array $files;
    public array $server;

    public function __construct(
        array $getParams,
        array $postParams,
        array $cookies,
        array $files,
        array $server
    ) {
        $this->getParams = $getParams;
        $this->postParams = $postParams;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = $server;
    }

    public static function createFromGlobals(): self
    {
        return new self($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'];
    }

    public function getPathInfo(): string
    {
        return strtok($this->server['REQUEST_URI'], '?');
    }

    public function getParams(): array
    {
        return $this->getMethod() === 'GET' ? $this->getParams : $this->postParams;
    }

    public function getLocation(): string
    {
        return $this->server['REQUEST_URI'];
    }

    public function getReferer(): string
    {
        return $this->server['HTTP_REFERER'];
    }
}
