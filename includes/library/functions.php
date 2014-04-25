<?

function crash($data)
{
	echo '<html><body><pre>'.print_r($data, true).'</pre></body></html>';
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