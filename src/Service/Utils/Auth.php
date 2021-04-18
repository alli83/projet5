<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Model\Repository\UserRepository;
use App\Service\Http\Session\Session;

class Auth
{
    private ?array $datasConnexion;
    private UserRepository $userRepo;
    private Session $session;

    public function __construct(?array $datasConnexion, UserRepository $userRepo, Session $session)
    {
        $this->datasConnexion = $datasConnexion;
        $this->userRepo = $userRepo;
        $this->session = $session;
    }

    public function getDatasConnexion(): ?array
    {
        return $this->datasConnexion;
    }

    public function isValidLoginForm(): bool
    {
        $infoUser = $this->getDatasConnexion();
        if ($infoUser == null) {
            return false;
        }

        if ($infoUser["email"] === "" || $infoUser["password"] === "") {
            return false;
        }
        $email = htmlspecialchars($infoUser['email']);
        $password = htmlspecialchars($infoUser['password']);

        $user = $this->userRepo->findOneBy(['email' => $email]);
        if ($user !== null) {
            password_verify($password, $user->getPassword());
            if (!password_verify($password, $user->getPassword())) {
                return false;
            }
            $this->session->set('user', $user); // remove password
            return true;
        } else {
            return false;
        }
    }
}
