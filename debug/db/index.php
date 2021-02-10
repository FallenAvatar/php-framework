<?php

$App = \Core\Application::Get();
if( !isset($App->Config->Core->debug) || $App->Config->Core->debug !== true )
	$this->Redirect('/');
	
$this->SetLayout('debug');
$temp_dbs = $App->Config->Database;
$dbs = [];

foreach($temp_dbs as $k => $v) {
	$dbs[] = ['name' => $k, 'conn' => $v];
}
	
?>
<article class="page" id="db-page">
	<h1>Databases</h1>
	<ul>
<?php foreach( $dbs as $db ) { ?>
		<li><a href="/debug/db/view?name=<?=$db['name']?>"><?=$db['name']?></a></li>
<?php } ?>
	</ul>
</article>