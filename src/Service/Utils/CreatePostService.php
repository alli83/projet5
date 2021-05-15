<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Service\Http\ParametersBag;
use App\Service\Http\Session\Session;

class CreatePostService
{
    public function paramsPost(
        array $params,
        ?ParametersBag $file,
        ?ParametersBag $request,
        ServiceProvider $serviceProvider,
        Session $session
    ): ?array {

        if ($request === null) {
            return null;
        }

        $fileAttached = isset($file) ? $file->get("file_attached") : null;

        if (isset($fileAttached) && !empty($fileAttached["tmp_name"] || !empty($fileAttached["tmp_name"]))) {
            $validityFile = $serviceProvider->getValidateFileService();
            $val = $validityFile->checkFileValidity($fileAttached, $session);
            if ($val === null) {
                return null;
            }
            $params["file_attached"] = $val;
        }

        $param = $request->all();

        if (empty($param["stand_first"]) || empty($param["title"]) || empty($param["text"])) {
            $session->addFlashes("warning", "Merci de compléter les champs");
            return null;
        }

        foreach ($param as $key => $el) {
            $params[$key] = $el;
        }
        $validToken = $serviceProvider->getTokenService()->validateToken($param, $session);
        if (!$validToken) {
            return null;
        }

        $validity = $serviceProvider->getValidityService();
        $params = $validity->validityVariables($params);

        return $params;
    }
}
