<?php

$App = \Core\Application::Get();
if( !isset($App->Config->Core->debug) || $App->Config->Core->debug !== true )
	$this->Redirect('/');
	
$this->SetLayout('debug');
$name = $this->Get['name'];
$db = \Core\Data\Database::Get($name);
$tbls = $db->ExecuteQuery("SELECT * FROM `information_schema`.`tables` WHERE `table_type` = 'BASE TABLE' AND table_schema = '".$App->Config->Database->$name->db_name."';");
	
?>
<article class="page" id="db-view-page">
	<h1>Database - <?=$name?></h1>
	<ul>
<?php foreach( $tbls as $tbl ) { ?>
		<li><?=$tbl['TABLE_NAME']?></li>
<?php } ?>
	</ul>
</article>