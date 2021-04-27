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
                $params[$key] = htmlspecialchars(strval($param));
                $params[$key] = intval($params[$key]);
            } else {
                $params[$key] = htmlspecialchars($param);
            }
            $newparams[$key] = $params[$key];
        }
        return $newparams;
    }
}
