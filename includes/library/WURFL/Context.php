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
 * @package    WURFL
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */
/**
 * WURFL Context stores the persistence provider, cache provider and logger objects
 * @package    WURFL
 */
namespace WURFL
{
class Context {
	
	/**
	 * @var \WURFL\Xml\PersistenceProvider\AbstractPersistenceProvider
	 */
	private $persistenceProvider;
	/**
	 * @var \WURFL\Cache\CacheProvider
	 */
	private $cacheProvider;
	/**
	 * @var \WURFL\Logger\Interface
	 */
	private $logger;
	
	public function __construct($persistenceProvider, $caheProvider = null, $logger = null) {
		$this->persistenceProvider = $persistenceProvider;
		$this->cacheProvider = is_null($caheProvider) ? new \WURFL\Cache\NullCacheProvider() : $caheProvider;
		$this->logger = is_null($logger) ? new \WURFL\Logger\NullLogger() : $logger;
	}
	
	public function cacheProvider($cacheProvider) {
		return new \WURFL\Context ( $this->persistenceProvider, $cacheProvider, $this->logger );
	}
	
	public function logger($logger) {
		return new \WURFL\Context ( $this->persistenceProvider, $this->cacheProvider, $logger );
	}
	
	public function __get($name) {
		return $this->$name;
	}

}
}