<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\View\View;
use App\Service\Http\Response;
use App\Model\Repository\PostRepository;
use App\Model\Repository\CommentRepository;
use App\Service\ErrorsHandlers\Errors;
use App\Service\Http\ParametersBag;
use App\Service\Http\Session\Session;
use App\Service\Utils\ServiceProvider;

final class PostController implements ControllerInterface
{
    private PostRepository $postRepository;
    private View $view;
    private Session $session;
    private ServiceProvider $serviceProvider;

    public function __construct(PostRepository $postRepository, View $view, Session $session, ServiceProvider $serviceProvider)
    {
        $this->postRepository = $postRepository;
        $this->view = $view;
        $this->session = $session;
        $this->serviceProvider = $serviceProvider;
    }

    public function displayOneAction(array $params, CommentRepository $commentRepository): Response
    {
        $validityTools =  $this->serviceProvider->getValidityService();
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
        $offset = $this->serviceProvider->getPaginationService()->setOffset($params);

        // set order
        $order = $this->serviceProvider->getSetOrderService()->setOrder($request, $this->serviceProvider);

        if (!$order) {
            $error = new Errors(404);
            return $error->handleErrors();
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
            ]]));
    }
}
