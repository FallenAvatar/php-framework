<?

namespace Core\Web
{
	class URI extends \Core\Object
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

		public function getRegisteredDomain($signingDomain) {

			$tldTree = \Core\Caching\Cache::CacheLoad('core\web\uri\tld');
			
			if( !isset($tldTree) )
			{
				$tldTree = $this->getTldTree();
				\Core\Caching\Cache::CacheArray('core\web\uri\tld', $tldTree);
			}

			$signingDomainParts = explode('.', strtolower($signingDomain));

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
				$result = $this->findRegisteredDomain($remainingSigningDomainParts, $treeNode[$sub]);
			} else if (is_array($treeNode) && array_key_exists('*', $treeNode)) {
				$result = $this->findRegisteredDomain($remainingSigningDomainParts, $treeNode['*']);
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
		
		protected function getTldTree()
		{
			$tldTree = array();
			
			$tld_names_str = \Core\Curl::GetContents('https://publicsuffix.org/list/effective_tld_names.dat');
			
			$lines = explode("\n", $tld_names_str);
			foreach( $lines as $line )
			{
				$line = trim($line);
						
				if( $line == '' )
					continue;
				if( $line[0] == '/' && $line[1] == '/' )
					continue;
					
				$is_exception = false;
				if( $line[0] == '!' )
				{
					$is_exception = true;
					$line = substr($line, 1);
				}
				
				$i = strpos($line, ' ');
				if( $i !== false )
					$line = substr($line, 0, $i);
				
				$parts = array_reverse(explode('.', $line));
				//$parts = explode('.', $line);
				$curr = &$tldTree;
				
				foreach( $parts as $part )
				{
					if( !isset($curr[$part]) )
						$curr[$part] = array();
						
					$curr = &$curr[$part];
				}
				
				if( $is_exception )
					$curr['!'] = '';
			}
			
			return $tldTree;
		}
	}
}