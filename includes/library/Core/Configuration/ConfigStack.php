<?

namespace Core\Configuration
{
	class ConfigStack extends \Core\Object
	{
		protected $configs;
		
		public function __construct(array $files = null)
		{
			$this->configs = array();
			
			if( isset($files) )
				$this->LoadFiles($files);
		}
		
		public function LoadFiles(array $files)
		{
			foreach( $files as $file )
			{
				$json = \file_get_contents($file);
				$json_arr = \json_decode($json, true);
				$this->configs[] = $json_arr;
			}
		}
		
		public function GetMergedConfig()
		{
			if( !isset($this->configs) || count($this->configs) <= 0 )
				return null;
			
			$ret = array();
			
			for( $i=0; $i<count($this->configs); $i++ )
			{
				$ret = $this->MergeHelper($ret, $this->configs[$i]);
			}
			
			$ret = $this->ProcessArray($ret);
			
			return new Config($ret);
		}
		
		protected function MergeHelper($one, $two)
		{
			if( !isset($one) || !\is_array($one) || empty($one) )
				return $two;
			else if( !isset($two) || !\is_array($two) || empty($two) )
				return $one;
			
			$is_one_assoc = \Core\ArrayHelper::IsAssoc($one);
			$is_two_assoc = \Core\ArrayHelper::IsAssoc($two);
			
			if( $is_one_assoc != $is_two_assoc )
				throw new ConfigurationException('Can not merge an associative array with one that is not.');
			
			$ret = array();
			
			if( $is_one_assoc )
			{
				foreach($one as $name => $value)
				{
					$item = null;
					if( array_key_exists($name, $two) )
						$item = $this->MergeHelper($value, $two[$name]);
					else
						$item = $value;
					
					$ret[$name] = $item;
				}
				
				foreach($two as $name => $value)
				{
					$item = null;
					if( array_key_exists($name, $one) )
						continue;
					else
						$item = $value;
					
					$ret[$name] = $item;
				}
			}
			else
			{
				foreach($one as $o)
					$ret[] = $o;
				foreach($two as $t)
					$ret[] = $t;
			}
			
			return $ret;
		}
		
		protected function ProcessArray($arr)
		{
			if( !isset($arr) || !is_array($arr) )
				return $arr;
			
			$ret = array();
			
			if( !\Core\ArrayHelper::IsAssoc($arr) )
			{
				foreach($arr as $v)
				{
					if( !isset($v['action']) || !in_array($v['action'], array('clear', 'add', 'remove') ) )
					{
						$ret = $arr;
						break;
					}
					
					if( $v['action'] == 'clear' )
						$ret = array();
					else if( $v['action'] == 'remove' && isset($v['name']) )
						unset($ret[$arr['name']]);
					else if( $v['action'] == 'add' && isset($v['name']) )
						$ret[$v['name']] = $v['value'];
					else
					{
						$ret = $arr;
						break;
					}
				}
			}
			else
				$ret = $arr;
			
			$temp = array();
			
			foreach( $ret as $k => $v )
				$temp[$k] = $this->ProcessArray($v);
			
			return $temp;
		}
	}
}