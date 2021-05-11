<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Model\Repository\UserRepository;
use App\Service\Http\Session\Session;

class InformUserService
{
    public function contactUserComment(Session $session, UserRepository $repo, array $params, string $header, string $template, string $data, string $conf): void
    {
        $session->addFlashes('warning', "La confirmation n'a pas pu être envoyée par mail");
        $user = $repo->findOneThroughComment(["id" => (int)$params["id"]]);
        if ($user) {
            $message = new MailerService();
            if (
                $message->sendMessage(
                    $header,
                    $template,
                    $user->getEmail(),
                    ["comment" => $data, "pseudo" => $user->getPseudo()]
                )
            ) {
                $session->addFlashes('success', $conf);
            }
        }
    }

    public function contactUserMember(Session $session, array $datas, string $email, string $header, string $template, string $conf): void
    {
        $message = new MailerService();
        $session->addFlashes('warning', "La confirmation n'a pas pu être envoyée par mail");
        if (
            $message->sendMessage(
                $header,
                $template,
                $email,
                $datas
            )
        ) {
            $session->addFlashes('success', $conf);
        }
    }

    public function contactUserAdmin(Session $session, array $request, string $header, string $template, string $conf): void
    {
        $message = new MailerService();

        $session->addFlashes('warning', "Nous sommes désolé mais votre message n'a pas pu être envoyé");
        if ($message->sendMessageContact($header, $template, $request)) {
            $session->addFlashes('success', $conf);
        }
    }
}
