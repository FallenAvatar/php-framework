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
 * @package    WURFL_Configuration
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * WURFL Configuration holder
 * @package    WURFL_Configuration
 */
namespace WURFL\Configuration
{
class ConfigHolder {
	
	/**
	 * @var WURFL_Configuration_Config
	 */
	private static $_wurflConfig = null;
	
	private function __construct() {
	}
	
	private function __clone() {
	}
	
	/**
	 * Returns a Configuration object
	 * @return WURFL_Configuration_Config
	 */
	public static function getWURFLConfig() {
		if (null === self::$_wurflConfig) {
			throw new \WURFL\Exception ( "The Configuration Holder is not initialized with a valid \WURFL\Config object" );
		}
		
		return self::$_wurflConfig;
	}
	
	/**
	 * Sets the Configuration object
	 * @param WURFL_Configuration_Config $wurflConfig
	 */
	public static function setWURFLConfig(Config $wurflConfig) {
		self::$_wurflConfig = $wurflConfig;
	}
}
}