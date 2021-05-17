<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\View\View;
use App\Model\Repository\UserRepository;
use App\Service\ErrorsHandlers\Errors;
use App\Service\Http\ParametersBag;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Service\Utils\ServiceProvider;

final class AdminMemberController implements ControllerInterface
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

    public function displayAllMembers(?array $params = [], ?ParametersBag $request = null): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();

        //  check if admin
        if (!$auth->isAdminAuth($this->session)) {
            $error = new Errors(403);
            return $error->handleErrors();
        }
        // set pagination
        $offset = $this->serviceProvider->getPaginationService()->setOffset($params);

        // set order
        $order = $this->serviceProvider->getSetOrderService()->setOrder($request, $this->serviceProvider);

        if (!$order) {
            $error = new Errors(404);
            return $error->handleErrors();
        }

        // set order and check if it's the last last page (useful for pagination on frontend)
        $users = $this->userRepository->findAll(4, $offset, ['order' => $order["order"]]);
        $end = false;
        if ($users) {
            if (!array_key_exists(3, $users)) {
                $end = true;
            }
            $users = array_slice($users, 0, 3);
        }

        // set security token
        $tokencsrf = $this->serviceProvider->getTokenService()->setToken($this->session);

        return new Response($this->view->render([
            'template' => 'listUsers',
            'data' => [
                'members' => $users,
                'page' => $params === null ? 0 : (int)$params["page"],
                'filter' => $order,
                "end" => $end,
                "tokencsrf" => $tokencsrf
            ],
            'env' => 'backoffice'
        ]));
    }

    public function editOneMember(array $params, ?ParametersBag $request): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();

        // check if admin
        if (!$auth->isSuperAdminAuth($this->session)) {
            $error = new Errors(403);
            return $error->handleErrors();
        }

        $this->session->addFlashes("danger", "Une erreur est survenue");

        if ($request === null) {
            return new Response("", 302, ["location" =>  "/admin/members"]);
        }

        $param = $request->all();

        // check token validity
        $validToken = $this->serviceProvider->getTokenService()->validateToken($param, $this->session);
        if (!$validToken) {
            return new Response("", 302, ["location" =>  "/admin/members"]);
        }

        foreach ($param as $key => $el) {
            if ($el === "") {
                $this->session->addFlashes("danger", "Vous devez sélectionner un role");
                return new Response("", 302, ["location" =>  "/admin/members"]);
            }
            $params[$key] = $el;
        }

        $validity = $this->serviceProvider->getValidityService();
        $params = $validity->validityVariables($params);

        $id = (int)$params["id"];

        $user = $this->userRepository->findOneBy(["id" => $id]);

        if (!$user) {
            $this->session->addFlashes("warning", "Aucun utilisateur trouvé");
            return new Response("", 302, ["location" =>  "/admin/members"]);
        }

        $user->setRole($params["role"]);

        if ($this->userRepository->update($user)) {
            $role = $user->getRole() === "admin" ? "administrateur" : "utilisateur";
            $datas = ["pseudo" => $user->getPseudo(), "role" => $role];

            $this->serviceProvider->getInformUserService()
                ->contactUserMember(
                    $this->session,
                    $datas,
                    $user->getEmail(),
                    "Modification de vos droits",
                    "frontoffice/mail/updateMember.html.twig",
                    "Les droits de l'utilisateur ont bien été modifiés et la confirmation envoyée par mail"
                );
        }
        return new Response("", 302, ["location" =>  "/admin/members"]);
    }

    public function deleteOneMember(array $params, ?ParametersBag $request): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();

        // check if admin
        if (!$auth->isSuperAdminAuth($this->session)) {
            $error = new Errors(403);
            return $error->handleErrors();
        }

        $this->session->addFlashes("danger", "Une erreur est survenue");

        if ($request === null) {
            return new Response("", 302, ["location" =>  "/admin/members"]);
        }
        $param = $request->all();

        // check validity security token
        $validToken = $this->serviceProvider->getTokenService()->validateToken($param, $this->session);
        if (!$validToken) {
            return new Response("", 302, ["location" =>  "/admin/members"]);
        }

        $validity = $this->serviceProvider->getValidityService();
        $params = $validity->validityVariables($params);

        $id = (int)$params["id"];
        $user = $this->userRepository->findOneBy(["id" => $id]);

        if (!$user) {
            $this->session->addFlashes("warning", "Aucun utilisateur trouvé");
            return new Response("", 302, ["location" =>  "/admin/members"]);
        }
        // delete superAdmin: forbidden
        if ($user->getRole() === "superAdmin") {
            $this->session->addFlashes("danger", "Cette opération est interdite");
            return new Response("", 302, ["location" =>  "/admin/members"]);
        }

        if ($this->userRepository->delete($user)) {
            $datas = ["pseudo" => $user->getPseudo()];
            $this->serviceProvider->getInformUserService()
                ->contactUserMember(
                    $this->session,
                    $datas,
                    $user->getEmail(),
                    "Suppression de votre compte",
                    "frontoffice/mail/deletedAccount.html.twig",
                    "Le compte de l'utilisateur a été supprimé et la confirmation envoyée par mail"
                );
        }
        return new Response("", 302, ["location" =>  "/admin/members"]);
    }
}
