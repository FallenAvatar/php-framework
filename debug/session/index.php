<?

$app = \Core\Application::GetInstance();
if( !isset($app->Config->Core->debug) || $app->Config->Core->debug !== true )
	$this->Redirect('/');
	
$this->SetLayout('debug');

?>
<article class="page" id="session-page">
	<h1>Session</h1>
	<a href="debug/session/clear">Clear Session</a>
	<pre><?=print_r($_SESSION, true)?></pre>
</article>