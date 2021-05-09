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

class AdminMemberController implements ControllerInterface
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
        if ($auth->isAdminAuth($this->session)) {
            // set pagination
            $offset = $this->serviceProvider->getPaginationService()->setOffset($params);

            $order = ($request !== null && $request->get("order") !== null)  ? htmlspecialchars($request->get("order")) : "desc";
            $order = $this->serviceProvider->getValidityService()->isInArray(["asc", "desc"], $order);
            // set order and check if it's the last last page (useful for pagination on frontend)
            if ($order) {
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
        }
        $auth->isNotAuth($this->session);
        $error = new Errors(403);
        return $error->handleErrors();
    }

    public function editOneMember(array $params, ?ParametersBag $request): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();
        // check if admin
        if ($auth->isAdminAuth($this->session)) {
            if ($request !== null) {
                $param = $request->all();
                // check token validity
                $validToken = $this->serviceProvider->getTokenService()->validateToken($param, $this->session);
                $this->session->addFlashes('danger', "Une erreur est survenue");
                if ($validToken) {
                    foreach ($param as $key => $el) {
                        if ($el === "") {
                            $this->session->addFlashes('danger', 'vous devez sélectionner un role');
                            return new Response("", 304, ["location" =>  "/admin/members"]);
                        }
                        $params[$key] = $el;
                    }

                    $validity = $this->serviceProvider->getValidityService();
                    $params = $validity->validityVariables($params);

                    $id = (int)$params["id"];

                    $user = $this->userRepository->findOneBy(["id" => $id]);
                    $this->session->addFlashes("warning", "Aucun utilisateur trouvé");
                    if ($user) {
                        $user->setRole($params["role"]);

                        $this->session->addFlashes("danger", "Une erreur s'est produite");
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
                                    "les droits de l'utilisateur ont bien été modifiés et la confirmation envoyée"
                                );
                        }
                    }
                }
            }
            return new Response("", 304, ["location" =>  "/admin/members"]);
        }
        $auth->isNotAuth($this->session);
        $error = new Errors(403);
        return $error->handleErrors();
    }

    public function deleteOneMember(array $params): Response
    {
        $auth = $this->serviceProvider->getAuthentificationService();
        // check if admin
        if ($auth->isAdminAuth($this->session)) {
            $validity = $this->serviceProvider->getValidityService();
            $params = $validity->validityVariables($params);

            $id = (int)$params["id"];

            $user = $this->userRepository->findOneBy(["id" => $id]);
            $this->session->addFlashes("warning", "Aucun utilisateur trouvé");
            if ($user) {
                $this->session->addFlashes('danger', "Une erreur est survenue");
                if ($this->userRepository->delete($user)) {
                    $datas = ["pseudo" => $user->getPseudo()];
                    $this->serviceProvider->getInformUserService()
                        ->contactUserMember(
                            $this->session,
                            $datas,
                            $user->getEmail(),
                            "Suppression de votre compte",
                            "frontoffice/mail/deletedAccount.html.twig",
                            "Le compte de l'utilisateur a été supprimé et la confirmation envoyée"
                        );
                }
            }
            return new Response("", 304, ["location" =>  "/admin/members"]);
        }
        $auth->isNotAuth($this->session);
        $error = new Errors(403);
        return $error->handleErrors();
    }
}
