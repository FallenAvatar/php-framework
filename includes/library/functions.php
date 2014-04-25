<?

function crash($data)
{
	echo '<html><body><h1>Debug - Crash</h1><br /><pre style="display: block; width: 90%; padding: 2%; border: 1px solid black;">'.print_r($data, true).'</pre></body></html>';
	exit();
}

function get_calling_class()
{
    $trace = debug_backtrace();

    // 0 = who called this func, 1 = who called them
    $class = $trace[1]['class'];

    for( $i=2; $i<count($trace); $i++ )
	{
        if( isset( $trace[$i] ) )
            if( $class != $trace[$i]['class'] )
                return $trace[$i]['class'];
    }
	
	return null;
}