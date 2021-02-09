<?php

namespace Core\Data;

class Migration extends \Core\Obj {
	public static function Run() {
		$inst = new static();
		$inst->RunSteps();
	}

	protected int $schema_version = 0;
	public function _getSchemaVersion(): int { return $this->schema_version; }
	protected array $steps;

	protected function __construct(int $ver = 0) {
		$this->schema_version = $ver;
		$this->steps = [];
	}

	protected function AddStep(string $name, $func_or_file): void {
		if( is_string($func_or_file) && is_file(\Core\IO\Path::Combine(\Core\Application::Get()->Dirs->Data,'migrations',$func_or_file)) ) {
			$this->steps[] = [
				'name' => $name,
				'type' => 'sql',
				'file' => \Core\IO\Path::Combine(\Core\Application::Get()->Dirs->Data, 'migrations', $func_or_file)
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

			$db->ExecuteNonQuery('UPDATE '.$db->DelimTable('_db_migrations').' SET '.$db->DelimColumn('value').' = '.$db->DelimParameter('v').' WHERE name = '.$db->DelimParameter('n'),['n' => 'schema_version', 'v' => $this->schema_version]);

			$db->CommitTransaction();
		} catch(\Throwable $t) {
			$db->RollbackTransaction();

			throw $t;
		}


	}
}