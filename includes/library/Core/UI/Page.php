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

		public function __construct()
		{
			$this->Layout = new Layout('default');
		}

		public function SetPath($path)
		{
			$this->AbsolutePath = $path;
		}
		
		protected function SetLayout($name)
		{
			if( isset($name) && $name != '' && $name != '_none' )
				$this->Layout = new Layout('default');
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
			$this->ui_page->Render($this->AbsolutePath);
			$this->OnPostRender();
		}

		// "Virtual" functions for specific page classes to override
		public function OnInit() {}
		public function OnLoad() {}
		public function OnPreRender() {}
		public function OnPostRender() {}
	}
}
