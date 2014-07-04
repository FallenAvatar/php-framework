<?php

namespace System\Web
{
	class Application extends \System\Application
	{
		protected $Request;
		public function _getRequest() { return $this->Request; }
		
		protected $Response;
		public function _getResponse() { return $this->Response; }
		
		protected function BuildDirs()
		{
			parent::BuildDirs();
			$this->AddDir('DocumentRoot', realpath(((isset($_SERVER['SUBDOMAIN_DOCUMENT_ROOT'])) ? $_SERVER['SUBDOMAIN_DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'])));
			$this->AddDir('WebRoot', str_replace($this->Dirs->DocumentRoot, '', $this->Dirs->Root));
		}
		
		public function ErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
		{
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
	<h2><?=$ex->getMessage()?></h2>
	At <?=$ex->getFile()?> on line [<?=$ex->GetLine()?>]<br />
	<br />
	<pre>
<?=print_r($ex->getTrace())?>
	</pre>
</body>
</html>
<?php
			exit();
		}

		public function ErrorPageHandler($errorCode)
		{
			$path = $this->Request->Url->Path;
			
			$codes = array();
			$codes[100] = "Continue";
			$codes[101] = "Switching Protocols";
			$codes[200] = "OK";
			$codes[201] = "Created";
			$codes[202] = "Accepted";
			$codes[203] = "Non-Authoritative Information";
			$codes[204] = "No Content";
			$codes[205] = "Reset Content";
			$codes[206] = "Partial Content";
			$codes[300] = "Multiple Choices";
			$codes[301] = "Moved Permanently";
			$codes[302] = "Found";
			$codes[303] = "See Other";
			$codes[304] = "Not Modified";
			$codes[305] = "Use Proxy";
			$codes[306] = "(Unused)";
			$codes[307] = "Temporary Redirect";
			$codes[400] = "Bad Request";
			$codes[401] = "Unauthorized";
			$codes[402] = "Payment Required";
			$codes[403] = "Forbidden";
			$codes[404] = "Not Found";
			$codes[405] = "Method Not Allowed";
			$codes[406] = "Not Acceptable";
			$codes[407] = "Proxy Authentication Required";
			$codes[408] = "Request Timeout";
			$codes[409] = "Conflict";
			$codes[410] = "Gone";
			$codes[411] = "Length Required";
			$codes[412] = "Precondition Failed";
			$codes[413] = "Request Entity Too Large";
			$codes[414] = "Request-URI Too Long";
			$codes[415] = "Unsupported Media Type";
			$codes[416] = "Requested Range Not Satisfiable";
			$codes[417] = "Expectation Failed";
			$codes[500] = "Internal Server Error";
			$codes[501] = "Not Implemented";
			$codes[502] = "Bad Gateway";
			$codes[503] = "Service Unavailable";
			$codes[504] = "Gateway Timeout";
			$codes[505] = "HTTP Version Not Supported";
			
			$txt = '';
			if( isset($codes[$errorCode]) )
				$txt = ' '.$codes[$errorCode];
			
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
	<h2>The page that you have requested could not be found.</h2><? if( $this->Config->System->debug == true ) { echo "\r\n"; ?>
	<!--
		<?=$path . "\r\n" . print_r(debug_backtrace(), true) ?>
	--><? echo "\r\n"; } ?>
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
			
			\System\Modules\Manager::FindModules();

			$this->Request = new \System\Web\HttpRequest();
			$this->Response = new \System\Web\HttpResponse();
		}

		protected function _loadConfig()
		{
			$files = array();
			$files[] = $this->Dirs->Configs.'system.json';

			$d = dir($this->Dirs->Configs);

			while (false !== ($entry = $d->read()))
			{
				$filePath = \System\IO\Path::Combine($this->Dirs->Configs, $entry);
				if( !is_file($filePath) )
					continue;
					
				if( substr($filePath, -5) != '.json' )
					continue;
					
				if( $entry == 'system.json' || $entry == 'site.json' )
					continue;
					
				$files[] = $filePath;
			}
			
			$files[] = $this->Dirs->Configs.'site.json';
			
			$configStack = new \System\Configuration\ConfigStack($files);
			$this->Config = $configStack->GetMergedConfig();
		}
		
		protected function _fixPhp()
		{
			if( isset($this->Config->PHP) )
			{
				if( isset($this->Config->PHP->functions) )
				{
					//crash($this->Config->PHP->functions);
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
			
			\System\Modules\Manager::LoadModules();

			\System\Web\HandlerFactory::ProcessRequest();
			
			\System\Modules\Manager::UnloadModules();
		}
	}
}