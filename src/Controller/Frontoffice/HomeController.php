<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Service\ErrorsHandlers\Errors;
use App\Service\Http\ParametersBag;
use App\Service\Http\Response;
use App\View\View;
use App\Service\Http\Session\Session;
use App\Service\Utils\ServiceProvider;

final class HomeController implements ControllerInterface
{
    private View $view;
    private Session $session;
    private ServiceProvider $serviceProvider;

    public function __construct(View $view, Session $session, ServiceProvider $serviceProvider)
    {
        $this->view = $view;
        $this->session = $session;
        $this->serviceProvider = $serviceProvider;
    }

    public function getHomePage(): Response
    {
        // set security token
        $tokencsrf = $this->serviceProvider->getTokenService()->setToken($this->session);

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
        $validToken = $this->serviceProvider->getTokenService()->validateToken($request, $this->session);
        if (!$validToken) {
            return new Response("", 302, ["location" =>  "/"]);
        }

        $validityTools = $this->serviceProvider->getValidityService();

        $this->session->addFlashes("warning", "Merci d'entrer un email valide");
        if ($validityTools->validateEmail($request["emailContact"]) !== null) {
            $request = $validityTools->validityVariables($request);

            $this->session->addFlashes("danger", "Une erreur est survenue");
            $this->serviceProvider->getInformUserService()
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
        $fileToDownload = $this->serviceProvider->getFileService();
        $fileName = $this->serviceProvider->getValidityService()->validityVariables($fileName);

        $result = $fileToDownload->downloadFile($fileName["file"]);

        if ($result === false) {
            $error = new Errors(404);
            return $error->handleErrors();
        }
        return new Response($this->view->render(['template' => 'accueil', 'data' => []]));
    }
}
