<?php

$app = \Core\Application::GetInstance();
if( !isset($app->Config->Core->debug) || $app->Config->Core->debug !== true )
	$this->Redirect('/');
	
$this->SetLayout('debug');
	
?>
<article class="page" id="dashboard-page">
	<h1>Debug Dashboard</h1>
</article>