<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Model\Entity\User;
use App\View\View;
use App\Model\Repository\UserRepository;
use App\Service\Http\ParametersBag;
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

        if ($auth->isAdminAuth($this->session)) {
            if ($params === null || ($params && $params["page"] === null)) {
                $offset = 0;
            } else {
                $validity = new Validity();
                $params = $validity->validityVariables($params);

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
        return new Response("forbidden", 403, ["location" =>  "/login"]);
    }

    public function editOneMember(array $params, ?ParametersBag $request): Response
    {
        $auth = new Authentification();

        if ($auth->isAdminAuth($this->session)) {
            if ($request !== null) {
                $param = $request->all();
                foreach ($param as $key => $el) {
                    if ($el === "") {
                        $this->session->addFlashes('danger', 'vous devez sélectionner un role');
                        return new Response("", 304, ["location" =>  "/admin/members"]);
                    }
                    $params[$key] = $el;
                }

                $validity = new Validity();
                $params = $validity->validityVariables($params);

                $params["id"] = (int)$params["id"];
                $user = new User($params);

                $this->session->addFlashes("danger", "Une erreur s'est produite");
                if ($this->userRepository->update($user)) {
                    $this->session->addFlashes("success", "les droits de l'utilisateur ont bien été modifiés");
                }
                return new Response("", 304, ["location" =>  "/admin/members"]);
            }
        }
        return new Response("", 304, ["location" =>  "/login"]);
    }

    public function deleteOneMember(array $params): Response
    {
        $auth = new Authentification();

        if ($auth->isAdminAuth($this->session)) {
            $validity = new Validity();
            $params = $validity->validityVariables($params);

            $user = new User(["id" => (int)$params["id"]]);

            $this->session->addFlashes('danger', "Une erreur est survenue");
            if ($this->userRepository->delete($user)) {
                $this->session->addFlashes('success', "l'utilisateur à bien été supprimé");
            }
            return new Response("", 304, ["location" =>  "/admin/members"]);
        }
        return new Response("", 304, ["location" =>  "/login"]);
    }
}
