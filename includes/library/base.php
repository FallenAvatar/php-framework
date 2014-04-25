<?php

error_reporting(E_ALL & ~E_NOTICE);
define('DS',DIRECTORY_SEPARATOR);

if( !isset($GLOBALS['type']) )
{
	echo "ERROR - Direct Access";
	exit();
}

require_once(dirname(__FILE__).DS.'functions.php');
require_once(dirname(__FILE__).DS.'System'.DS.'Autoloader.php');

\System\Autoloader::Init(dirname(__FILE__).DS);

if( \System\Autoloader::CanLoadClass("\\Site\\".$type."\\Application") )
	$class = "\\Site\\".$type."\\Application";
else
	$class = "\\System\\".$type."\\Application";

$class::Run();

function crash($data)
{
	echo '<html><body><pre>'.print_r($data, true).'</pre></body></html>';
	exit();
}
