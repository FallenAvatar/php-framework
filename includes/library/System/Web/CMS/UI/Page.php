<?php

namespace System\Web\CMS\UI
{
class Page extends \System\Web\UI\Page
{
	private $data_page;
	
	public function __construct($data)
	{
		parent::__construct();
		$this->data_page = $data;
		$this->Layout = new \System\Web\CMS\UI\Layout(new \System\Web\CMS\Data\Layout($this->data_page->layout_id));
		$this->Layout->SetTitle($this->data_page->title);
	}

	public function SetLayout($name)
	{
		if( !isset($this->Layout) )
			throw new \Exception('You can not set a Layout after Layouts have been disabled for the page.');
		return $this->Layout->SetLayout($name);
	}
	
	public function Render($path)
	{
		$content = $this->RenderContent(null);
		
		if( !isset($this->Layout) || $this->Layout == '' || $this->Layout == '_none' )
		{
			foreach($content as $area)
				echo $area;
		}
		else
			$this->Layout->Render($content);
	}
	
	private function RenderContent($unused)
	{
		$content = array();
		$areas = \System\Web\CMS\Data\LayoutArea::FindByLayoutID($this->data_page->layout_id);

		foreach($areas as $area)
		{
			$pc = new \System\Web\CMS\Data\PageContent(array(
				'page_id' => $this->data_page->id,
				'area_id' => $area->id
			));

			$content[$area->name] = $pc->content;
		}

		return $content;
	}
}
}