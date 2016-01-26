<?php

namespace Core
{
	class Application extends Object
	{
		public static $HttpErrorCodeText = array(
			100 => "Continue",
			101 => "Switching Protocols",
			200 => "OK",
			201 => "Created",
			202 => "Accepted",
			203 => "Non-Authoritative Information",
			204 => "No Content",
			205 => "Reset Content",
			206 => "Partial Content",
			300 => "Multiple Choices",
			301 => "Moved Permanently",
			302 => "Found",
			303 => "See Other",
			304 => "Not Modified",
			305 => "Use Proxy",
			306 => "(Unused)",
			307 => "Temporary Redirect",
			400 => "Bad Request",
			401 => "Unauthorized",
			402 => "Payment Required",
			403 => "Forbidden",
			404 => "Not Found",
			405 => "Method Not Allowed",
			406 => "Not Acceptable",
			407 => "Proxy Authentication Required",
			408 => "Request Timeout",
			409 => "Conflict",
			410 => "Gone",
			411 => "Length Required",
			412 => "Precondition Failed",
			413 => "Request Entity Too Large",
			414 => "Request-URI Too Long",
			415 => "Unsupported Media Type",
			416 => "Requested Range Not Satisfiable",
			417 => "Expectation Failed",
			500 => "Internal Server Error",
			501 => "Not Implemented",
			502 => "Bad Gateway",
			503 => "Service Unavailable",
			504 => "Gateway Timeout",
			505 => "HTTP Version Not Supported"
		);

		protected static $s_inst = null;
		
		public static function GetInstance()
		{
			return self::$s_inst;
		}
		
		public static function RunApp()
		{
			$class = '';
			if( \Core\Autoload\StandardAutoloader::CanLoadClass("\\Site\\Application") )
				$class = "\\Site\\Application";
			else
				$class = "\\Core\\Application";
			
			self::$s_inst = new $class();
			
			if( !(self::$s_inst instanceof \Core\Application) )
				throw new \Exception('Application class ['.$class.'] found, but it does not entend \Core\Application.');
			
			self::$s_inst->_init();
			self::$s_inst->_run();
			
			exit();
		}
		
		protected $Config = null;
		public function _getConfig() { return $this->Config; }

		protected $Dirs;
		public function _getDirs() { return $this->Dirs; }
		
		protected $Request;
		public function _getRequest() { return $this->Request; }

		protected $LastError;
		public function _getLastError() { return $this->LastError; }
		
		protected function __construct() {
			$this->Dirs = new DynObject(array(), false, true);
			
			$this->BuildDirs();
		}
		
		protected function BuildDirs()
		{
			$this->AddDir('Root', $this->GetRootDir());
			$this->AddDir('Includes', $this->Dirs->Root.'includes'.DS);
			$this->AddDir('Library', $this->Dirs->Includes.'library'.DS);
			$this->AddDir('Configs', $this->Dirs->Includes.'configs'.DS);
			$this->AddDir('Layouts', $this->Dirs->Includes.'layouts'.DS);
			$this->AddDir('Data', $this->Dirs->Includes.'data'.DS);
			$this->AddDir('Cache', $this->Dirs->Data.'cache'.DS);
			$this->AddDir('DocumentRoot', realpath(((isset($_SERVER['SUBDOMAIN_DOCUMENT_ROOT'])) ? $_SERVER['SUBDOMAIN_DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'])));
			$this->AddDir('WebRoot', str_replace($this->Dirs->DocumentRoot, '', $this->Dirs->Root));
		}
		
		protected function GetRootDir()
		{
			return realpath(dirname(__FILE__).DS.'..'.DS.'..'.DS.'..'.DS).DS;
		}
		
		protected function AddDir($name, $path)
		{
			$this->Dirs->$name = $path;
		}
		
		public function ErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
		{
			// Ignore notices except "Undefined variable" errors
			if( $errno == E_NOTICE && strcmp(substr($errstr,0,19), 'Undefined variable:') != 0 )
				return true;
				
			if( $errno == E_RECOVERABLE_ERROR )
			{
				// from http://www.php.net/manual/en/language.oop5.typehinting.php#111411
				// order this according to what your app uses most
				$typehint = strpos($errstr, 'must be an instance of string, string')
							|| strpos($errstr, 'must be an instance of integer, integer')
							|| strpos($errstr, 'must be an instance of float, double')
							|| strpos($errstr, 'must be an instance of boolean, boolean')
							|| strpos($errstr, 'must be an instance of resource, resource');
							
				if( $typehint )
					return true;
			}
			
			// Call Exception Handler
			throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
		}
		
		public function ExceptionHandler($ex) {
			$this->LastError = $ex;
			$this->ErrorPageHandler(500);
			exit();
		}

		public function ErrorPageHandler($errorCode)
		{
			$path = $this->Request->Url->Path;
			
			$txt = '';
			if( isset(self::$HttpErrorCodeText[$errorCode]) )
				$txt = ' '.self::$HttpErrorCodeText[$errorCode];
			
			ob_end_clean();
			header('HTTP/1.0 '.$errorCode.$txt);
			
			$errorPath = $this->Dirs->Root.DS.'error'.DS.$errorCode.'.phtml';
			
			if( is_file($errorPath) )
			{
				// Print pretty error
				$handler = new \Core\Handlers\PageHandler();
				$handler->ExecuteErrorRequest($errorPath, $errorCode);
			}

			if( $errorCode == 500 )
			{
				try {
					$logger = \Core\Log\Manager::Get();
					$logger->Error($ex->Message, $ex);
				} catch(\Exception $e) {
					// Do nothing. Just make sure logging can't trigger an error.
				}
			}
			
			exit(0);
		}

		private function _init()
		{
			set_error_handler(array($this,'ErrorHandler'));
			set_exception_handler(array($this,'ExceptionHandler'));

			$this->_loadConfig();
			$this->_fixPhp();

			$this->Request = new \Core\Web\Request();
			
			$this->Init();
		}
		
		protected function Init() {
			$this->OnInit();
		}
		
		protected function OnInit() {
			// For base classes to override
		}

		private function _loadConfig()
		{
			$files = array();
			$files[] = $this->Dirs->Configs.'core.json';

			$d = dir($this->Dirs->Configs);

			while (false !== ($entry = $d->read()))
			{
				$filePath = \Core\IO\Path::Combine($this->Dirs->Configs, $entry);
				if( !is_file($filePath) )
					continue;
					
				if( substr($filePath, -5) != '.json' )
					continue;
					
				if( $entry == 'core.json' || $entry == 'site.json' )
					continue;
					
				$files[] = $filePath;
			}
			
			$files[] = $this->Dirs->Configs.'site.json';
			
			$configStack = new \Core\Configuration\ConfigStack($files);
			$this->Config = $configStack->GetMergedConfig();
		}
		
		private function _fixPhp()
		{
			if( isset($this->Config->PHP) )
			{
				if( isset($this->Config->PHP->functions) )
				{
					$functions = $this->Config->PHP->functions;
					foreach($functions as $f)
					{
						$func = $f['name'];
						$params_to_pass = $f['args'];
						
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
		
		private function _run()
		{
			session_start();

			$this->Run();
		}
		
		protected function Run() {
			$this->OnPreRun();
			
			$logger = \Core\Log\Manager::Get();
			$logger->Debug('Starting Request Processing.');
			\Core\Handlers\HandlerFactory::ProcessRequest();
			
			$this0>OnPostRun();
		}
		
		protected function OnPreRun() {
			// For base classes to override
		}
		
		protected function OnPostRun() {
			// For base classes to override
		}
	}
}