<?php

declare(strict_types=1);

namespace App\Service\ErrorsHandlers;

use App\Service\Http\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class Errors
{
    private object $e;
    private int $code;
    private Environment $twig;

    public function __construct(object $e, int $code)
    {
        $this->e = $e;
        $this->code = $code;
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader);
    }

    public function handleErrors(): Response
    {
        switch ($this->code) {
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
