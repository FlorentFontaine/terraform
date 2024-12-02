<?php

namespace Classes\Http;

class Response
{
    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    private ?string $content;

    private int $status;

    private array $header;

    public function __construct(?string $content = '', int $status = 200, array $header = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->header = $header;
        http_response_code($this->status);
    }

    public function send(): void
    {
        echo $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }
}
