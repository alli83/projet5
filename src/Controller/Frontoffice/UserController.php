<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\View\View;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Model\Repository\UserRepository;
use App\Service\Http\ParametersBag;
use App\Service\Utils\ServiceProvider;

final class UserController implements ControllerInterface
{
    private UserRepository $userRepository;
    private View $view;
    private Session $session;
    private ServiceProvider $serviceProvider;

    public function __construct(UserRepository $userRepository, View $view, Session $session, ServiceProvider $serviceProvider)
    {
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
        $this->serviceProvider = $serviceProvider;
    }

    public function loginAction(?ParametersBag $request): Response
    {
        // if already logged in => redirect
        if ($this->session->get("role")) {
            return new Response("", 302, ["location" =>  "/posts"]);
        }
        if ($request !== null) {
            $auth = $this->serviceProvider->getAuthService();
            $this->session->addFlashes("danger", "Mauvais identifiants");
            if ($auth->isValidLoginForm($this->session, $request->all(), $this->userRepository)) {
                $this->session->addFlashes("success", "Vous êtes désormais connecté");
                return new Response("", 302, ["location" =>  "/posts"]);
            }
        }
        // set security token
        $tokencsrf = $this->serviceProvider->getTokenService()->setToken($this->session);
        return new Response($this->view->render(['template' => 'login', 'data' => ["tokencsrf" => $tokencsrf]]));
    }

    public function resetLoginAction(?ParametersBag $request): Response
    {
        $this->session->addFlashes("danger", "Une erreur est survenue");

        if ($request === null) {
            return new Response("", 302, ["location" =>  "/login"]);
        }
        $params = $request->all();

        // check validity security token (compared with the one on login page)
        $validToken = $this->serviceProvider->getTokenService()->validateToken($params, $this->session);

        if (!$validToken) {
            return new Response("", 302, ["location" =>  "/login"]);
        }

        if (!isset($params['emailReset']) || empty($params['emailReset'])) {
            return new Response("", 302, ["location" =>  "/login"]);
        }

        $validity = $this->serviceProvider->getValidityService();
        // check email validity
        $email = $validity->validateEmail($params['emailReset']);

        if (!$email) {
            $this->session->addFlashes("warning", "Le format de votre email est invalide");
            return new Response("", 302, ["location" =>  "/login"]);
        }
        $user = $this->userRepository->findOneBy(["email" => $email]);

        if (!$user) {
            $this->session->addFlashes("warning", "Aucun compte ne correspond à cet email");
            return new Response("", 302, ["location" =>  "/login"]);
        }
        // set security token
        $tokencsrf = $this->serviceProvider->getTokenService()->setToken($this->session);
        $user->setToken($tokencsrf);

        if ($this->userRepository->update($user)) {
            //set token in url
            $datas = ["pseudo" => $user->getPseudo(), "id" => $user->getId(), "token" => $tokencsrf];
            $this->serviceProvider->getInformUserService()
                ->contactUserMember(
                    $this->session,
                    $datas,
                    $email,
                    "Réinitialisation du mot de passe",
                    "frontoffice/mail/reinitializePass.html.twig",
                    "Un lien de réinitialisation vous a été envoyé par mail"
                );
        }
        return new Response("", 302, ["location" =>  "/login"]);
    }

    public function confirmResetAction(array $param, ?ParametersBag $request): Response
    {
        $validity = $this->serviceProvider->getValidityService();
        $param = $validity->validityVariables($param);

        if ($request === null) {
            return new Response($this->view->render([
                'template' => 'reset',
                'data' => [
                    "id" => $param["id"],
                    "token" => $param["token"]
                ]
            ]));
        }

        $this->session->addFlashes("danger", "Une erreur est survenue");

        $user = $this->userRepository->findOneBy(["id" => (int)$param["id"]]);

        // check validity security token
        if (!$user || $user->getToken() !== $param["token"]) {
            $this->session->addFlashes("danger", "Une erreur est survenue. Si vous souhaitez toujours réinitialiser votre mot de passe,
            merci de générer à nouveau un lien en cliquant sur ' J'ai oublié mon mot de passe' ");

            return new Response("", 302, ["location" =>  "/login"]);
        }
        $params = $request->all();

        if (empty($params['password2']) || empty($params['passwordConfirm2'])) {
            return new Response("", 302, ["location" =>  "/login"]);
        }

        $password = $validity->validatePassword($params['password2'], $params['passwordConfirm2']);

        if ($password === null) {
            return new Response("", 302, ["location" =>  "/login"]);
        }
        $user->setPassword($password);

        $this->session->addFlashes("danger", "Une erreur est survenue. Si vous souhaitez toujours réinitialiser votre mot de passe,
        merci de générer à nouveau un lien en cliquant sur ' J'ai oublié mon mot de passe' ");
        if ($this->userRepository->update($user)) {
            $this->session->addFlashes("success", "Votre mot de passe a bien été réinitialisé. 
                            Vous pouvez désormais vous connecter avec votre nouveau mot de passe");
        }
        return new Response("", 302, ["location" =>  "/login"]);
    }

    public function logoutAction(): Response
    {
        $this->session->remove('role');
        $this->session->remove('pseudo');
        $this->session->remove('email');
        $this->session->addFlashes('success', 'Vous êtes déconnecté');
        return new Response("", 302, ["location" =>  "/posts"]);
    }

    public function signupAction(?ParametersBag $request): Response
    {
        // redirect if already logged in
        if ($this->session->get("role")) {
            return new Response("", 302, ["location" =>  "/posts"]);
        }
        if ($request === null) {
            $tokencsrf = $this->serviceProvider->getTokenService()->setToken($this->session);
            return new Response($this->view->render(['template' => 'signup', 'data' => [
                "tokencsrf" => $tokencsrf
            ]]));
        }

        $this->session->addFlashes("danger", "Une erreur est survenue");
        $params = $request->all();

        if (empty($params['emailSignup']) || empty($params['password']) || empty($params['pseudoSignup'])) {
            return new Response("", 302, ["location" =>  "/signup"]);
        }
        // check validity security token
        $validToken = $this->serviceProvider->getTokenService()->validateToken($params, $this->session);
        if (!$validToken) {
            return new Response("", 302, ["location" =>  "/signup"]);
        }

        $validity = $this->serviceProvider->getValidityService();
        $params = $validity->validityVariables($params);
        $email = $validity->validateEmail($params['emailSignup']);

        if (!$email) {
            $this->session->addFlashes("warning", "Le format de votre email est invalide");
            return new Response("", 302, ["location" =>  "/signup"]);
        }
        $params['emailSignup'] = $email;
        $auth = $this->serviceProvider->getAuthService();

        $this->session->addFlashes("warning", "Ce pseudo est déjà pris");
        if ($auth->register($this->session, $params, $this->userRepository)) {
            $user = $this->userRepository->findOneBy(['email' => $email]);

            $this->session->addFlashes("danger", "Une erreur est survenue");

            if (!$user) {
                return new Response("", 304, ["location" =>  "/login"]);
            }

            $this->session->addFlashes("warning", "L'email n'a pas pu être envoyé");
            $message = $this->serviceProvider->getMailService()->sendMessage(
                "création de compte",
                "frontoffice/mail/validateRegistration.html.twig",
                $email,
                ["pseudo" => $user->getPseudo()]
            );
            if ($message) {
                $this->session->addFlashes("success", "Votre inscription a bien été prise en compte. Vous pouvez désormais vous connecter");
            }
            return new Response("", 302, ["location" =>  "/login"]);
        }
        return new Response("", 302, ["location" =>  "/signup"]);
    }
}
