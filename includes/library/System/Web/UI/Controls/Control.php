<?php

namespace System\Web\UI\Controls
{
	abstract class Control
	{
		public static function Factory($type,$attirbs=array())
		{
			global $Config;
			
			if( !isset($type) || $type == '' )
			{
				closedir($dh);
				return null;
			}
			
			$path=System_Path::Combine($Config['Dirs']['lib'],'System','Web','UI','Control');
			
			if( !is_dir($path) )
			{
				closedir($dh);
				return null;
			}
				
			$dh = opendir($path);
			while( ($file = readdir($dh)) !== FALSE )
			{
				if( is_dir($file) )
					continue;
				
				$idx = strrpos($file,'.');
				$f = substr($file,0,$idx);
				if( $f == $type )
				{
					include_once(System_Path::Combine($path,$file));
					
					$className = "System\\Web\\Control\\UI\\".$type;
					if( !class_exists($className,FALSE) )
					{
						closedir($dh);
						return null;
					}
					
					closedir($dh);
					return new $className($type,$attribs);
				}
			}
			
			closedir($dh);
			return null;
		}
		
		protected $_type;
		protected $_attribs;
		protected $_styles;
		
		public function __construct($type, $attribs=array(), $styles=array())
		{
			$this->_type = strtolower($type);
			$this->_attribs=$attribs;
			$this->_styles=$styles;
		}
		
		public function Render($tabs=0)
		{
			$ret = str_repeat('\t',$tabs);
			$ret .= '<'.$this->_type;
			
			$ret .= $this->RenderAttribs();
				
			$ret .= $this->RenderStyles();
			
			if( $this->AllowedCloseType() & self::CloseType_Short > 0 )
				$ret .= ' />';
			else
				$ret .= '></'.$this->_type.'>';
				
			return $ret;
		}
		
		protected function RenderAttribs()
		{
			$ret='';
			
			foreach($this->_attribs as $k => $v)
				$ret .= ' '.$k."'"".$v.""";
			
			return $ret;
		}
		
		protected function RenderStyles()
		{
			$ret='';
			
			foreach($this->_styles as $k => $v)
				$ret .= $k.":".$v.";";
			
			if( $ret != '' )
				$ret = " style=\"".$ret."\"";
			
			return $ret;
		}
		
		protected const CloseType_Short=1;
		protected const CloseType_Full=2;
		
		protected function AllowedCloseType()
		{
			return self::CloseType_Short & self::CloseType_Full;
		}
	}
}