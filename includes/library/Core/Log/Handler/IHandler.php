<?

namespace Core\Log\Handler
{
	interface IHandler
	{
		public function Log($level, $message, $source, $details = null);
		
		public function Debug($message, $details);
		public function Info($message, $details);
		public function Warn($message, $details);
		public function Error($message, $details);
	}
}