<?php

namespace App;

class ConfigSetUp
{
    private static array $settingsConfig;
    private static ?object $instance;

    public function __construct()
    {
        ConfigSetUp::$settingsConfig = require('config/config.php');
    }

    public static function getSettingsDb(): array
    {
        if (empty(ConfigSetUp::$settingsConfig)) {
            ConfigSetUp::$instance = new ConfigSetUp();
        }
        return ConfigSetUp::$settingsConfig["database"];
    }

    public static function getSettingsMailer(): array
    {
        if (empty(ConfigSetUp::$settingsConfig)) {
            ConfigSetUp::$instance = new ConfigSetUp();
        }
        return ConfigSetUp::$settingsConfig["emailTransport"];
    }
}
