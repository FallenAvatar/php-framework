<?php

namespace System\Web\CMS\UI
{
class Layout extends \System\Web\UI\Layout
{
	protected $data_layout;
	
	public function __construct($name)
	{
		if( $name instanceof \System\Web\CMS\Data\Layout )
		{
			$this->data_layout = $name;
			$name = '_none';
		}
		
		parent::__construct($name);
	}

	public function SetLayout($name)
	{
		if( isset($name) && $name != '' && $name != '_none' )
		{
			$layout = \System\Web\CMS\Data\Layout::FindByName($name);
			if( !isset($layout) )
				throw new \Exception("Layout [".$name."] not found in the database.");

			if( !is_file(\System\IO\Path::Combine($this->Application->Dirs->Layouts,$layout->path)) )
				throw new \Exception("Layout file for [".$name."] (".$this->Application->Dirs->Layouts.$name.'.phtml'.") not found.");

			$this->data_layout = $layout;
		}
		$this->Name = $name;
	}
	
	public function Render($content)
	{
		$this->content = $content;

		ob_start();
		require_once(\System\IO\Path::Combine($this->Application->Dirs->Layouts,$this->data_layout->path));
		ob_end_flush();
	}
}
}