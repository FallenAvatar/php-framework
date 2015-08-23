<?php

namespace Core\Data\Statement
{
	class InsertSingle extends \Core\Object
	{
		protected $_db;
		protected $_tbl;
		protected $_vars;
		
		public function __construct($db, $tbl)
		{
			$this->_db=$db;
			$this->_tbl=is_string($tbl) ? $tbl : $tbl->Name;
			$this->_vars=array();
		}
		
		public function Add($col,$val)
		{
			$this->_vars[$col]=$val;
		}
		
		public function Execute()
		{
			$sql="INSERT INTO ".$this->_db->DelimTable($this->_tbl)."(";
			$sql2=") VALUES (";
			$params=array();
			
			$first=true;
			foreach( $this->_vars as $col => $val )
			{
				if( !$first )
				{
					$sql.=", ";
					$sql2.=", ";
				}
				$sql.=$this->_db->DelimColumn($col);
				$colClean=preg_replace('/\s+/','_',preg_replace('/[^a-zA-Z0-9]*/','',$col));
				$sql2.=':'.$colClean;
				$params[$colClean] = $val;
				
				$first = false;
			}
			
			$sql = $sql.$sql2.")";
			
			return $this->_db->ExecuteNonQuery($sql,$params);
		}
	}
}