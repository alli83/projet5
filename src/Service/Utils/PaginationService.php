<?php

declare(strict_types=1);

namespace App\Service\Utils;

class PaginationService
{
    public function setOffset(?array $params): int
    {
        if (!isset($params) || ($params && !isset($params["page"]))) {
            return 0;
        }
        $validity = new ValidityService();
        $params = $validity->validityVariables($params);
        return (int)$params["page"] * 3;
    }
}
