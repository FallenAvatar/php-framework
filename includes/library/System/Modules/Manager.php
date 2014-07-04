<?

namespace System\Modules
{
	class Manager extends System\Object
	{
		private static $modules;
		
		public static function FindModules()
		{
			$app = \System\Application::GetInstance();
			
			self::$modules = array();
			
			$dir = $app->Dirs->Modules;
			$d = dir($dir);
			
			while( false !== ($e = $d->read()) )
			{
				if( $e == '.' || $e == '..' )
					continue;
					
				$entry = \System\IO\Path::Combine($dir, $e);
				$config_file = \System\IO\Path::Combine($entry, 'module.json');
				
				if( !\System\IO\File::Exists($config_file) )
					continue;
					
				$config = new \System\Configuration\Config(json_decode(\System\IO\File::ReadAllText($file), true));
				
				// TODO: Check format of config object
				$mod_class = $config->code->main_class;
				$mod = new $mod_class($config);
				
				$mod->Init();
				
				self::$modules[$config->safe_name] = $mod;
			}
		}
		
		public static function LoadModules()
		{
			foreach( self::$modules as $name => $mod )
			{
				$mod->Load();
			}
		}
		
		public static function UnloadModules()
		{
			foreach( self::$modules as $name => $mod )
			{
				$mod->Unload();
			}
		}
		
		public static function RegisterControl($mod_name, $control_name, $options = array())
		{
		}
	}
}