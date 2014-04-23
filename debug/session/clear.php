<?php

	$app = \System\Application::GetInstance();
	if( !isset($app->Config->System->debug) || $app->Config->System->debug !== true )
		$this->Redirect('/');
		
	session_destroy();

	$this->Redirect('/debug/session/');