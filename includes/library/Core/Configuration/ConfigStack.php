<?php declare(strict_types=1);

namespace Core\Configuration;

class ConfigStack extends \Core\Obj {
	protected array $files;
	protected array $configs;

	public function __construct(array $files = []) {
		$this->files = $files;
		$this->configs = [];
		$this->LoadFiles($files);
	}

	public function LoadFiles(array $files): void {
		foreach( $files as $file ) {
			$j = \Core\JSON::ParseFile($file);

			if( $j?->IsError )
				throw new \Exception('JSON Error ['.$j->ErrorNumber.'] loading file: ['.$file.']. Error: '.$j->ErrorString);

			$this->configs[] = $j->ToArray();
		}
	}

	public function GetMergedConfig(): ?Config {
		if( !isset($this->configs) || count($this->configs) <= 0 )
			return null;

		$ret = [];

		for( $i=0; $i<count($this->configs); $i++ ) {
			$ret = $this->MergeHelper($ret, $this->configs[$i]);
		}

		$ret = $this->ProcessArray($ret);
		//$j = new \Core\JSON($ret);
		//$q = '$..ns[*].name';
		//$q = '$..book[?(@.price<10)]';

		//echo '<br /><br />Query("'.$q.'")<br /><pre>'.print_r($j->Query($q), true).'</pre><br /><br />';
		//$this->traverse($ret, '$');
		//echo '<br /><br />';

		return new Config($ret);
	}

	//protected function traverse($node, $path) {

	//    //echo $path.'<br />';
	//    if( is_array($node) ) {
	//        foreach( $node as $k => $n ) {
	//            $p = '';
	//            if( is_int($k) )
	//                $p = '['.$k.']';
	//            else
	//                $p = '.'.$k;

	//            $this->traverse($n, $path.$p);
	//        }
	//    }

	//    return $node;
	//}

	protected function MergeHelper(array $one, array $two): array {
		if( !isset($one) || !\is_array($one) || empty($one) )
			return $two;
		else if( !isset($two) || !\is_array($two) || empty($two) )
			return $one;

		$is_one_assoc = \Core\ArrayHelper::IsAssoc($one);
		$is_two_assoc = \Core\ArrayHelper::IsAssoc($two);

		if( $is_one_assoc != $is_two_assoc )
			throw new ConfigurationException('Can not merge an associative array with one that is not.');

		$ret = [];

		if( $is_one_assoc ) {
			foreach($one as $name => $value) {
				$item = null;
				if( array_key_exists($name, $two) ) {
					if( is_array($value) )
						$item = $this->MergeHelper($value, $two[$name]);
					else
						$item = $two[$name];
				} else
					$item = $value;

				$ret[$name] = $item;
			}

			foreach($two as $name => $value) {
				$item = null;
				if( array_key_exists($name, $one) )
					continue;
				else
					$item = $value;

				$ret[$name] = $item;
			}
		} else {
			foreach($one as $o)
				$ret[] = $o;
			foreach($two as $t)
				$ret[] = $t;
		}

		return $ret;
	}

	protected function ProcessArray(array $arr): array {
		$ret = [];

		if( !\Core\ArrayHelper::IsAssoc($arr) ) {
			foreach($arr as $v) {
				if( !isset($v['action']) || !in_array($v['action'], ['clear', 'add', 'remove'] ) ) {
					$ret = $arr;
					break;
				}

				if( $v['action'] == 'clear' )
					$ret = [];
				else if( $v['action'] == 'remove' && isset($v['name']) )
					unset($ret[$arr['name']]);
				else if( $v['action'] == 'add' && isset($v['name']) )
					$ret[$v['name']] = $v['value'];
				else {
					$ret = $arr;
					break;
				}
			}
		} else {
			$ret = $arr;
		}

		$temp = [];

		foreach( $ret as $k => $v )
			$temp[$k] = is_array($v) ? $this->ProcessArray($v): $v;

		return $temp;
	}
}