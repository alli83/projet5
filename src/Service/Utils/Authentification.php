<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Service\Http\Session\Session;

class Authentification
{

    public function isAuth(Session $session): bool
    {
        return in_array($session->get("role"), ["user", "admin", "superAdmin"]);
    }

    public function isAdminAuth(Session $session): bool
    {
        return ($session->get("role") === "admin" || $session->get("role") === "superAdmin");
    }
}
