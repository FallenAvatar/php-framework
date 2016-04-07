<?php

namespace Core\Data {
	abstract class ActiveRecord extends \Core\Object implements \JsonSerializable {
		public static function FindAll($order_by = null) {
			$db = static::GetDb();
			$tbl_name = static::$table_name;
			$sql = 'SELECT * FROM '.$db->DelimTable($tbl_name);
			
			if( isset($order_by) )
				$sql .= \Core\Data\Helper::ParseOrderBy($db, $order_by);
		
			return $db->ExecuteQuery($sql, array(), get_called_class());
		}
		
		public static function FindAllBy($params, $order_by = null, $type = 'AND') {
			$db = static::GetDb();
			$tbl_name = static::$table_name;
			$sql = 'SELECT * FROM '.$db->DelimTable($tbl_name).' WHERE ';
		
			$first = true;
			$ps = array();
		
			foreach( $params as $k => $v ) {
				if( !$first )
					$sql .= ' '.$type.' ';
			
				$first = false;
			
				$sql .= \Core\Data\Helper::HandleParameter( $k, $v, $db, $ps );
			}
			
			if( isset($order_by) )
				$sql .= \Core\Data\Helper::ParseOrderBy($db, $order_by);
			
			return $db->ExecuteQuery($sql, $ps, get_called_class());
		}
		
		public static function FindFirstBy($params, $order_by = null, $type = 'AND') {
			$rows = static::FindAllBy($params, $order_by);
			
			if( !isset($rows) || !is_array($rows) || count($rows) <= 1 )
				return null;
			
			return $rows[0];
		}
		
		public static function FindOnlyBy($params, $type = 'AND') {
			$rows = static::FindAllBy($params, null, $type);
			
			if( !isset($rows) || !is_array($rows) || count($rows) != 1 )
				return null;
			
			return $rows[0];
		}
		
		protected static function GetDb() {
			return \Core\Data\Database::Get(isset(static::$db_name) ? static::$db_name : null);
		}
		protected static function GetTable() {
			$db = static::GetDb();
			return \Core\Data\Table::Get($db, static::$table_name);
		}

		protected static function ParseType($name, $type) {
			$chars = array(
				'allow_null' => '?',
				'unsigned' => '+',
				'auto_increment' => '@',
				'primary_key' => '!',
				'unique' => '*',
				'index' => '#',
			);
			
			if( is_array($type) ) {
				// validate type def, add defaults
				if( !isset($type['type']) )
					throw new \Exception('Invalid column definition \'type\' ['.$type['type'].'] for DB ['.static::GetDb()->Name.'], Table ['.static::GetTable()->Name.'].');
				else if( isset($type['length']) && !is_int($type['length']) )
					throw new \Exception('Invalid column definition \'length\' ['.$type['length'].'] for DB ['.static::GetDb()->Name.'], Table ['.static::GetTable()->Name.'].');
					
				foreach($chars as $name => $char) {
					if( isset($type[$name]) && !is_bool($type[$name]) )
						throw new \Exception('Invalid column definition \''.$name.'\' ['.$type[$name].'] for DB ['.static::GetDb()->Name.'], Table ['.static::GetTable()->Name.'].');
					else
						$type[$name] = false;
				}
			} else if( is_string($type) ) {
				$ret = array();
				$ret['length'] = null;
				
				$max = -1;
				// parse
				foreach($chars as $name => $char) {
					$idx = strpos($type, $char);
					if( $idx !== false )
					{
						$ret[$name] = true;
						$max = max($max, $idx);
					} else
						$ret[$name] = false;
				}
			
				if( $max >= 0 )
					$type = substr($type, $max+1);

				$lbracket = strpos($type, '[');
				if( $lbracket !== false && $lbracket >= 0 ) {
					$rbracket = strpos($type, ']');
					$lenstr = substr($type, $lbracket+1, ($rbracket-$lbracket)-1);
					$ret['length'] = intval($lenstr);
			
					$ret['type'] = substr($type, 0, $lbracket);
				} else
					$ret['type'] = $type;
			
				return $ret;
			}
			else
				throw new \Exception('Invalid column definition ['.$type.'] for DB ['.static::GetDb()->Name.'], Table ['.static::GetTable()->Name.'].');
		}
		
		protected $data;
		protected $data_original;
		protected $loaded;
		protected $keys;
		protected $cols;
		
		public function __construct($id_or_data = null) {
			$this->loadInfo();
			
			if( !isset($id_or_data) ) {
				// New row, do nothing
				$this->data = array();
				
				foreach($this->cols as $col => $info)
					$this->data[$col] = null;
			
				$this->data_original = $this->data;
			} else if( is_array($id_or_data) && count($id_or_data) == count($this->cols) )
				$this->LoadData($id_or_data);
			else if( is_array($id_or_data) && count($id_or_data) == count($this->keys) )
				$this->Load($id_or_data);
			else if( !is_array($id_or_data) && count($this->keys) == 1 )
				$this->Load(array($this->keys[0] => $id_or_data));
			else
				throw new \Exception('Invalid id or data array passed to ActiveRecord. Expected data with ['.count($this->cols).'] columns or ['.count($this->keys).'] keys.');
		}
			
		private function loadInfo() {
			$this->cols = array();
			
			foreach(static::$columns as $n => $t) {
				$this->cols[$n] = static::ParseType($n, $t);
				if( $this->cols[$n]['primary_key'] )
					$this->keys[] = $n;
			}
			
			$this->loaded = false;
		}
		
		protected function Load($ids) {
			$where = '';
			$first = true;
			$data = array();
					
			foreach($this->keys as $k) {
				if( !$first )
					$where .= ' AND ';
				
				$cleanName = preg_replace('/[^a-zA-Z0-9]*/','',preg_replace('/\s+/','_',$k));
				$where .= static::GetDb()->DelimColumn($k) . ' = :' . $cleanName;
				$data[$cleanName] = $ids[$k];
				
				$first = false;
			}
				
			$rows = static::GetTable()->Select(null, $where, $data);
				
			if( count($rows) > 0 )
				$this->LoadData($rows[0]);
		}
		
		protected function LoadData($data) {
			$this->data_original = $data;
			$this->data = $data;
			$this->loaded = true;
		}
				
		public function Save() {
			$changed = array();
				
			foreach( $this->data as $n => $v )
				if( $this->data_original[$n] != $v )
					$changed[$n] = $v;
				
			$stmt = null;
				
			if( $this->loaded ) {
				if( count($changed) <= 0 )
					return;
					
				$sql = '';
				$params = array();
				
				$first = true;
				foreach($this->keys as $key) {
					if( !$first )
						$sql .= ' AND ';
					
					$sql .= \Core\Data\Helper::HandleParameter( $key, $this->data_original[$key], static::GetDb(), $params );
					$first = false;
				}
				
				static::GetTable()->Update($changed, $sql, $params);
			} else {
				$id = static::GetTable()->InsertSingle($changed);
				if( count($this->keys) == 1 )
					$this->data[$this->keys[0]] = $id;
				
				$this->loaded = true;
			}

			foreach($this->data as $n => $v)
				$this->data_original[$n] = $v;
		}

		public function Delete() {
			if(!$this->loaded)
				return 0;

			$sql = '';
			$params = array();
			
			$first = true;
			foreach($this->keys as $key) {
				if( !$first )
					$sql .= ' AND ';
					
				$sql .= \Core\Data\Helper::HandleParameter( $key, $this->data_original[$key], static::GetDb(), $params );
				$first = false;
			}

			return static::GetTable()->Delete($sql, $params);
		}
		
		public function jsonSerialize() {
			return $this->data;
		}
		
		public function __get($name) {
			if( method_exists($this,'_get'.$name) )
				return call_user_func_array(array($this,'_get'.$name),array());
			else if( array_key_exists($name, $this->data) )
				return $this->data[$name];
			
			throw new \Core\Exception('Property ['.$name.'] not found.');
		}
		
		public function __set($name,$value)
		{
			if( method_exists($this,'_set'.$name) )
				return call_user_method_array('_set'.$name,$this,array($value));
			else if( in_array($name, $this->keys) && count($this->keys) == 1 )
				throw new \Core\Exception('You can not set the primary key for a table with one primary key.');
			
			if( array_key_exists($name, $this->data) ) {
				$this->data[$name] = $value;
				return;
			}
			
			throw new \Core\Exception('Property ['.$name.'] not found.');
		}
		
		public function __isset($name) {
			if( !isset($this->data) || !is_array($this->data) )
				return false;
			
			if( array_key_exists($name, $this->data) )
				return isset($this->data[$name]);

			return false;
		}
		
		public function __unset($name) {
			if( in_array($name, $this->keys) && count($this->keys) == 1 )
				throw new \Core\Exception('You can not set the primary key for a table with one primary key.');
			
			if( array_key_exists($name, $this->data) )
				return $this->data[$name] = null;
		}
		
		public function __call($name, $args) {
			if( !isset(static::$relationships) )
				throw new \Exception('No method ['.$name.'] on class ['.get_called_class().'].');
			
			$rels = static::$relationships;
			$rel_name = null;
			$type = null;
			
			// Has Add Remove Get
			if( \startsWith($name, 'Has') ) {
				$rel_name = substr($name, 3);
				$type = 'has';
			} else if( \startsWith($name, 'Add') ) {
				$rel_name = substr($name, 3);
				$type = 'add';
			} else if( \startsWith($name, 'Remove') ) {
				$rel_name = substr($name, 6);
				$type = 'remove';
			} else if( \startsWith($name, 'Get') ) {
				$rel_name = substr($name, 3);
				$type = 'get';
			} else if( \startsWith($name, 'Clear') ) {
				$rel_name = substr($name, 5);
				$type = 'clear';
			} else
				throw new \Exception('No method ['.$name.'] on class ['.get_called_class().'].');
		
			$r = null;
			$is_plural = false;
			foreach($rels as $rname => $info) {
				if( $rname == $rel_name ) {
					$r = $info;
					break;
				} else if( (isset($info['plural_name']) && $rel_name == $info['plural_name']) || (!isset($info['plural_name']) && $rel_name == $rname.'s') ) {
					$r = $info;
					$is_plural = true;
					break;
				}
			}
			
			if( !isset($r) )
				throw new \Exception('No relationship with name ['.$rel_name.'] found on class ['.get_called_class().'].');
			
			$db = static::GetDb();
		
			if( isset($info['table']) ) {		// many-to-many
				$link_tbl = $info['table'];
				$class_name = $info['class'];
				$tbl = $class_name::$table_name;
				
				if( $type == 'get' ) {
					if( !$is_plural )
						throw new \Exception();

					$sql = 'SELECT '.$db->DelimTable('t').'.* ' .
							'FROM '.$db->DelimTable($link_tbl).' AS '.$db->DelimTable('l').' ' .
								'LEFT JOIN '.$db->DelimTable($tbl).' AS '.$db->DelimTable('t').' ' .
									'ON '.$db->DelimTable('l').'.'.$db->DelimColumn($info['foreign_id']).' = '.$db->DelimTable('t').'.'.$db->DelimColumn('id').' ' .
							'WHERE '.$db->DelimTable('l').'.'.$db->DelimTable($info['local_id']).' = :id';
					
					return $db->ExecuteQuery($sql, array('id' => $this->id), $class_name);
				} else if( $type == 'has' ) {
					if( $is_plural )
						throw new \Exception();
					
					$sql = 'SELECT COUNT(1) ' .
							'FROM '.$db->DelimTable($link_tbl).' ' .
							'WHERE '.$db->DelimTable($info['local_id']).' = :id AND '.$db->DelimTable($info['foreign_id']).' = :id2';
					return ($db->ExecuteScalar($sql, array('id' => $this->id, 'id2' => $args[0])) > 0);
				} else if( $type == 'add' ) {
					if( $is_plural )
						throw new \Exception();
					
					$stmt = new \Core\Data\Statement\InsertSingle($db, $link_tbl);
					$stmt->Add($info['local_id'], $this->id);
					$stmt->Add($info['foreign_id'], $args[0]);
					$stmt->Execute();
				} else if( $type == 'remove' ) {
					if( $is_plural )
						throw new \Exception();
					
					$where = $db->DelimColumn($info['local_id']).' = :id AND '.$db->DelimColumn($info['foreign_id']).' = :id2';
					
					$stmt = new \Core\Data\Statement\Delete($db, $link_tbl);
					$stmt->Where($where, array('id' => $this->id, 'id2' => $args[0]));
					$stmt->Execute();
				} else if( $type == 'clear' ) {
					if( !$is_plural )
						throw new \Exception();
					
					$where = $db->DelimColumn($info['local_id']).' = :id';
					
					$stmt = new \Core\Data\Statement\Delete($db, $link_tbl);
					$stmt->Where($where, array('id' => $this->id));
					$stmt->Execute();
				}
			} else if( isset($info['local_id']) ) {		// one-to-many
				throw new \Exception('Not Implemented.');
				if( !$is_plural )
					throw new \Exception();
			
				$class_name = $info['class'];
				$tbl = $class_name::$table_name;
			} else if( isset($info['foreign_id']) ) {	// many-to-one
				throw new \Exception('Not Implemented.');
				if( $is_plural )
					throw new \Exception();
				
				$class_name = $info['class'];
				$tbl = $class_name::$table_name;
			}
		}
	}
}