<?php

declare(strict_types=1);

namespace  App\Service;

use App\Service\ErrorsHandlers\Errors;
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

            if ($tagroute->hasAttribute('varnames')) {
                $varsnames = explode(',', $tagroute->getAttribute('varnames'));
            } else {
                $varsnames = null;
            }
            $this->addRoutes($this->container->getRoutes($url, $module, $action, $accessory, $varsnames));
        }
        try {
            $goodRoad = $this->getRoute($_SERVER['REQUEST_URI']);

            if ($goodRoad == null) {
                $error = new Errors(404);
                return $error->handleErrors();
            } else {
                $module = $goodRoad->getModule();
                $method = $goodRoad->getAction();
                $accessory = $goodRoad->getAccessory();

                $varsvalues = $goodRoad->getVarsValues();
                $varsnames = $goodRoad->getVarsNames();

                $goodCont = $this->container->callGoodController($module);

                if ($goodRoad->hasVarsName()) {
                    $goodRoad->setParams($varsnames, $varsvalues);
                    $get = $goodRoad->getParams();

                    if ($accessory !== "") {
                        $accessory = $this->container->setRepositoryClass(($accessory));

                        return $this->request->getMethod() === "POST" ?
                            $goodCont->$method($get, $accessory, $this->request->request()) :
                            $goodCont->$method($get, $accessory, null);
                    } else {
                        return $this->request->getMethod() === "POST" ?
                            $goodCont->$method($get, $this->request->request()) :
                            $goodCont->$method($get, null);
                    }
                }
                if ($accessory !== "") {
                    $accessory = $this->container->setRepositoryClass(($accessory));
                    return $this->request->getMethod() === "POST" ?
                        $goodCont->$method($accessory, $this->request->request()) : $goodCont->$method($accessory, null);
                } else {
                    return $this->request->getMethod() === "POST" ?
                        $goodCont->$method($this->request->request()) : $goodCont->$method(null);
                }
            }
        } catch (Exception $e) {
            $error = new Errors(500);
            return $error->handleErrors();
        }
    }

    public function run(): Response
    {
        return $this->getRoads();
    }
}
