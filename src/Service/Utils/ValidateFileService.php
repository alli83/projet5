<?php

declare(strict_types=1);

namespace App\Service\Utils;

use App\Service\Http\Session\Session;

final class ValidateFileService
{
    public function checkFileValidity(array $fileAttached, Session $session, FileService $fileService): ?string
    {
        if ($fileAttached["size"] > 150000) {
            $session->addFlashes("danger", "fichier trop lourd");
            return null;
        }
            $file = $fileService;
            $targetFile = $file->registerFile($fileAttached["tmp_name"], $fileAttached["name"]);

        if ($targetFile === null) {
            $session->addFlashes("warning", "Cette image (par ce nom ) est déjà associée à un post");
            return null;
        }
            $content = file_get_contents($targetFile);
        if ($content) {
            return base64_encode($content);
        }
            return null;
    }
}
