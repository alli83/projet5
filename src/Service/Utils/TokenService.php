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
        if (empty($datas["tokencsrf"]) || empty($session->get("tokencsrf")) || empty($session->get("tokenTime"))) {
            return false;
        }
        if ($datas["tokencsrf"] !== $session->get("tokencsrf")) {
            return false;
        }
        $oldTokenTime = time() - (15 * 60);
        if ($session->get("tokenTime") >= $oldTokenTime) {
            return true;
        }
        return false;
    }
}
