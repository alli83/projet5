<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Model\Entity\Post;
use App\View\View;
use App\Model\Repository\PostRepository;
use App\Model\Repository\UserRepository;
use App\Service\ErrorsHandlers\Errors;
use App\Service\Http\ParametersBag;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Service\Utils\ServiceProvider;

class AdminPostController implements ControllerInterface
{

    private PostRepository $postRepository;
    private UserRepository $userRepository;
    private View $view;
    private Session $session;
    private ServiceProvider $serviceProvider;

    public function __construct(
        PostRepository $postRepository,
        UserRepository $userRepository,
        View $view,
        Session $session,
        ServiceProvider $serviceProvider
    ) {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
        $this->serviceProvider = $serviceProvider;
    }

    public function displayAllPosts(?array $params = [], ?ParametersBag $request = null): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();
        // check if admin
        if ($auth->isAdminAuth($this->session)) {
            // set pagination
            $offset = $this->serviceProvider->getPaginationService()->setOffset($params);
            $order = ($request !== null && $request->get("order") !== null)  ? $request->get("order") : "desc";
            // set order and check if it's the last page
            $order = $this->serviceProvider->getValidityService()->isInArray(["asc", "desc"], $order);
            if ($order) {
                $posts = $this->postRepository->findAll(4, $offset, ['order' => $order["order"]]);
                $end = false;
                if ($posts) {
                    if (!array_key_exists(3, $posts)) {
                        $end = true;
                    }
                    $posts = array_slice($posts, 0, 3);
                }
                return new Response($this->view->render([
                    'template' => 'listPosts',
                    'data' => [
                        'posts' => $posts,
                        'page' => $params === null ? 0 : (int)$params["page"],
                        'filter' => $order,
                        "end" => $end
                    ],
                    'env' => 'backoffice'
                ]));
            }
        }
        $auth->isNotAuth($this->session);
        $error = new Errors(403);
        return $error->handleErrors();
    }

    public function editOnePost(array $params): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();
        // check if admin
        if ($auth->isAdminAuth($this->session)) {
            $validity = $this->serviceProvider->getValidityService();
            $params = $validity->validityVariables($params);

            $post = $this->postRepository->findOneBy(["id" => (int)$params["id"]]);
            $users = $this->userRepository->findBy(["role1" => "admin", "role2" => "superAdmin"]);
            // load all admin and users => useful for select input in frontend
            if ($users && $post) {
                $pseudos = [];
                foreach ($users as $user) {
                    $pseudos[] = $user->getPseudo() . ", " . $user->getEmail();
                }
                // set security token
                $tokencsrf = $this->serviceProvider->getTokenService()->setToken($this->session);

                return new Response($this->view->render([
                    'template' => 'editPost',
                    'data' => [
                        'post' => $post,
                        'usersToSearch' => $pseudos,
                        'url' => "/post-" . $params["id"] . "/edit-confirm",
                        "tokencsrf" => $tokencsrf
                    ],
                    'env' => "backoffice"
                ]));
            }
            $this->session->addFlashes('danger', "Une erreur est survenue");
            return new Response("", 302, ["location" =>  "/admin/posts"]);
        }
        $auth->isNotAuth($this->session);
        $error = new Errors(403);
        return $error->handleErrors();
    }

    public function editOnePostSave(array $params, ?ParametersBag $request = null, ?ParametersBag $file = null): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();
        // check if admin
        if ($auth->isAdminAuth($this->session)) {
            $fileAttached = isset($file) ? $file->get("file_attached") : null;

            if (isset($fileAttached) && !empty($fileAttached["tmp_name"] || $fileAttached["tmp_name"] !== "")) {
                $validityFile = $this->serviceProvider->getValidateFileService();
                $val = $validityFile->checkFileValidity($fileAttached, $this->session);
                if ($val === null) {
                    return new Response("", 304, ["location" =>  "/admin/posts"]);
                }
                $params["file_attached"] = $val;
            }
            $this->session->addFlashes("danger", "Une erreur est survenue");
            if ($request !== null) {
                $param = $request->all();
                foreach ($param as $key => $el) {
                    $params[$key] = $el;
                }
                // check validity security token
                $validToken = $this->serviceProvider->getTokenService()->validateToken($param, $this->session);
                if ($validToken) {
                    $validity = $this->serviceProvider->getValidityService();
                    $params = $validity->validityVariables($params);

                    $post = new Post($params);

                    $email = trim(explode(",", $params["usersToComplete"])[1]);

                    $user = $this->userRepository->findOneBy(["email" => $email]);

                    if ($user) {
                        $post->setUserId($user->getId());

                        if ($this->postRepository->update($post)) {
                            $this->session->addFlashes("success", "le post a bien été modifié et mis à jour");
                        }
                    }
                }
            }
            return new Response("", 304, ["location" =>  "/admin/posts"]);
        }
        $auth->isNotAuth($this->session);
        $error = new Errors(403);
        return $error->handleErrors();
    }

    public function createNewPost(): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();
        //  check if admin
        if ($auth->isAdminAuth($this->session)) {
            $users = $this->userRepository->findAll();

            if ($users) {
                // load all admin and user member
                $pseudos = [];
                foreach ($users as $user) {
                    $pseudos[] = $user->getPseudo() . ", " . $user->getEmail();
                }
                // set security token
                $tokencsrf = $this->serviceProvider->getTokenService()->setToken($this->session);

                return new Response($this->view->render([
                    'template' => 'editPost',
                    'data' => [
                        'usersToSearch' => $pseudos,
                        'url' => "/post/create-confirm",
                        "tokencsrf" => $tokencsrf
                    ],
                    'env' => "backoffice"
                ]));
            }
            $this->session->addFlashes("danger", "Une erreur s'est produite");
            return new Response("", 302, ["location" =>  "/admin/posts"]);
        }
        $auth->isNotAuth($this->session);
        $error = new Errors(403);
        return $error->handleErrors();
    }

    public function createNewPostSave(?ParametersBag $request = null, ?ParametersBag $file = null): Response
    {
        $params = [];
        $auth = $this->serviceProvider->getAuthentificationService();
        // check if admin
        if ($auth->isAdminAuth($this->session)) {
            $fileAttached = isset($file) ? $file->get("file_attached") : null;

            if (isset($fileAttached) && !empty($fileAttached["tmp_name"] || $fileAttached["tmp_name"] !== "")) {
                $validityFile = $this->serviceProvider->getValidateFileService();
                $val = $validityFile->checkFileValidity($fileAttached, $this->session);
                if ($val === null) {
                    return new Response("", 304, ["location" =>  "/admin/posts"]);
                }
                $params["file_attached"] = $val;
            }
            if ($request !== null) {
                $param = $request->all();

                $this->session->addFlashes('warning', "Merci de compléter les champs");

                if (!empty($param["stand_first"]) && !empty($param["title"]) && !empty($param["text"])) {
                    foreach ($param as $key => $el) {
                        $params[$key] = $el;
                    }
                    // check validity security token
                    $validToken = $this->serviceProvider->getTokenService()->validateToken($param, $this->session);
                    if ($validToken) {
                        $validity = $this->serviceProvider->getValidityService();
                        $params = $validity->validityVariables($params);

                        $email = trim(explode(",", $params["usersToComplete"])[1]);

                        $user = $this->userRepository->findOneBy(["email" => $email]);
                        $this->session->addFlashes("danger", "Une erreur est survenue");
                        if ($user) {
                            $params["userId"] = $user->getId();
                            $post = new Post($params);

                            if ($this->postRepository->create($post)) {
                                $this->session->addFlashes("success", "le post a bien été modifié et mis à jour");
                            }
                        }
                    }
                }
            }
            return new Response("", 304, ["location" =>  "/admin/posts"]);
        }
        $auth->isNotAuth($this->session);
        $error = new Errors(403);
        return $error->handleErrors();
    }

    public function deleteOnePost(array $params): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();
        //  check if admin
        if ($auth->isAdminAuth($this->session)) {
            $this->session->addFlashes('danger', "Une erreur est survenue");
            $validity = $this->serviceProvider->getValidityService();
            $params = $validity->validityVariables($params);

            $post = new Post(["id" => (int)$params["id"]]);

            if ($this->postRepository->delete($post)) {
                $this->session->addFlashes('success', "Le post à bien été supprimé");
            }
            return new Response("", 304, ["location" =>  "/admin/posts"]);
        }
        $auth->isNotAuth($this->session);
        $error = new Errors(403);
        return $error->handleErrors();
    }
}
