<?php

namespace System
{
	class Utilities
	{
		public static function GeneratePassword($len)
		{
			$ret='';
			$chars='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			
			for($i=0;$i<$len;$i++)
				$ret .= substr($chars,rand(0,strlen($chars)),1);
				
			return $ret;
		}
		public static function FormatDate($dt)
		{
			$ret='';

			$created_day = date('j', $dt);
			$actual_day = date('j', time());

			if($created_day == $actual_day)
				$ret = 'Today, '.date('h:i A', $dt);
			else if($created_day-1 == $actual_day)
				$ret = 'Yesterday, '.date('h:i A', $dt);
			else
				$ret = date('d M Y', $dt);

			return $ret;
		}
		public static function BreadCrumbs($crumbs)
		{
			$ret = '';
			$count = 1;

			foreach($crumbs as $key => $value)
			{
				if($count == count($crumbs))
					$ret .= "<b>$key</b>";
				else
					$ret .= "<a href='$value'>$key</a> -> ";
				$count++;
			}

			return $ret;
		}
	}
}