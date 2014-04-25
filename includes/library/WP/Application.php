<?php

namespace WP
{
	class Application extends \System\Web\Application
	{
		protected $plugins;
		protected $theme;
		protected $runCalled;
		
		protected function BuildDirs()
		{
			$this->AddDir('Root', $this->GetRootDir());
			$this->AddDir('Includes', $this->Dirs->Root.WPINC.DS);
			$this->AddDir('Content', WP_CONTENT_DIR.DS);
			$this->AddDir('Plugins', WP_PLUGIN_DIR.DS);
			$this->AddDir('Themes', get_theme_root().DS);
			
			$upload_dir = wp_upload_dir();
			
			$this->AddDir('Uploads', $upload_dir['basedir'].DS);
			$this->AddDir('UploadsDate', $upload_dir['path'].DS);
			
			$this->AddDir('Library', $this->Dirs->Plugins.'thisguys-framework'.DS);
			
			$this->AddDir('Data', $this->Dirs->Content.'data'.DS);
			$this->AddDir('Cache', $this->Dirs->Data.'cache'.DS);
		}
		
		protected function GetRootDir()
		{
			return realpath(ABSPATH).DS;
		}
		
		protected function _init()
		{
			set_error_handler(array($this,'ErrorHandler'));
			set_exception_handler(array($this,'ExceptionHandler'));
			
			$this->runCalled = false;

			$this->_loadConfig();
			//$this->_fixPhp();

			// TODO: Replace these with WP Versions?
			//$this->Request = new \System\Web\HttpRequest();
			//$this->Response = new \System\Web\HttpResponse();
		}
		
		protected function _loadConfig()
		{
			global $table_prefix;
			$config = array();
			
			$config['System\Data'] = array();
			$config['System\Data']['defaultDbName'] = 'wordpress';
			
			$config['Database'] = array();
			$config['Database']['wordpress'] = array();
			$config['Database']['wordpress']['driver'] = 'MySql';
			$config['Database']['wordpress']['host'] = DB_HOST;
			$config['Database']['wordpress']['user'] = DB_USER;
			$config['Database']['wordpress']['pass'] = DB_PASSWORD;
			$config['Database']['wordpress']['db_name'] = DB_NAME;
			$config['Database']['wordpress']['tbl_prefix'] = $table_prefix;
			
			$config['Framework'] = array();
			$config['Framework']['base_url'] = '';
			
			$this->Config = new \System\DynObject($config, true);
			
			$this->plugins = array();
			$this->theme = null;
		}
		
		protected function _run()
		{
			
			// TODO: Register WP Hooks/Actions/Filters as needed
			add_action('plugins_loaded', '\WP\Application::Action_PluginsLoaded_S');
		}
		
		protected function _runPlugin($name)
		{
			if( !\System\Autoloader::CanLoadClass($name) )
				throw new \System\Exception('Plugin class ['.$name.'] could not be loaded. Did you forget to register with \System\Autoloader?');
				
			$p = new $name();
			
			if( !($p instanceof \WP\Plugin) )
				throw new \System\Exception('Plugin class ['.$name.'] does not inherit from \WP\Plugin.');
			
			$p->OnInit();
			
			return $p;
		}
		
		public function RegisterPlugin($strName)
		{
			if( in_array($strName, $this->plugins) )
				return;
			
			if( !$this->runCalled )
				$this->plugins[] = $strName;
			else
				$this->plugins[] = $this->_runPlugin($strName);
		}
		
		public function RegisterCurrentTheme()
		{
			if( isset($this->theme) )
				return;
				
			$this->theme = Theme::CurrentTheme();
		}
		
		public static function Action_PluginsLoaded_S()
		{
			\System\Application::GetInstance()->Action_PluginsLoaded();
		}
		
		public function Action_PluginsLoaded()
		{
			// TODO: Fire any Plugins that registered themselves with us
			$plugins = array();
			foreach($this->plugins as $name)
				$plugins[] = $this->_runPlugin($name);
				
			$this->plugins = $plugins;
			
			$this->runCalled = true;
			
			//echo '<pre>'.print_r($this,true).'</pre>'; exit();
		}
	}
}