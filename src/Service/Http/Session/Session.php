<?php

declare(strict_types=1);

namespace App\Service\Http\Session;

use App\ConfigSetUp;

final class Session
{
    private SessionParametersBag $sessionParamBag;
    private ?array $instance  = [];

    function __construct()
    {
        $this->instance = (new ConfigSetUp())->getSettingsCookies();
        session_set_cookie_params($this->instance);
        session_start();
        $this->sessionParamBag = new SessionParametersBag($_SESSION);
    }

    // PHP 8 version -> public function set(string $name, mixed $value): void
    /**
     * @param mixed $value
     */
    public function set(string $name, $value): void
    {
        $this->sessionParamBag->set($name, $value);
    }

    // PHP 8 version -> public function get(string $name): ?mixed
    /**
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->sessionParamBag->get($name);
    }

    /**
     * @return string[]
     */
    public function toArray(): ?array
    {
        return $this->sessionParamBag->all();
    }

    public function remove(string $name): void
    {
        $this->sessionParamBag->unset($name);
    }

    public function addFlashes(string $type, string $message): void
    {
        $this->set('flashes', [$type => $message]);
    }

    /**
     * @return string[]
     */
    public function getFlashes(): ?array
    {
        $flashes = $this->get('flashes');
        $this->remove('flashes');

        return $flashes;
    }
}
