<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\View\View;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Model\Repository\UserRepository;
use App\Service\Http\ParametersBag;
use App\Service\Utils\AuthService;
use App\Service\Utils\CheckSignupService;
use App\Service\Utils\InformUserService;
use App\Service\Utils\MailerService;
use App\Service\Utils\TokenService;
use App\Service\Utils\ValidityService;

final class UserController implements ControllerInterface
{
    private UserRepository $userRepository;
    private View $view;
    private Session $session;
    private AuthService $authService;
    private InformUserService $informUserService;
    private CheckSignupService $checkSignupService;
    private MailerService $mailService;
    private TokenService $tokenService;
    private ValidityService $validityService;

    public function __construct(
        UserRepository $userRepository,
        View $view,
        Session $session,
        AuthService $authService,
        InformUserService $informUserService,
        CheckSignupService $checkSignupService,
        MailerService $mailService,
        TokenService $tokenService,
        ValidityService $validityService
    ) {
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
        $this->authService = $authService;
        $this->informUserService = $informUserService;
        $this->checkSignupService = $checkSignupService;
        $this->mailService = $mailService;
        $this->tokenService = $tokenService;
        $this->validityService = $validityService;
    }

    public function loginAction(?ParametersBag $request): Response
    {
        // if already logged in => redirect
        if ($this->session->get("role")) {
            return new Response("", 302, ["location" =>  "/posts"]);
        }
        if ($request !== null) {
            $this->session->addFlashes("danger", "Mauvais identifiants");
            if (
                $this->authService->isValidLoginForm(
                    $this->session,
                    $request->all(),
                    $this->userRepository,
                    $this->tokenService,
                    $this->validityService
                )
            ) {
                $this->session->addFlashes("success", "Vous ??tes d??sormais connect??");
                return new Response("", 302, ["location" =>  "/posts"]);
            }
        }
        // set security token
        $tokencsrf = $this->tokenService->setToken($this->session);
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
        $validToken = $this->tokenService->validateToken($params, $this->session);

        if (!$validToken) {
            return new Response("", 302, ["location" =>  "/login"]);
        }

        if (!isset($params['emailReset']) || empty($params['emailReset'])) {
            return new Response("", 302, ["location" =>  "/login"]);
        }

        // check email validity
        $email = $this->validityService->validateEmail($params['emailReset']);

        if (!$email) {
            $this->session->addFlashes("warning", "Le format de votre email est invalide");
            return new Response("", 302, ["location" =>  "/login"]);
        }
        $user = $this->userRepository->findOneBy(["email" => $email]);

        if (!$user) {
            $this->session->addFlashes("warning", "Aucun compte ne correspond ?? cet email");
            return new Response("", 302, ["location" =>  "/login"]);
        }
        // set security token
        $tokencsrf = $this->tokenService->setToken($this->session);
        $user->setToken($tokencsrf);

        if ($this->userRepository->update($user)) {
            //set token in url
            $datas = ["pseudo" => $user->getPseudo(), "id" => $user->getId(), "token" => $tokencsrf];
            $this->informUserService
                ->contactUserMember(
                    $this->session,
                    $datas,
                    $email,
                    "R??initialisation du mot de passe",
                    "frontoffice/mail/reinitializePass.html.twig",
                    "Un lien de r??initialisation vous a ??t?? envoy?? par mail"
                );
        }
        return new Response("", 302, ["location" =>  "/login"]);
    }

    public function confirmResetAction(array $param, ?ParametersBag $request): Response
    {
        $param = $this->validityService->validityVariables($param);

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
            $this->session->addFlashes(
                "danger",
                "Une erreur est survenue. Si vous souhaitez toujours r??initialiser votre mot de passe,
            merci de g??n??rer ?? nouveau un lien en cliquant sur ' J'ai oubli?? mon mot de passe' "
            );

            return new Response("", 302, ["location" =>  "/login"]);
        }
        $params = $request->all();
        $params = $this->validityService->validityVariables($params);

        if (empty($params['password2']) || empty($params['passwordConfirm2'])) {
            return new Response("", 302, ["location" =>  "/login"]);
        }

        $password = $this->validityService->validatePassword($params['password2'], $params['passwordConfirm2']);

        if ($password === null) {
            return new Response("", 302, ["location" =>  "/login"]);
        }
        $user->setPassword($password);

        $this->session->addFlashes(
            "danger",
            "Une erreur est survenue. Si vous souhaitez toujours r??initialiser votre mot de passe,
        merci de g??n??rer ?? nouveau un lien en cliquant sur ' J'ai oubli?? mon mot de passe' "
        );
        if ($this->userRepository->update($user)) {
            $this->session->addFlashes("success", "Votre mot de passe a bien ??t?? r??initialis??. 
                            Vous pouvez d??sormais vous connecter avec votre nouveau mot de passe");
        }
        return new Response("", 302, ["location" =>  "/login"]);
    }

    public function logoutAction(): Response
    {
        $this->session->remove('role');
        $this->session->remove('pseudo');
        $this->session->remove('email');
        $this->session->addFlashes('success', 'Vous ??tes d??connect??');
        return new Response("", 302, ["location" =>  "/posts"]);
    }

    public function signupAction(?ParametersBag $request): Response
    {
        // redirect if already logged in
        if ($this->session->get("role")) {
            return new Response("", 302, ["location" =>  "/posts"]);
        }
        if ($request === null) {
            $tokencsrf = $this->tokenService->setToken($this->session);
            return new Response($this->view->render(['template' => 'signup', 'data' => [
                "tokencsrf" => $tokencsrf
            ]]));
        }

        $this->session->addFlashes("danger", "Une erreur est survenue");

        $params = $this->checkSignupService
            ->paramsSignUp(
                $request,
                $this->session,
                $this->tokenService,
                $this->validityService
            );

        if ($params === null) {
            return new Response("", 302, ["location" =>  "/signup"]);
        }

        $email = $this->validityService->validateEmail($params['emailSignup']);

        if (!$email) {
            $this->session->addFlashes("warning", "Le format de votre email est invalide");
            return new Response("", 302, ["location" =>  "/signup"]);
        }
        $params['emailSignup'] = $email;

        $this->session->addFlashes("warning", "Ce pseudo est d??j?? pris");
        if ($this->authService->register($this->session, $params, $this->userRepository)) {
            $user = $this->userRepository->findOneBy(['email' => $email]);

            $this->session->addFlashes("danger", "Une erreur est survenue");

            if (!$user) {
                return new Response("", 304, ["location" =>  "/login"]);
            }

            $this->session->addFlashes("warning", "L'email n'a pas pu ??tre envoy??");
            $message = $this->mailService->sendMessage(
                "cr??ation de compte",
                "frontoffice/mail/validateRegistration.html.twig",
                $email,
                ["pseudo" => $user->getPseudo()]
            );
            if ($message) {
                $this->session->addFlashes(
                    "success",
                    "Votre inscription a bien ??t?? prise en compte. Vous pouvez d??sormais vous connecter"
                );
            }
            return new Response("", 302, ["location" =>  "/login"]);
        }
        return new Response("", 302, ["location" =>  "/signup"]);
    }
}
