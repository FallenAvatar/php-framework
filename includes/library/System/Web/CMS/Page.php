<?php

namespace System\Web\CMS
{
class Page extends \System\Web\Page
{
	protected $data_page;

	public function SetDataPage($page)
	{
		$this->data_page = $page;
	}

	public function Init()
	{
		$this->ui_page = new \System\Web\CMS\UI\Page($this->data_page);
		$this->OnInit();
	}

	public function Load()
	{
		// Databind controls
		$this->OnLoad();
	}

	public function Render()
	{
		$this->OnPreRender();

		// Render Content from database
		$this->ui_page->Render(null);

		$this->OnPostRender();
	}
}
}