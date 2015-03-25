<?

namespace Core\Log
{
	class Wrapper extends \Core\Log\Handler\BaseHandler implements \Core\Log\Handler\IHandler
	{
		private $loggers;
		public function __construct($loggers)
		{
			if( !is_array($loggers) || count($loggers) <= 0 )
				$this->loggers = array();
			else
				$this->loggers = $loggers;
		}
		
		public function Log($level, $message, $source, $details = null)
		{
			foreach($this->loggers as $logger)
			{
				$logger->Log($level, $message, $source, $details);
			}
		}
	}
}