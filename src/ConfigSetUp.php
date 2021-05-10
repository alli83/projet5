<?php

namespace App;

class ConfigSetUp
{
    private array $settingsConfig;
    private ?ConfigSetUp $instance;

    public function __construct()
    {
        $this->settingsConfig = require 'config/config.php';
    }

    public function getSettingsDb(): array
    {
        if (empty($this->settingsConfig)) {
            $this->instance = new ConfigSetUp();
        }
        return $this->settingsConfig["database"];
    }

    public function getSettingsMailer(): array
    {
        if (empty($this->settingsConfig)) {
            $this->instance = new ConfigSetUp();
        }
        return $this->settingsConfig["emailTransport"];
    }
}
