<?php
	$app = \System\Application::GetInstance();
	if( !isset($app->Config->Site->debug) || $app->Config->Site->debug !== "true" )
		$this->Redirect('/');
		
	$this->SetLayout('debug');
?>
<article class="page" id="dashboard-page">
	<h1>Debug Dashboard</h1>
</article>