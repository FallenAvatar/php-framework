<?php

function crash($data)
{
	echo '<html><body><pre>'.print_r($data, true).'</pre></body></html>';
	exit();
}