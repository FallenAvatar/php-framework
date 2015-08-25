<?

namespace Site\API\Admin
{
	class Page extends \Core\Web\BaseObject
	{
		public function Load($page)
		{
			$user = \Site\Systems\Security::GetUser();
			/*if( !isset($user) )
				return array('error' => true, 'message' => 'You are not logged in.');*/
			
			$base_path = \Core\IO\Path::Combine($this->App->Dirs->Root,'admin','pages');
			$path = \Core\IO\Path::Combine($base_path, $page.'.phtml');
			
			if( !is_file($path) )
			{
				$path2 = \Core\IO\Path::Combine($base_path, $page, 'index.phtml');
				if( !is_file($path2) )
					return array('error' => true, 'message' => 'Page not found', 'url' => $page);
				else
					$path = $path2;
			}
			
			$page = new \Site\AdminPage();
			$page->LoadPath($path);
			
			return array('error' => false, 'content' => $page->Content, 'title' => $page->Title, 'stylesheets' => $page->StyleSheets, 'jsFiles' => $page->JSFiles, 'styles' => $page->Styles, 'options' => $page->Options);
		}
	}
}
