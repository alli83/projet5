<?php

declare(strict_types=1);

namespace App\Service\Http;

final class Response
{
    /**
     * @var string[] $headers
     */
    private string $content;
    private int $status;
    private array $headers = [];

    /**
     * @param string[] $headers
     */
    function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    public function has(string $key): bool
    {
        return isset($this->headers[$key]);
    }

    /**
     * @return mixed
     */
    public function get(string $key) //: mixed //uniquement en PHP 8.0
    {
        return $this->has($key) ? $this->headers[$key] : null;
    }


    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeader(string $key, string $header): void
    {
        $statut = $this->status;
        $goodHeader = $key . ":" . $header;
        header($goodHeader, false, $statut);
    }

    public function send(): void
    {
        if ($this->has("location")) {
            $param = $this->get("location");

            $goodHeader = "location: " . $param;
            header($goodHeader);
            exit();
        }
        $headers = $this->getHeaders();
        foreach ($headers as $key => $header) {
            $this->setHeader($key, $header);
        }
        echo $this->content;
    }
}
