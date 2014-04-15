<?php

namespace System\Cli
{
	class Application extends \System\Application
	{
		public function ErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
		{
			if( $errno == E_NOTICE && strcmp(substr($errstr,0,19), 'Undefined variable:') != 0 )
				return true;
			ob_end_clean();
			
			echo '[ERROR] '.$errstr."\n";
			echo "\t".' @ '.$errfile.' on line ['.$errline.']'."\n";
			echo "\t".'Error #: '.$errno."\n";
			print_r($errcontext);
			exit();
		}
		
		public function ExceptionHandler($ex)
		{
			ob_end_clean();
			
			echo '[ERROR] '.$ex->getMessage()."\n";
			echo "\t".' @ '.$ex->getFile().' on line ['.$ex->GetLine().']'."\n";
			print_r($ex->getTrace());
			exit();
		}

		protected function _init()
		{
			set_error_handler(array($this,'ErrorHandler'));
			set_exception_handler(array($this,'ExceptionHandler'));

			$this->_loadConfig();
			$this->_fixPhp();
		}

		protected function _loadConfig()
		{
			$this->Config = new \System\Configuration\Config($this->Dirs->Configs.'site.ini');
			$configs = array();

			$d = dir($this->Dirs->Configs);

			while (false !== ($entry = $d->read()))
			{
				$filePath = \System\IO\Path::Combine($this->Dirs->Configs, $entry);
				if( !is_file($filePath) )
					continue;
					
				if( substr($filePath, -4) != '.ini' )
					continue;
					
				if( $entry == 'site.ini' )
					continue;
					
				$configName = substr($entry,0,-4);
				
				$c = new \System\Configuration\Config($filePath, true);
				$this->Config->Merge($c);
			}
		}
		
		protected function _fixPhp()
		{
			if( isset($this->Config->PHP) )
			{
				if( isset($this->Config->PHP->functions) )
				{
					$functions = $this->Config->PHP->functions->ToArray();
					foreach($functions as $func => $params)
					{
						$params_to_pass = array();
						
						if( !is_array($params) )
							$params_to_pass[] = $params;
						else
						{
							foreach($params as $param)
							{
								$params_to_pass[] = $param;
							}
						}
						
						call_user_func_array($func, $params_to_pass);
					}
				}
				
				if( isset($this->Config->PHP->ini) )
				{
					$values = $this->Config->PHP->ini->ToArray();
					foreach($values as $name => $value)
					{
						ini_set($name,$value);
					}
				}
			}
		}
		
		protected function _run()
		{
			throw new \Exception('Not Implemented!');
		}
	}
}