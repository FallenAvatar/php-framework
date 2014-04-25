<?

namespace System
{
	class Module extends System\Object
	{
		protected $name;
		protected function _getName() { return $this->name; }
		
		protected $description;
		protected function _getDescription() { return $this->description; }
		
		protected $author;
		protected function _getAuthor() { return $this->author; }
		
		protected $website;
		protected function _getWebsite() { return $this->website; }
		
		protected $safe_name;
		protected function _getSafeName() { return $this->safe_name; }
		
		protected $version;
		protected function _getVersion() { return $this->version; }
		
		public function __construct($config)
		{
			$this->name = $config->name;
			$this->description = $config->description;
			$this->author = $config->author;
			$this->website = $config->website;
			$this->safe_name = $config->safe_name;
			$this->version = $config->version;
		}
		
		public function Init()
		{
			$this->OnInit();
		}
		public function OnInit() {}
		
		public function Load()
		{
			$this->OnLoad();
		}
		public function OnLoad() {}
		
		public function Unload()
		{
			$this->OnUnload();
		}
		public function OnUnload() {}
	}
}