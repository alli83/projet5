<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Service\Container;
use App\Service\Router;
use App\Service\Http\Request;
use App\Service\Utils\ServiceProvider;

const APP_ENV = 'dev';

if (APP_ENV === 'dev') {
       $whoops = new \Whoops\Run();
       $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
       $whoops->register();
}

$serviceProvider = new ServiceProvider();
$container = new Container($serviceProvider);

$request = new Request($_GET, $_POST, $_FILES, $_SERVER);
$router = new Router($request, $container);

$response = $router->run();
$response->send();
