<?php

declare(strict_types=1);

namespace App\Service\Http;

final class Request
{
    private ParametersBag $query;
    private ParametersBag $request;
    private ParametersBag $files;
    private ParametersBag $server;

    function __construct(array $query, array $request, array $files, array $server)
    {
        $this->query = new ParametersBag($query);
        $this->request = new ParametersBag($request);
        $this->files = new ParametersBag($files);
        $this->server = new ParametersBag($server);
    }

    public function query(): ParametersBag
    {
        return $this->query;
    }

    public function request(): ParametersBag
    {
        return $this->request;
    }

    public function files(): ParametersBag
    {
        return $this->files;
    }

    public function server(): ParametersBag
    {
        return $this->server;
    }

    public function getMethod(): string
    {
        return $this->server->get('REQUEST_METHOD');
    }
}
