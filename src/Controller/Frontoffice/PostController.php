<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\View\View;
use App\Service\Http\Response;
use App\Model\Repository\PostRepository;
use App\Model\Repository\CommentRepository;
use App\Service\Http\ParametersBag;
use App\Service\Http\Session\Session;
use App\Service\Utils\PaginationService;
use App\Service\Utils\SetOrderService;
use App\Service\Utils\ValidityService;

final class PostController implements ControllerInterface
{
    private PostRepository $postRepository;
    private View $view;
    private Session $session;
    private ValidityService $validityService;
    private PaginationService $paginationService;
    private SetOrderService $setOrderService;

    public function __construct(
        PostRepository $postRepository,
        View $view,
        Session $session,
        ValidityService $validityService,
        PaginationService $paginationService,
        SetOrderService $setOrderService
    ) {
        $this->postRepository = $postRepository;
        $this->view = $view;
        $this->session = $session;
        $this->validityService = $validityService;
        $this->paginationService = $paginationService;
        $this->setOrderService = $setOrderService;
    }

    public function displayOneAction(array $params, CommentRepository $commentRepository): Response
    {
        $validityTools =  $this->validityService;
        $params = $validityTools->validityVariables($params);

        $post = $this->postRepository->findOneBy(['id' => (int)$params['id']]);
        $comments = $commentRepository->findBy(['idPost' => (int)$params['id']], ['order' => "asc"]);

        if ($post !== null) {
            return new Response($this->view->render(
                [
                    'template' => 'post',
                    'data' => [
                        'post' => $post,
                        'comments' => $comments,
                    ],
                ],
            ));
        }
        $this->session->addFlashes("danger", "Une erreur est survenue");
        return new Response("", 302, ["location" =>  "/posts"]);
    }

    public function displayAllAction(?array $params = [], ?ParametersBag $request = null): Response
    {
        // set pagination
        $offset = $this->paginationService->setOffset($params, $this->validityService);

        // set order
        $order = $this->setOrderService->setOrder($request, $this->validityService);

        if (!$order) {
            return new Response("", 302, ["location" =>  "/error/404"]);
        }
        $posts = $this->postRepository->findAll(4, $offset, ['order' => $order["order"]]);
        $end = false;
        if ($posts) {
            if (!array_key_exists(3, $posts)) {
                $end = true;
            }
            $posts = array_slice($posts, 0, 3);
        }

        return new Response($this->view->render([
            'template' => 'posts',
            'data' => [
                'posts' => $posts,
                'page' => $params === null ? 0 : (int)$params["page"],
                'filter' => $order,
                "end" => $end
            ]
        ]));
    }
}
