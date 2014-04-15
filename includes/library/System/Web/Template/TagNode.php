<?php


namespace System\Web\Template
{
class TagNode extends \System\Web\Template\Node
{
	public $Children;
	public $CanSelfClose;
	
	public function __construct($name)
	{
		$this->Name=$name;
		$this->NodeType='tag';
		$this->CanSelfClose=false;
		
		$this->Children = array();
	}
	
	public function AddChild($child)
	{
		$this->Children[]=$child;
	}
	
	public function GetChild($idx)
	{
		return $this->Children[$idx];
	}
	
	public function RemoveChild($child)
	{
		$newArr=array();
		
		foreach($this->Children as $c)
			if($c !== $child)
				$newArr[]=$c;
		
		$this->Children=$newArr;
		
		return $child;
	}
	
	public function RemoveChildByIndex($idx)
	{
		$newArr=array();
		$cnt=count($this->Children);
		
		$save=null;
		
		for($i=0;$i<$cnt;$i++)
		{
			if($i!=$idx)
				$newArr[]=$this->Children[$i];
			else
				$save=$this->Children[$i];
		}
		
		$this->Children=$newArr;
		
		return $save;
	}
	
	public function Render($lvl=0)
	{
		$pad = str_repeat("\t",$lvl);
		$child=false;
		if( count($this->Children) > 0 )
			$child=true;
		
		$ret=$pad.'<'.$this->Name;
		
		if( isset($this->Attributes) && count($this->Attributes) > 0 )
		{
			foreach( $this->Attributes as $attr )
				$ret .= ' ' . $attr;
		}
		
		if( !$child && $this->CanSelfClose )
			$ret .= ' /';
		
		$ret .= '>'."\r\n";
		
		foreach( $this->Children as $child )
			$ret .= $child->Render($lvl+1);
		
		if( $child || !$this->CanSelfClose )
			$ret .= $pad.'</' . $this->Name . '>'."\r\n";
		
		return $ret;
	}
}
}