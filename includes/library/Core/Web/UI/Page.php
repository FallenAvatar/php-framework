<?php

namespace Core\Web\UI
{
	class Page extends \Core\Web\BaseObject
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
		
		private $render_regions;
		private $render_content;

		public function __construct()
		{
			parent::__construct();
			
			$this->Title = null;
			
			$this->StyleSheets = array();
			$this->JSFiles = array();
			$this->Styles = array();
			$this->Options = array();
			
			$this->Layout = 'default';
		}

		public function SetPath($path)
		{
			$this->AbsolutePath = $path;
		}
		
		protected function SetLayout($name)
		{
			if( isset($name) && $name != '' && $name != '_none' )
				$this->Layout = $name;
			else
				$this->Layout = null;
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
			
			$content = $this->RenderContent();
			
			if( !isset($this->Layout) || $this->Layout == '' || $this->Layout == '_none' )
			{
				foreach($content as $name => $area)
					echo $area;
			}
			else
			{
				$l = new Layout($this->Layout);
				$l->SetPage($this);
				
				$l->Render($content);
			}
				
			$this->OnPostRender();
		}
		
		private function RenderContent()
		{
			$this->render_content = array();
			$this->render_regions = array();
			
			$this->StartRegion('default');
			
			require_once($this->AbsolutePath);
			
			$this->EndRegion();
			
			return $this->render_content;
		}
		
		protected function StartRegion($name)
		{
			array_push($this->render_regions, $name);
			ob_start();
		}
		
		protected function EndRegion()
		{
			$content = ob_get_contents();
			ob_end_clean();
			
			$name = array_pop($this->render_regions);
			
			$this->render_content[$name] = $content;
		}

		// "Virtual" functions for specific page classes to override
		protected function OnInit() {}
		protected function OnLoad() {}
		protected function OnPreRender() {}
		protected function OnPostRender() {}
	}
}
