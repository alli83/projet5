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
use App\Service\Utils\Authentification;
use App\Service\Utils\Mailer;
use App\Service\Utils\Validity;

class AdminCommentController implements ControllerInterface
{
    private CommentRepository $commentRepository;
    private UserRepository $userRepository;
    private View $view;
    private Session $session;

    public function __construct(CommentRepository $commentRepository, UserRepository $userRepository, View $view, Session $session)
    {
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
    }

    public function displayAllComments(?array $params = []): Response
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

            $comments = $this->commentRepository->findAll(3, $offset);

            return new Response($this->view->render([
                'template' => 'listComments',
                'data' => [
                    'comments' => $comments,
                    'page' => $params === null ? 0 : (int)$params["page"]
                ],
                'env' => 'backoffice'
            ]));
        }
        return new Response("", 304, ["location" =>  "/login"]);
    }

    public function validateOneComment(array $params, ?ParametersBag $request): Response
    {
        $auth = new Authentification();
        if ($auth->isAdminAuth($this->session)) {
            $param = $request->all();

            $params["text"] = $param["text"];
            $validity = new Validity();
            $params = $validity->validityVariables($params);

            $comment = new Comment(["id" => (int)$params["id"], "text" => $params["text"]]);

            $this->session->addFlashes('danger', "Une erreur est survenue");
            if ($this->commentRepository->validate($comment)) {
                $user = $this->userRepository->findOneThroughComment(["id" => (int)$params["id"]]);
                if ($user) {
                    $message = new Mailer("Commentaire validé");
                    if (
                        !$message->sendMessage(
                            "frontoffice/mail/publishedComment.html.twig",
                            $user->getEmail(),
                            ["comment" => $comment->getText(), "pseudo" => $user->getPseudo()]
                        )
                    ) {
                        $this->session->addFlashes('warning', "la confirmation n'a pas pu être envoyée par mail");
                    }
                }
                $this->session->addFlashes('success', "le commentaire est validé");
            }
            return new Response("", 304, ["location" =>  "/admin/comments"]);
        }
        return new Response("", 304, ["location" =>  "/login"]);
    }

    public function deleteOneComment(array $params, ?ParametersBag $request): Response
    {
        $auth = new Authentification();

        if ($auth->isAdminAuth($this->session)) {
            $param = $request->all();

            $params["text"] = $param["text"];
            $validity = new Validity();
            $params = $validity->validityVariables($params);

            $comment = new Comment(["id" => (int)$params["id"], "text" => $params["text"]]);

            $this->session->addFlashes('danger', "Une erreur est survenue");
            if ($this->commentRepository->delete($comment)) {
                $user = $this->userRepository->findOneThroughComment(["id" => (int)$params["id"]]);
                if ($user) {
                    $message = new Mailer("commentaire supprimé");
                    if (
                        !$message->sendMessage(
                            "frontoffice/mail/deletedComment.html.twig",
                            $user->getEmail(),
                            ["comment" => $comment->getText(), "pseudo" => $user->getPseudo()]
                        )
                    ) {
                        $this->session->addFlashes('warning', "la confirmation n'a pas pu être envoyée par mail");
                    }
                }
                $this->session->addFlashes('success', "le commentaire à bien été supprimé");
            }
            return new Response("", 304, ["location" =>  "/admin/comments"]);
        }
        return new Response("", 304, ["location" =>  "/login"]);
    }
}
