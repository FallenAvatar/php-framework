<?

namespace Core
{
	class CoreException extends \Core\Exception
	{
		public function __construct($msg = 'A system error has occured.')
		{
			parent::__construct($msg);
		}
	}
}