<?php

namespace WP
{
	class Site extends \System\Object
	{
		public static function CurrentSite()
		{
			global $domain, $cookie_domain;
			
			if( !is_multisite() )
			{
				$site = new \stdClass();
				
				$site->id = 1;
				$site->domain = $_SERVER['HTTP_HOST'];
				$site->path = \realpath(dirname(__FILE__).DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS);
				$site->blog_id = 1;
				
				if ( substr( $site->domain, 0, 4 ) == 'www.' )
					$site->cookie_domain = substr( $site->domain, 4 );
				else
					$site->cookie_domain = $site->domain;
				
				return new Site($site);
			}
			
			return new Site(wpmu_current_site());
		}
		
		protected $internal_site_object;
		
		public function __construct($site_object = null)
		{
			$this->internal_site_object = $site_object;
		}
	}
}