<?

namespace Core
{
	class Array
	{
		public static function IsAssoc($array)
		{
			return array_keys($array) !== range(0, count($array) - 1);
		}
	}
}