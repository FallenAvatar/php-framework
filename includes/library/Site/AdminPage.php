<?

namespace Site
{
	class AdminPage extends \Core\Web\BaseObject
	{
		protected $content;
		public function _getContent() { return $this->content; }
		
		protected $Title;
		public function _getTitle() { return $this->Title; }
		
		protected $StyleSheets;
		public function _getStyleSheets() { return $this->StyleSheets; }
		
		protected $JSFiles;
		public function _getJSFiles() { return $this->JSFiles; }
		
		protected $Styles;
		public function _getStyles() { return $this->Styles; }
		
		protected $Options;
		public function _getOptions() { return $this->Options; }
		
		public function __construct()
		{
			parent::__construct();
			
			$this->Title = null;
			
			$this->StyleSheets = array();
			$this->JSFiles = array();
			$this->Styles = array();
			$this->Options = array();
		}
		
		public function LoadPath($path)
		{
			ob_start();
			include_once($path);
			$this->content = ob_get_contents();
			ob_end_clean();
		}
		
		public function SetOption($name,$value)
		{
			$this->Options[$name]=$value;
		}

		public function GetOption($name,$default='')
		{
			if( !isset($this->Options[$name]) )
				return $default;
				
			return $this->Options[$name];
		}
		
		public function AddStyleSheet($path,$rel='StyleSheet',$media=null)
		{
			$this->StyleSheets[] = array(
				'path' => $path,
				'rel' => $rel,
				'media' => $media
			);
		}
		
		public function AddJSFile($path,$cond='')
		{
			$this->JSFiles[] = array(
				'cond' => $cond,
				'path' => $path
			);
		}
		
		public function AddStyle($selector,$arrStyles)
		{
			if( isset($this->Styles[$selector]) )
				$this->Styles[$selector] = array_merge($this->Styles[$selector],$arrStyles);
			else
				$this->Styles[$selector] = $arrStyles;
		}
	}
}