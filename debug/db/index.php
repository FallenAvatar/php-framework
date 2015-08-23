<?php

$app = \Core\Application::GetInstance();
if( !isset($app->Config->Core->debug) || $app->Config->Core->debug !== true )
	$this->Redirect('/');
	
$this->SetLayout('debug');
	
?>
<article class="page" id="db-page">
	<h1>Databases</h1>
</article>