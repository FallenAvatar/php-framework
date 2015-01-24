<?

namespace Core\UI
{
	class Page extends BaseObject
	{
		protected $AbsolutePath;
		
		protected $Layout;
		
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
		
		private $ContentSections;
		private $RenderedContents;

		public function __construct()
		{
			parent::__construct();
			
			$this->SetLayout( $this->App->Config->Core->UI->default_layout );
			
			$this->Title = '';
			$this->StyleSheets = array();
			$this->JSFiles = array();
			$this->Styles = array();
			$this->Options = array();
			
			$this->ContentSections = array();
			$this->RenderedContents = array();
		}

		public function SetPath($path)
		{
			$this->AbsolutePath = $path;
		}
		
		protected function SetLayout($name)
		{
			if( isset($name) && $name != '' )
				$this->Layout = new Layout($name, $this);
			else
				$this->Layout = null;
		}

		public function Init()
		{
			$this->OnInit();
		}

		public function Load()
		{
			// Databind controls
			$this->OnLoad();
		}

		public function Render()
		{
			$this->OnPreRender();
			
			$this->RenderContent();
			
			if( $this->Layout == null )
			{
				foreach($this->ContentSections as $area)
					echo $area;
			}
			else
				$this->Layout->Render($this->ContentSections);
			
			$this->OnPostRender();
		}
		
		protected function RenderContent()
		{
			$this->StartSection('default');
			
			require_once($this->AbsolutePath);
			
			// Any number of sections can be left open, make sure we close them all
			while( count($this->ContentSections) > 0 )
				$this->EndSection();
		}
		
		protected function StartSection($name)
		{
			array_push($this->ContentSections, $name);
			ob_start();
		}
		
		protected function EndSection()
		{
			if( count($this->ContentSections) <= 0 )
				throw new \Exception('All content sections have already ended.');
				
			$content = ob_get_contents();
			ob_end_clean();
			
			$name = array_pop($this->ContentSections);
			
			if( isset($content) && trim($content) != '' )
				$this->RenderedContents[$name] = $content;
			else
				$this->RenderedContents[$name] = null;
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
			return $this->RenderedContents[$name];
		}
		
		public function GetOption($name,$default='')
		{
			if( !isset($this->Options[$name]) )
				return $default;
				
			return $this->Options[$name];
		}
		
		public function SetOption($name,$value)
		{
			$this->Options[$name]=$value;
		}

		// "Virtual" functions for specific page classes to override
		public function OnInit() {}
		public function OnLoad() {}
		public function OnPreRender() {}
		public function OnPostRender() {}
	}
}
