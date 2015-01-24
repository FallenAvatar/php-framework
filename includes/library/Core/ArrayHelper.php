<?

namespace Core
{
	class ArrayHelper
	{
		public static function IsAssoc($array)
		{
			return array_keys($array) !== range(0, count($array) - 1);
		}
	}
}