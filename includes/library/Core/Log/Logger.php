<?php

namespace Core\Log
{
	class Logger
	{
		protected $storages;
		
		public function __construct($storages)
		{
			if( is_array($storages) && count($storages) > 0 )
				$this->storages = $storages;
			else if( $storages instanceof \Core\Log\Storage\IStorage )
				$this->storages = array($storages);
			else
				$this->storages = array();
		}
		
		public function Log($level, $message, $source = null, $details = null)
		{
			if( !isset($source) )
				$source = $this->getSourceInfo();
			
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
				'type' => $bt[$depth-1]['type'],
				'args' => $bt[$depth-1]['args']
			);
		}
		
		public function Debug($message, $details = null)
		{
			$source = $this->getSourceInfo();
			
			$this->Log(\Core\Log\LEVEL_DEBUG, $message, $source, $details);
		}
		
		public function Info($message, $details = null)
		{
			$source = $this->getSourceInfo();
			
			$this->Log(\Core\Log\LEVEL_INFO, $message, $source, $details);
		}
		
		public function Warn($message, $details = null)
		{
			$source = $this->getSourceInfo();
			
			$this->Log(\Core\Log\LEVEL_WARN, $message, $source, $details);
		}
		
		public function Error($message, $details = null)
		{
			$source = $this->getSourceInfo();
			
			$this->Log(\Core\Log\LEVEL_ERROR, $message, $source, $details);
		}
	}
}