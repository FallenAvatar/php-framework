<?

namespace Core\Data
{
	class Helper
	{
		public static function HandleParameter($name, $value, $db, &$params)
		{
			$ret = $db->DelimColumn( $name );
			$compare_type = '=';
			if( isset($value) && $value instanceof SpecialValue )
			{
				switch( $value->Name )
				{
				case 'is-null':
					$ret .= ' IS NULL';
					return $ret;
				case 'is-not-null':
					$ret .= ' IS NOT NULL';
					return $ret;
				case "lit-value":
					$ret .= ' = ' . $value->TextValue;
					return $ret;
				default:
					throw new \Exception( 'Unhandled Special Value ['.get_class($value).'].' );
				}
			} else if( isset($value) && is_array($value) && count($value) == 2 ) {
				$allowed = array('=','<','<=','>','>=','<>','!=');
				if( in_array($value[0], $allowed) ) {
					$compare_type = $value[0];
					$value = $value[1];
				}
			}

			$pname = 'auto_'.$name.'_'.count($params);
			$ret .= ' '.$compare_type.' ' . $db->DelimParameter( $pname );

			$params[$pname] = $value;

			return $ret;
		}
		
		public static function ParseOrderBy( $db, $order_by )
		{
			if( !isset($order_by) )
				return '';

			$ret = ' ORDER BY ';
			$first = true;

			foreach( $order_by as $o )
			{
				if( !isset($o) || trim($o) == '' )
					continue;

				if( !$first )
					$ret .= ', ';

				$first = false;

				$dir = 'ASC';
				$name = $o;

				if( $o[0] == '+' )
				{
					$name = substr($o, 1 );
				}
				else if( $o[0] == '-' )
				{
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
}