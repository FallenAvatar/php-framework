<?

namespace Core\Log
{
	class Logger
	{
		protected $storages;
		protected $levels;
		
		public function __construct($storages, $levels = -1)
		{
			if( is_array($storages) && count($storages) > 0 )
				$this->storages = $storages;
			else if( $storages instanceof \Core\Log\Storage\IStorage )
				$this->storages = array($storages);
			else
				$this->storages = array();
				
			if( $levels <= 0 )
				$this->levels = LEVEL_DEBUG | LEVEL_INFO | LEVEL_WARN | LEVEL_ERROR
			else
				$this->levels = $levels;
		}
		
		public function Log($level, $message, $source, $details = null)
		{
			if( ($level & $this->levels) == 0 )
				return;
			
			foreach($this->storages as $s)
				$s->Log($level, $message, $source, $details);
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