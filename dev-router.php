<?php 

namespace PhpBuiltinServer\Router;

function startsWith($subject, $test) {
	if( strlen($subject) < strlen($test) )
		return false;

	if( substr($subject, 0, strlen($test)) == $test )
		return true;

	return false;
}

$htpath = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'.htaccess';
$lines = explode("\n", str_replace("\r\n","\n", file_get_contents($htpath)));

$engine_on = false;
$base = '/';
$dir_index = 'index.php';
$rules = [];

foreach( $lines as $line ) {
	$line = trim($line);

	if( $line == '' )	// empty line
		continue;

	if( $line[0] == '#' )	// Comment
		continue;

	if( startsWith($line, 'DirectoryIndex') ) {
		$idx = strpos($line, ' ');
		$dir_index = substr($line, $idx + 1);
	} else if( startsWith($line, 'RewriteEngine') ) {
		$idx = strpos($line, ' ');
		$t = substr($line, $idx + 1);

		if( $t == 'On' )
			$engine_on = true;
	} else if( startsWith($line, 'RewriteBase') ) {
		$idx = strpos($line, ' ');
		$base = substr($line, $idx + 1);
	} else if( startsWith($line, 'RewriteRule') ) {
		$idx = strpos($line, ' ');
		$t = substr($line, $idx + 1);
		$flags = [];

		if( $t[strlen($t)-1] == ']' ) {
			$idx2 = strrpos($t,'[');
			$f = substr($t, $idx2+1,-1);
			$t = trim(substr($t, 0, $idx2));
			$flags = explode(',',$f);
		}

		// TODO: Split rule; spaces can be escaped in rules with a \
		$parts = explode(' ', $t);
		$pat = $parts[0];
		$rewrite = null;
		$curr_idx = 0;

		while( $curr_idx < count($parts) ) {
			$curr_idx++;
			if( $pat[strlen($pat)-1] == '\\' && $pat[strlen($pat)-2] != '\\' ) {
				$pat .= ' '.$parts[$curr_idx];
			} else {
				break;
			}
		}

		while( $curr_idx < count($parts) ) {
			$rewrite .= ' '.$parts[$curr_idx];
			$curr_idx++;
		}

		$rules[] = [
			'pattern' => '~'.trim($pat).'~'.(in_array('NC',$flags) ? 'i' : ''),
			'rewrite' => trim($rewrite),
			'flags' => $flags
		];
	}
}

/* echo 'DirIndex: '.$dir_index."\n";
echo 'Engine: '.($engine_on ? 'On' : 'Off')."\n";
echo 'Base: '.$base."\n";
echo "\n";
print_r($rules);
exit(); */

//print_r($_SERVER);

if( !$engine_on )
	return false;

$matched_rule = false;
$url = $_SERVER['REQUEST_URI'];

if( startsWith($url, '/') )
	$url = substr($url,1);

foreach($rules as $rule) {
	$matches = null;

	if( preg_match_all($rule['pattern'], $url, $matches, PREG_OFFSET_CAPTURE) != false ) {
		//echo 'Rule: '.print_r($rule, true)."\n".'Matches:'.print_r($matches,true)."\n\n";
		$matched_rule = true;

		if( $rule['rewrite'] != '-' ) {
			$url = $rule['rewrite'];

			for( $i=1; $i<count($matches); $i++ ) {
				$url = str_replace('$'.$i, $matches[$i][0][0], $url);
			}
		}

		if( in_array('R=301', $rule['flags']) ) {
			header('Location: '.$base.$url);
			exit();
		}

		if( in_array('L', $rule['flags']) ) {
			break;
		}
	}
}

if( !$matched_rule )
	return false;

//echo 'requiring '.$_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$url;
// set server vars and require
require($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$url);