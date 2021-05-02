<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

define('ROOT_DIR', dirname(__DIR__));

use App\Service\Container;
use App\Service\Router;
use App\Service\Http\Request;

const APP_ENV = 'dev';

if (APP_ENV === 'dev') {
      $whoops = new \Whoops\Run();
      $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
      $whoops->register();
}

$container = new Container();
$request = new Request($_GET, $_POST, $_FILES, $_SERVER);
$router = new Router($request, $container);

$response = $router->run();
$response->send();
