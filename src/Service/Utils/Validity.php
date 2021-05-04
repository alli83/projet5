<?php

declare(strict_types=1);

namespace App\Service\Utils;

class Validity
{

    public function validityVariables(array $params): array
    {
        $newparams = [];
        foreach ($params as $key => $param) {
            if (is_int($param)) {
                $params[$key] = htmlspecialchars((string)$param);
                $params[$key] = (int)$params[$key];
            } else {
                $params[$key] = htmlspecialchars($param);
            }
            $newparams[$key] = $params[$key];
        }
        return $newparams;
    }

    public function validateEmail(string $param): ?string
    {
        if (filter_var($param, FILTER_VALIDATE_EMAIL)) {
            $email = filter_var($param, FILTER_SANITIZE_EMAIL);
            if ($email === false) {
                return null;
            }
            return $email;
        }
        return null;
    }

    public function validatePassword(string $param, string $param2): ?string
    {
        if (preg_match('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@%^&*-]).{8,}$/', $param) && ($param === $param2)) {
            $password = password_hash($param, PASSWORD_DEFAULT);
            if ($password === false) {
                return null;
            }
            return $password;
        }
        return null;
    }
}
