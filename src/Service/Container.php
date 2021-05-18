<?php

declare(strict_types=1);

namespace  App\Service;

use App\Controller\Backoffice\AdminCommentController;
use App\Controller\Backoffice\AdminMemberController;
use App\Controller\Backoffice\AdminPostController;
use App\Controller\ControllerInterface\ControllerInterface;
use App\Controller\Frontoffice\CommentController;
use App\Controller\Frontoffice\ErrorController;
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
    private Database $database;
    private View $view;
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
            case "error":
                return $this->getErrorController();
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
        if (empty($this->database)) {
            $this->database = new Database();
        }
        return $this->database;
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
        if (empty($this->view)) {
            $this->view = new View($this->getSession());
        }
        return $this->view;
    }



    public function getHomeController(): ControllerInterface
    {
        return new HomeController(
            $this->getView(),
            $this->getSession(),
            $this->serviceProvider->getAuthentificationService(),
            $this->serviceProvider->getValidityService(),
            $this->serviceProvider->getTokenService(),
            $this->serviceProvider->getInformUserService(),
            $this->serviceProvider->getFileService()
        );
    }
    public function getPostController(): ControllerInterface
    {
        return new PostController(
            $this->getPostRepository(),
            $this->getView(),
            $this->getSession(),
            $this->serviceProvider->getValidityService(),
            $this->serviceProvider->getPaginationService(),
            $this->serviceProvider->getSetOrderService()
        );
    }
    public function getUserController(): ControllerInterface
    {
        return new UserController(
            $this->getUserRepository(),
            $this->getView(),
            $this->getSession(),
            $this->serviceProvider->getAuthService(),
            $this->serviceProvider->getInformUserService(),
            $this->serviceProvider->getCheckSignupService(),
            $this->serviceProvider->getMailService(),
            $this->serviceProvider->getTokenService(),
            $this->serviceProvider->getValidityService()
        );
    }
    public function getCommentController(): ControllerInterface
    {
        return new CommentController(
            $this->getCommentRepository(),
            $this->getView(),
            $this->getSession(),
            $this->serviceProvider->getAuthentificationService(),
            $this->serviceProvider->getValidityService(),
        );
    }
    public function getAdminPostController(): ControllerInterface
    {
        return new AdminPostController(
            $this->getPostRepository(),
            $this->getUserRepository(),
            $this->getView(),
            $this->getSession(),
            $this->serviceProvider->getCreatePostService(),
            $this->serviceProvider->getAuthentificationService(),
            $this->serviceProvider->getPaginationService(),
            $this->serviceProvider->getSetOrderService(),
            $this->serviceProvider->getTokenService(),
            $this->serviceProvider->getValidityService(),
            $this->serviceProvider->getValidateFileService(),
            $this->serviceProvider->getFileService()
        );
    }
    public function getAdminCommentController(): ControllerInterface
    {
        return new AdminCommentController(
            $this->getCommentRepository(),
            $this->getUserRepository(),
            $this->getView(),
            $this->getSession(),
            $this->serviceProvider->getAuthentificationService(),
            $this->serviceProvider->getPaginationService(),
            $this->serviceProvider->getSetOrderService(),
            $this->serviceProvider->getTokenService(),
            $this->serviceProvider->getValidityService(),
            $this->serviceProvider->getInformUserService()
        );
    }
    public function getAdminMemberController(): ControllerInterface
    {
        return new AdminMemberController(
            $this->getUserRepository(),
            $this->getView(),
            $this->getSession(),
            $this->serviceProvider->getAuthentificationService(),
            $this->serviceProvider->getPaginationService(),
            $this->serviceProvider->getSetOrderService(),
            $this->serviceProvider->getTokenService(),
            $this->serviceProvider->getValidityService(),
            $this->serviceProvider->getInformUserService()
        );
    }
    public function getErrorController(): ControllerInterface
    {
        return new ErrorController($this->getView());
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
