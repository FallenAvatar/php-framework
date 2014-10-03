<?

namespace Core
{
	class Curl extends Object
	{
		public static function GetContents($url)
		{
			$curl = new Curl($url);
			$curl->PrepareGet();
			
			return $curl->Execute();
		}
		
		protected $handle;
		protected $url;
		protected $qs;
		protected $options;
		protected $error;
		protected $cm;
		protected $headers;
		
		public function __construct($url,$cookie_mod = '')
		{
			$this->handle = curl_init();
			$this->url = $url;
			$this->qs = array();
			$this->options = array();
			$this->error = false;
			$this->cm = $cookie_mod;
			$this->headers = array();
		}
		
		public function SetQueryString($arr)
		{
			$this->qs = $arr;
		}
		
		public function PrepareGet()
		{
			$this->options[CURLOPT_HTTPGET] = true;
			
			unset($this->options[CURLOPT_POST]);
			unset($this->options[CURLOPT_POSTFIELDS]);
			unset($this->options[CURLOPT_CUSTOMREQUEST]);
		}
		
		public function PreparePost($data)
		{
			if( is_array($data) && ArrayHelper::IsAssoc($data) )
			{
				$parts = array();
					
				foreach($data as $name => $value)
					$parts[] = urlencode($name).'='.urlencode($value);
				
				$data = implode('&',$parts);
			}
			
			$this->options[CURLOPT_POST] = true;
			$this->options[CURLOPT_POSTFIELDS] = $data;
			
			unset($this->options[CURLOPT_HTTPGET]);
			unset($this->options[CURLOPT_CUSTOMREQUEST]);
		}
		
		public function PrepareDelete()
		{
			$this->options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
			
			unset($this->options[CURLOPT_HTTPGET]);
			unset($this->options[CURLOPT_POST]);
			unset($this->options[CURLOPT_POSTFIELDS]);
		}
		
		public function Execute()
		{
			$url = $this->url;
			
			if( isset($this->qs) )
			{
				if( !is_array($this->qs) )
				{
					if( substr($this->qs,0,1) != '?' )
						$url .= '?';
					
					$url .= $this->qs;
				}
				else if( count($this->qs) > 0 )
				{
					$url .= '?';
					
					$parts = array();
					
					foreach($this->qs as $name => $value)
					{
						$parts[] = urlencode($name).'='.urlencode($value);
					}
					
					$url .= implode('&',$parts);
				}
			}
			
			$defaults = array( 
				CURLOPT_HEADER => 0,
				CURLOPT_URL => $url,
				CURLOPT_FRESH_CONNECT => 1,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FORBID_REUSE => 1,
				CURLOPT_COOKIEJAR => 'cookies'.$this->cm.'.txt',
				CURLOPT_COOKIEFILE => 'cookies'.$this->cm.'.txt'
				);
				
			if( count($this->headers) > 0 )
				$this->options[CURLOPT_HEADER] = $this->headers;
			
			curl_setopt_array($this->handle, ($this->options + $defaults)); 
			$ret = curl_exec($this->handle);
			
			if( !$ret )
				$this->error = curl_error($this->handle);
			
			curl_close($this->handle);
			
			return $ret;
		}
		
		private function _getError()
		{
			return $this->error;
		}
	}
}