<?php declare(strict_types=1);

namespace Core\Log\Storage;

class File extends BaseStorage implements IStorage {
	public function Log(int $level, string $message, string $source, $details): void {
	}
}