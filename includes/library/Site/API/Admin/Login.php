<?

namespace Site\API\Admin
{
	class Login extends \Core\Web\BaseObject
	{
		public function Post($username, $password)
		{
			$user = \Site\Systems\Security::Login($username, $password);
			if( !$user )
				return array('error' => true, 'message' => 'Invalid Username/Password.');
			
			return array('error' => false, 'redirect' => '/admin');
		}
	}
}