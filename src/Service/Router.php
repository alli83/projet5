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

            $varsnames = null;

            if ($tagroute->hasAttribute('varnames')) {
                $varsnames = explode(',', $tagroute->getAttribute('varnames'));
            }

            $this->addRoutes($this->container->getRoutes($url, $module, $action, $accessory, $varsnames));
        }
        try {
            $goodRoad = $this->getRoute($this->request->server()->get("REQUEST_URI"));

            if ($goodRoad === null) {
                $error = new Errors(404);
                return $error->handleErrors();
            }

            $module = $goodRoad->getModule();
            $method = $goodRoad->getAction();
            $accessory = $goodRoad->getAccessory();

            $varsvalues = $goodRoad->getVarsValues();
            $varsnames = $goodRoad->getVarsNames();

            $goodCont = $this->container->callGoodController($module);

            switch ($goodRoad->hasVarsName()) {
                case true:
                    $goodRoad->setParams($varsnames, $varsvalues);
                    $get = $goodRoad->getParams();
                    switch ($accessory) {
                        case "":
                            switch ($this->request->getMethod()) {
                                case "POST":
                                    switch ($this->request->files()->has("file_attached")) {
                                        case true:
                                            return $goodCont->$method($get, $this->request->request(), $this->request->files());

                                        default:
                                            return $goodCont->$method($get, $this->request->request());
                                    }
                                default:
                                    return $goodCont->$method($get, null);
                            }
                        default:
                            $accessory = $this->container->setRepositoryClass(($accessory));
                            switch ($this->request->getMethod()) {
                                case "POST":
                                    switch ($this->request->files()->has("file_attached")) {
                                        case true:
                                            return $goodCont->$method($get, $accessory, $this->request->request(), $this->request->files());

                                        default:
                                            return $goodCont->$method($get, $accessory, $this->request->request());
                                    }
                                default:
                                    switch ($this->request->files()->has("file_attached")) {
                                        case true:
                                            return $goodCont->$method($get, $accessory, null, $this->request->files());

                                        default:
                                            return $goodCont->$method($get, $accessory, null);
                                    }
                            }
                    }
                default:
                    switch ($accessory) {
                        case "":
                            switch ($this->request->getMethod()) {
                                case "POST":
                                    switch ($this->request->files()->has("file_attached")) {
                                        case true:
                                            return $goodCont->$method($this->request->request(), $this->request->files());
                                        default:
                                            return $goodCont->$method($this->request->request());
                                    }
                                default:
                                    switch ($this->request->files()->has("file_attached")) {
                                        case true:
                                            return $goodCont->$method(null, $this->request->files());
                                        default:
                                            return $goodCont->$method(null);
                                    }
                            }
                        default:
                            $accessory = $this->container->setRepositoryClass(($accessory));
                            switch ($this->request->getMethod()) {
                                case "POST":
                                    switch ($this->request->files()->has("file_attached")) {
                                        case true:
                                            return $goodCont->$method($accessory, $this->request->request(), $this->request->files());
                                        default:
                                            return $goodCont->$method($accessory, $this->request->request());
                                    }
                                default:
                                    switch ($this->request->files()->has("file_attached")) {
                                        case true:
                                            return $goodCont->$method($accessory, null, $this->request->files());
                                        default:
                                            return $goodCont->$method($accessory, null);
                                    }
                            }
                    }
            }
        } catch (Exception $e) {
            $error = new Errors((int)($e->getCode()));
            return $error->handleErrors();
        }
    }

    public function run(): Response
    {
        return $this->getRoads();
    }
}
