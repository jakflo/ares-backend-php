<?php

ini_set( 'session.cookie_httponly', 1 );
session_start();

$uber_root = str_replace('www', '', $_SERVER['DOCUMENT_ROOT']);
require_once $uber_root.'\app\conf\Env.php';
$env = new App\Conf\Env($uber_root);
$webroot = $env->get_param('webroot');

use App\Conf\Router;
$router = new Router($env);
$control = $router->load($_SERVER['REQUEST_URI']);
$control->zobraz();
