<?php

namespace Core\Data\Statement
{
	class Select extends \Core\Object /*implements IStatement*/
	{
		protected $_db;
		protected $_tbl;
		protected $_cols;
		protected $_where;
		protected $_params;
		
		public function __construct($db, $tbl, $cols = null)
		{
			$this->_db=$db;
			$this->_tbl=is_string($tbl) ? $tbl : $tbl->Name;
			$this->_cols=$cols;
			$this->_where=null;
			$this->_params=array();
		}
		
		public function Where($where, $ps)
		{
			$this->_where = $where;
			$this->_params = array_merge($this->_params, $ps);
		}
		
		public function Execute($rowClass = null)
		{
			$sql='SELECT ';
			
			if( isset($this->_cols) )
			{
				$first = true;
				foreach( $this->_cols as $col )
				{
					if( !$first )
						$sql .= ', ';
						
					$name = $col;
					$alias = null;
					
					if( is_array($col) )
					{
						$name = $col[0];
						$alias = $col[1];
					}
					
					$sql .= $this->_db->DelimColumn($col);
					
					if( isset($alias) )
						$sql .= ' AS '.$this->_db->DelimColumn($alias);
				}
			}
			else
				$sql .= '*';
			
			$sql .= ' FROM '.$this->_db->DelimTable($this->_tbl);
			
			if( isset($this->_where) && $this->_where != '' )
				$sql.=' WHERE '.$this->_where;
			
			return $this->_db->ExecuteQuery($sql, $this->_params, $rowClass);
		}
	}
}