<?php

namespace Core
{
	class ArrayHelper
	{
		public static function IsAssoc($array)
		{
			return array_keys($array) !== range(0, count($array) - 1);
		}
		
		public static function CheckSchema($arr, $schema, &$errs) {
			$errs = array();
			
			if( !isset($arr) ) {
				$errs[] = 'Input was null.';
				return false;
			} else if( !is_array($arr) ) {
				$errs[] = 'Input was not an array.';
				return false;
			}
			
			$err = false;
			foreach($schema as $name => $type) {
				$type = static::ParseType($type);
				
				if( $type['type'] == 'ignore' )
					continue;
				
				if( !isset($arr[$name]) ) {
					if( !$type['allow_null'] ) {
						$err = true;
						$errs[] = 'Missing required field ['.$name.'].';
					}
				} else {
					if( $type['type'] == 'string' && !is_string($arr[$name]) ) {
						$err = true;
						$errs[] = 'Expected field ['.$name.'] to be of type ['.$type['type'].'].';
					} else if( $type['type'] == 'int' && !is_int($arr[$name]) ) {
						$err = true;
						$errs[] = 'Expected field ['.$name.'] to be of type ['.$type['type'].'].';
					} else if( $type['bool'] == 'string' && !is_bool($arr[$name]) ) {
						$err = true;
						$errs[] = 'Expected field ['.$name.'] to be of type ['.$type['type'].'].';
					} else if( $type['type'] == 'float' && !is_float($arr[$name]) ) {
						$err = true;
						$errs[] = 'Expected field ['.$name.'] to be of type ['.$type['type'].'].';
					}
				}
			}
			
			if( $err )
				return false;
			
			return true;
		}
		
		private static function ParseType($type) {
			$chars = array(
				'allow_null' => '?',
				'unsigned' => '+'
			);
			
			if( is_array($type) ) {
				// validate type def, add defaults
				if( !isset($type['type']) )
					throw new \Exception('Invalid column definition \'type\' ['.$type['type'].'] for Schema.');
				else if( isset($type['length']) && !is_int($type['length']) )
					throw new \Exception('Invalid column definition \'length\' ['.$type['length'].'] for Schema, expected int.');
					
				foreach($chars as $name => $char) {
					if( isset($type[$name]) && !is_bool($type[$name]) )
						throw new \Exception('Invalid column definition \''.$name.'\' ['.$type[$name].'] for Schema, expected bool.');
					else
						$type[$name] = false;
				}
			} else if( is_string($type) ) {
				$ret = array();
				$ret['length'] = null;
				
				$max = -1;
				// parse
				foreach($chars as $name => $char) {
					$idx = strpos($type, $char);
					if( $idx !== false )
					{
						$ret[$name] = true;
						$max = max($max, $idx);
					} else
						$ret[$name] = false;
				}
			
				if( $max >= 0 )
					$type = substr($type, $max+1);

				$lbracket = strpos($type, '[');
				if( $lbracket !== false && $lbracket >= 0 ) {
					$rbracket = strpos($type, ']');
					$lenstr = substr($type, $lbracket+1, ($rbracket-$lbracket)-1);
					$ret['length'] = intval($lenstr);
			
					$ret['type'] = substr($type, 0, $lbracket);
				} else
					$ret['type'] = $type;
			
				return $ret;
			}
			else
				throw new \Exception('Invalid column definition ['.$type.'] for Schema.');
		}
	}
}