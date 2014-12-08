<?

$app = \Core\Application::GetInstance();
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
		<? if( count($errors) > 0 ) { ?>
			<? foreach( $errors as $err ) {?>
			<tr>
				<td><a href="view?id=<?=$err['id']?>" class="button">View</a></td>
				<td><?=$err['message']?></td>
				<td><?=$err['dt']?></td>
			</tr>
			<? } ?>
		<? } else { ?>
			<tr>
				<td colspan="3" class="table-empty">
					No errors at this time, yay!
				</td>
			</tr>
		<? } ?>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
</article>