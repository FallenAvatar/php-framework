<?php declare(strict_types=1);

namespace Core;

final class Password implements \JsonSerializable {
	private string $val;
	public function _getValue(): string { return $this->val; }

	public function __construct(string $v) {
		$this->val = $v;
	}

	public function __toString(): string {
		return '******';
	}

	public function jsonSerialize(): string {
        return ''.$this;
    }
}