<?php

declare(strict_types=1);

namespace App\Service\Utils;

use Twig\Environment;
use App\Config;
use Twig\Loader\FilesystemLoader;
use App\Model\Entity\User;

class Mailer
{

    private Environment $twig;
    private array $settings;

    public function __construct()
    {
        $this->settings = Config::getSettingsMailer();
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader);
    }

    public function getSettings(): array
    {
        return $this->settings;
    }


    public function sendMessage(string $template, User $data, string $dest): bool
    {
        $settings = $this->getSettings();

        $transport = new \Swift_SmtpTransport($settings["smtp"], intval($settings["smtp_port"]));
        $mailer = new \Swift_Mailer($transport);

        $message = new \Swift_Message();
        $message->setTo($dest);
        $message->setSubject('Inscription validée');
        $message->setFrom([$settings["from"] => $settings["sender"]]);
        $message->setBody(
            $this->twig->render(
                $template,
                ['pseudo' => $data->getPseudo(), 'email' =>  $data->getEmail()]
            ),
            'text/html'
        );
        $result = $mailer->send($message);
        if ($result === 1) {
            return true;
        } else {
            return false;
        }
    }
}
