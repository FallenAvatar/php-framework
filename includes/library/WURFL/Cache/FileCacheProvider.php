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
 * @package    WURFL_Cache
 * @copyright  ScientiaMobile, Inc.
 * @license    GNU Affero General Public License
 * @version    $id$
 */

/**
 * A Cache Provider that uses the File System as a storage
 *
 * @category   WURFL
 * @package    WURFL_Cache
 */
namespace WURFL\Cache
{
class FileCacheProvider implements CacheProvider {
	
	private $_cacheDir;
	const DIR = "dir";
	
	private $cacheIdentifier = "FILE_CACHE_PROVIDER";
	private $expire;
	private $root;
	
	function __construct($params) {
		if (is_array ( $params )) {
			if (! array_key_exists ( self::DIR, $params )) {
				throw new \WURFL\Exception ( "Specify a valid cache dir in the configuration file" );
			}
			
			// Check if the directory exist and it is also write access
			if (! is_writable ( $params [self::DIR] )) {
				throw new \WURFL\Exception ( "The diricetory specified <" . $params [self::DIR] . " > for the cache provider does not exist or it is not writable\n" );
			}
			
			$this->_cacheDir = $params [self::DIR] . DIRECTORY_SEPARATOR . $this->cacheIdentifier;
			$this->root = $params [self::DIR] . DIRECTORY_SEPARATOR . $this->cacheIdentifier;
			$this->expire = isset ( $params [CacheProvider::EXPIRATION] ) ? $params [CacheProvider::EXPIRATION] : CacheProvider::NEVER;
			
			\WURFL\FileUtils::mkdir( $this->_cacheDir );
		}
		
	}
	
	public function get($key) {
		$path = $this->keyPath ( $key );
		$data = \WURFL\FileUtils::read ( $path );
		if (! is_null ( $data ) && $this->expired ( $path )) {
			unlink ( $path );
			return NULL;
		}
		return $data;
	}
	
	public function put($key, $value) {
		$mtime = time () + $this->expire;
		$path = $this->keyPath ( $key );
		\WURFL\FileUtils::write ( $path, $value, $mtime );
	}
	
	public function clear() {
		\WURFL\FileUtils::rmdirContents($this->root);
	}
	
	private function expired($path) {
		if ($this->expire === 0) {
			return FALSE;
		}
		return filemtime ( $path ) < time ();
	}
	
	private function neverToExpire() {
		return $this->expire === 0;
	}
	
	private function keyPath($key) {
		return \WURFL\FileUtils::join ( array ($this->root, $this->spread ( md5 ( $key ) ) ) );
	}
	
	public function spread($md5, $n = 2) {
		$path = "";
		for($i = 0; $i < $n; $i ++) {
			$path .= $md5 [$i] . DIRECTORY_SEPARATOR;
		}
		$path .= substr ( $md5, $n );
		return $path;
	}

}
}