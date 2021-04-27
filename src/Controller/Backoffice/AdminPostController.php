<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Model\Entity\Post;
use App\View\View;
use App\Model\Repository\PostRepository;
use App\Model\Repository\UserRepository;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Service\Utils\Auth;
use App\Service\Utils\Authentification;
use App\Service\Utils\Validity;

class AdminPostController implements ControllerInterface
{

    private PostRepository $postRepository;
    private UserRepository $userRepository;
    private View $view;
    private Session $session;

    public function __construct(PostRepository $postRepository, UserRepository $userRepository, View $view, Session $session)
    {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
    }

    public function displayAllPosts(?array $params = []): Response
    {
        $auth = new Authentification();
        $user = $auth->isAuth($this->session, $this->userRepository);
        if ($user !== null) {
            if ($params === null || ($params && $params["page"] === null)) {
                $offset = 0;
            } else {
                $offset = intval($params["page"]) * 3;
            }

            $posts = $this->postRepository->findAll(3, $offset);

            return new Response($this->view->render([
                'template' => 'listPosts',
                'data' => ['posts' => $posts,
                'page' => $params === null ? 0 : intval($params["page"])],
                'env' => 'backoffice'
            ]));
        }
        return new Response("", 304, ["location" =>  "/"]);
    }

    public function editOnePost(array $params): Response
    {
        $auth = new Authentification();
        $validity = new Validity();
        $params = $validity->validityVariables($params);

        $user = $auth->isAuth($this->session, $this->userRepository);
        if ($user !== null) {
            $post = $this->postRepository->findOneBy(["id" => intval($params["id"])]);

            if ($post !== null) {
                return new Response($this->view->render([
                    'template' => 'editPost',
                    'data' => [
                        'post' => $post,
                        'url' => "/post-" . $params["id"] . "/edit-confirm"
                    ],
                    'env' => "backoffice"
                ]));
            } else {
                $this->session->addFlashes('danger', "Une erreur est survenue");
            }
        }
        return new Response("", 304, ["location" =>  "/"]);
    }

    public function editOnePostSave(array $params, ?object $request): Response
    {

        $param = $request->all();
        foreach ($param as $key => $el) {
            $params[$key] = $el;
        }

        $auth = new Authentification();
        $validity = new Validity();
        $params = $validity->validityVariables($params);

        $user = $auth->isAuth($this->session, $this->userRepository);
        if ($user !== null) {
            $post = new Post($params);
            if ($this->postRepository->update($post)) {
                $this->session->addFlashes("success", "le post a bien été modifié et mis à jour");
            } else {
                $this->session->addFlashes("danger", "Une erreur s'est produite");
            }
            return new Response("", 304, ["location" =>  "/admin/posts"]);
        }
        return new Response("", 304, ["location" =>  "/"]);
    }

    public function createNewPost(): Response
    {
        $auth = new Authentification();

        $user = $auth->isAuth($this->session, $this->userRepository);
        if ($user !== null) {
            return new Response($this->view->render([
                'template' => 'editPost',
                'data' => [
                    'url' => "/post/create-confirm"
                ],
                'env' => "backoffice"
            ]));
        }
        return new Response("", 304, ["location" =>  "/"]);
    }

    public function createNewPostSave(object $request): Response
    {

        $params = [];
        $param = $request->all();
        foreach ($param as $key => $el) {
            $params[$key] = $el;
        }

        $auth = new Authentification();
        $validity = new Validity();
        $params = $validity->validityVariables($params);

        $user = $auth->isAuth($this->session, $this->userRepository);
        if ($user !== null) {
            $user = $this->userRepository->findOneBy(["email" => $this->session->get("email")]);
            $userId = $user->getId();

            $params['userId'] = $userId;

            $post = new Post($params);

            if ($this->postRepository->create($post)) {
                $this->session->addFlashes('success', "le post à bien été créé");
            } else {
                $this->session->addFlashes('danger', "Une erreur est survenue");
            }
            return new Response("", 304, ["location" =>  "/admin/posts"]);
        }
        return new Response("", 304, ["location" =>  "/"]);
    }

    public function deleteOnePost(array $params): Response
    {
        $auth = new Authentification();
        $validity = new Validity();
        $params = $validity->validityVariables($params);

        $user = $auth->isAuth($this->session, $this->userRepository);
        if ($user !== null) {
            $post = new Post(["id" => intval($params["id"])]);
            $deletedPost = $this->postRepository->delete($post);
            if ($deletedPost) {
                $this->session->addFlashes('success', "le post à bien été supprimé");
            } else {
                $this->session->addFlashes('danger', "Une erreur est survenue");
            }
            return new Response("", 304, ["location" =>  "/admin/posts"]);
        }
        return new Response("", 304, ["location" =>  "/"]);
    }
}
