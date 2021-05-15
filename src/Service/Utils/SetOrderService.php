<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Service\Http\ParametersBag;

class SetOrderService
{
    public function setOrder(?ParametersBag $request, ServiceProvider $serviceProvider): ?array
    {
        $orderToSet = "desc";
        if (!empty($request) && $request->get("order") !== null) {
            $request = $request->all();
            $request = $serviceProvider->getValidityService()->validityVariables($request);
            $orderToSet = $request["order"];
        }
        $order = $serviceProvider->getValidityService()->isInArray(["asc", "desc"], $orderToSet);
        return $order;
    }
}
