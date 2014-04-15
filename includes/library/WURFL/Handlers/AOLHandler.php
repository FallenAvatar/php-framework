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
 * @category   WURFL
 * @package    WURFL_Handlers
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

/**
 * AOLHanlder
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
class AOLHandler extends Handler {
	
	protected $prefix = "AOL";
	
	// LD Match Tollerance
	const AOL_LD_TOLLERANCE = 5;
	
	function __construct($wurflContext, $userAgentNormalizer = null) {
		parent::__construct ( $wurflContext, $userAgentNormalizer );
	}
	
	/**
	 * Intercept all UAs Containing AOL and are not mobile browsers
	 *
	 * @param string $userAgent
	 * @return boolean
	 */
	public function canHandle($userAgent) {
		if (Utils::isMobileBrowser ( $userAgent )) {
			return false;
		}
		
		return Utils::checkIfContains ( $userAgent, "AOL" );
	}
	
	/**
	 * Apply LD Match with tollerance 5
	 *
	 */
	function lookForMatchingUserAgent($userAgent) {
		return Utils::ldMatch ( array_keys ( $this->userAgentsWithDeviceID ), $userAgent, self::AOL_LD_TOLLERANCE );
	}

}
}