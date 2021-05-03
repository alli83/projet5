<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Model\Entity\Post;
use App\View\View;
use App\Model\Repository\PostRepository;
use App\Model\Repository\UserRepository;
use App\Service\Http\ParametersBag;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Service\Utils\Authentification;
use App\Service\Utils\File;
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

        if ($auth->isAdminAuth($this->session)) {
            if ($params === null || ($params && $params["page"] === null)) {
                $offset = 0;
            } else {
                $validity = new Validity();
                $params = $validity->validityVariables($params);

                $offset = (int)$params["page"] * 3;
            }

            $posts = $this->postRepository->findAll(3, $offset);

            return new Response($this->view->render([
                'template' => 'listPosts',
                'data' => [
                    'posts' => $posts,
                    'page' => $params === null ? 0 : (int)$params["page"]
                ],
                'env' => 'backoffice'
            ]));
        }
        return new Response("", 304, ["location" =>  "/login"]);
    }

    public function editOnePost(array $params): Response
    {
        $auth = new Authentification();

        if ($auth->isAdminAuth($this->session)) {
            $validity = new Validity();
            $params = $validity->validityVariables($params);

            $post = $this->postRepository->findOneBy(["id" => (int)$params["id"]]);

            if ($post !== null) {
                return new Response($this->view->render([
                    'template' => 'editPost',
                    'data' => [
                        'post' => $post,
                        'url' => "/post-" . $params["id"] . "/edit-confirm"
                    ],
                    'env' => "backoffice"
                ]));
            }
            $this->session->addFlashes('danger', "Une erreur est survenue");
        }
        return new Response("", 304, ["location" =>  "/login"]);
    }

    public function editOnePostSave(array $params, ?ParametersBag $request, ?ParametersBag $file): Response
    {
        $auth = new Authentification();
        $fileAttached = $file->get("file_attached");

        if ($auth->isAdminAuth($this->session)) {
            if (!empty($fileAttached["tmp_name"])) {
                if ($fileAttached["size"] > 150000) {
                    $this->session->addFlashes("danger", "fichier trop lourd");
                    return new Response("", 304, ["location" =>  "/admin/posts"]);
                }
                $file = new File($fileAttached["name"]);
                $targetFile = $file->registerFile($fileAttached["tmp_name"]);

                if ($targetFile === null) {
                    $this->session->addFlashes("warning", "Cette image (par ce nom ) est déjà associée à un post");
                    return new Response("", 304, ["location" =>  "/admin/posts"]);
                }
                $params["file_attached"] =  base64_encode(file_get_contents($targetFile));
            }

            $param = $request->all();
            foreach ($param as $key => $el) {
                $params[$key] = $el;
            }

            $validity = new Validity();
            $params = $validity->validityVariables($params);

            // all mandatory ? 
            $post = new Post($params);

            $this->session->addFlashes("danger", "Une erreur s'est produite");
            if ($this->postRepository->update($post)) {
                $this->session->addFlashes("success", "le post a bien été modifié et mis à jour");
            }
            return new Response("", 304, ["location" =>  "/admin/posts"]);
        }
        return new Response("", 304, ["location" =>  "/login"]);     
    }

    public function createNewPost(): Response
    {
        $auth = new Authentification();

        if ($auth->isAdminAuth($this->session)) {
            return new Response($this->view->render([
                'template' => 'editPost',
                'data' => [
                    'url' => "/post/create-confirm"
                ],
                'env' => "backoffice"
            ]));
        }
        return new Response("", 304, ["location" =>  "/login"]);
    }

    public function createNewPostSave(ParametersBag $request, ?ParametersBag $file): Response
    {
        $params = [];
        $fileAttached = $file->get("file_attached");

        $auth = new Authentification();
        
        if ($auth->isAdminAuth($this->session)) {
            if (!empty($fileAttached["tmp_name"])) {
                
                if ($fileAttached["size"] > 150000) {

                    $this->session->addFlashes("danger", "fichier trop lourd");
                    return new Response("", 304, ["location" =>  "/admin/posts"]);
                }

                $file = new File($fileAttached["name"], $fileAttached);
                $targetFile = $file->registerFile($fileAttached["tmp_name"]);

                if ($targetFile === null) {
                    $this->session->addFlashes("warning", "Cette image (par ce nom ) est déjà associée à un post");
                    return new Response("", 304, ["location" =>  "/admin/posts"]);
                }
                $params["file_attached"] =  base64_encode(file_get_contents($targetFile));
            }

            $param = $request->all();

            foreach ($param as $key => $el) {
                $params[$key] = $el;
            }

            $validity = new Validity();
            $params = $validity->validityVariables($params);

            $user = $this->userRepository->findOneBy(["email" => $this->session->get("email")]);
            $userId = $user->getId();

            $params['userId'] = $userId;

                // all mandatory ? 
            $post = new Post($params);

            $this->session->addFlashes('danger', "Une erreur est survenue");
            if ($this->postRepository->create($post)) {
                $this->session->addFlashes('success', "le post à bien été créé");
            }
            return new Response("", 304, ["location" =>  "/admin/posts"]);
        }
        return new Response("", 304, ["location" =>  "/login"]);
    }

    public function deleteOnePost(array $params): Response
    {
        $auth = new Authentification();

        if ($auth->isAdminAuth($this->session)) {
            $validity = new Validity();
            $params = $validity->validityVariables($params);

            $post = new Post(["id" => (int)$params["id"]]);

            $this->session->addFlashes('danger', "Une erreur est survenue");
            if ($this->postRepository->delete($post)) {
                $this->session->addFlashes('success', "le post à bien été supprimé");
            }
            return new Response("", 304, ["location" =>  "/admin/posts"]);
        }
        return new Response("", 304, ["location" =>  "/login"]);
    }
}
