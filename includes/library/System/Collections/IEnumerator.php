<?php

namespace System\Collections
{
	interface IEnumerator
	{
		public function getCurrent();
		
		public function MoveNext();
		public function Reset();
	}
}