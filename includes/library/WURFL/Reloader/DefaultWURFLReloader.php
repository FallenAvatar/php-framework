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
 * @package    WURFL_Reloader
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 * @deprecated
 */
/**
 * WURFL Reloader
 * @package    WURFL_Reloader
 * @deprecated
 */
namespace WURFL\Reloader
{
class DefaultWURFLReloader implements ReloaderInterface {
	
	public function reload($wurflConfigurationPath) {
		$wurflConfig = $this->fromFile ( $wurflConfigurationPath );
		touch($wurflConfig->wurflFile);
		$wurflManagerFactory = new \WURFL\ManagerFactory($wurflConfig);
		$wurflManagerFactory->create();	
		
	}
	
	private function fromFile($wurflConfigurationPath) {
		if ($this->endsWith ( $wurflConfigurationPath, ".xml" )) {
			return new \WURFL\Configuration\XmlConfig ( $wurflConfigurationPath );
		}
		return new \WURFL\Configuration\ArrayConfig($wurflConfigurationPath);
	}
	
	private function endsWith($haystack, $needle) {
		return strrpos($haystack, $needle) === strlen($haystack)-strlen($needle);
	}
}
}