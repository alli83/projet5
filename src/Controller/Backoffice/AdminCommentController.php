<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Model\Entity\Comment;
use App\Model\Repository\CommentRepository;
use App\View\View;
use App\Model\Repository\UserRepository;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Service\Utils\Authentification;
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
        $user = $auth->isAuth($this->session, $this->userRepository);

        if ($user !== null) {
            if ($params === null || ($params && $params["page"] === null)) {
                $offset = 0;
            } else {
                $offset = intval($params["page"]) * 3;
            }

            $comments = $this->commentRepository->findAll(3, $offset);

            return new Response($this->view->render([
                'template' => 'listComments',
                'data' => [
                    'comments' => $comments,
                    'page' => $params === null ? 0 : intval($params["page"])
                ],
                'env' => 'backoffice'
            ]));
        }
        return new Response($this->view->render(['template' => 'accueil', 'data' => []]));
    }

    public function validateOneComment(array $params): Response
    {
        $auth = new Authentification();
        $validity = new Validity();
        $params = $validity->validityVariables($params);

        $user = $auth->isAuth($this->session, $this->userRepository);

        if ($user !== null) {
            $comment = new Comment(["id" => intval($params["id"])]);
            $validatedComment = $this->commentRepository->validate($comment);
            if ($validatedComment) {
                $this->session->addFlashes('success', "le commentaire à bien été validé"); // envoyer un message!
            } else {
                $this->session->addFlashes('danger', "Une erreur est survenue");
            }
            return new Response("", 304, ["location" =>  "/admin/comments"]);
        }
        return new Response($this->view->render(['template' => 'accueil', 'data' => []]));
    }


    public function deleteOneComment(array $params): Response
    {
        $auth = new Authentification();
        $validity = new Validity();
        $params = $validity->validityVariables($params);

        $user = $auth->isAuth($this->session, $this->userRepository);
        if ($user !== null) {
            $comment = new Comment(["id" => intval($params["id"])]);
            $deletedComment = $this->commentRepository->delete($comment);
            if ($deletedComment) {
                $this->session->addFlashes('success', "le commentaire à bien été supprimé"); // envoyer un message!
            } else {
                $this->session->addFlashes('danger', "Une erreur est survenue");
            }
            return new Response("", 304, ["location" =>  "/admin/comments"]);
        }
        return new Response($this->view->render(['template' => 'accueil', 'data' => []]));
    }
}
