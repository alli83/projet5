<?php

declare(strict_types=1);

namespace  App\Service;

use App\Controller\Backoffice\AdminCommentController;
use App\Controller\Backoffice\AdminMemberController;
use App\Controller\Backoffice\AdminPostController;
use App\Controller\ControllerInterface\ControllerInterface;
use App\Controller\Frontoffice\CommentController;
use App\Controller\Frontoffice\HomeController;
use App\Controller\Frontoffice\PostController;
use App\Controller\Frontoffice\UserController;
use App\Model\Repository\CommentRepository;
use App\Model\Repository\Interfaces\EntityRepositoryInterface;
use App\Model\Repository\PostRepository;
use App\Model\Repository\UserRepository;
use App\Service\Http\Session\Session;
use App\Service\Utils\ServiceProvider;
use App\View\View;

class Container
{

    private Session $session;
    private ServiceProvider $serviceProvider;

    public function __construct(ServiceProvider $serviceProvider)
    {
        $this->serviceProvider = $serviceProvider;
    }

    public function callGoodController(string $name): ControllerInterface
    {
        switch ($name) {
            case "home":
                return $this->getHomeController();
            case "post":
                return $this->getPostController();
            case "user":
                return $this->getUserController();
            case "comment":
                return $this->getCommentController();
            case "adminpost":
                return $this->getAdminPostController();
            case "admincomment":
                return $this->getAdminCommentController();
            case "adminmember":
                return $this->getAdminMemberController();
            default:
                return $this->getHomeController();
        }
    }

    public function setRepositoryClass(string $name): ?EntityRepositoryInterface
    {
        switch ($name) {
            case "comment":
                return $this->getCommentRepository();
            case "user":
                return $this->getUserRepository();
            default:
                return null;
        }
    }

    public function getDatabase(): Database
    {
        return new Database();
    }
    public function getSession(): Session
    {
        if (empty($this->session)) {
            $this->session = new Session();
        }
        return $this->session;
    }
    public function getView(): View
    {
        return new View($this->getSession());
    }
    public function getHomeController(): ControllerInterface
    {
        return new HomeController($this->getView(), $this->getSession(), $this->serviceProvider);
    }
    public function getPostController(): ControllerInterface
    {
        return new PostController($this->getPostRepository(), $this->getView(), $this->getSession(), $this->serviceProvider);
    }
    public function getUserController(): ControllerInterface
    {
        return new UserController($this->getUserRepository(), $this->getView(), $this->getSession(), $this->serviceProvider);
    }
    public function getCommentController(): ControllerInterface
    {
        return new CommentController($this->getCommentRepository(), $this->getView(), $this->getSession(), $this->serviceProvider);
    }
    public function getAdminPostController(): ControllerInterface
    {
        return new AdminPostController($this->getPostRepository(), $this->getUserRepository(), $this->getView(), $this->getSession(), $this->serviceProvider);
    }
    public function getAdminCommentController(): ControllerInterface
    {
        return new AdminCommentController($this->getCommentRepository(), $this->getUserRepository(), $this->getView(), $this->getSession(), $this->serviceProvider);
    }
    public function getAdminMemberController(): ControllerInterface
    {
        return new AdminMemberController($this->getUserRepository(), $this->getView(), $this->getSession(), $this->serviceProvider);
    }
    public function getPostRepository(): PostRepository
    {
        return new PostRepository($this->getDatabase());
    }
    public function getCommentRepository(): CommentRepository
    {
        return new CommentRepository($this->getDatabase());
    }
    public function getUserRepository(): UserRepository
    {
        return new UserRepository($this->getDatabase());
    }

    public function getRoutes(string $url, string $module, string $action, string $accessory, ?array $varsnames): Route
    {
        return new Route($url, $module, $action, $accessory, $varsnames);
    }
}
