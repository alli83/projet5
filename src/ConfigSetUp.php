<?php

namespace App;

class Config
{
    private static array $settingsConfig;
    private static ?object $instance;

    public function __construct()
    {
        Config::$settingsConfig = require('config/config.php');
    }

    public static function getSettingsDb(): array
    {
        if (empty(Config::$settingsConfig)) {
            Config::$instance = new Config();
        }
        return Config::$settingsConfig["database"];
    }

    public static function getSettingsMailer(): array
    {
        if (empty(Config::$settingsConfig)) {
            Config::$instance = new Config();
        }
        return Config::$settingsConfig["emailTransport"];
    }
}
