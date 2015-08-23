<?php

namespace Core\Data
{
	class Table extends \Core\Object
	{
		private static $_insts = array();
		public static function Get($db, $tbl)
		{
			if( !($db instanceof Database) )
				$db = Database::Get($db);
				
			if( !isset(self::$_insts[$db->DBName]) )
				self::$_insts[$db->DBName] = array();
				
			if( isset(self::$_insts[$db][$tbl]) )
				return self::$_insts[$db][$tbl];
				
			$t = new Table($db, $tbl);
			
			self::$_insts[$db][$tbl] = $t;
			
			return $t;
		}
		
		protected $db;
		public function _getDB() { return $this->db; }
		
		protected $tbl_name;
		public function _getName() { return $this->tbl_name; }
		
		protected function __construct($db, $tbl)
		{
			$this->db = $db;
			$this->tbl_name = $tbl;
		}
		
		public function Select($cols = null, $where = null, $ps = null, $rowClass = null)
		{
			$sql = 'SELECT ';
			
			if( isset($cols) && is_array($cols) && count($cols) > 0 )
			{
				$first = true;
				
				foreach($cols as $col)
				{
					if( !$first )
						$sql .= ', ';
					
					$col_id = null;
					$alias = null;
					
					if( is_array($col) )
					{
						$col_id = $col[0];
						$alias = $col[1];
					}
					else
						$col_id = $col;
					
					$sql .= $this->db->DelimColumn($col_id);

					if( isset($alias) )
						$sql .= ' AS ' . $this->db->DelimColumn($alias);
					
					$first = false;
				}
			}
			else
				$sql .= '*';
				
			$sql .= ' FROM ' . $this->db->DelimTable($this->tbl_name);
			
			if( isset($where) && trim($where) != '' )
			{
				$sql .= ' WHERE ' . $where;
			}
			
			return $this->db->ExecuteQuery($sql, $ps, $rowClass);
		}
		
		public function InsertSingle($row)
		{
			$count = $this->Insert(array($row));
			return $this->db->LastInsertId();
		}
		
		public function Insert($rows)
		{
			if( count($rows) <= 0 )
				return 0;
			
			$sql = 'INSERT INTO '.$this->db->DelimTable($this->tbl_name).' (';
			
			$firstrow = $rows[0];
			$param_names = array();
			$first = true;
			
			foreach($firstrow as $col => $v)
			{
				$colClean=preg_replace('/\s+/','_',preg_replace('/[^a-zA-Z0-9]*/','',$col));
				$param_names[$col] = $colClean;
				
				if( !$first )
					$sql .= ', ';
					
				$sql .= $this->db->DelimColumn($col);
				
				$first = false;
			}
			
			$sql .= ') VALUES ';
			$row_idx = 0;
			$data = array();
			
			foreach($rows as $row)
			{
				if( $row_idx > 0 )
					$sql .= ', ';
				
				$sql .= '(';
				
				$first = true;
				foreach( $rows as $name => $val )
				{
					if( !$first )
						$sql .= ', ';
						
					$c = $param_names[$name].$row_idx;
						
					$sql .= $this->db->DelimParameter($c);
					$data[$c] = $val;
					
					$first = false;
				}
				
				$sql .= ')';
				
				$row_idx++;
			}
			
			return $this->db->ExecuteNonQuery($sql, $data);
		}
		
		public function Update($row, $where = null, $ps = null)
		{
		}
		
		public function Delete($where = null, $ps = null)
		{
		}
	}
}