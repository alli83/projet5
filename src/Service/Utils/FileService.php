<?php

declare(strict_types=1);

namespace App\Service\Utils;

class FileService
{
    private string $targetDir = "/src/doc/";
    private array $format = ["jpg", "jpeg", "png"];


    public function getPathInfo(string $targetFile): string
    {
        return strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    }

    public function registerFile(string $file, string $nameFile): ?string
    {
        $targetFile = dirname(dirname(dirname(__DIR__))) . $this->targetDir . basename($nameFile);
        if (!file_exists($targetFile) && in_array($this->getPathInfo($targetFile), $this->format)) {
            $move = move_uploaded_file($file, $targetFile);
            $result = $move === true ? $targetFile : null;
            return $result;
        }
        return null;
    }

    public function downloadFile(string $fileToSearch): bool
    {
        $fileToSearch = $fileToSearch . ".pdf";

        $base = dirname(dirname(dirname(__DIR__)));

        if (file_exists($base . "/src/doc/" . $fileToSearch)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $fileToSearch);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($base . "/src/doc/" . $fileToSearch));
            $result = readfile($base . "/src/doc/" . $fileToSearch);

            if ($result !== false) {
                return true;
            }
        }
        return false;
    }
}
