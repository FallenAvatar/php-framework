<?php declare(strict_types=1);

define('FRAMEWORK_TIMING_START', microtime(true));

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
define('DS', DIRECTORY_SEPARATOR);

include_once('functions.php');

record_manual_timing('start', FRAMEWORK_TIMING_START);
record_timing('functions.php loaded');

$libPath = realpath(dirname(__FILE__)) . DS;
require_once($libPath . 'Core' . DS . 'Autoload' . DS . 'StandardAutoloader.php');

record_timing('Autoloader loaded');

\Core\Autoload\StandardAutoloader::Register('Core', $libPath.'Core'.DS);
\Core\Autoload\StandardAutoloader::Register('Leafo', $libPath.'Leafo'.DS);
\Core\Autoload\StandardAutoloader::Register('Mandrill', $libPath.'Mandrill'.DS);
\Core\Autoload\StandardAutoloader::Register('Site', $libPath.'Site'.DS);

\Core\Application::Run();