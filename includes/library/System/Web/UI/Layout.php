<?php

namespace System\Web\UI
{
	class Layout extends \System\Object
	{
		public $Title;
		public $Name;
		protected $StyleSheets;
		protected $JSFiles;
		protected $Styles;
		protected $Options;
		
		protected $Application;

		protected $Get;
		protected $Post;
		protected $Session;
		protected $Cookies;
		protected $Files;
		
		protected $content;
		
		public function __construct($name)
		{
			$this->Name = $name;

			$this->StyleSheets = array();
			$this->JSFiles = array();
			$this->Styles = array();
			$this->Options = array();
			
			$this->Application = \System\Web\Application::GetInstance();

			$this->Get = $_GET;
			$this->Post = $_POST;
			$this->Session = $_SESSION;
			$this->Cookies = $_COOKIE;
			$this->Files = $_FILES;
		}

		public function SetTitle($title)
		{
			$this->Title = $title;
		}
		
		public function SetOption($name,$value)
		{
			$this->Options[$name]=$value;
		}
		
		public function AddStyleSheet($path, $rel='StyleSheet', $media=null)
		{
			$t = array();
			$t['path']=$path;
			$t['rel']=$rel;
			$t['media']=$media;
			
			$this->StyleSheets[]=$t;
		}
		
		public function AddJSFile($path,$cond)
		{
			$this->JSFiles[]=array('cond'=>$cond,'path'=>$path);
		}
		
		public function AddStyle($selector,$arrStyles)
		{
			if( isset($this->Styles[$selector]) )
				$this->Styles[$selector] = array_merge($this->Styles[$selector],$arrStyles);
			else
				$this->Styles[$selector] = $arrStyles;
		}
		
		public function GetOption($name,$default='')
		{
			if( !isset($this->Options[$name]) )
				return $default;
				
			return $this->Options[$name];
		}
		
		public function GetStyleSheets()
		{
			$ret="\n";
			
			foreach( $this->StyleSheets as $stylesheet )
				$ret .= "\t".'<link href="'.$stylesheet['path'].'" rel="'.$stylesheet['rel'].'"'.((isset($stylesheet['media'])) ? ' media="'.$stylesheet['media'].'"' : '').' type="text/css" />'."\n";
				
			return $ret;
		}
		
		public function GetJSFiles()
		{
			$ret = "\n";
			
			foreach( $this->JSFiles as $js )
			{
				$cond = $js['cond'];
				$ret .= "\t";

				if( isset($cond) && trim($cond) != '' )
					$ret .= '<!--['.$cond.']>';

				$ret .= '<script src="'.$js['path'].'" type="text/javascript"></script>';

				if( isset($cond) && trim($cond) != '' )
					$ret .= '<![endif]-->';

				$ret .= "\n";
			}
				
			return $ret;
		}
		
		public function GetStyles()
		{
			if( count($this->Styles) == 0 )
				return "";
				
			$ret = "\n\t<style type=\"text/css\">\n\t<!--\n";
			
			foreach( $this->Styles as $k => $v )
			{
				$ret .= "\t\t".$k."{";
				
				foreach( $v as $ik => $iv )
					$ret .= $ik.":".$iv.";";
				
				$ret .= "}\n";
			}
			
			$ret .= "\t-->\n\t</style>\n";
			
			return $ret;
		}
		
		public function GetContent($name)
		{
			return $this->content[$name];
		}
		
		public function SetLayout($name)
		{
			if( isset($name) && $name != '' && $name != '_none' )
			{
				if( !is_file($this->Application->Dirs->Layouts.$name.'.phtml') )
					throw new \Exception("Layout file for [".$name."] (".$this->Application->Dirs->Layouts.$name.'.phtml'.") not found.");
			}
			$this->Name = $name;
		}
		
		public function Render($content)
		{
			$this->content = $content;
			$name = $this->Name;
			ob_start();
			require_once($this->Application->Dirs->Layouts.$this->Name.'.phtml');
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