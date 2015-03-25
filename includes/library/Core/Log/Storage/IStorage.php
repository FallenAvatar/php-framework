<?

namespace Core\Log\Storage
{
	interface IStorage
	{
		public function Log($level, $message, $source, $details);
	}
}