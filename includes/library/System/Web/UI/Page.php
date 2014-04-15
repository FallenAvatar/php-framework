<?php

namespace System\Web\UI
{
	class Page extends \System\Object
	{
		private $Layout;
		private $Application;

		protected $Get;
		protected $Post;
		protected $Session;
		protected $Cookies;
		protected $Files;
		
		public function __construct()
		{
			$this->Layout = new \System\Web\UI\Layout('default');
			$this->Application = \System\Web\Application::GetInstance();

			$this->Get = $_GET;
			$this->Post = $_POST;
			$this->Session = $_SESSION;
			$this->Cookies = $_COOKIE;
			$this->Files = $_FILES;
		}

		public function DisableLayout()
		{
			$this->Layout = null;
		}

		public function SetLayout($name)
		{
			if( !isset($this->Layout) )
				throw new \Exception('You can not set a Layout after Layouts have been disabled for the page.');
			return $this->Layout->SetLayout($name);
		}
		
		public function SetTitle($title)
		{
			return $this->Layout->SetTitle($title);
		}

		public function SetOption($name,$value)
		{
			return $this->Layout->SetOption($name,$value);
		}

		public function GetOption($name,$default='')
		{
			return $this->Layout->GetOption($name);
		}
		
		public function AddStyleSheet($path,$rel='StyleSheet',$media=null)
		{
			return $this->Layout->AddStyleSheet($path,$rel,$media);
		}
		
		public function AddJSFile($path,$cond='')
		{
			return $this->Layout->AddJSFile($path,$cond);
		}
		
		public function AddStyle($selector,$arrStyles)
		{
			return $this->Layout->AddStyle($selector, $arrStyles);
		}
		
		public function Render($path)
		{
			$content = $this->RenderContent($path);
			
			if( !isset($this->Layout) || $this->Layout == '' || $this->Layout == '_none' )
			{
				foreach($content as $area)
					echo $area;
			}
			else
				$this->Layout->Render($content);
		}
		
		private function RenderContent($_path)
		{
			global $_GET,$_POST,$_SESSION;
			ob_start();
			
			require_once($_path);
			
			$content = array();
			
			$content['default'] = ob_get_contents();
			ob_end_clean();
			
			return $content;
		}
		
		private function RenderTemplate($content)
		{
			global $_GET,$_POST,$_SESSION;
			ob_start();
			require_once($this->Config['Layouts'][$this->template]);
			ob_end_flush();
		}
		
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