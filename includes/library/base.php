<?php

error_reporting(E_ALL & ~E_NOTICE);
define('DS',DIRECTORY_SEPARATOR);

if( !isset($GLOBALS['type']) )
{
	echo "ERROR - Direct Access";
	exit();
}

require_once(dirname(__FILE__).DS.'..'.DS.'library'.DS.'System'.DS.'Autoloader.php');

\System\Autoloader::Init(dirname(__FILE__).DS.'..'.DS.'library'.DS);

if( \System\Autoloader::CanLoadClass("\\Site\\".$type."\\Application") )
	$class = "\\Site\\".$type."\\Application";
else
	$class = "\\System\\".$type."\\Application";

$class::Run();