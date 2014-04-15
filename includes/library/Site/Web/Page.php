<?php

namespace Site\Web
{
class Page extends \System\Web\Page
{
	protected $Site;
	
	public function Init()
	{
		parent::Init();
		$this->Site = \Site\Data\Site::FindByDomain($this->Request->URI->Host);
	}

}
}