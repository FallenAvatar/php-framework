<?php

namespace System\Web
{
	interface IRequestHandler
	{
		public function CanHandleRequest($App);
		public function ExecuteRequest($App);
	}
}