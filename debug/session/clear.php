<?php

	$app = \System\Application::GetInstance();
	if( !isset($app->Config->Site->debug) || $app->Config->Site->debug !== "true" )
		$this->Redirect('/');
		
	session_destroy();

	$this->Redirect('/debug/session/');