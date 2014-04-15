<?php

namespace System\Web\UI\Controls
{
	class Input : \System\Web\UI\Controls\Control
	{
		private $_type;
		public function __construct($type='text',$value='')
		{
			parent::__construct('input',(($value=='') ? array() : array('value'=>$value)));
			$this->_type=$type;
		}
		
		protected function AllowedCloseType()
		{
			return parent::CloseType_Short;
		}
	}
}