<?php declare(strict_types=1);

namespace Core\Data;

class Migration extends \Core\Obj {
	public static function Run() {
		$inst = new static();
		$inst->RunSteps();
	}

	protected int $schema_version = 0;
	public function _getSchemaVersion(): int { return $this->schema_version; }
	protected string $module_name = 'core';
	public function _getModuleName(): string { return $this->module_name; }
	protected array $steps;
	protected string $base_dir;
	public function _getBaseDir(): string { return $this->base_dir; }

	protected function __construct(string $module = 'core', int $ver = 0, ?string $base_dir = null) {
		$this->module_name = $module;
		$this->schema_version = $ver;
		$this->steps = [];
		if( !isset($base_dir) )
			$this->base_dir = \Core\IO\Path::Combine(\Core\Application::Get()->Dirs->Data, 'migrations');
		else
			$this->base_dir = \Core\IO\Path::Combine(\Core\Application::Get()->Dirs->Includes, $base_dir);
	}

	protected function AddStep(string $name, $func_or_file): void {
		if( is_string($func_or_file) && is_file(\Core\IO\Path::Combine($this->base_dir,$func_or_file)) ) {
			$this->steps[] = [
				'name' => $name,
				'type' => 'sql',
				'file' => \Core\IO\Path::Combine($this->base_dir, $func_or_file)
			];
		} else if( is_callable($func_or_file) ) {
			$this->steps[] = [
				'name' => $name,
				'type' => 'callable',
				'func' => $func_or_file
			];
		} else {
			throw new \Core\Exception('Expected filename of a sql file that exists in migrations folder or callable.');
		}
	}

	public function RunSteps(): void {
		$db = \Core\Data\Database::Get();
		$db->StartTransaction();

		try {
			foreach( $this->steps as $step ) {
				if( $step['type'] == 'callable' ) {
					$step['func']();
				} else if( $step['type'] == 'sql' ) {
					$db->ExecuteScript(file_get_contents($step['file']));
				} else {
					throw new \Core\Exception("Unexpected migration step type. Expected 'callable' or 'sql', found [".$step['type']."]");
				}
			}

			$db->ExecuteNonQuery('INSERT INTO '.$db->DelimTable('_db_migrations').'('.$db->DelimColumn('module').','.$db->DelimColumn('version').','.$db->DelimColumn('dt').') VALUES ('.$db->DelimParameter('m').', '.$db->DelimParameter('v').', UNIX_TIMESTAMP())',['m' => $this->module_name, 'v' => $this->schema_version]);

			$db->CommitTransaction();
		} catch(\Throwable $t) {
			$db->RollbackTransaction();

			throw $t;
		}


	}
}