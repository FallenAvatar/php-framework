<?

namespace Core\Autoload
{
	class StandardAutoloader
	{
		protected static $libPath;
		
		public static function Init($libPath)
		{
			self::$libPath = $libPath;
			spl_autoload_register('\Core\Autoload\StandardAutoloader::LoadClass');
		}
		
		public static function ClassExists($name)
		{
			return (self::IsClassLoaded($name) || self::CanLoadClass($name));
		}
		
		public static function IsClassLoaded($name)
		{
			$classes = get_declared_classes();

			if( $name[0] == '\\' )
				$name = substr($name,1);

			foreach($classes as $class)
				if( $class == $name )
					return true;

			return false;
		}

		public static function CanLoadClass($name)
		{
			$path = self::$libPath . str_replace("\\",DS,$name) . '.php';
			
			return (file_exists($path) && is_file($path));
		}
		
		public static function LoadClass($className)
		{
			$path = self::$libPath . str_replace("\\",DS,$className) . '.php';
			
			if( file_exists($path) && is_file($path) )
			{
				include_once($path);
				return;
			}
			
			$loaders = spl_autoload_functions();
			
			if( count($loaders) > 1 || (is_array($loaders[0]) && count($loaders[0]) > 1) )
				return false;
			
			throw new \Core\Autoload\Exception('Unable to load class ['.$className.'], expected path was ['.$path.'].');
		}
	}
}