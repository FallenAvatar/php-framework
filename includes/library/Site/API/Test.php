<?

namespace Site\API
{
	class Test extends \Core\Web\BaseObject
	{
		public function One($varOne, $two, $opt = 'asdf')
		{
			return array(
				'error' => false,
				'varOne' => $varOne,
				'two' => $two,
				'opt' => $opt,
			);
		}
		
		/*public function Setup() {
			$api = new \Site\Data\Security\API();
			$api->name = 'SleepVM';
			$api->api_key = \Core\Guid::NewGuid();
			
			$sk = '';
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789[]{};:,.<>/?!@#^&*()_-=+';
			$len = 128;
			
			for( $i=0; $i<$len; $i++ )
				$sk .= $chars[rand(0,strlen($chars)-1)];
			
			$api->secret_key = $sk;
			$api->Save();
			
			return array('error' => false);
		}*/
	}
}