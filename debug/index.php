<?php
	$app = \System\Application::GetInstance();
	if( !isset($app->Config->System->debug) || $app->Config->System->debug !== true )
		$this->Redirect('/');
		
	$this->SetLayout('debug');
?>
<article class="page" id="dashboard-page">
	<h1>Debug Dashboard</h1>
</article>