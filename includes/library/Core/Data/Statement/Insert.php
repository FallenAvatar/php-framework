<?php

namespace Core\Data\Statement
{
	class Insert extends \Core\Object
	{
		protected $tbl;
		protected $vals;
		
		public function __construct($tbl)
		{
			$this->tbl=$tbl;
			$this->vals=array();
		}
		
		public function Add($col,$val)
		{
			$this->vals[$col]=$val;
		}
		
		public function Execute()
		{
			$db=\Core\Data\Database::Get();
			
			$sql="INSERT INTO ".$db->DelimTable($this->tbl)."(";
			$sql2=") VALUES (";
			$params=array();
			
			$first=true;
			foreach( $this->vals as $col => $val )
			{
				if( !$first )
				{
					$sql.=", ";
					$sql2.=", ";
				}
				$sql.=$db->DelimColumn($col);
				$colClean=preg_replace('/\s+/','_',preg_replace('/[^a-zA-Z0-9]*/','',$col));
				$sql2.=':'.$colClean;
				$params[$colClean] = $val;
				
				$first = false;
			}
			
			$sql = $sql.$sql2.")";
			
			return $db->ExecuteNonQuery($sql,$params);
		}
	}
}