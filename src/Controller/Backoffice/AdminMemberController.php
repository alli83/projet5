<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Model\Entity\User;
use App\View\View;
use App\Model\Repository\UserRepository;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Service\Utils\Authentification;
use App\Service\Utils\Validity;

class AdminMemberController implements ControllerInterface
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

    public function displayAllMembers(?array $params = []): Response
    {
        $auth = new Authentification();
        $user = $auth->isAuth($this->session, $this->userRepository);

        if ($user !== null) {
            if ($params === null || ($params && $params["page"] === null)) {
                $offset = 0;
            } else {
                $offset = (int)$params["page"] * 3;
            }

            $users = $this->userRepository->findAll(3, $offset);
            return new Response($this->view->render([
                'template' => 'listUsers',
                'data' => [
                    'members' => $users,
                    'page' => $params === null ? 0 : (int)$params["page"]
                ],
                'env' => 'backoffice'
            ]));
        }
        return new Response("", 304, ["location" =>  "/"]);
    }

    public function editOneMember(array $params, ?object $request): Response
    {
        if ($request !== null) {
            $param = $request->all();

            foreach ($param as $key => $el) {
                if ($el === "") {
                    $this->session->addFlashes('danger', 'vous devez sélectionner un role');
                    return new Response("", 304, ["location" =>  "/admin/members"]);
                }
                $params[$key] = $el;
            }

            $auth = new Authentification();
            $validity = new Validity();
            $params = $validity->validityVariables($params);

            $user = $auth->isAuth($this->session, $this->userRepository);
            if ($user !== null) {
                $params["id"] = (int)$params["id"];
                $user = new User($params);
                if ($this->userRepository->update($user)) {
                    $this->session->addFlashes("success", "les droits de l'utilisateur ont bien été modifié");
                } else {
                    $this->session->addFlashes("danger", "Une erreur s'est produite");
                }
                return new Response("", 304, ["location" =>  "/admin/members"]);
            }
        }

        return new Response("", 304, ["location" =>  "/"]);
    }

    public function deleteOneMember(array $params): Response
    {
        $auth = new Authentification();
        $validity = new Validity();
        $params = $validity->validityVariables($params);

        $user = $auth->isAuth($this->session, $this->userRepository);
        if ($user !== null) {
            $user = new User(["id" => (int)$params["id"]]);
            $deletedUser = $this->userRepository->delete($user);
            if ($deletedUser) {
                $this->session->addFlashes('success', "l'utilisateur à bien été supprimé"); // envoyer un message!
            } else {
                $this->session->addFlashes('danger', "Une erreur est survenue");
            }
            return new Response("", 304, ["location" =>  "/admin/members"]);
        }
        return new Response("", 304, ["location" =>  "/"]);
    }
}
