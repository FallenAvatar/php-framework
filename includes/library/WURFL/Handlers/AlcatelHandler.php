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
 * AlcatelUserAgentHanlder
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
class AlcatelHandler extends Handler {
	
	function __construct($wurflContext, $userAgentNormalizer = null) {
		parent::__construct($wurflContext, $userAgentNormalizer);
	}
	
	/**
	 * Intercept all UAs starting with "Alcatel" or "ALCATEL"
	 *
	 * @param string $userAgent
	 * @return boolean 
	 */
	public function canHandle($userAgent) {
		return Utils::checkIfStartsWith ( $userAgent, "Alcatel" ) || Utils::checkIfStartsWith ( $userAgent, "ALCATEL" );
	}
	
	protected $prefix = "ALCATEL";
}
}