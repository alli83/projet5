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
use App\Service\Http\ParametersBag;
use App\Service\Http\Session\Session;
use App\Service\Utils\ServiceProvider;

final class CommentController implements ControllerInterface
{
    private CommentRepository $commentRepository;
    private View $view;
    private Session $session;
    private ServiceProvider $serviceProvider;

    public function __construct(CommentRepository $commentRepository, View $view, Session $session, ServiceProvider $serviceProvider)
    {
        $this->commentRepository = $commentRepository;
        $this->view = $view;
        $this->session = $session;
        $this->serviceProvider = $serviceProvider;
    }

    public function createComment(?UserRepository $userRepository = null, ?ParametersBag $request = null): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();
        // check if auth
        if (!$auth->isAuth($this->session)) {
            $error = new Errors(403);
            return $error->handleErrors();
        }

        $this->session->addFlashes("danger", "Une erreur est survenue");

        if (empty($request) || empty($request->get("textComment")) || empty($request->get("post")) || empty($userRepository)) {
            return new Response("", 302, ["location" =>  "/posts"]);
        }

        $post = (int)$request->get("post");
        $user = $userRepository->findOneBy(["email" => $this->session->get("email")]);

        if (!$user) {
            return new Response("", 302, ["location" =>  "/post-${post}"]);
        }

        $params = ['text', 'idPost', 'idUser'];
        $values = [$request->get("textComment"), $post, $user->getId()];
        $param = array_combine($params, $values);

        $validityTools = $this->serviceProvider->getValidityService();
        $param = $validityTools->validityVariables($param);
        $object = new Comment($param);

        if ($this->commentRepository->create($object)) {
            $this->session->addFlashes("success", "Merci pour votre commentaire!  Dès qu'il sera validé par notre équipe, il sera publié");
        }
        return new Response("", 302, ["location" =>  "/post-${post}"]);
    }
}
