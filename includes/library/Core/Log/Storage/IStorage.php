<?php

namespace Core\Log\Storage;

interface IStorage {
	public function Log(int $level, string $message, array $source, $details): void;
}