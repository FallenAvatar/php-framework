<?php declare(strict_types=1);

namespace Core\Log\Storage;

interface IStorage {
	public function Log(int $level, string $message, array $source, $details): void;
}