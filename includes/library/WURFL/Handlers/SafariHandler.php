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
 * SafariHanlder
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
class SafariHandler extends Handler {
	
	protected $prefix = "SAFARI";
	
	function __construct($wurflContext, $userAgentNormalizer = null) {
		parent::__construct ( $wurflContext, $userAgentNormalizer );
	}
	
	/**
	 * Intercept all UAs Starting with Mozilla and Containing Safari and are not mobile browsers
	 *
	 * @param string $userAgent
	 * @return boolean
	 */
	public function canHandle($userAgent) {
		if (Utils::isMobileBrowser ( $userAgent )) {
			return false;
		}
		
		return Utils::checkIfStartsWith ( $userAgent, "Mozilla" ) && Utils::checkIfContains ( $userAgent, "Safari" );
	}

}
}