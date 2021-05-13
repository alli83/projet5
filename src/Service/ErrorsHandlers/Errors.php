<?php

declare(strict_types=1);

namespace App\Service\ErrorsHandlers;

use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class Errors
{
    private int $code;
    private Environment $twig;

    public function __construct(int $code)
    {
        $this->code = $code;
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader);
    }

    public function handleErrors(): Response
    {
        switch ($this->code) {
            case 403:
                return new Response(
                    $this->twig->render(
                        "frontoffice/error/error_403.html.twig",
                        []
                    ),
                    403,
                    ['Content-Type' => 'text/html; charset=utf-8']
                );
            case 404:
                return new Response(
                    $this->twig->render(
                        "frontoffice/error/error_404.html.twig",
                        []
                    ),
                    404,
                    ['Content-Type' => 'text/html; charset=utf-8']
                );
            case 2002:
                return new Response(
                    $this->twig->render(
                        "frontoffice/error/error_500.html.twig",
                        []
                    ),
                    500,
                    ['Content-Type' => 'text/html; charset=utf-8']
                );
            default:
                return new Response(
                    $this->twig->render(
                        "frontoffice/error/error_500.html.twig",
                        []
                    ),
                    500,
                    ['Content-Type' => 'text/html; charset=utf-8']
                );
        }
    }
}
