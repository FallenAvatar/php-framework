<?php

namespace Core\Data {
	class CommandBuilder extends \Core\Object {
		protected $db;
		protected $cmd;
		protected $returnType;
		
		public function __construct($db) {
			$this->db = $db;
			$this->cmd = null;
			$this->returnType = null;
		}
		
		public function Select(array $cols, string $activeRecordClass = null) {
			$this->cmd = new \Core\Data\Command\Select($this->db, $cols);
			$this->returnType = $activeRecordClass;
			return $this;
		}
		
		public function Insert($tbl, array $cols) {
			$this->cmd = new \Core\Data\Command\Insert($this->db, $tbl, $cols);
			$this->returnType = 'int';
			return $this;
		}
		
		public function Update($tbl) {
			$this->cmd = new \Core\Data\Command\Update($this->db, $tbl);
			$this->returnType = 'int';
			return $this;
		}
		
		public function Delete() {
			$this->cmd = new \Core\Data\Command\Delete($this->db);
			$this->returnType = 'int';
			return $this;
		}
		
		public function __call(string $name, array $args) {
			if( !is_callable(array($this->cmd, $name)) )
				throw new \Exception('Method ['.$name.'] not found on type ['.get_class($this).'].');
			
			call_user_func_array(array($this->cmd, $name), $args);
			
			return $this;
		}
	}
}