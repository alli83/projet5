<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Service\Http\Response;
use App\View\View;

final class ErrorController implements ControllerInterface
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function handleErrors(array $params): Response
    {
        switch ($params["error"]) {
            case 403:
                return new Response($this->view->render([
                    'template' => 'error/error_403',
                    'data' => []
                ]), 403, ['Content-Type' => 'text/html; charset=utf-8']);

            case 404:
                return new Response($this->view->render([
                    'template' => 'error/error_404',
                    'data' => []
                ]), 404, ['Content-Type' => 'text/html; charset=utf-8']);

            case 2002:
                return new Response($this->view->render([
                    'template' => 'error/error_500',
                    'data' => []
                ]), 500, ['Content-Type' => 'text/html; charset=utf-8']);

            default:
                return new Response($this->view->render([
                    'template' => 'error/error_404',
                    'data' => []
                ]), 404, ['Content-Type' => 'text/html; charset=utf-8']);
        }
    }
}
