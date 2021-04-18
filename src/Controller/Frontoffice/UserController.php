<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\View\View;
use App\Service\Http\Request;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Model\Repository\UserRepository;
use App\Service\Utils\Auth;
use App\Service\Utils\Mailer;

final class UserController
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

    public function loginAction(Request $request): Response
    {
        if ($request->getMethod() === 'POST') {
            $auth = new Auth($request->request()->all(), $this->userRepository, $this->session);
            if ($auth->isValidLoginForm()) {
                $this->session->addFlashes('success', 'Vous êtes connecté');
                return new Response("", 304, ["location" =>  "index.php?action=posts"]);
            }
            $this->session->addFlashes('error', 'Mauvais identifiants');
        }
        return new Response($this->view->render(['template' => 'login', 'data' => []]));
    }


    public function signupAction(Request $request): Response
    {
        if ($request->getMethod() === 'POST') {
            $params = $request->request()->all();

            if (isset($params['email']) && isset($params['password']) && isset($params['pseudo'])) {
                $auth = new Auth($params, $this->userRepository, $this->session);

                if ($auth->register()) {
                    $user = $this->userRepository->findOneBy(['email' => $params['email']]);
                    if ($user !== null) {
                        $message = new Mailer();
                        $message->sendMessage("frontoffice/mail/validateRegistration.html.twig", $user, $params['email']);
                        $this->session->addFlashes('success', 'Votre inscription a bien été prise en compte. Vous allez recevoir un lien de validation');
                    }
                    else {
                        $this->session->addFlashes('error', 'une erreur s\'est produite');
                    }
                } else {
                    $this->session->addFlashes('error', 'une erreur s\'est produite');
                }
            } else {
                $this->session->addFlashes('error', 'une erreur s\'est produite');
            }
        }
        return new Response($this->view->render(['template' => 'signup', 'data' => []]));
    }

    public function logoutAction(): Response
    {
        $this->session->remove('user');
        return new Response('<h1>Utilisateur déconnecté</h1><h2>faire une redirection vers la page d\'accueil</h2><a href="index.php?action=posts">Liste des posts</a><br>', 200);
    }
}
