<?php

namespace System\Web\Template
{
class TextNode extends \System\Web\Template\Node
{
	public $InnerText;
	
	public function __construct($txt)
	{
		$this->Name='Text Node';
		$this->NodeType='text';
		
		$this->InnerText=$txt;
	}
	
	public function Render()
	{
		return $this->InnerText;
	}
}
}