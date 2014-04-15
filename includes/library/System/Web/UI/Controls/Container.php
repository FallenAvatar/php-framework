<?php

namespace System\Web\UI\Controls
{
	class Container : \System\Web\UI\Controls\Control
	{
		protected $_children;
		
		public function __construct($type, $attribs=array(), $styles=array())
		{
			parent::__construct($type,$attribs,$styles);
			$this->_children = array();
		}
		
		public function AddChild(System\Web\UI\Controls\Control $con)
		{
			$this->_controls[] = $con;
		}
		
		public function Render($tabs=0)
		{
			$ret = str_repeat('\t',$tabs);
			$ret .= '<'.$this->_type;
			
			$ret .= $this->RenderAttribs();
				
			$ret .= $this->RenderStyles();
			
			$ret .= ">";
			
			if( count($this->_controls) > 0 )
			{
				$ret .= "\n";
			
				foreach($this->_controls as $c)
					$ret .= $c->Render($tabs+1);
			}
			
			$ret .= "</".$this->_type.">\n";
				
			return $ret;
		}
		
		protected function AllowedCloseType()
		{
			return parent::CloseType_Full;
		}
	}
}