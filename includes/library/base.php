<?php

error_reporting(E_ALL & ~E_NOTICE);
define('DS',DIRECTORY_SEPARATOR);

require_once(dirname(__FILE__).DS.'..'.DS.'library'.DS.'Core'.DS.'Autoload'.DS.'StandardAutoloader.php');

\Core\Autoload\StandardAutoloader::Init(dirname(__FILE__).DS.'..'.DS.'library'.DS);

include_once('functions.php');

\Core\Application::RunApp();