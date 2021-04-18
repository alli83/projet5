<?php

declare(strict_types=1);

namespace  App\Service;

use App\Controller\Frontoffice\HomeController;
use App\Controller\Frontoffice\PostController;
use App\Controller\Frontoffice\UserController;
use App\Model\Repository\PostRepository;
use App\Model\Repository\CommentRepository;
use App\Model\Repository\UserRepository;
use App\Service\ErrorsHandlers\Errors;
use App\Service\Http\Request;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\View\View;

final class Router
{
    private Database $database;
    private View $view;
    private Request $request;
    private Session $session;

    public function __construct(Request $request)
    {
        $this->database = new Database();
        $this->session = new Session();
        $this->view = new View($this->session);
        $this->request = $request;
    }

    public function run(): Response
    {
        $action = $this->request->query()->has('action') ? $this->request->query()->get('action') : 'accueil';
        $method = $this->request->getMethod() === "POST" ? $this->request->getMethod() : '';

        // *** @Route http://localhost:8000/***
        if ($action === 'accueil' && empty($method)) {
            var_dump("la");
            $controller = new HomeController($this->view, $this->session);

            return $controller->getHomePage();

            // *** @Route http://localhost:8000/contact***
        } elseif ($action === 'accueil' && !empty($method)) {
            $controller = new HomeController($this->view, $this->session);

            return $controller->contactDev($this->request);

            // *** @Route http://localhost:8000/?action=posts ***
        } elseif ($action === 'posts') {
            try {
                $postRepo = new PostRepository($this->database);
            } catch (\Exception $e) {
                $redir = new Errors($e, $e->getCode());
                return $redir->handleErrors();
            }
            $controller = new PostController($postRepo, $this->view);

            return $controller->displayAllAction();

            // *** @Route http://localhost:8000/?action=post&id=5 ***
        } elseif ($action === 'post' && $this->request->query()->has('id')) {
            try {
                $postRepo = new PostRepository($this->database);
                $commentRepo = new CommentRepository($this->database);
            } catch (\Exception $e) {
                $redir = new Errors($e, $e->getCode());
                return $redir->handleErrors();
            }
            $controller = new PostController($postRepo, $this->view);

            return $controller->displayOneAction((int) $this->request->query()->get('id'), $commentRepo);

            // *** @Route http://localhost:8000/?action=login ***
        } elseif ($action === 'login') {
            try {
                $userRepo = new UserRepository($this->database);
            } catch (\Exception $e) {
                $redir = new Errors($e, $e->getCode());
                return $redir->handleErrors();
            }
            $controller = new UserController($userRepo, $this->view, $this->session);

            return $controller->loginAction($this->request);

            // *** @Route http://localhost:8000/?action=signup ***
        } elseif ($action === 'signup') {
            try {
                $userRepo = new UserRepository($this->database);
            } catch (\Exception $e) {
                $redir = new Errors($e, $e->getCode());
                return $redir->handleErrors();
            }
            $controller = new UserController($userRepo, $this->view, $this->session);

            return $controller->signupAction($this->request);

            // *** @Route http://localhost:8000/?action=logout ***
        } elseif ($action === 'logout') {
            try {
                $userRepo = new UserRepository($this->database);
            } catch (\Exception $e) {
                $redir = new Errors($e, $e->getCode());
                return $redir->handleErrors();
            }
            $controller = new UserController($userRepo, $this->view, $this->session);

            return $controller->logoutAction();
        } else {
            return new Response("Error 404 - cette page n'existe pas<br><a href='index.php?action=posts'>Aller Ici</a>", 404);
        }
    }
}
