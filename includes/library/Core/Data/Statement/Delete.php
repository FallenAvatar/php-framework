<?php

namespace Core\Data\Statement {
	class Delete extends \Core\Obj {
		protected $_db;
		protected $_tbl;
		protected $_where;
		protected $_params;
		protected $_paramPrefix;
		public function _setParamPrefix($prefix) { $this->_paramPrefix = $prefix; }
		
		public function __construct($db, $tbl) {
			$this->_db=$db;
			$this->_tbl=is_string($tbl) ? $tbl : $tbl->Name;
			$this->_where=null;
			$this->_params=[];
		}
		
		public function Where($where, $ps) {
			$this->_where = $where;
			$this->_params = array_merge($this->_params, $ps);
		}
		
		public function Execute()
		{
			return $this->_db->ExecuteNonQuery($this->Sql,$this->_params);
		}

		public function _getSql()
		{
			$sql="DELETE FROM ".$this->_db->DelimTable($this->_tbl);
			
			if( isset($this->_where) && $this->_where != '' )
				$sql.=" WHERE ".$this->_where;

			return $sql;
		}
	}
}