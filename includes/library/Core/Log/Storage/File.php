<?php

namespace Core\Log\Storage {
	class File extends BaseStorage implements IStorage {
		protected $dir;
		protected $fp = null;
		
		public function __construct($settings) {
			parent::__construct($settings);
			
			$this->dir = \Core\IO\Path::Combine($this->App->Dirs->Includes, $settings->folder);
		}
		
		public function __destruct() {
			if( isset($this->fp) )
				\fclose($this->fp);
		}
		
		public function Store($level, $message, $source, $details) {
			if( !isset($this->fp) )
				$this->fp = \fopen(\Core\IO\Path::Combine($this->dir, date('Ymd').'.log'), 'a');
			
			$str = '['.$this->FormatDate().'] {'.$this->FormatLevel($level).'}: '.$message.' @ '.$this->FormatSource($source)."\n\nBacktrace:\n".$this->FormatBacktrace(2).((isset($details)) ? "\n\nAdditional Details:\n".print_r($details,true) : '');
			
			\fwrite($this->fp, $str);
		}
	}
}