<?php

use Symfony\Component\HttpFoundation\Request;

header('Access-Control-Allow-Origin: *');//desde cualquier dominio se puedan hacer peticiones
header('Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Access-Control-Request-Method');//los tipos de cabeceras permitidos
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');//los metodos disponibles (OPTIONS para ficheros en AngularJS)
header('Allow: GET, POST, OPTIONS, PUT, DELETE');
$method = $_SERVER['REQUEST_METHOD'];
if($method == 'OPTIONS'){ //en angular da problemas este metodo y entonces tiene que enviarse por POST
    die();
}

/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
