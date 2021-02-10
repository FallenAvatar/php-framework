<?php

$app = \Core\Application::Get();
if( !isset($app->Config->Core->debug) || $app->Config->Core->debug !== true )
	$this->Redirect('/');
	
$this->SetLayout('debug');

$errors = array();
	
?>
<article class="page" id="errors-page">
	<h1>Errors</h1>
	<table>
		<thead>
			<tr>
				<td style="width: 200px;"><td>
				<td>Error Message<td>
				<td style="width: 200px;">Date<td>
			</tr>
		</thead>
		<tbody>
		<?php if( count($errors) > 0 ) { ?>
			<?php foreach( $errors as $err ) {?>
			<tr>
				<td><a href="view?id=<?=$err['id']?>" class="button">View</a></td>
				<td><?=$err['message']?></td>
				<td><?=$err['dt']?></td>
			</tr>
			<?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="3" class="table-empty">
					No errors at this time, yay!
				</td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
</article>