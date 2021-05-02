<?php

declare(strict_types=1);

namespace App\View;

use Twig\Environment;
use App\Service\Http\Session\Session;
use Twig\Loader\FilesystemLoader;

final class View
{
    private Environment $twig;
    private Session $session;

    public function __construct(Session $session)
    {
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader, [
            'debug' => true]);
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        $this->session = $session;
    }


    public function render(array $data): string
    {

        $data['data']['session'] = $this->session->toArray();
        $data['data']['flashes'] = $this->session->getFlashes();
        $env = array_key_exists("env", $data) ? $data["env"] : "frontoffice";

        return $this->twig->render("${env}/${data['template']}.html.twig", $data['data']);
    }
}
