<?php
/**
 * Copyright (c) 2011 ScientiaMobile, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the COPYING file distributed with this package.
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

/**
 * SamsungUserAgentHanlder
 *
 *
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
namespace WURFL\Handlers
{
class SamsungHandler extends Handler {
	
	function __construct($wurflContext, $userAgentNormalizer = null) {
		parent::__construct ( $wurflContext, $userAgentNormalizer );
	}
	
	/**
	 *
	 * @param string $userAgent
	 * @return boolean
	 */
	function canHandle($userAgent) {
		return Utils::checkIfContains ( $userAgent, "Samsung/SGH" )
			|| Utils::checkIfStartsWithAnyOf ( $userAgent, array("SEC-","Samsung","SAMSUNG", "SPH", "SGH", "SCH"));
	}


	/**
	* If UA starts with one of the following ("SEC-", "SAMSUNG-", "SCH"), apply RIS with FS.
	* If UA starts with one of the following ("Samsung-","SPH", "SGH" ), apply RIS with First Space (not FS).
	* If UA starts with "SAMSUNG/", apply RIS with threshold SS (Second Slash)
	*
	* @param string $userAgent
	* @return string
	*/
	function lookForMatchingUserAgent($userAgent) {
		$tolerance = $this->tolerance($userAgent);
		$this->logger->log ( "$this->prefix :Applying Conclusive Match for ua: $userAgent with tolerance $tolerance" );
		return Utils::risMatch ( array_keys ( $this->userAgentsWithDeviceID ), $userAgent, $tolerance );
	}

	
	private function tolerance($userAgent) {
		if(Utils::checkIfStartsWithAnyOf($userAgent, array("SEC-", "SAMSUNG-", "SCH"))) {
			return Utils::firstSlash($userAgent);
		}
		if(Utils::checkIfStartsWithAnyOf($userAgent, array("Samsung-","SPH", "SGH"))) {
			return Utils::firstSpace($userAgent);
		}
		if(Utils::checkIfStartsWith($userAgent, "SAMSUNG/")) {
			return Utils::secondSlash($userAgent);
		}
		return Utils::firstSlash($userAgent);
	}

	protected $prefix = "SAMSUNG";
}
}