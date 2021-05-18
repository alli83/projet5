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
use App\Service\Utils\AuthentificationService;
use App\Service\Utils\CreatePostService;
use App\Service\Utils\FileService;
use App\Service\Utils\PaginationService;
use App\Service\Utils\SetOrderService;
use App\Service\Utils\TokenService;
use App\Service\Utils\ValidateFileService;
use App\Service\Utils\ValidityService;

final class AdminPostController implements ControllerInterface
{

    private PostRepository $postRepository;
    private UserRepository $userRepository;
    private View $view;
    private Session $session;
    private CreatePostService $createPostService;
    private AuthentificationService $authentificationService;
    private PaginationService $paginationService;
    private SetOrderService $setOrderService;
    private TokenService $tokenService;
    private ValidityService $validityService;
    private ValidateFileService $validateFileService;
    private FileService $fileService;

    public function __construct(
        PostRepository $postRepository,
        UserRepository $userRepository,
        View $view,
        Session $session,
        CreatePostService $createPostService,
        AuthentificationService $authentificationService,
        PaginationService $paginationService,
        SetOrderService $setOrderService,
        TokenService $tokenService,
        ValidityService $validityService,
        ValidateFileService $validateFileService,
        FileService $fileService
    ) {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
        $this->createPostService = $createPostService;
        $this->authentificationService = $authentificationService;
        $this->paginationService = $paginationService;
        $this->setOrderService = $setOrderService;
        $this->tokenService = $tokenService;
        $this->validityService = $validityService;
        $this->validateFileService = $validateFileService;
        $this->fileService = $fileService;
    }

    public function displayAllPosts(?array $params = [], ?ParametersBag $request = null): Response
    {
        // check if admin
        if (!$this->authentificationService->isAdminAuth($this->session)) {
            return new Response("", 302, ["location" =>  "/error/403"]);
        }

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

        //set security token
        $tokencsrf = $this->tokenService->setToken($this->session);

        return new Response($this->view->render([
            'template' => 'listPosts',
            'data' => [
                'posts' => $posts,
                'page' => $params === null ? 0 : (int)$params["page"],
                'filter' => $order,
                "end" => $end,
                "tokencsrf" => $tokencsrf
            ],
            'env' => 'backoffice'
        ]));
    }

    public function editOnePost(array $params): Response
    {
        // check if admin
        if (!$this->authentificationService->isAdminAuth($this->session)) {
            return new Response("", 302, ["location" =>  "/error/403"]);
        }

        $params = $this->validityService->validityVariables($params);

        $post = $this->postRepository->findOneBy(["id" => (int)$params["id"]]);
        // load all admin andsuperAdmin => useful for select input in frontend
        $users = $this->userRepository->findBy(["role1" => "admin", "role2" => "superAdmin"]);

        if (empty($users) || empty($post)) {
            $this->session->addFlashes("danger", "Une erreur est survenue");
            return new Response("", 302, ["location" =>  "/admin/posts"]);
        }
        $pseudos = [];
        foreach ($users as $user) {
            $pseudos[] = $user->getPseudo() . ", " . $user->getEmail();
        }
        // set security token
        $tokencsrf = $this->tokenService->setToken($this->session);

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

    public function editOnePostSave(array $params, ?ParametersBag $request = null, ?ParametersBag $file = null): Response
    {
        // check if admin
        if (!$this->authentificationService->isAdminAuth($this->session)) {
            return new Response("", 302, ["location" =>  "/error/403"]);
        }
        $this->session->addFlashes("danger", "Une erreur est survenue");

        $params = $this->createPostService
            ->paramsPost(
                $params,
                $file,
                $request,
                $this->validateFileService,
                $this->fileService,
                $this->tokenService,
                $this->validityService,
                $this->session
            );

        if ($params === null) {
            return new Response("", 302, ["location" =>  "/admin/posts"]);
        }

        $post = new Post($params);

        $part = explode(",", $params["usersToComplete"]);
        if (!array_key_exists(1, $part)) {
            $this->session->addFlashes("danger", "L'administrateur associé est introuvable");
            return new Response("", 302, ["location" =>  "/admin/posts"]);
        }
        $email = trim($part[1]);
        $user = $this->userRepository->findOneBy(["email" => $email]);

        if (!$user) {
            $this->session->addFlashes("danger", "L'administrateur associé est introuvable");
            return new Response("", 302, ["location" =>  "/admin/posts"]);
        }

        $post->setUserId($user->getId());

        if ($this->postRepository->update($post)) {
            $this->session->addFlashes("success", "Le post a bien été modifié et mis à jour");
        }
        return new Response("", 302, ["location" =>  "/admin/posts"]);
    }

    public function createNewPost(): Response
    {
        //  check if admin
        if (!$this->authentificationService->isAdminAuth($this->session)) {
            return new Response("", 302, ["location" =>  "/error/403"]);
        }
        // load all admin and superAdmin
        $users = $this->userRepository->findBy(["role1" => "admin", "role2" => "superAdmin"]);

        // can't be null. at least super admin or 1 admin to access admin dashboard
        if (!$users) {
            $this->session->addFlashes("danger", "Une erreur s'est produite");
            return new Response("", 302, ["location" =>  "/admin/posts"]);
        }
        $pseudos = [];
        foreach ($users as $user) {
            $pseudos[] = $user->getPseudo() . ", " . $user->getEmail();
        }
        // set security token
        $tokencsrf = $this->tokenService->setToken($this->session);

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

    public function createNewPostSave(?ParametersBag $request = null, ?ParametersBag $file = null): Response
    {
        $params = [];
        $auth = $this->authentificationService;

        // check if admin
        if (!$auth->isAdminAuth($this->session)) {
            return new Response("", 302, ["location" =>  "/error/403"]);
        }

        $this->session->addFlashes("danger", "Une erreur est survenue");

        $params = $this->createPostService
            ->paramsPost(
                $params,
                $file,
                $request,
                $this->validateFileService,
                $this->fileService,
                $this->tokenService,
                $this->validityService,
                $this->session
            );

        if ($params === null) {
            return new Response("", 302, ["location" =>  "/admin/posts"]);
        }

        $post = new Post($params);

        $part = explode(",", $params["usersToComplete"]);
        if (!array_key_exists(1, $part)) {
            $this->session->addFlashes("danger", "L'administrateur associé est introuvable");
            return new Response("", 302, ["location" =>  "/admin/posts"]);
        }
        $email = trim($part[1]);

        $user = $this->userRepository->findOneBy(["email" => $email]);

        if (!$user) {
            $this->session->addFlashes("danger", "L'administrateur associé est introuvable");
            return new Response("", 302, ["location" =>  "/admin/posts"]);
        }

        $post->setUserId($user->getId());

        if ($this->postRepository->create($post)) {
            $this->session->addFlashes("success", "Le post a bien été modifié et mis à jour");
        }
        return new Response("", 302, ["location" =>  "/admin/posts"]);
    }

    public function deleteOnePost(array $params, ?ParametersBag $request): Response
    {
        //  check if admin
        if (!$this->authentificationService->isAdminAuth($this->session)) {
            return new Response("", 302, ["location" =>  "/error/403"]);
        }

        $this->session->addFlashes("danger", "Une erreur est survenue");

        if ($request === null) {
            return new Response("", 302, ["location" =>  "/admin/posts"]);
        }
        $param = $request->all();

        // check validity security token
        $validToken = $this->tokenService->validateToken($param, $this->session);
        if (!$validToken) {
            return new Response("", 302, ["location" =>  "/admin/posts"]);
        }

        $validity = $this->validityService;
        $params = $validity->validityVariables($params);

        $post = new Post(["id" => (int)$params["id"]]);

        if ($this->postRepository->delete($post)) {
            $this->session->addFlashes("success", "Le post à bien été supprimé");
        }
        return new Response("", 302, ["location" =>  "/admin/posts"]);
    }
}
