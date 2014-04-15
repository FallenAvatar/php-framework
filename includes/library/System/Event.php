<?php

namespace System
{
	class Event extends Object
	{
		protected $handlers;
		
		public function __construct()
		{
			$this->handlers = array();
		}
		
		public function Register(callable $handler)
		{
			$this->handlers[] = $handler;
		}
		
		public function Unregister(callable $handler)
		{
			$temp = array();
			
			foreach( $this->handlers as $h )
			{
				if( $h !== $handler )
					$temp[] = $h;
			}
			
			$this->handlers = $temp;
		}
		
		public function Call($sender, EventArgs $e)
		{
			foreach( $this->handlers as $h )
				$h($sender, $e);
		}
	}
}