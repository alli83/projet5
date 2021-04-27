<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Model\Entity\User;
use App\Model\Repository\UserRepository;
use App\Service\Http\Session\Session;

class Authentification
{

    public function isAuth(Session $session, UserRepository $userRepo): ?User
    {
        $user = $userRepo->findOneBy(["email" => $session->get("email")]);
        return $user;
    }
}
