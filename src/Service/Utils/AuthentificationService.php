<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Service\Http\Session\Session;

class AuthentificationService
{
    public function isAuth(Session $session): bool
    {
        return in_array($session->get("role"), ["user", "admin", "superAdmin"]);
    }

    public function isAdminAuth(Session $session): bool
    {
        return ($session->get("role") === "admin" || $session->get("role") === "superAdmin");
    }

    public function isSuperAdminAuth(Session $session): bool
    {
        return $session->get("role") === "superAdmin";
    }

    public function isNotAuth(Session $session): void
    {
        $session->remove('role');
        $session->remove('pseudo');
        $session->remove('email');
    }
}
