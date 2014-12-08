<?

namespace Core\Data
{
	abstract class ActiveRecord extends \Core\Object
	{
		protected $tbl;
		protected $cols;
		protected $prikeycol;
		
		protected $keys;
		
		protected $data;
		protected $data_changed;
		
		protected $loaded;
		
		protected function __construct($options=null)
		{
			$this->loaded=false;
			
			if( !isset($options) || !is_array($options) || count($options) == 0 )
				throw new \Core\Exception('Core\Data\ActiveRecord must be initialized with an options array containing { "table","columns","primaryidname" }.');
			
			if( !isset($options['table']) )
				throw new \Core\Exception('Core\Data\ActiveRecord must be initialized with an options array containing { "table","columns","primaryidname" }.1');
			
			if( !isset($options['columns']) )
				throw new \Core\Exception('Core\Data\ActiveRecord must be initialized with an options array containing { "table","columns","primaryidname" }.2');
			
			if( !isset($options['primaryidname']) )
				throw new \Core\Exception('Core\Data\ActiveRecord must be initialized with an options array containing { "table","columns","primaryidname" }.3');
			
			$this->tbl = $options['table'];
			$this->cols = $options['columns'];
			$this->prikeycol = $options['primaryidname'];
			$this->keys = array();
			
			if( isset($options['id']) )
			{
				$id = $options['id'];
				if( (!is_array($id) || count($id) == 1) && (!is_array($this->prikeycol) || count($this->prikeycol) == 1) )
					$this->Load($options['id']);
				else if( is_array($id) && is_array($this->prikeycol) && count($this->prikeycol) == count($id) )
					$this->Load($options['id']);
				else if( is_array($options['id']) && count($id) == ((is_array($this->prikeycol)) ? count($this->prikeycol) : 1) + count($this->cols)  )
					$this->LoadData($options['id']);
				else
					throw new \Core\Exception('Invalid id/data passed to Core\Data\ActiveRecord');
			}
		}
		
		protected function Load($id)
		{
			$db = Database::Get();
			$sql = '';

			if( is_array($this->prikeycol) )
			{
				$sql = "SELECT * FROM ".$db->Delim($this->tbl,Database::Delim_Table)." WHERE ";
				$first=true;
				foreach( $this->prikeycol as $name )
				{
					if( !$first )
						$sql .= " AND ";
					
					$sql .= $db->Delim($name,Database::Delim_Column)." = :".$name;
					$first=false;
				}
				$sql .= " LIMIT 1";
				$this->keys = $id;
			}
			else
			{
				$sql = "SELECT * FROM ".$db->Delim($this->tbl,Database::Delim_Table)." WHERE ".$db->Delim($this->prikeycol,Database::Delim_Column)." = :".$this->prikeycol." LIMIT 1";
				$this->keys=array();
				$this->keys[$this->prikeycol]=$id;
			}
			
			$results = $db->ExecuteQuery($sql, $this->keys);

			$this->data = $results[0];
			$this->data_changed = array();
			
			foreach( array_keys($this->data) as $n )
				$this->data_changed[$n] = false;
			
			$this->loaded=true;
		}
		
		public function LoadData($d)
		{
			$this->data = $d;
			$this->data_changed = array();
			
			foreach( array_keys($this->data) as $n )
				$this->data_changed[$n] = false;
			
			if( is_array( $this->prikeycol ) )
			{
				foreach( $this->prikeycol as $name )
				{
					$this->keys[$name] = $this->data[$name];
				}
			}
			else
				$this->keys[$this->prikeycol] = $this->data[$this->prikeycol];
			
			$this->loaded=true;
		}
		
		public function Save()
		{
			$db = Database::Get();
			if($this->loaded)
			{
				$where = '';
				
				if( is_array($this->prikeycol) )
				{
					$first = true;
					
					foreach( $this->prikeycol as $name )
					{
						if( !$first )
							$where .= ' AND ';
						
						$where .= $db->Delim($name,Database::Delim_Column).' = '.$db->Escape($this->keys[$name]);
						
						$first = false;
					}
				}
				else
					$where = $db->Delim($this->prikeycol,Database::Delim_Column).' = '.$db->Escape($this->keys[$this->prikeycol]);
				
				$update = new \Core\Data\Statement\Update($this->tbl,$where);
				
				foreach( $this->cols as $col )
					if( $this->data_changed[$col] )
						$update->Add($col, $this->data[$col]);
				
				if( is_array($this->prikeycol) )
					foreach( $this->prikeycol as $name )
						if( $this->data_changed[$name] )
							$update->Add($name, $this->data[$name]);
				
				$update->Execute();
			}
			else
			{
				$insert = new \Core\Data\Statement\Insert($this->tbl);
				
				foreach( $this->cols as $col )
					if( $this->data_changed[$col] )
						$insert->Add($col, $this->data[$col]);
				
				if( is_array($this->prikeycol) )
					foreach( $this->prikeycol as $name )
						if( $this->data_changed[$name] )
							$insert->Add($name, $this->keys[$name]);
				
				$insert->Execute();
				
				if( is_array($this->prikeycol) )
				{
					foreach( $this->prikeycol as $name )
					{
						$this->keys[$name] = $this->data[$name];
					}
				}
				else
					$this->keys[$this->prikeycol] = $this->data[$this->prikeycol] = $db->LastInsertId();
			}
		}

		public function Delete()
		{
			$db = Database::Get();
			if(!$this->loaded)
				return;

			$where = '';
			$params = array();
			
			if( is_array($this->prikeycol) )
			{
				$first = true;
				
				foreach( $this->prikeycol as $name )
				{
					if( !$first )
						$where .= ' AND ';
					
					$where .= $db->Delim($name,Database::Delim_Column).' = :'.$name;
					$params[$name] = $this->keys[$name];
					
					$first = false;
				}
			}
			else
			{
				$where = $db->Delim($this->prikeycol,Database::Delim_Column).' = :'.$this->prikeycol;
				$params[$this->prikeycol] = $this->keys[$this->prikeycol];
			}

			$sql = "DELETE FROM ".$db->Delim($this->tbl,Database::Delim_Table)." WHERE ".$where;
			$db->ExecuteNonQuery($sql,$params);

			$this->data = array();
			$this->keys = array();
		}
		
		public function __get($name)
		{
			if( method_exists($this,'_get'.$name) )
			{
				return call_user_func_array(array($this,'_get'.$name),array());
			}
			else if( is_array($this->prikeycol) )
			{
				foreach( $this->prikeycol as $col )
				{
					if( $col == $name )
					{
						return $this->data[$name];
					}
				}
			}
			else if( $this->prikeycol == $name )
				return $this->data[$this->prikeycol];
			
			foreach( $this->cols as $col )
				if( $col == $name )
					return $this->data[$name];
			
			throw new \Core\Exception('Property ['.$name.'] not found.');
		}
		
		public function __set($name,$value)
		{
			if( method_exists($this,'_set'.$name) )
			{
				return call_user_method_array('_set'.$name,$this,array($value));
			}
			else if( is_array($this->prikeycol) )
			{
				foreach( $this->prikeycol as $col )
				{
					if( $col == $name )
					{
						$this->data[$name] = $value;
						$this->data_changed[$name] = true;
						return;
					}
				}
			}
			else if( $this->prikeycol == $name )
				throw new \Core\Exception('You can not set the primary key for a table with one primary key.');
			
			foreach( $this->cols as $col )
			{
				if( $col == $name )
				{
					$this->data[$name] = $value;
					$this->data_changed[$name] = true;
					return;
				}
			}
			
			throw new \Core\Exception('Property ['.$name.'] not found.');
		}
		
		public function __isset($name)
		{
			if( is_array($this->prikeycol) )
			{
				foreach( $this->prikeycol as $col )
				{
					if( $col == $name )
					{
						return isset($this->data[$name]);
					}
				}
			}
			else if( $this->prikeycol == $name )
				return isset($this->data[$this->prikeycol]);
			
			foreach( $this->cols as $col )
				if( $col == $name )
					return isset($this->data[$name]);
			
			return false;
		}
		
		public function __unset($name)
		{
			if( is_array($this->prikeycol) )
			{
				foreach( $this->prikeycol as $col )
				{
					if( $col == $name )
					{
						$this->data[$name] = null;
						$this->data_changed[$name] = true;
						return;
					}
				}
			}
			else if( $this->prikeycol == $name )
				throw new \Core\Exception('You can not set the primary key for a table with one primary key.');
			
			foreach( $this->cols as $col )
			{
				if( $col == $name )
				{
					$this->data[$name] = null;
					$this->data_changed[$name] = true;
					return;
				}
			}
		}
	}
}