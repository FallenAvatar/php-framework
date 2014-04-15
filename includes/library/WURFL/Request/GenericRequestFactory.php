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
 * @package    WURFL_Request
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @author     Fantayeneh Asres Gizaw
 * @version    $id$
 */
/**
 * Creates a Generic WURFL Request from the raw HTTP Request
 * @package    WURFL_Request
 */
namespace WURFL\Request
{
class GenericRequestFactory {


	/**
	 * Creates Generic Request from the given HTTP Request (normally $_SERVER)
	 * @param array $request HTTP Request
	 * @return WURFL_Request_GenericRequest
	 */
	public function createRequest($request) {
		$userAgent = \WURFL\Utils::getUserAgent($request);
		$userAgentProfile = \WURFL\Utils::getUserAgentProfile($request);
		$isXhtmlDevice = \WURFL\Utils::isXhtmlRequester($request);

		return new GenericRequest($userAgent, $userAgentProfile, $isXhtmlDevice);
	}
	
	/**
	 * Create a Generic Request from the given $userAgent
	 * @param string $userAgent
	 * @return WURFL_Request_GenericRequest
	 */
	public function createRequestForUserAgent($userAgent) {
		return new GenericRequest($userAgent, null, false);
	}
}
}