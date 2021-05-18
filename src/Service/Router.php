<?php

declare(strict_types=1);

namespace  App\Service;

use App\Service\Http\Request;
use App\Service\Http\Response;
use Exception;

final class Router
{

    private Container $container;
    private Request $request;
    private array $routes = [];

    public function __construct(Request $request, Container $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    public function addRoutes(Route $route): void
    {
        if (!in_array($route, $this->routes)) {
            $this->routes[] = $route;
        }
    }

    public function getRoute(string $url): ?Route
    {
        foreach ($this->routes as $route) {
            $comp = $route->match($url);
            if ($comp !== false) {
                return $route;
            }
        }
        return null;
    }
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRoads(): Response
    {
        $xml = new \DOMDocument();
        $xml->load('../src/config/routes.xml');
        $tagroutes = $xml->getElementsByTagName('route');

        foreach ($tagroutes as $tagroute) {
            $url = $tagroute->getAttribute('url');
            $module = $tagroute->getAttribute('module');
            $action = $tagroute->getAttribute('action');
            $accessory = $tagroute->getAttribute('accessory');

            $varsnames = null;

            if ($tagroute->hasAttribute('varnames')) {
                $varsnames = explode(',', $tagroute->getAttribute('varnames'));
            }

            $this->addRoutes($this->container->getRoutes($url, $module, $action, $accessory, $varsnames));
        }
        try {
            $goodRoad = $this->getRoute($this->request->server()->get("REQUEST_URI"));

            if ($goodRoad === null) {
                return new Response("", 302, ["location" =>  "/error/404"]);
            }

            $module = $goodRoad->getModule();
            $method = $goodRoad->getAction();
            $accessory = $goodRoad->getAccessory();

            $varsvalues = $goodRoad->getVarsValues();
            $varsnames = $goodRoad->getVarsNames();

            $goodCont = $this->container->callGoodController($module);

            $number = null;
            $get = null;
            $accessoryClass = null;

            if ($goodRoad->hasVarsName()) {
                $goodRoad->setParams($varsnames, $varsvalues);
                $get = $goodRoad->getParams();

                $number += 1;
            }
            if ($accessory) {
                $accessoryClass = $this->container->setRepositoryClass(($accessory));

                $number += 2;
            }
            if ($this->request->getMethod() === "POST") {
                $number += 4;
            }
            if ($this->request->files()->has("file_attached")) {
                $number += 8;
            }

            switch ($number) {
                case 1:
                    return $goodCont->$method($get, null);
                case 3:
                    return $goodCont->$method($get, $accessoryClass, null);
                case 4:
                    return $goodCont->$method($this->request->request());
                case 5:
                    return $goodCont->$method($get, $this->request->request());
                case 6:
                    return $goodCont->$method($accessoryClass, $this->request->request());
                case 12:
                    return $goodCont->$method($this->request->request(), $this->request->files());
                case 13:
                    return $goodCont->$method($get, $this->request->request(), $this->request->files());
                default:
                    return ($goodCont->$method(null));
            }
        } catch (Exception $e) {
            $code = (int)($e->getCode());
            return new Response("", 302, ["location" =>  "/error/${code}"]);
        }
    }

    public function run(): Response
    {
        return $this->getRoads();
    }
}
