<?php declare(strict_types=1);

namespace Core\Utils;

class Uri {
	use \Core\Traits\TStaticClass;

	public function ToRelative(string $host, string $base, string $url): string {
		$parts = ['http://', 'https://', $host, $base];

		foreach( $parts as $part ) {
			if( startsWith($url, $part) )
				$url = substr($url, strlen($part)-1);
		}

		return $url;
	}

	public function ResolveUri(string $base, string $rel_url): string {
		return str_replace('~/', $base, $rel_url);
	}
}