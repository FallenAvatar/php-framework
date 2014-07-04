<?

namespace System
{
	class Module extends System\Object
	{
		protected $config;
		protected function _getName() { return $this->config->name; }
		protected function _getDescription() { return $this->config->description; }
		protected function _getAuthor() { return $this->config->author; }
		protected function _getWebsite() { return $this->config->website; }
		protected function _getSafeName() { return $this->config->safe_name; }
		protected function _getVersion() { return $this->config->version; }
		
		protected function _getCodeNamespaces() { return $this->config->code->namespaces; }
		
		public function __construct($config)
		{
			$this->config = $config;
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