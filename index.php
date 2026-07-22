<?php
declare(strict_types=1);
// var_dump($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI']); exit;

require_once __DIR__ . '/public/includes/db.php';
require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/core/Router.php';
require_once __DIR__ . '/app/core/helpers.php';

require_once __DIR__ . '/app/controllers/HomeController.php';
require_once __DIR__ . '/app/controllers/ProductController.php';

$base = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"]));
define("BASE_PATH", $base === "/" ? "" : $base);

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
if (BASE_PATH !== "" && str_starts_with($uri, BASE_PATH)) {
    $uri = substr($uri, strlen(BASE_PATH));
}
$uri = "/" . trim($uri, "/");

$routes = require __DIR__ . "/app/routes.php";
$router = new Router($routes, $pdo);
$router->dispatch($_SERVER["REQUEST_METHOD"], $uri);