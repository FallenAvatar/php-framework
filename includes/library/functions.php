<?php

function crash($data)
{
	echo '<html><body><pre>'.print_r($data, true).'</pre></body></html>';
	exit();
}

function startsWith($haystack, $needle) {
	return ($haystack == $needle || (strlen($haystack) > strlen($needle) && substr($haystack, 0, strlen($needle)) == $needle));
}

function endsWith($haystack, $needle) {
	return ($haystack == $needle || (strlen($haystack) > strlen($needle) && substr($haystack, -1 * strlen($needle), strlen($needle)) == $needle));
}