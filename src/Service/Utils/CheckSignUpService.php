<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Service\Http\ParametersBag;
use App\Service\Http\Session\Session;

class CheckSignupService
{
    public function paramsSignUp(
        ?ParametersBag $request,
        Session $session,
        ServiceProvider $serviceProvider
    ): ?array {

        if ($request === null) {
            return null;
        }

        $params = $request->all();

        if (empty($params['emailSignup']) || empty($params['password']) || empty($params['pseudoSignup'])) {
            return null;
        }
        // check validity security token
        $validToken = $serviceProvider->getTokenService()->validateToken($params, $session);
        if (!$validToken) {
            return null;
        }

        $validity = $serviceProvider->getValidityService();
        $params = $validity->validityVariables($params);
        return $params;
    }
}
