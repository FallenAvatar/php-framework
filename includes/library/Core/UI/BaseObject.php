<?

namespace Core\UI
{
	class BaseObject extends \Core\Object
	{
		protected $Get;
		protected $Post;
		protected $Session;
		protected $Cookies;
		protected $Files;
		protected $Server;
		
		protected $OutHeaders;
		
		public function __construct()
		{
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
		
		// Helper Functions
		public function GetMethod()
		{
			global $_SERVER;
			return strtoupper($_SERVER['REQUEST_METHOD']);
		}
	
		public function IsPost()
		{
			return ($this->GetMethod() == 'POST');
		}

		public function IsGet()
		{
			return ($this->GetMethod() == 'GET');
		}

		public function IsPut()
		{
			return ($this->GetMethod() == 'PUT');
		}

		public function IsDelete()
		{
			return ($this->GetMethod() == 'DELETE');
		}
		
		public function Redirect($url)
		{
			header('Location: '.$url);
			exit();
		}
	}
}