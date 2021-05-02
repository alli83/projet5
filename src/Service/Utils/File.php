<?php

declare(strict_types=1);

namespace App\Service\Utils;

class File
{
    private string $fileName;
    private string $targetDir = ROOT_DIR . "/src/doc/";
    private string $targetFile;
    private array $format = ["jpg", "jpeg", "png"];

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
        $this->targetFile = $this->targetDir . basename($fileName);
    }

    public function getPathInfo(): string
    {
        return strtolower(pathinfo($this->targetFile, PATHINFO_EXTENSION));
    }


    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function registerFile($file): ?string
    {
        if (!file_exists($this->targetFile) && in_array($this->getPathInfo(), $this->format)) {
            $move = move_uploaded_file($file, $this->targetFile);
            $result = $move === true ? $this->targetFile : null;
            return $result;
        }
        return null;
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
            }
        }
        return false;
    }
}
