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
 * PantechUserAgentHandler
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
class PantechHandler extends Handler {
	
	function __construct($wurflContext, $userAgentNormalizer = null) {
		parent::__construct ( $wurflContext, $userAgentNormalizer );
	}
	
	/**
	 * Intercept all UAs starting with "Pantech","PANTECH","PT-" or "PG-"
	 *
	 * @param string $userAgent
	 * @return boolean
	 */
	public function canHandle($userAgent) {
		return Utils::checkIfStartsWith ( $userAgent, "Pantech" ) || Utils::checkIfStartsWith ( $userAgent, "PANTECH" ) || Utils::checkIfStartsWith ( $userAgent, "PT-" ) || Utils::checkIfStartsWith ( $userAgent, "PG-" );
	}
	
	/**
	 * If starts with "PT-", "PG-" or "PANTECH", use RIS with FS
	 * Otherwise LD with threshold 4
	 *
	 * @param string $userAgent
	 * @return string
	 */
	function lookForMatchingUserAgent($userAgent) {
		if (Utils::checkIfStartsWith ( $userAgent, "Pantech" )) {
			return Utils::ldMatch ( array_keys ( $this->userAgentsWithDeviceID ), $userAgent, self::PANTECH_TOLLERANCE );
		}
		$tollerance = Utils::firstSlash ( $userAgent );
		return Utils::risMatch ( array_keys ( $this->userAgentsWithDeviceID ), $userAgent, $tollerance );
		
	}
	
	const PANTECH_TOLLERANCE = 4;
	protected $prefix = "PANTECH";
}
}