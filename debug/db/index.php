<?php
	$app = \System\Application::GetInstance();
	if( !isset($app->Config->System->debug) || $app->Config->System->debug !== true )
		$this->Redirect('/');
		
	$this->SetLayout('debug');
?>
<article class="page" id="db-page">
	<h1>Databases</h1>
</article>