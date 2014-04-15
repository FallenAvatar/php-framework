<?php

namespace System\Web\Template
{
class PageInfo
{
	public $TemplateHeader;
	public $DocType;
	public $PageType;
	
	public $Nodes;
	public $Controls;
	
	public function __construct()
	{
		$this->TemplateHeader=array();
		$this->Nodes=array();
		$this->Controls=array();
	}
}
}