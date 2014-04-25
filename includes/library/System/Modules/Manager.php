<?

namespace System\Modules
{
	class Manager extends System\Object
	{
		public static function FindModules()
		{
			$app = \System\Application::GetInstance();
			
			$ret = array();
			
			$dir = $app->Dirs->Modules;
			$d = dir($dir);
			
			while( false !== ($e = $d->read()) )
			{
				if( $e == '.' || $e == '..' )
					continue;
					
				$entry = \System\IO\Path::Combine($dir, $e);
				$config = \System\IO\Path::Combine($entry, 'module.json');
				
				if( !\System\IO\File::Exists($config) )
					continue;
					
				
			}
			
			return $ret;
		}
	}
}