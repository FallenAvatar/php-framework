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
				
			if( !isset(self::$_insts[$db->Name]) )
				self::$_insts[$db->Name] = array();
				
			if( isset(self::$_insts[$db->Name][$tbl]) )
				return self::$_insts[$db->Name][$tbl];
				
			$t = new Table($db, $tbl);
			
			self::$_insts[$db->Name][$tbl] = $t;
			
			return $t;
		}
		
		protected $db;
		public function _getDb() { return $this->db; }
		
		protected $tbl_name;
		public function _getName() { return $this->tbl_name; }
		
		protected function __construct($db, $tbl)
		{
			$this->db = $db;
			$this->tbl_name = $tbl;
		}
		
		public function Select($cols = null, $where = null, $ps = null, $rowClass = null)
		{
			$stmt = new \Core\Data\Statement\Select($this->db, $this->tbl_name, $cols);
			$stmt->Where($where, $ps);
			
			return $stmt->Execute($rowClass);
		}
		
		public function InsertSingle($row)
		{
			$count = $this->Insert(array($row));
			return $this->db->LastInsertId();
		}
		
		public function Insert($rows, $cols = null)
		{
			if( count($rows) <= 0 )
				return 0;
			
			$stmt = new \Core\Data\Statement\Insert($this->db, $this->tbl_name);
			foreach($rows as $row)
			{
				$stmt->Add($row);
			}
					
			return $stmt->Execute($cols);
			}
			
		public function Update($map, $where = null, $ps = null)
			{
			$stmt = new \Core\Data\Statement\Update($this->db, $this->tbl_name);
			foreach($map as $col => $val)
				{
				$stmt->Add($col, $val);
				}
			$stmt->Where($where, $ps);
				
			return $stmt->Execute();
			}
			
		public function Delete($where = null, $ps = null)
		{
			$stmt = new \Core\Data\Statement\Delete($this->db, $this->tbl_name);
			$stmt->Where($where, $ps);
		
			return $stmt->Execute();
		}
	}
}