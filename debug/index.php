<?php

declare(strict_types=1);

$App = \Core\Application::Get();
if( !isset($App->Config->Core->debug) || $App->Config->Core->debug !== true )
	$this->Redirect('/');

$this->SetLayout('debug');

