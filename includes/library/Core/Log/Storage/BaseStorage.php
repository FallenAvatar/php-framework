<?php declare(strict_types=1);

namespace Core\Log\Storage;

abstract class BaseStorage implements IStorage {
	protected function FormatSource(array $source_array): string {
		return $source_array['class'].$source_array['type'].$source_array['function'].' ('.$source_array['file'].':'.$source_array['line'].')';
	}
}