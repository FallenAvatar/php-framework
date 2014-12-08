<?

namespace Core\Handlers
{
	interface IRequestHandler
	{
		public function CanHandleRequest($App);
		public function ExecuteRequest($App);
	}
}