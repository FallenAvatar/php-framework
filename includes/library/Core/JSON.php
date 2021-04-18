<?php declare(strict_types=1);

// TODO: Finish JsonPath support
// refs:
// http://goessner.net/articles/JsonPath/
// https://github.com/Peekmo/JsonPath/blob/master/src/Peekmo/JsonPath/JsonPath.php
// https://github.com/FlowCommunications/JSONPath/tree/master/src/Flow/JSONPath

namespace Core;

class JSON extends \Core\Obj {

	public static function ParseFile(string|array $file): JSON {
		if( \is_string($file) )
			return new \Core\JSON(\file_get_contents($file));
		else
			return new \Core\JSON($file);
	}

	private ?array $data;
	private int $last_error;
	public function _getErrorNumber(): int {
		return $this->last_error;
	}
	private ?string $last_error_str;
	public function _getErrorString(): ?string {
		return $this->last_error_str;
	}

	public function _getIsError(): bool {
		return ($this->last_error != JSON_ERROR_NONE);
	}

	public function __construct(string|array|null $str = null) {
		$this->data = null;
		$this->last_error = 0;
		$this->last_error_str = null;

		if( isset($str) ) {
			if( is_string($str) )
				$this->Decode($str);
			else {
				$this->data = $str;
				$this->last_error = JSON_ERROR_NONE;
				$this->last_error_str = null;
			}
		}
	}

	public function LoadData(array $data) {
		$this->data = $data;
	}

	public function Decode(string $str) {
		$this->data = json_decode($str, true);
		$this->last_error = json_last_error();
		$this->last_error_str = null;

		if( $this->last_error != JSON_ERROR_NONE ) {
			$this->last_error_str = json_last_error_msg();
		}
	}

	public function Query(string $path) {
		$ret = [];
		$qPath = $this->parsePath($path);

		if( isset($this->data) && is_array($this->data) )
			$this->traverse($this->data, '$', $qPath, $ret);

		return $ret;
	}

	protected function parsePath(string $path) {
		$parts = \preg_split('/([\.|\[|\]|@|\*|\,|\?|\(|\)])/', $path, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$p2 = [];
		/*
		IN: '$..ns[*].name'
		OUT: [
		'$',
		'.',
		'.',
		'ns',
		'[',
		'*',
		']',
		'.',
		'name'
		]

		IN: '$..book[?(@.price<10)]'
		OUT: [
		'$',
		'.',
		'.',
		'book',
		'[',
		'?',
		'(',
		'@',
		'.',
		'price<10',
		')',
		']'
		)
		 */

		foreach( $parts as $p ) {

		}

		echo '<pre>'.print_r($parts, true).'</pre>';

		if( $parts === false )
			throw new \Core\Exception('Invalid JPath.');

		return $parts;
	}

	protected function traverse($node, string $path, string $qPath, array &$matches): int {
		$cnt = 0;

		if( $this->isMatch($node, $path, $qPath) ) {

			$matches[] = $node;
			$cnt++;
		}

		if( is_array($node) ) {
			foreach( $node as $k => $n ) {
				$p = '';

				if( is_int($k) )
					$p = '['.$k.']';
				else
					$p = '.'.$k;

				$cnt += $this->traverse($n, $path.$p, $qPath, $matches);
			}
		}

		return $cnt;
	}

	protected function isMatch($node, array $path, array $qPath) {

		return false;
	}

	public function ToArray(): ?array {
		return $this->data;
	}
}