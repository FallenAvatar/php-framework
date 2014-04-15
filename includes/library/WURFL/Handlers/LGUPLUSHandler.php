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
 * LGPLUSUserAgentHandler
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
class LGUPLUSHandler extends Handler {

	protected $prefix = "LGUPLUS";

	function __construct($wurflContext, $userAgentNormalizer = null) {
		parent::__construct($wurflContext, $userAgentNormalizer);
	}

	/**
	 *
	 * @param string $userAgent
	 * @return string
	 */
	public function canHandle($userAgent) {
		return Utils::checkIfContainsAnyOf($userAgent, array("LGUPLUS", "lgtelecom"));
	}

	
	/**
	 *
	 * @param string $userAgent
	 * @return string
	 */
	function applyConclusiveMatch($userAgent) {
		return \WURFL\Constants::GENERIC;
	}


	private $lgupluses = array(
		"generic_lguplus_rexos_facebook_browser" => array("Windows NT 5", "POLARIS"),
		"generic_lguplus_rexos_webviewer_browser" => array("Windows NT 5"),
		"generic_lguplus_winmo_facebook_browser" => array("Windows CE", "POLARIS"),
		"generic_lguplus_android_webkit_browser" => array("Android", "AppleWebKit")
		);

	function applyRecoveryMatch($userAgent) {
		foreach($this->lgupluses as $deviceId => $values) {
			if(Utils::checkIfContainsAll($userAgent, $values)) {
				return $deviceId;
			}
		}
		return "generic_lguplus";
	}

}
}