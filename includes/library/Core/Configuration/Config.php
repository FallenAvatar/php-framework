<?

namespace Core\Configuration
{
	class Config extends \Core\DynObject
	{
		public function __construct($config)
		{
			parent::__construct($config, true);
		}
	}
}