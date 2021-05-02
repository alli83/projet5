<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\View\View;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Model\Repository\UserRepository;
use App\Service\Http\ParametersBag;
use App\Service\Utils\Auth;
use App\Service\Utils\Mailer;

final class UserController implements ControllerInterface
{
    private UserRepository $userRepository;
    private View $view;
    private Session $session;

    public function __construct(UserRepository $userRepository, View $view, Session $session)
    {
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
    }

    public function loginAction(?ParametersBag $request): Response
    {
        if ($this->session->get("role")) {
            return new Response("", 304, ["location" =>  "/posts"]);
        }

        if ($request !== null) {
            $auth = new Auth($request->all(), $this->userRepository, $this->session);

            if ($auth->isValidLoginForm()) {
                $this->session->addFlashes('success', 'Vous êtes désormais connecté');
                return new Response("", 304, ["location" =>  "/posts"]);
            }
            $this->session->addFlashes('danger', 'Mauvais identifiants');
        }
        return new Response($this->view->render(['template' => 'login', 'data' => []]));
    }


    public function logoutAction(): Response
    {
        $this->session->remove('role');
        $this->session->remove('pseudo');
        $this->session->remove('email');
        $this->session->addFlashes('success', 'Vous êtes déconnecté');
        return new Response("", 304, ["location" =>  "/posts"]);
    }

    public function signupAction(?ParametersBag $request): Response
    {

        if ($this->session->get("role")) {
            return new Response("", 304, ["location" =>  "/posts"]);
        }
        $template = ['template' => 'signup', 'data' => []];

        if ($request !== null) {
            $params = $request->all();

            if (isset($params['email']) && isset($params['password']) && isset($params['pseudo'])) {
                $auth = new Auth($params, $this->userRepository, $this->session);

                $this->session->addFlashes('danger', 'une erreur s\'est produite');
                if ($auth->register()) {
                    $user = $this->userRepository->findOneBy(['email' => $params['email']]);

                    $message = new Mailer("création de compte");
                    $message = $message->sendMessage(
                        "frontoffice/mail/validateRegistration.html.twig",
                        $params['email'],
                        ["pseudo" => $user->getPseudo()]
                    );

                    $this->session->addFlashes('success', 'Votre inscription a bien été prise en compte. Vous pouvez désormais vous connecter');
                    $template = ['template' => 'login', 'data' => []];
                }
            }
        }
        return new Response($this->view->render($template));
    }
}
