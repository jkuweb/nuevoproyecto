<?php
require_once('settings.php');
require_once('core/europio_code.php');
require_once('core/db.php');
require_once('core/template.php');
require_once('core/collector.php');
require_once('core/helper.php');

$uri = $_SERVER['REQUEST_URI'];
@list($null, $modulo, $recurso, $arg) = explode('/', $uri);

if(!$modulo) $modulo = "dashboard";
if(!(file_exists("{$modulo}.php"))) $modulo = "dashboard";

$controlador = ucwords($modulo) . "Controller";// ProductoController, DomicilioController...
require_once "{$modulo}.php"; // producto.php, domicilio.php...
$c = new $controlador();// new ProductoController(), new DomicilioController()...

if(!$recurso) $recurso = "home";
if(!method_exists($c, $recurso)) $recurso = "home";
$c->$recurso($arg);// ProductoController->agregar()..

?>
