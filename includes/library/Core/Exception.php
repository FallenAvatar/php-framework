<?php declare(strict_types=1);

namespace Core;

class Exception extends \Exception {
	public static function ToJsonObject(\Throwable $ex): array {
		$inner = $ex->getPrevious();
		if( isset($inner) )
			$inner = static::ToJsonObject($inner);

		return [
			'message' => $ex->getMessage(),
			'code' => $ex->getCode(),
			'file' => $ex->getFile(),
			'line' => $ex->getLine(),
			'trace' => $ex->getTrace(),
			'inner' => $inner
		];
	}

	/* Inherited properties */
	//protected string $message;
	public function _getMessage(): string { return $this->message; }
	//protected int $code;
	public function _getCode(): ?int { return $this->code; }
	//protected string $file;
	public function _getFile(): ?string { return $this->file; }
	//protected int $line;
	public function _getLine(): ?int { return $this->line; }

	/* Properties */
	protected int $severity;
	public function _getSeverity(): int { return $this->severity; }
	protected ?\Throwable $previous;
	public function _getPrevious(): ?\Throwable { return $this->previous; }
	protected ?array $backtrace;
	public function _getBacktrace(): ?array { return $this->backtrace; }

	public function __construct( string $message, ?int $code = null, ?int $severity = null, ?string $filename = null, ?int $lineno = null, ?Exception $previous = null, ?array $backtrace = null ) {
		$this->message = $message;
		$this->code = $code;
		$this->severity = $severity;
		$this->file = $filename;
		$this->line = $lineno;
		$this->previous = $previous;
		$this->backtrace = $backtrace;
	}
}