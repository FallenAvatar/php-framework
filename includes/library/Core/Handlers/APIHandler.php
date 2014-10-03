<?

namespace Core\Handlers
{
	class APIHandler extends \Core\Object implements IRequestHandler
	{
		protected $path;
		
		public function CanHandleRequest($App)
		{
			if( $App->Config->API->enabled !== true )
				return false;
			
			$this->path = $App->Request->Url->Path;
			$api_path = $App->Config->API->base_url;
			
			$path_start = substr($this->path, 0, strlen($api_path));
			if( $path_start != $api_path )
				return false;
				
			$this->path = substr($this->path, strlen($api_path));

			return true;
		}

		public function ExecuteRequest($App)
		{
			$ret = $this->GetResponse($App);
			
			if( !isset($ret) )
				$ret = array('error' => true, 'statusCode' => 500, 'message' => 'An unknown error has occured');
			
			if( isset($ret['statusCode']) )
			{
				$txt = '';
				if( isset(\Core\Application::$HttpErrorCodeText[$ret['statusCode']]) )
				{
					$txt = ' '.\Core\Application::$HttpErrorCodeText[$ret['statusCode']];
					if( !isset($ret['message']) )
						$ret['message'] = \Core\Application::$HttpErrorCodeText[$ret['statusCode']];
				}
				
				header('HTTP/1.0 '.$ret['statusCode'].$txt);
			}
			
			header('Content-Type: application/json');
			echo json_encode($ret);
		}
		
		protected function GetResponse($App)
		{
			$pos = strrpos($this->path,'/');
			if( $pos === false )
				return array('error' => true, 'statusCode' => 404, 'message' => 'Invalid API Path.');
			
			$method = substr($this->path, $pos+1);
			$this->path = substr($this->path,0,$pos);
			
			if( !isset($method) || trim($method) == '' || !isset($this->path) || trim($this->path) == '' )
				return array('error' => true, 'statusCode' => 404, 'message' => 'Invalid API Path.');
			
			$class = $this->FindClass($App, $this->path);
			
			if( $class === false )
				return array('error' => true, 'statusCode' => 404, 'message' => 'The requested API class ['.$this->path.'] could not be found.');
			
			if( !method_exists($class, $method) )
				return array('error' => true, 'statusCode' => 404, 'message' => 'The requested API class does not contain a method ['.$method.'].');
			
			$method_info = new \ReflectionMethod($class,$method);
			$method_info->setAccessible(true);
			
			$inst = null;

			if( !$method_info->isStatic() )
				$inst = new $class();
			
			if( $method_info->isPublic() )
			{
			}
			else if( $method_info->isProtected() )
			{
				$signature = strtolower($_GET['signature']);
				
				if( !isset($signature) || trim($signature) == '' )
					return array('error' => true, 'statusCode' => 403, 'message' => 'The requested API method requires a signature to be passed in the query string.');

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
					return array('error' => true, 'statusCode' => 403, 'message' => 'Invalid signature supplied.');
			}
			else if( $method_info->isPrivate() )
			{
				$user = \Site\Security::GetUser();
				if( !isset($user) )
					return array('error' => true, 'statusCode' => 403, 'message' => 'No logged in user in the current session.');
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
							return array('error' => true, 'statusCode' => 400, 'message' => 'Missing required parameter ['.$name.'].');
					}
					else
						$args_to_pass[] = $val;
				}
			}
			
			return $method_info->invokeArgs($inst, $args_to_pass);
		}
		
		protected function FindClass($App, $path)
		{
			$ns = $App->Config->API->ns;
			$class = str_replace('/','\\',$path);
			
			if( is_object($ns) )
			{
				$pos = strpos($class,'\\');
				$n = substr($class,0,$pos);
				$class = substr($class,$pos+1);
				if( !isset($ns->$n) )
					return false;
				
				$n = $ns->$n;
				
				if( \Core\Autoload\StandardAutoLoader::ClassExists($n.'\\'.$class) )
					return $n.'\\'.$class;
			}
			else if( is_array($ns) )
			{
				foreach( $ns as $n )
				{
					$ret = \Core\Autoload\StandardAutoLoader::ClassExists($n.'\\'.$class);
					if( $ret !== false )
						return $n.'\\'.$class;
				}
			}
			else if( \Core\Autoload\StandardAutoLoader::ClassExists($ns.'\\'.$class) )
				return $ns.'\\'.$class;
			
			return false;
		}
	}
}