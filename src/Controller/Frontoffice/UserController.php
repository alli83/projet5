<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Model\Entity\User;
use App\View\View;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Model\Repository\UserRepository;
use App\Service\Http\ParametersBag;
use App\Service\Utils\Auth;
use App\Service\Utils\Mailer;
use App\Service\Utils\Validity;

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

    public function resetLoginAction(?ParametersBag $request): Response
    {
        if ($request !== null) {
            $params = $request->all();

            if (isset($params['emailReset']) && !empty($params['emailReset'])) {
                $validity = new Validity();
                $email = $validity->validateEmail($params['emailReset']);

                $user = $this->userRepository->findOneBy(["email" => $email]);

                $this->session->addFlashes("warning", "Aucun compte ne correspond à cet email");
                if ($user) {
                    $message = new Mailer("Réinitialisation du mot de passe");
                    $message = $message->sendMessage(
                        "frontoffice/mail/reinitializePass.html.twig",
                        $email,
                        [
                            "pseudo" => $user->getPseudo(),
                            "id" => $user->getId()
                        ]
                    );
                    $this->session->addFlashes("success", "Un email vous a été envoyé");
                }
            }
        }
        return new Response($this->view->render(['template' => 'login', 'data' => []]));
    }

    public function confirmResetAction(array $param, ?ParametersBag $request): Response
    {
        $validity = new Validity();
        $param = $validity->validityVariables(["id" => $param["id"]]);
        
        if ($request !== null) {
            $params = $request->all();

            if (isset($params['password2']) && isset($params['passwordConfirm2'])) {
                var_dump("ou");
                $password = $validity->validatePassword($params['password2'], $params['passwordConfirm2']);

                if ($password === null) {
                    $this->session->addFlashes("danger", "une erreur est survenue");
                }

                $user = new User(["id" => (int)$param["id"], "password" => $password]);

                $this->session->addFlashes("danger", "votre mot de pass a bien été réinitialisé");
                if ($this->userRepository->updatePass($user)) {
                    $this->session->addFlashes("success", "votre mot de pass a bien été réinitialisé");
                    return new Response($this->view->render(['template' => 'login', 'data' => []]));
                }
            }
        }
        return new Response($this->view->render(['template' => 'reset', 'data' => ["id" => $param["id"]]]));
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

            if (isset($params['emailSignup']) && isset($params['password']) && isset($params['pseudoSignup'])) {
                $auth = new Auth($params, $this->userRepository, $this->session);

                $this->session->addFlashes('danger', 'une erreur s\'est produite');
                if ($auth->register()) {
                    $validity = new Validity();
                    $email = $validity->validateEmail($params['emailSignup']);

                    $user = $this->userRepository->findOneBy(['email' => $email]);

                    $message = new Mailer("création de compte");
                    $message = $message->sendMessage(
                        "frontoffice/mail/validateRegistration.html.twig",
                        $email,
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
