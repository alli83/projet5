<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Service\Http\ParametersBag;

class SetOrderService
{
    public function setOrder(?ParametersBag $request, ValidityService $validityService): ?array
    {
        $orderToSet = "desc";
        if (!empty($request) && $request->get("order") !== null) {
            $request = $request->all();
            $request = $validityService->validityVariables($request);
            $orderToSet = $request["order"];
        }
        $order = $validityService->isInArray(["asc", "desc"], $orderToSet);
        return $order;
    }
}
