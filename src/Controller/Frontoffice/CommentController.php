<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Model\Entity\Comment;
use App\View\View;
use App\Service\Http\Response;
use App\Model\Repository\CommentRepository;
use App\Model\Repository\UserRepository;
use App\Service\ErrorsHandlers\Errors;
use App\Service\Http\Session\Session;
use App\Service\Utils\Authentification;
use App\Service\Utils\Mailer;
use App\Service\Utils\Validity;

final class CommentController implements ControllerInterface
{
    private CommentRepository $commentRepository;
    private View $view;
    private Session $session;

    public function __construct(CommentRepository $commentRepository, View $view, Session $session)
    {
        $this->commentRepository = $commentRepository;
        $this->view = $view;
        $this->session = $session;
    }

    public function createComment(UserRepository $userRepository, ?object $request): Response
    {
        if ($request !== null && !empty($request->get("pseudo"))  && !empty($request->get("text"))  && !empty($request->get("post"))) {
            $auth = new Authentification();
            $user = $auth->isAuth($this->session, $userRepository);

            if ($user !== null) {
                $post = intval($request->get("post"));
                $params = ['pseudo', 'text', 'idPost', 'idUser'];
                $values = [$request->get("pseudo"),$request->get("text"),$post, $user->getId()];
                $param = array_combine($params, $values);

                $validityTools = new Validity();
                $param = $validityTools->validityVariables($param);

                $object = new Comment($param);

                $result = $this->commentRepository->create($object);
                if ($result === true) {
                    $message = new Mailer();
                    $validate = $message->sendMessage("frontoffice/mail/validateComment.html.twig", $user, $user->getEmail());

                    $validate === true ? $this->session->addFlashes('success', 'Une confirmation vous a été envoyée par mail') :
                                        $this->session->addFlashes('warning', 'Une erreur s\'est produite au niveau de l\'envoi de la confirmation par mail');
                }
                $result === true ? $this->session->addFlashes('success', 'Votre commentaire a été envoyé pour validation') :
                                   $this->session->addFlashes('error', 'Une erreur est survenue');

                return new Response("", 304, ["location" =>  "/post-${post}"]);
            } else {
                $error = new Errors(404);
                return $error->handleErrors();
            }
        } else {
            $error = new Errors(404);
            return $error->handleErrors();
        }
    }
}
