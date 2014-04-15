<?php
	$app = \System\Application::GetInstance();
	if( !isset($app->Config->Site->debug) || $app->Config->Site->debug !== "true" )
		$this->Redirect('/');
		
	$this->SetLayout('debug');
	
	function PrintDynObject($o, $lvl = 0)
	{
		$ret = '';
		
		$tabs = str_repeat("\t", $lvl);
		
		foreach($o as $name => $value)
		{
			$ret .= $tabs . '<b>[' . $name . ']</b> => ';
			
			if( $value instanceof \System\DynObject )
				$ret .= "\n" . PrintDynObject($value, $lvl + 1);
			else if( is_array($value) )
			{
				$tl = $lvl + 1;
				$ttabs = str_repeat("\t", $tl);
				
				$ret .= "\n";
				
				foreach( $value as $n => $v )
				{
					$ret .= $ttabs . '<b>[' . $n . ']</b> => "' . $v . '"' . "\n";
				}
			}
			else if( !isset($value) )
				$ret .= '<i>null</i>' . "\n";
			else
				$ret .= '"' . $value . '"' . "\n";
		}
		
		return $ret;
	}
?>
<article class="page" id="config-page">
	<h1>Config</h1>
	<pre><?=PrintDynObject($app->Config); ?></pre>
</article>