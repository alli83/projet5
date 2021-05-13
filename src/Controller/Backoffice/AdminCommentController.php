<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Model\Entity\Comment;
use App\Model\Repository\CommentRepository;
use App\View\View;
use App\Model\Repository\UserRepository;
use App\Service\ErrorsHandlers\Errors;
use App\Service\Http\ParametersBag;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Service\Utils\ServiceProvider;

final class AdminCommentController implements ControllerInterface
{
    private CommentRepository $commentRepository;
    private UserRepository $userRepository;
    private View $view;
    private Session $session;
    private ServiceProvider $serviceProvider;

    public function __construct(
        CommentRepository $commentRepository,
        UserRepository $userRepository,
        View $view,
        Session $session,
        ServiceProvider $serviceProvider
    ) {
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
        $this->serviceProvider = $serviceProvider;
    }

    public function displayAllComments(?array $params = [], ?ParametersBag $request = null): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();
        // check if admin
        if (!$auth->isAdminAuth($this->session)) {
            $error = new Errors(403);
            return $error->handleErrors();
        }

        // set pagination
        $offset = $this->serviceProvider->getPaginationService()->setOffset($params);

        // set order
        $orderToSet = "desc";
        if (!empty($request) && $request->get("order") !== null) {
            $request = $request->all();
            $request = $this->serviceProvider->getValidityService()->validityVariables($request);
            $orderToSet = $request["order"];
        }
        $order = $this->serviceProvider->getValidityService()->isInArray(["asc", "desc"], $orderToSet);

        if (!$order) {
            $error = new Errors(404);
            return $error->handleErrors();
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
        $tokencsrf = $this->serviceProvider->getTokenService()->setToken($this->session);

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
        $auth = $this->serviceProvider->getAuthentificationService();

        // check if admin
        if (!$auth->isAdminAuth($this->session)) {
            $error = new Errors(403);
            return $error->handleErrors();
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
        $validToken = $this->serviceProvider->getTokenService()->validateToken($param, $this->session);

        if (!$validToken) {
            return new Response("", 302, ["location" =>  "/admin/comments"]);
        }

        $params["text"] = $param["text"];
        $validity = $this->serviceProvider->getValidityService();
        $params = $validity->validityVariables($params);

        $comment = new Comment(["id" => (int)$params["id"], "text" => $params["text"]]);
        $text = $comment->getText();

        if ($this->commentRepository->update($comment)) {
            $this->serviceProvider->getInformUserService()
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
        $auth = $this->serviceProvider->getAuthentificationService();
        // check if admin
        if (!$auth->isAdminAuth($this->session)) {
            $error = new Errors(403);
            return $error->handleErrors();
        }

        $this->session->addFlashes('danger', "Une erreur est survenue");
        if ($request === null) {
            return new Response("", 302, ["location" =>  "/admin/comments"]);
        }
        $param = $request->all();

        if (empty($param["text"])) {
            return new Response("", 302, ["location" =>  "/admin/comments"]);
        }

        // check validity security token
        $validToken = $this->serviceProvider->getTokenService()->validateToken($param, $this->session);
        if (!$validToken) {
            return new Response("", 302, ["location" =>  "/admin/comments"]);
        }

        $params["text"] = $param["text"];
        $validity = $this->serviceProvider->getValidityService();
        $params = $validity->validityVariables($params);

        $comment = new Comment(["id" => (int)$params["id"], "text" => $params["text"]]);
        $text = $comment->getText();

        if ($this->commentRepository->delete($comment)) {
            $this->serviceProvider->getInformUserService()
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
