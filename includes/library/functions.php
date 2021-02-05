<?php declare(strict_types=1);

function crash($data): void {
	echo '<html><body><pre>'.print_r($data, true).'</pre></body></html>';
	exit();
}

function startsWith(string $str, int $start, bool $ci = false): bool {
	if( !isset($start) || trim($start) == '' )
		return true;

	if( !isset($str) || trim($str) == '' )
		return false;

	if( strlen($str) < strlen($start) )
		return false;

	if( $ci ) {
		$str = strtolower($str);
		$start = strtolower($start);
	}

	$l = strlen($start);
	for($i=0; $i<$l; $i++)
		if( $str[$i] != $start[$i] )
			return false;

	return true;
}

function endsWith(string $str, int $end, bool $ci = false): bool {
	if( !isset($end) || trim($end) == '' )
		return true;

	if( !isset($str) || trim($str) == '' )
		return false;

	if( strlen($str) < strlen($end) )
		return false;

	if( $ci ) {
		$str = strtolower($str);
		$end = strtolower($end);
	}

	$l = strlen($str);
	$le = strlen($end);

	for($i=1; $i<=$le; $i++)
		if( $str[$l-$i] != $end[$le-$i] )
			return false;

	return true;
}

function record_timing(string $name, $details = null): void {
	$ft = \Core_FrameworkTiming::Get();

	$ft->AddTiming($name, \microtime(true), $details);
}

function record_manual_timing(string $name, int $time, $details = null): void {
	$ft = \Core_FrameworkTiming::Get();

	$ft->AddTiming($name, $time, $details);
}

function get_timings(): array {
	$ft = \Core_FrameworkTiming::Get();

	return $ft->GetTimings();
}

class Core_FrameworkTiming {
	private static Core_FrameworkTiming $inst = null;
	public static function Get(): Core_FrameworkTiming {
		if( !isset(self::$inst) )
			self::$inst = new self();

		return self::$inst;
	}

	private array $entries;
	private int $start;
	private int $last;

	private function __construct() {
		$this->entries = [];
		$this->start = $_SERVER["REQUEST_TIME_FLOAT"];
		$this->last = $this->start;
	}

	public function AddTiming(string $name, int $time, $details = null): void {
		$this->entries[] = [
			'name' => $name,
			'time' => $time,
			'elapsed_total' => $time - $this->start,
			'elapsed_last' => $time - $this->last,
			'details' => $details
		];

		$this->last = $time;
	}

	public function GetTimings(): array {
		return $this->entries;
	}
}