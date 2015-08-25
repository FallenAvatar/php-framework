<?php

namespace Core\Data\Statement {
	class Insert extends \Core\Object implements IStatement {
		protected $_db;
		protected $_tbl;
		protected $_rows;
		protected $_cols;
		public function _setCols($cols) { $this->_cols = $cols; }
		public function _getCols() { return $this->_cols; }
		
		protected $_actParams;
		public function _getParams() { return $this->_actParams; }
		
		public function __construct($db, $tbl) {
			$this->_db = $db;
			$this->_tbl = is_string($tbl) ? $tbl : $tbl->Name;
			$this->_rows = array();
			$this->_cols = null;
			$this->_actParams = array();
		}
		
		public function Add($row) {
			$this->_rows[] = $row;
		}
		
		public function Execute() {
			if( count($this->_rows) <= 0 )
				return 0;
			
			return $this->_db->ExecuteNonQuery($this->Sql,$this->Params);
		}
		
		public function _getSql()
		{
			$this->_actParams = array();
			if( count($this->_rows) <= 0 )
				return '';
			
			$sql="INSERT INTO ".$this->_db->DelimTable($this->_tbl)."(";
			$sql2=") VALUES (";
			
			$firstrow = $this->_rows[0];
			$first=true;
			foreach($firstrow as $col => $val)
			{
				if( isset($this->_cols) && !in_array($col, $this->_cols) )
					continue;
					
				if( !$first )
					$sql .= ', ';
				
				$sql .= $this->_db->DelimColumn($col);
				$first = false;
			}
			
			$first=true;
			$idx=0;
			foreach( $this->_rows as $row )
			{
				if( !$first )
					$sql2.="), (";

				$firstcol = true;
				foreach($row as $col => $val)
				{
					if( isset($this->_cols) && !in_array($col, $this->_cols) )
						continue;
					
					if( !$firstcol )
						$sql2 .= ', ';
						
					$colClean=preg_replace('/[^a-zA-Z0-9]*/','',preg_replace('/\s+/','_',$col)).$idx;
				}
				$sql2.=':'.$colClean;
				$this->_actParams[$colClean] = $val;
				
					$firstcol = false;
				}
				
				$first = false;
				$idx++;
			}
			
			$sql = $sql.$sql2.")";
			
			return $sql;
		}
	}
}