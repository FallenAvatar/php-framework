<?

namespace Core\Log\Handler
{
	abstract class BaseHandler implements IHandler
	{
		public abstract function Log($level, $message, $source, $details = null);
		
		protected function FormatSource($source_array)
		{
			if( !is_array($source_array) )
				return $source_array;
			
			return $source_array['class'].$source_array['type'].$source_array['function'].' ('.$source_array['file'].':'.$source_array['line'].')'
		}
		
		private function getSourceInfo($depth = 2)
		{
			$bt = debug_backtrace(false, $depth);
			
			return array(
				'file' => $bt[$depth-1]['file'],
				'line' => $bt[$depth-1]['line'],
				'class' => $bt[$depth-1]['class'],
				'function' => $bt[$depth-1]['function'],
				'type' => $bt[$depth-1]['type']
			);
		}
		
		public function Debug($message, $details)
		{
			$source = $this->getSourceInfo();
			
			$this->Log(\Core\Log\LEVEL_DEBUG, $message, $source, $details);
		}
		
		public function Info($message, $details)
		{
			$source = $this->getSourceInfo();
			
			$this->Log(\Core\Log\LEVEL_INFO, $message, $source, $details);
		}
		
		public function Warn($message, $details)
		{
			$source = $this->getSourceInfo();
			
			$this->Log(\Core\Log\LEVEL_WARN, $message, $source, $details);
		}
		
		public function Error($message, $details)
		{
			$source = $this->getSourceInfo();
			
			$this->Log(\Core\Log\LEVEL_ERROR, $message, $source, $details);
		}
	}
}