<?php

namespace System\Web
{
	class URI extends \System\Object
	{
		protected $parts;

		public function _getScheme() { return $this->parts['scheme']; }
		public function _getHost() { return $this->parts['host']; }
		public function _getTLD() { return $this->parts['tld']; }
		public function _getPort() { return $this->parts['port']; }
		public function _getUser() { return $this->parts['user']; }
		public function _getPassword() { return $this->parts['pass']; }
		public function _getPath() { return $this->parts['path']; }
		public function _getQuery() { return $this->parts['query']; }
		public function _getFragment() { return $this->parts['fragment']; }

		public function __construct($uri)
		{
			$this->parts = @parse_url($uri);

			$domain = $this->getRegisteredDomain($this->parts['host']);
			$this->parts['tld'] = substr($domain,strpos($domain,'.')+1);
		}

		protected function getRegisteredDomain($signingDomain) {

			$tldTree = \System\Web\Caching\Cache::CacheLoad('system\web\uri\tld');

			$signingDomainParts = explode('.', $signingDomain);

			$result = $this->findRegisteredDomain($signingDomainParts, $tldTree);

			if ($result===NULL || $result=="") {
				// this is an invalid domain name
				return NULL;
			}

			// assure there is at least 1 TLD in the stripped signing domain
			if (!strpos($result, '.')) {
				$cnt = count($signingDomainParts);
				if ($cnt==1 || $signingDomainParts[$cnt-2]=="") return NULL;
				return $signingDomainParts[$cnt-2].'.'.$signingDomainParts[$cnt-1];
			}
			return $result;
		}

		// recursive helper method
		protected function findRegisteredDomain($remainingSigningDomainParts, &$treeNode) {

			$sub = array_pop($remainingSigningDomainParts);

			$result = NULL;
			if (isset($treeNode['!'])) {
				return '#';
			} else if (is_array($treeNode) && array_key_exists($sub, $treeNode)) {
				$result = findRegisteredDomain($remainingSigningDomainParts, $treeNode[$sub]);
			} else if (is_array($treeNode) && array_key_exists('*', $treeNode)) {
				$result = findRegisteredDomain($remainingSigningDomainParts, $treeNode['*']);
			} else {
				return $sub;
			}

			// this is a hack 'cause PHP interpretes '' as NULL
			if ($result == '#') {
				return $sub;
			} else if (strlen($result)>0) {
				return $result.'.'.$sub;
			}
			return NULL;
		}
	}
}