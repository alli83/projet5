<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Service\Http\Request;
use App\Service\Http\Response;
use App\View\View;
use App\Service\Http\Session\Session;
use App\Service\Utils\File;
use App\Service\Utils\Mailer;

final class HomeController
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

    public function contactDev(Request $request): Response
    {
        $request = $request->request()->all();
        $message = new Mailer();
        $message->sendMessageContact("frontoffice/mail/contactAdmin.html.twig", $request);
        $this->session->addFlashes('success', 'Votre message a été envoyé');
        return new Response($this->view->render(['template' => 'accueil', 'data' => []]));
    }

    public function getCv(string $fileName): Response
    {
        $fileToDownload = new File($fileName);
        $result = $fileToDownload->downloadFile();

        if ($result !== false) {
            // TO DO Response
            return new Response("fichier téléchargé", 200);
        }
        return new Response("une erreur est survenue", 404);
    }
}
