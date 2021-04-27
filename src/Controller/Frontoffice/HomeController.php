<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Controller\ControllerInterface\ControllerInterface;
use App\Service\ErrorsHandlers\Errors;
use App\Service\Http\Response;
use App\View\View;
use App\Service\Http\Session\Session;
use App\Service\Utils\File;
use App\Service\Utils\Mailer;
use App\Service\Utils\Validity;

final class HomeController implements ControllerInterface
{
    private View $view;
    private Session $session;

    public function __construct(View $view, Session $session)
    {
        $this->view = $view;
        $this->session = $session;
    }

    public function getHomePage(): Response
    {
        return new Response($this->view->render(['template' => 'accueil']));
    }

    public function contactDev(?object $request): Response
    {
        if ($request !== null) {
            $request = $request->all();

            $validityTools = new Validity();
            $request = $validityTools->validityVariables($request);

            $message = new Mailer();
            $contact = $message->sendMessageContact("frontoffice/mail/contactAdmin.html.twig", $request);
            if ($contact === false) {
                $this->session->addFlashes('danger', 'Nous sommes désolé mais votre message n\'a pas pu être envoyé');
            } elseif ($contact === true) {
                $this->session->addFlashes('success', 'Votre message a bien été envoyé');
            }
        }
        return new Response($this->view->render(['template' => 'accueil', 'data' => []]));
    }

    public function getCv(array $fileName): Response
    {
        $fileToDownload = new File(htmlspecialchars($fileName["file"]));
        $result = $fileToDownload->downloadFile();

        if ($result === false) {
            $error = new Errors(404);
            return $error->handleErrors();
        }
        return new Response("fichier téléchargé", 200);
    }
}
