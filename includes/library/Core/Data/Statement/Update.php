<?php

namespace Core\Data\Statement
{
	class Update extends \Core\Object implements IStatement
	{
		protected $_db;
		protected $_tbl;
		protected $_vals;
		protected $_where;
		protected $_params;
		
		public function __construct($db, $tbl)
		{
			$this->_db=$db;
			$this->_tbl=is_string($tbl) ? $tbl : $tbl->Name;
			$this->_vals=array();
			$this->_where=null;
			$this->_params=array();
		}
		
		public function Add($col,$val)
		{
			$this->_vals[$col]=$val;
		}
		
		public function Where($where, $ps)
		{
			$this->_where = $where;
			$this->_params = array_merge($this->_params, $ps);
		}
			
		public function Execute()
		{
			$sql="UPDATE ".$this->_db->DelimTable($this->_tbl)." SET ";
			$params = array();
			
			$first=true;
			foreach( $this->_vals as $col => $val )
			{
				if( !$first )
					$sql.=", ";

				$sql.=$this->_db->DelimColumn($col)." = :".preg_replace('/\s/','_',preg_replace('/([^a-zA-Z0-9]*)/','',$col));
				$params[preg_replace('/\s/','_',preg_replace('/[^a-zA-Z0-9]*/','',$col))]=$val;
				
				$first = false;
			}
			
			if( isset($this->_where) && $this->_where != '' )
				$sql.=" WHERE ".$this->_where;
			
			return $this->_db->ExecuteNonQuery($sql,array_merge($this->_params,$params));
		}
	}
}