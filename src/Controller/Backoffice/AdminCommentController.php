<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Model\Entity\Comment;
use App\Model\Repository\CommentRepository;
use App\View\View;
use App\Model\Repository\UserRepository;
use App\Service\Http\ParametersBag;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Service\Utils\AuthentificationService;
use App\Service\Utils\InformUserService;
use App\Service\Utils\PaginationService;
use App\Service\Utils\SetOrderService;
use App\Service\Utils\TokenService;
use App\Service\Utils\ValidityService;

final class AdminCommentController implements ControllerInterface
{
    private CommentRepository $commentRepository;
    private UserRepository $userRepository;
    private View $view;
    private Session $session;
    private AuthentificationService $authentificationService;
    private PaginationService $paginationService;
    private SetOrderService $setOrderService;
    private TokenService $tokenService;
    private ValidityService $validityService;
    private InformUserService $informUserService;


    public function __construct(
        CommentRepository $commentRepository,
        UserRepository $userRepository,
        View $view,
        Session $session,
        AuthentificationService $authentificationService,
        PaginationService $paginationService,
        SetOrderService $setOrderService,
        TokenService $tokenService,
        ValidityService $validityService,
        InformUserService $informUserService
    ) {
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
        $this->authentificationService = $authentificationService;
        $this->paginationService = $paginationService;
        $this->setOrderService = $setOrderService;
        $this->tokenService = $tokenService;
        $this->validityService = $validityService;
        $this->informUserService = $informUserService;
    }

    public function displayAllComments(?array $params = [], ?ParametersBag $request = null): Response
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
        // to determine if it's the last page
        $comments = $this->commentRepository->findAll(4, $offset, ['order' => $order["order"]]);
        $end = false;

        if ($comments) {
            if (!array_key_exists(3, $comments)) {
                $end = true;
            }
            $comments = array_slice($comments, 0, 3);
        }

        //set security token
        $tokencsrf = $this->tokenService->setToken($this->session);

        return new Response($this->view->render([
            'template' => 'listComments',
            'data' => [
                'comments' => $comments,
                'page' => $params === null ? 0 : (int)$params["page"],
                'filter' => $order,
                "end" => $end,
                "tokencsrf" => $tokencsrf
            ],
            'env' => 'backoffice'
        ]));
    }

    public function validateOneComment(array $params, ?ParametersBag $request): Response
    {
        // check if admin
        if (!$this->authentificationService->isAdminAuth($this->session)) {
            return new Response("", 302, ["location" =>  "/error/403"]);
        }

        $this->session->addFlashes("danger", "Une erreur est survenue");
        if ($request === null) {
            return new Response("", 302, ["location" =>  "/admin/comments"]);
        }
        $param = $request->all();

        if (empty($param["text"])) {
            return new Response("", 302, ["location" =>  "/admin/comments"]);
        }

        // check validity security token
        $validToken = $this->tokenService->validateToken($param, $this->session);

        if (!$validToken) {
            return new Response("", 302, ["location" =>  "/admin/comments"]);
        }

        $params["text"] = $param["text"];
        $params = $this->validityService->validityVariables($params);

        $comment = new Comment(["id" => (int)$params["id"], "text" => $params["text"]]);
        $text = $comment->getText();

        if ($this->commentRepository->update($comment)) {
            $this->informUserService
                ->contactUserComment(
                    $this->session,
                    $this->userRepository,
                    $params,
                    "Commentaire validé",
                    "frontoffice/mail/publishedComment.html.twig",
                    $text,
                    "Le commentaire est validé et la confirmation envoyée"
                );
        }
        return new Response("", 302, ["location" =>  "/admin/comments"]);
    }

    public function deleteOneComment(array $params, ?ParametersBag $request): Response
    {
        // check if admin
        if (!$this->authentificationService->isAdminAuth($this->session)) {
            return new Response("", 302, ["location" =>  "/error/403"]);
        }

        $this->session->addFlashes("danger", "Une erreur est survenue");
        if ($request === null) {
            return new Response("", 302, ["location" =>  "/admin/comments"]);
        }
        $param = $request->all();

        if (empty($param["text"])) {
            return new Response("", 302, ["location" =>  "/admin/comments"]);
        }

        // check validity security token
        $validToken = $this->tokenService->validateToken($param, $this->session);
        if (!$validToken) {
            return new Response("", 302, ["location" =>  "/admin/comments"]);
        }

        $params["text"] = $param["text"];
        $params = $this->validityService->validityVariables($params);

        $comment = new Comment(["id" => (int)$params["id"], "text" => $params["text"]]);
        $text = $comment->getText();

        if ($this->commentRepository->delete($comment)) {
            $this->informUserService
                ->contactUserComment(
                    $this->session,
                    $this->userRepository,
                    $params,
                    "Commentaire supprimé",
                    "frontoffice/mail/deletedComment.html.twig",
                    $text,
                    "Le commentaire à bien été supprimé et la confirmation envoyée"
                );
        }
        return new Response("", 304, ["location" =>  "/admin/comments"]);
    }
}
