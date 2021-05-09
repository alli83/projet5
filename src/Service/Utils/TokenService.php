<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Service\Http\Session\Session;

class TokenService
{
    public function setToken(Session $session): string
    {
        $tokencsrf = md5((string)time());
        $session->set("tokencsrf", $tokencsrf);
        $session->set("tokenTime", time());

        return $tokencsrf;
    }

    public function validateToken(array $datas, Session $session): bool
    {
        if (isset($datas["tokencsrf"]) && $session->get("tokencsrf") !== null && $session->get("tokenTime") !== null) {
            if ($datas["tokencsrf"] === $session->get("tokencsrf")) {
                $oldTokenTime = time() - (15 * 60);
                if ($session->get("tokenTime") >= $oldTokenTime) {
                    return true;
                }
            }
        }
        return false;
    }
}
