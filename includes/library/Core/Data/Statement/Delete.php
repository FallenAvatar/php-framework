<?php

namespace Core\Data\Statement
{
	class Delete extends \Core\Object
	{
		protected $_tbl;
		protected $_cond;
		protected $_condVars;
		
		public function __construct($tbl,$cond,$condVars=array())
		{
			$this->_tbl=$tbl;
			$this->_cond=$cond;
			$this->_condVars=$condVars;
		}
		
		public function Execute($condVars=array())
		{
			$db=\Core\Data\Database::Get();
			
			$sql="DELETE FROM ".$db->Delim($this->_tbl,\Core\Data\Database::Delim_Table);
			
			if( isset($this->_cond) && $this->_cond != '' )
				$sql.=" WHERE ".$this->_cond;
				
			if( $condVars != null && count($condVars) > 0 )
				$this->_condVars = $condVars;
			
			return $db->ExecuteNonQuery($sql,$this->_condVars);
		}
	}
}