<?php

namespace System
{
	class Version
	{
		public $MajorVersion;
		public $MinorVersion;
		public $Revision
		public $BuildNumber;
		
		public function __construct($major, $minor = 0, $rev = 0, $build = 0)
		{
			$this->MajorVersion = $major;
			$this->MinorVersion = $minor;
			$this->Revision = $rev;
			$this->BuildNumber = $build;
		}
		
		public function __toString()
		{
			return $this->MajorVersion . '.' . $this->MinorVersion . (($this->Revision > 0) ? '.' . $this->Revision : '') . (($this->BuildNumber > 0) ? '.' . $this->BuildNumber : '');
		}
	}
}