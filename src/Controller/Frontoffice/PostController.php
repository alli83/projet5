<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\View\View;
use App\Service\Http\Response;
use App\Model\Repository\PostRepository;
use App\Model\Repository\CommentRepository;
use App\Service\Http\Session\Session;
use App\Service\Utils\Validity;

final class PostController implements ControllerInterface
{
    private PostRepository $postRepository;
    private View $view;
    private Session $session;

    public function __construct(PostRepository $postRepository, View $view, Session $session)
    {
        $this->postRepository = $postRepository;
        $this->view = $view;
        $this->session = $session;
    }

    public function displayOneAction(array $params, CommentRepository $commentRepository): Response
    {
        $validityTools = new Validity();
        $params = $validityTools->validityVariables($params);

        if ($params["id"]) {
            $params['id'] = (int)$params['id'];
        }

        $post = $this->postRepository->findOneBy(['id' => $params['id']]);

        $allIds = $this->postRepository->findAllIds();

        $comments = $commentRepository->findBy(['idPost' => $params['id']], ['order' => "asc"]);

        if ($post !== null) {
            return new Response($this->view->render(
                [
                    'template' => 'post',
                    'data' => [
                        'post' => $post,
                        'ids' => $allIds,
                        'comments' => $comments,
                    ],
                ],
            ));
        }
        $this->session->addFlashes('danger', 'Une erreur est survenue');
        return $this->displayAllAction();
    }

    public function displayAllAction(?array $params = []): Response
    {
        if ($params === null || ($params && $params["page"] === null)) {
            $offset = 0;
        } else {
            $validity = new Validity();
            $params = $validity->validityVariables($params);

            $offset = (int)$params["page"] * 3;
        }

        $posts = $this->postRepository->findAll(3, $offset);

         return new Response($this->view->render([
             'template' => 'posts',
             'data' => ['posts' => $posts,
             'page' => $params === null ? 0 : (int)$params["page"]]
         ]));
    }
}
