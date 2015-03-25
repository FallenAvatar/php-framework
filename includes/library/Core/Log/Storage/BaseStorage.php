<?

namespace Core\Log
{
	class BaseStorage implements IStorage
	{
		protected function FormatSource($source_array)
		{
			if( !is_array($source_array) )
				return $source_array;
			
			return $source_array['class'].$source_array['type'].$source_array['function'].' ('.$source_array['file'].':'.$source_array['line'].')'
		}
	}
}