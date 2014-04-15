<?php

namespace Site\Web
{
	class Application extends \System\Web\Application
	{
		public function BeginRequest()
		{
			parent::BeginRequest();
			$this->Settings->AppName = 'osCommerce Online Merchant';
			$this->Settings->Version = new \System\Version(2,3,3);
			
			//TODO: Load configuration from `configuration` into $this->Settings->*
			//TODO: GZip compression handling
			//TODO: SEO Friendly URLs
			//TODO: Cookie Handling
			//TODO: Cache Handling
		}
		
		public function SessionStarted()
		{
			parent::SessionStarted();
			
			//TODO: OSC Session Handling
			//TODO: Spider Blocking
			//TODO: SSL Checking
			//TODO: IP Address Checking
			//TODO: Language
			//TODO: Currency
			//TODO: Product Handling? (Action Handling/Request Processing)
		}
		
		public function EndRequest()
		{
			parent::EndRequest();
		}
	}
}