<?php

namespace Core\Data\Statement
{
	class Update extends \Core\Object
	{
		protected $_tbl;
		protected $_vals;
		protected $_cond;
		protected $_condVars;
		
		public function __construct($tbl,$cond,$condVars=array())
		{
			$this->_tbl=$tbl;
			$this->_cond=$cond;
			$this->_condVars=$condVars;
			$this->_vals=array();
		}
		
		public function Add($col,$val)
		{
			$this->_vals[$col]=$val;
		}
		
		public function Execute($condVars=array())
		{
			$db=\Core\Data\Database::Get();
			
			$sql="UPDATE ".$db->Delim($this->_tbl,\Core\Data\Database::Delim_Table)." SET ";
			$params = array();
			
			$first=true;
			foreach( $this->_vals as $col => $val )
			{
				if( !$first )
					$sql.=", ";

				$sql.=$db->Delim($col,\Core\Data\Database::Delim_Column)." = :".preg_replace('/\s/','_',preg_replace('/([^a-zA-Z0-9]*)/','',$col));
				$params[preg_replace('/\s/','_',preg_replace('/[^a-zA-Z0-9]*/','',$col))]=$val;
				
				$first = false;
			}
			
			if( isset($this->_cond) && $this->_cond != '' )
				$sql.=" WHERE ".$this->_cond;
				
			if( $condVars != null && count($condVars) > 0 )
				$this->_condVars = $condVars;
			
			return $db->ExecuteNonQuery($sql,array_merge($params,$this->_condVars));
		}
	}
}