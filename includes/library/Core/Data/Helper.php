<?php declare(strict_types=1);

namespace Core\Data;

class Helper {
	public static function HandleParameter(string $name, $value, \Core\Data\Database $db, array &$params): string {
		$ret = $db->DelimColumn( $name );
		$pname = 'auto_'.$name.'_'.count($params);
		
		if( isset($value) && $value instanceof SpecialValue ) {
			switch( $value->Name ) {
			case 'is-null':
				$ret .= ' IS NULL';
				return $ret;
			case 'is-not-null':
				$ret .= ' IS NOT NULL';
				return $ret;
			case "lit-value":
				$ret .= ' = ' . $value->TextValue;
				return $ret;
			case "func-value":
				$ret .= ' = ' . $value->TextValue;
				$params[$value->ParamName] = $value->ParamValue;
				return $ret;
			case "like":
				$ret .= ' LIKE ' . $db->DelimParameter($pname);
				$value = $value->Value;
				break;
			default:
				throw new \Exception( 'Unhandled Special Value ['.get_class($value).'].' );
			}
		} else if( !isset($value) ) {
			$ret .= ' IS NULL';
			return $ret;
		} else {
			$ret .= ' = ' . $db->DelimParameter( $pname );
		}
		
		$params[$pname] = $value;

		return $ret;
	}
	
	public static function ParseOrderBy( \Core\Data\Database $db, array $order_by ): string {
		if( !isset($order_by) )
			return '';

		$ret = ' ORDER BY ';
		$first = true;

		foreach( $order_by as $o ) {
			if( !isset($o) || trim($o) == '' )
				continue;

			if( !$first )
				$ret .= ', ';

			$first = false;

			$dir = 'ASC';
			$name = $o;

			if( $o[0] == '+' ) {
				$name = substr($o, 1 );
			} else if( $o[0] == '-' ) {
				$dir = 'DESC';
				$name = substr($o, 1 );
			}

			$ret .= $db->DelimColumn( $name ) . ' ' . $dir;
		}

		if( $first == true ) // Nothing was added
			return '';

		return $ret;
	}
}