<?php

namespace Core\IO
{
	class Path extends \Core\Object
	{
		protected static function Combine_helper($one,$two)
		{
			$one = trim($one);
			$two = trim($two);
			
			if( isset($one) && $one != '' )
			{
				if( substr($one,strlen($one)-1) == DS )
					$one = substr($one,0,strlen($one)-1);
			}
			
			if( isset($two) && $two != '' )
			{
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

		public static function Combine()
		{
			$num = func_num_args();
			$args = func_get_args();
			
			if( $num == 0 )
				return '';
			else if( $num == 1 )
				return $args[0];
				
			$ret=$args[0];
			
			for( $i=1; $i<$num; $i++ )
			{
				$ret = self::Combine_helper($ret,$args[$i]);
			}
			
			return $ret;
		}
	}
}