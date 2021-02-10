<?php

$app = \Core\Application::Get();
if( !isset($app->Config->Core->debug) || $app->Config->Core->debug !== true )
	$this->Redirect('/');
	
session_destroy();

$this->Redirect('/debug/session/');