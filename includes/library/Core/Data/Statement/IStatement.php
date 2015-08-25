<?php

namespace Core\Data\Statements {
	interface IStatement {
		public function _getSql();
		public function _getParams();
		public function _setParamPrefix();
	}
}