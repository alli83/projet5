<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;
use PDO;

final class Database
{
    private array $settings;

    public function __construct()
    {
        $this->settings = Config::getSettingsDb();
    }

    public function connectToDb(): PDO
    {
        $dbparams = $this->getSettings();
        try {
            $db = new \PDO('mysql:host=' . $dbparams["db_host"] . ';dbname=' . $dbparams["db_name"] . ';charset=utf8', $dbparams["db_user"], $dbparams["db_pass"]);
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $db;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
