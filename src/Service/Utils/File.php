<?php

declare(strict_types=1);

namespace App\Service\Utils;

define('ROOT_DIR', dirname(__DIR__));

class File
{
    private string $fileName;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function downloadFile(): bool
    {
        $fileToSearch = $this->getFileName() . ".pdf";

        if (file_exists(ROOT_DIR . "/src/Service/Utils/" . $fileToSearch)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $fileToSearch);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize(ROOT_DIR . "/src/utils/" . $fileToSearch));
            $result = readfile(ROOT_DIR . "/src/Service/Utils/" . $fileToSearch);

            if ($result !== false) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}
