<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Service\Http\ParametersBag;
use App\Service\Http\Response;
use App\View\View;
use App\Service\Http\Session\Session;
use App\Service\Utils\FileService;
use App\Service\Utils\InformUserService;
use App\Service\Utils\TokenService;
use App\Service\Utils\ValidityService;

final class HomeController implements ControllerInterface
{
    private View $view;
    private Session $session;
    private ValidityService $validityService;
    private TokenService $tokenService;
    private InformUserService $informUserService;
    private FileService $fileService;

    public function __construct(
        View $view,
        Session $session,
        ValidityService $validityService,
        TokenService $tokenService,
        InformUserService $informUserService,
        FileService $fileService
    ) {
        $this->view = $view;
        $this->session = $session;
        $this->validityService = $validityService;
        $this->tokenService = $tokenService;
        $this->informUserService = $informUserService;
        $this->fileService = $fileService;
    }

    public function getHomePage(): Response
    {
        // set security token
        $tokencsrf = $this->tokenService->setToken($this->session);

        return new Response($this->view->render([
            'template' => 'accueil',
            'data' => [
                "tokencsrf" => $tokencsrf
            ]
        ]));
    }

    public function contactDev(?ParametersBag $request): Response
    {
        $this->session->addFlashes("danger", "Une erreur est survenue");

        if ($request === null) {
            return new Response("", 302, ["location" =>  "/"]);
        }
        $request = $request->all();
        if (
            empty($request["nameContact"]) || empty($request["lastNameContact"])
            || empty($request["emailContact"]) || empty($request["messageContact"])
        ) {
            $this->session->addFlashes("warning", "Veuillez renseigner les champs");
            return new Response("", 302, ["location" =>  "/"]);
        }

        // check validity security token
        $validToken = $this->tokenService->validateToken($request, $this->session);
        if (!$validToken) {
            return new Response("", 302, ["location" =>  "/"]);
        }

        $validityTools = $this->validityService;

        $this->session->addFlashes("warning", "Merci d'entrer un email valide");
        if ($validityTools->validateEmail($request["emailContact"]) !== null) {
            $request = $validityTools->validityVariables($request);

            $this->session->addFlashes("danger", "Une erreur est survenue");
            $this->informUserService
                ->contactUserAdmin(
                    $this->session,
                    $request,
                    "un nouveau message",
                    "frontoffice/mail/contactAdmin.html.twig",
                    "Votre message a bien été envoyé!"
                );
        }
        return new Response("", 302, ["location" =>  "/"]);
    }

    public function getCv(array $fileName): Response
    {
        $fileToDownload = $this->fileService;
        $fileName = $this->validityService->validityVariables($fileName);

        $result = $fileToDownload->downloadFile($fileName["file"]);

        if ($result === false) {
            return new Response("", 302, ["location" =>  "/error/404"]);
        }
        return new Response($this->view->render(['template' => 'accueil', 'data' => []]));
    }

    public function getMentions(): Response
    {
        return new Response($this->view->render(['template' => 'mentions', 'data' => []]));
    }
}
