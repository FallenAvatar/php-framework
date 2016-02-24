<?php

namespace Site {
	class APISecurityHandler {
		public static function GetSecretKey($api_key) {
			$api = \Site\Data\Security\API::FindByAPIKey($api_key);
			if( !isset($api) || $api->id <= 0 || $api->api_key != $api_key )
				return null;
			
			return $api->secret_key;
		}
	}
}