<?php

declare(strict_types=1);

namespace Core\IO;

class Path extends \Core\Obj {
	protected static function Combine_helper(string $one, string $two): string {
		$one = trim($one);
		$two = trim($two);

		if( $one != '' ) {
			if( substr($one,strlen($one)-1) == DS )
				$one = substr($one,0,strlen($one)-1);
		}

		if( $two != '' ) {
			if( substr($two,0,1) == DS && isset($one) && $one != '' )
				$two = substr($two,1);
			if( substr($two,strlen($two)-1) == DS )
				$two = substr($two,0,strlen($two)-1);
		}

		if( !isset($one) || $one == '' )
			return $two;

		if( !isset($two) || $two == '' )
			return $one;

		return $one . DS . $two;
	}

	public static function Combine(string ...$args): string {
		if( count($args) == 0 )
			return '';

		if( count($args) == 1 )
			return $args[0];

		$ret = '';

		foreach( $args as $p )
			$ret = self::Combine_helper($ret,$p);

		return $ret;
	}
}