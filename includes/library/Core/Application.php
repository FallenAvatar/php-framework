<?

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
		
		public static function Run()
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
		
		protected function __construct()
		{
			$this->Dirs = new DynObject(array(), false, true);
			
			$this->BuildDirs();
		}
		
		protected function BuildDirs()
		{
			$this->AddDir('Root', $this->GetRootDir());
			$this->AddDir('Library', $this->Dirs->Root.'includes'.DS.'library'.DS);
			$this->AddDir('Configs', $this->Dirs->Root.'includes'.DS.'configs'.DS);
			$this->AddDir('Layouts', $this->Dirs->Root.'includes'.DS.'layouts'.DS);
			$this->AddDir('Data', $this->Dirs->Root.'includes'.DS.'data'.DS);
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
				$typehint = strpos($ErrMessage, 'must be an instance of string, string')
							|| strpos($ErrMessage, 'must be an instance of integer, integer')
							|| strpos($ErrMessage, 'must be an instance of float, double')
							|| strpos($ErrMessage, 'must be an instance of boolean, boolean')
							|| strpos($ErrMessage, 'must be an instance of resource, resource');
							
				if( $typehint )
					return true;
			}
			
			// Call Exception Handler
			throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
		}
		
		public function ExceptionHandler($ex)
		{
			ob_end_clean();
			
			header('HTTP/1.0 500 Server Error');
?>
<!DOCTYPE html>
<html>
<head>
	<title>Error in Site</title>
	<link rel="stylesheet" type="text/css" href="css/reset.css" />
	<style type="text/css">
		h1{width: 100%;display:block;background-color:#f7fdb6;color:#FF0000;padding:10px;margin:25px 0;font-size:20pt;font-weight:bold;}
		h2{font-weight:bold;font-size:16pt;}
	</style>
</head>
<body>
	<h1>Error</h1>
	<? if( !$this->Config->Core->debug ) { ?>
	An error has occured.
	<? } else { ?>
	<h2><?=$ex->getMessage()?></h2>
	At <?=$ex->getFile()?> on line [<?=$ex->GetLine()?>]<br />
	<br />
	<pre><?=print_r($ex->getTrace())?></pre>
	<? } ?>
</body>
</html>
<?
			exit();
		}

		public function ErrorPageHandler($errorCode)
		{
			$path = $this->Request->Url->Path;
			
			$txt = '';
			if( isset(self::$HttpErrorCodeText[$errorCode]) )
				$txt = ' '.self::$HttpErrorCodeText[$errorCode];
			
			header('HTTP/1.0 '.$errorCode.$txt);
			
			if( $errorCode == 404 )
			{
?>
<!DOCTYPE html>
<html>
<head>
	<title>Page not found</title>
	<link rel="stylesheet" type="text/css" href="css/reset.css" />
	<style type="text/css">
		h1{width: 100%;display:block;background-color:#f7fdb6;color:#FF0000;padding:10px;margin:25px 0;font-size:20pt;font-weight:bold;}
		h2{font-weight:bold;font-size:16pt;}
	</style>
</head>
<body>
	<h1>404 Page not found</h1>
	<h2>The page that you have requested could not be found.</h2>
	<!-- Path: <?=$path?> -->
</body>
</html>
<?php
			}
			
			exit(0);
		}

		protected function _init()
		{
			set_error_handler(array($this,'ErrorHandler'));
			set_exception_handler(array($this,'ExceptionHandler'));

			$this->_loadConfig();
			$this->_fixPhp();

			$this->Request = new \Core\Web\Request();
		}

		protected function _loadConfig()
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
		
		protected function _fixPhp()
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
		
		protected function _run()
		{
			session_start();

			\Core\Handlers\HandlerFactory::ProcessRequest();
		}
	}
}