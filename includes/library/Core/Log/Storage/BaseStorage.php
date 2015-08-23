<?php

namespace Core\Log\Storage {
	abstract class BaseStorage implements IStorage {
	
		protected $App;
		
		protected $settings;
		protected $enabled;
		protected $levels;
		
		public function __construct($settings) {
			$this->settings = $settings;
			
			$this->App = \Core\Application::GetInstance();
			
			$this->enabled = $settings->enabled;
			$this->levels = intval($settings->level);
		}
		
		protected function FormatSource($source_array) {
			if( !is_array($source_array) )
				return $source_array;
			
			return $source_array['class'].$source_array['type'].$source_array['function'].'(...) ['.$source_array['file'].':'.$source_array['line'].']';
		}
		
		protected function FormatDate($ts = null) {
			if( !isset($ts) )
				$ts = time();
				
			return date('Y-m-d H:i:s', $ts);
		}
		
		protected function FormatLevel($lvl) {
			switch($lvl) {
			case \Core\Log\LEVEL_DEBUG:
				return 'DEBUG';
			case \Core\Log\LEVEL_INFO:
				return 'INFO';
			case \Core\Log\LEVEL_WARN:
				return 'WARN';
			case \Core\Log\LEVEL_ERROR:
				return 'ERROR';
			}
			
			return 'UNKWN';
		}
		
		protected function FormatBacktrace($skipLvls = 1) {
			$ret = '';
			$bt = debug_backtrace();
			
			for( $i=$skipLvls; $i<count($bt); $i++ )
				$ret .= $this->FormatSource($bt[$i])."\n";
			
			return $ret;
		}
		
		public function Log($level, $message, $source, $details) {
			if( !$this->enabled || ($this->levels & $level) <= 0 )
				return;
			
			$this->Store($level, $message, $source, $details);
		}
		
		public abstract function Store($level, $message, $source, $details);
	}
}