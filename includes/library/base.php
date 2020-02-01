<?php

error_reporting(E_ALL & ~E_NOTICE);

require_once('autoload.php');
require_once('functions.php');

\Core\Application::RunApp();