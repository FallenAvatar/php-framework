<?

namespace Core\UI
{
	class Layout extends BaseObject
	{
		protected $Name;
		public function _getName() { return $this->Name; }
		
		protected $Page;
		
		public function _getTitle() { return $this->Page->Title; }
		
		public function __construct($name, \Core\UI\Page $page)
		{
			parent::__construct();
			
			if( !is_file($this->Application->Dirs->Layouts.$name.'.phtml') )
				throw new \Exception("Layout file for [".$name."] (".$this->Application->Dirs->Layouts.$name.'.phtml'.") not found.");
				
			$this->Name = $name;
			$this->Page = $page;
		}
		
		public function GetStyleSheets()
		{
			return $this->Page->GetStyleSheets();
		}
		
		public function GetJSFiles()
		{
			return $this->Page->GetJSFiles();
		}
		
		public function GetStyles()
		{
			return $this->Page->GetStyles();
		}
		
		public function GetContent($name)
		{
			return $this->Page->GetContent($name);
		}
		
		public function GetOption($name,$default='')
		{
			return $this->Page->GetContent($name, $default);
		}
	}
}
