<?php

namespace Core\Data\Statement {
	interface IStatement {
		public function _getSql();
		public function _getParams();
		public function _setParamPrefix($prefix);
	}
}