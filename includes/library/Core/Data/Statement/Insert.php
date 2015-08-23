<?php

namespace Core\Data\Statement
{
	class Insert extends \Core\Object
	{
		protected $_db;
		protected $_tbl;
		protected $_rows;
		
		public function __construct($db, $tbl)
		{
			$this->_db=$db;
			$this->_tbl=is_string($tbl) ? $tbl : $tbl->Name;
			$this->_rows=array();
		}
		
		public function Add($row)
		{
			$this->_rows[]=$row;
		}
		
		public function Execute($cols = null)
		{
			if( count($this->_rows) <= 0 )
				return 0;
			
			$sql="INSERT INTO ".$this->_db->DelimTable($this->_tbl)."(";
			$sql2=") VALUES (";
			$params=array();
			
			$firstrow = $this->_rows[0];
			$first=true;
			foreach($firstrow as $col => $val)
			{
				if( isset($cols) && !in_array($col, $cols) )
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
					if( isset($cols) && !in_array($col, $cols) )
						continue;
					
					if( !$firstcol )
						$sql2 .= ', ';
						
					$colClean=preg_replace('/[^a-zA-Z0-9]*/','',preg_replace('/\s+/','_',$col)).$idx;
				}
				$sql2.=':'.$colClean;
				$params[$colClean] = $val;
				
					$firstcol = false;
				}
				
				$first = false;
				$idx++;
			}
			
			$sql = $sql.$sql2.")";
			
			return $this->_db->ExecuteNonQuery($sql,$params);
		}
	}
}