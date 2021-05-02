<?php

namespace  App\Service;

class Route
{
    protected string $url;
    protected string $module;
    protected string $action;
    protected string $accessory = "";
    protected ?array $varsnames;
    protected ?array $varsvalues;
    protected ?array $params;

    public function __construct(string $url, string $module, string $action, string $accessory, ?array $varnames)
    {
        $this->url = $url;
        $this->module = $module;
        $this->action = $action;
        $this->accessory = $accessory;
        $this->varsnames = $varnames;
    }

    public function hasVarsName(): bool
    {
        return (!empty($this->varsnames));
    }

    public function match(string $url): bool
    {
        if (preg_match('#^' . $this->url . '$#', $url, $matches)) {
            $this->setVarsValues([]);
            $vars = $this->hasVarsName();
            if ($vars) {
                $varsvalues = [];
                foreach ($matches as $key => $match) {
                    if ($key !== 0) {
                        $varsvalues[$key - 1] = htmlspecialchars($match);
                    }
                }
                $this->setVarsValues($varsvalues);
            }
            return true;
        }
        return false;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getAction(): string
    {
        return $this->action;
    }
    public function getAccessory(): ?string
    {
        return $this->accessory;
    }
    public function getVarsNames(): ?array
    {
        return $this->varsnames;
    }

    public function getVarsValues(): ?array
    {
        return $this->varsvalues;
    }

    public function getParams(): ?array
    {
        return $this->params;
    }

    public function setVarsValues(array $values): void
    {
        $this->varsvalues = $values;
    }

    public function setParams(array $varsnames, array $varsvalues): void
    {

        foreach ($varsnames as $key => $varname) {
            $this->params[$varname] = $varsvalues[$key];
        }
    }
}
