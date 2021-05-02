<?php

declare(strict_types=1);

namespace App\Service\Utils;

use Twig\Environment;
use App\ConfigSetUp;
use Twig\Loader\FilesystemLoader;

class Mailer
{
    private Environment $twig;
    private array $settings;
    private string $subject;

    public function __construct(string $subject = "un nouveau message")
    {
        $this->settings = (new ConfigSetUp())->getSettingsMailer();
        $loader = new FilesystemLoader('../templates');
        $this->twig = new Environment($loader);
        $this->subject = $subject;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function sendMessage(string $template, string $dest, ?array $datasToInsert = []): bool
    {
        $settings = $this->getSettings();

        $transport = new \Swift_SmtpTransport($settings["smtp"], (int)$settings["smtp_port"]);
        $mailer = new \Swift_Mailer($transport);

        $message = new \Swift_Message();
        $message->setTo($dest);
        $message->setSubject($this->subject);
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
    }

    public function sendMessageContact(string $template, array $request): bool
    {
        $settings = $this->getSettings();
        $transport = new \Swift_SmtpTransport($settings["smtp"], (int)$settings["smtp_port"]);
        $mailer = new \Swift_Mailer($transport);

        $message = new \Swift_Message();
        $message->setTo($request["email"]);
        $message->setSubject($this->subject);
        $message->setFrom([$settings["from"] => $settings["sender"]]);
        $message->setBody(
            $this->twig->render(
                $template,
                [
                    'reason' => $request["reason"],
                    'name' => $request["name"],
                    'lastName' => $request["lastName"],
                    'contactEmail' => $request["email"],
                    'message' => $request["message"]
                ]
            ),
            'text/html'
        );
        $result = $mailer->send($message);

        if ($result === 1) {
            return true;
        }
        return false;
    }
}
