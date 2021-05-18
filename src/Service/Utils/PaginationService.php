<?php

declare(strict_types=1);

namespace App\Service\Utils;

class PaginationService
{
    public function setOffset(?array $params, ValidityService $validityService): int
    {
        if (!isset($params) || ($params && !isset($params["page"]))) {
            return 0;
        }
        $params = $validityService->validityVariables($params);
        return (int)$params["page"] * 3;
    }
}
