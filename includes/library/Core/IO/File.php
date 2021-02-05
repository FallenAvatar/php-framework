<?php

declare(strict_types=1);

namespace Core\IO;

class File {
	public static function Exists(string $path): bool {
		return file_exists($path);
	}
}