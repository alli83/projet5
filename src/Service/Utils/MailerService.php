<?php

declare(strict_types=1);

namespace App\Service\Utils;

use Twig\Environment;
use App\ConfigSetUp;
use Swift_TransportException;
use Twig\Loader\FilesystemLoader;

class MailerService
{
    private Environment $twig;
    private array $settings;

    public function __construct()
    {
        $this->settings = (new ConfigSetUp())->getSettingsMailer();
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader);
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function sendMessage(string $subject, string $template, string $dest, array $datasToInsert = []): bool
    {
        try {
            $settings = $this->getSettings();
            $transport = new \Swift_SmtpTransport($settings["smtp"], (int)$settings["smtp_port"]);

            $mailer = new \Swift_Mailer($transport);

            $message = new \Swift_Message();
            $message->setTo($dest);
            $message->setSubject($subject);
            $message->setFrom([$settings["from"] => $settings["sender"]]);
            $message->setBody(
                $this->twig->render(
                    $template,
                    $datasToInsert
                ),
                'text/html'
            );
            $result = $mailer->send($message);
            if ($result === 1) {
                return true;
            }
            return false;
        } catch (Swift_TransportException $e) {
            return false;
        }
    }


    public function sendMessageContact(string $subject, string $template, array $request): bool
    {
        try {
            $settings = $this->getSettings();
            $transport = new \Swift_SmtpTransport($settings["smtp"], (int)$settings["smtp_port"]);
            $mailer = new \Swift_Mailer($transport);

            $message = new \Swift_Message();
            $message->setTo($request["emailContact"]);
            $message->setSubject($subject);
            $message->setFrom([$settings["from"] => $settings["sender"]]);
            $message->setBody(
                $this->twig->render(
                    $template,
                    [
                        'reason' => $request["reason"],
                        'name' => $request["nameContact"],
                        'lastName' => $request["lastNameContact"],
                        'contactEmail' => $request["emailContact"],
                        'message' => $request["messageContact"]
                    ]
                ),
                'text/html'
            );
            $result = $mailer->send($message);

            if ($result === 1) {
                return true;
            }
            return false;
        } catch (Swift_TransportException $e) {
            return false;
        }
    }
}
