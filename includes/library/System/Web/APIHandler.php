<?php

namespace System\Web
{
	class APIHandler extends \System\Object implements \System\Web\IRequestHandler
	{
		public function CanHandleRequest($App)
		{
			$Config = $App->Config;
			
			if( $Config->API->enabled !== "true" )
				return false;
			
			$path = $App->Request->Url->Path;
			$api_path = $Config->API->base_url;
			
			$path_start = substr($path, 0, strlen($api_path));
			if( $path_start != $api_path )
				return false;

			return true;
		}

		public function ExecuteRequest($App)
		{
			$Config = $App->Config;
			$path = $App->Request->Url->Path;

			$path = $App->Request->Url->Path;
			$api_path = $Config->API->base_url;
			
			$path = substr($path, strlen($api_path));
			
			$pos = strrpos($path,'/');
			if( $pos === false )
			{
				$App->ErrorPageHandler(404);
				return;
			}
			
			$method = substr($path, $pos+1);
			$path = substr($path,0,$pos);
			
			if( !isset($method) || trim($method) == '' || !isset($path) || trim($path) == '' )
			{
				$App->ErrorPageHandler(404);
				return;
			}
			
			$class = $this->FindClass($App, $path);
			
			if( $class === false )
			{
				$App->ErrorPageHandler(404);
				return;
			}
			
			if( !method_exists($class, $method) )
			{
				$App->ErrorPageHandler(404);
				return;
			}
			
			$method_info = new \ReflectionMethod($class,$method);
			$method_info->setAccessible(true);
			
			$inst = null;

			if( !$method_info->isStatic() )
			{
				$App->ErrorPageHandler(403);
				return;
			}
			
			if( $method_info->isPublic() )
			{
			}
			else if( $method_info->isProtected() )
			{
				$signature = strtolower($_GET['signature']);
				
				if( !isset($signature) || trim($signature) == '' )
				{
					$App->ErrorPageHandler(403);
					return;
				}

				$message = '';
				foreach( $_GET as $name => $value )
				{
					if( $name == 'signature' )
						continue;

					$message .= '&'.urlencode($name).'='.urlencode($value);
				}
				
				if( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && $_SERVER['CONTENT_TYPE'] == 'application/json' )
				{
					$input = fopen('php://input','r');
					$json_string = fgets($input);
					$message .= "\n\n".$json_string;
				}
				else
				{
					foreach( $_POST as $name => $value )
					{
						if( $name == 'signature' )
							continue;

						$message .= '&'.urlencode($name).'='.urlencode($value);
					}
				}

				$hash = hash_hmac('md5', $message, $App->Config->API->auth_key);

				if( $signature != $hash )
				{
					$App->ErrorPageHandler(403);
					return;
				}
			}
			else if( $method_info->isPrivate() )
			{
				$user = \Site\Security::GetUser();
				if( !isset($user) )
				{
					$App->ErrorPageHandler(403);
					return;
				}
			}
			
			$args = $method_info->getParameters();
			$args_to_pass = array();
			
			$input = $_POST;
			if( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && $_SERVER['CONTENT_TYPE'] == 'application/json' )
			{
				$input = fopen('php://input','r');
				$json_string = fgets($input);
				$input = json_decode($json_string,true);
			}

			foreach($_GET as $n => $v)
			{
				if( !isset($input[$n]) )
					$input[$n] = $v;
			}
			
			if( isset($args) && is_array($args) )
			{
				foreach($args as $arg)
				{
					$name = $arg->name;
					$name = preg_replace('/\_/','-',$name);
					
					$val = $input[$name];
					
					if( !isset($val) )
					{
						if( !$arg->isDefaultValueAvailable() )
						{
							$App->ErrorPageHandler(501);
							return;
						}
					}
					else
						$args_to_pass[] = $val;
				}
			}
			
			$ret = $method_info->invokeArgs($inst, $args_to_pass);
			header('Content-Type: application/json');
			echo json_encode($ret);
		}
		
		protected function FindClass($App, $path)
		{
			$Config = $App->Config;
			$ns = $Config->API->ns;
			$class = str_replace('/','\\',$path);
			
			if( is_object($ns) )
			{
				$pos = strpos($class,'\\');
				$n = substr($class,0,$pos);
				$class = substr($class,$pos+1);
				if( !isset($ns->$n) )
					return false;
				
				$n = $ns->$n;
				
				if( \System\AutoLoader::ClassExists($n.'\\'.$class) )
					return $n.'\\'.$class;
			}
			else if( is_array($ns) )
			{
				foreach( $ns as $n )
				{
					$ret = \System\AutoLoader::ClassExists($n.'\\'.$class);
					if( $ret !== false )
						return $n.'\\'.$class;
				}
			}
			else if( \System\AutoLoader::ClassExists($ns.'\\'.$class) )
				return $ns.'\\'.$class;
			
			return false;
		}
	}
}