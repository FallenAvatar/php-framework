<?

namespace Core\Web
{
	class BaseObject extends \Core\Object
	{
		protected $App;
		
		protected $Get;
		protected $Post;
		protected $Session;
		protected $Cookies;
		protected $Files;
		protected $Server;
		
		protected $OutHeaders;
		
		public function __construct()
		{
			$this->App = \Core\Application::GetInstance();
			
			$this->Get = $_GET;
			$this->Post = $_POST;
			$this->Session = $_SESSION;
			$this->Cookies = $_COOKIE;
			$this->Files = $_FILES;
			$this->Server = $_SERVER;
			
			$this->OutHeaders = array();
		}
		
		public function GetValue($name)
		{
			if( isset($this->Post[$name]) )
				return $this->Post[$name];
			
			return $this->Get[$name];
		}
		
		public function Redirect($url)
		{
			$this->Header('Location', $url, true);
			exit();
		}

		public function Header($name, $value, $overwrite = false)
		{
			if( !$overwrite && in_array($name, $this->OutHeaders) )
				return;
			
			$this->OutHeaders[] = $name;
			header($name.': '.$value);
		}
		
		// Helper Properties
		public function _getRequestMethod()
		{
			return strtoupper($this->Server['REQUEST_METHOD']);
		}
	
		public function _getIsPost()
		{
			return ($this->RequestMethod == 'POST');
		}

		public function _getIsGet()
		{
			return ($this->RequestMethod == 'GET');
		}

		public function _getIsPut()
		{
			return ($this->RequestMethod == 'PUT');
		}

		public function _getIsDelete()
		{
			return ($this->RequestMethod == 'DELETE');
		}
	}
}
