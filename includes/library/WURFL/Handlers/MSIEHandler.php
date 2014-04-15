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
 * MSIEAgentHanlder
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
class MSIEHandler extends Handler {
	
	protected $prefix = "MSIE";
	
	function __construct($wurflContext, $userAgentNormalizer = null) {
		parent::__construct ( $wurflContext, $userAgentNormalizer );
	}
	/*	
		function applyRecoveryMatch($userAgent){
			return ($userAgent == 'MSIE 9.0')? 'generic_web_browser': null;
		}
	*/	
	/**
	 * Intercept all UAs Starting with Mozilla and Containing MSIE and are not mobile browsers
	 *
	 * @param string $userAgent
	 * @return boolean
	 */
	public function canHandle($userAgent) {
		if (Utils::isMobileBrowser ( $userAgent )) {
			return false;
		}
		
		return Utils::checkIfStartsWith ( $userAgent, "Mozilla" ) && Utils::checkIfContains ( $userAgent, "MSIE" );
	}

}
}