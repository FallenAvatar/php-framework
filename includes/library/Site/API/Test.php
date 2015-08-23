<?php

namespace Site\API
{
	class Test extends \Core\Web\BaseObject
	{
		public function One($varOne, $two, $opt = 'asdf')
		{
			return array(
				'error' => false,
				'varOne' => $varOne,
				'two' => $two,
				'opt' => $opt,
			);
		}
	}
}