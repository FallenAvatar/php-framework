<?

namespace Core\UI
{
	class Page extends BaseObject
	{
		protected $path;
		protected $ui_page;
		protected $Application;
		protected $Request;

		public function __construct()
		{
			$this->Application = \Core\Application::GetInstance();
			$this->Request = $this->Application->Request;

			$this->Init();
		}

		public function SetPath($path)
		{
			$this->path = $path;
		}

		public function Init()
		{
			$this->ui_page = new \System\Web\UI\Page();
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
			$this->ui_page->Render($this->path);
			$this->OnPostRender();
		}

		// "Virtual" functions for specific page classes to override
		public function OnInit() {}
		public function OnLoad() {}
		public function OnPreRender() {}
		public function OnPostRender() {}
	}
}