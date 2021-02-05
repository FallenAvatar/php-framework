<?php

declare(strict_types=1);

namespace Core;

class Curl extends Obj {
	public static function GetContents(string $url) {
		$curl = new Curl($url);
		$curl->PrepareGet();

		return $curl->Execute();
	}

	protected $handle;
	protected string $url;
	protected array $qs;
	protected array $options;
	protected ?string $error;
	protected string $cm;
	protected array $headers;

	public function __construct(string $url, string $cookie_mod = '') {
		$this->handle = curl_init();
		$this->url = $url;
		$this->qs = [];
		$this->options = [];
		$this->error = null;
		$this->cm = $cookie_mod;
		$this->headers = [];
	}

	public function AddHeader(string $hdr): void {
		$this->AddHeaders([$hdr]);
	}

	public function AddHeaders(array $hdrs): void {
		foreach($hdrs as $n => $v) {
			if( isset($n) && is_string($n) )
				$this->headers[] = $n.': '.$v;
			else
				$this->headers[] = $v;
		}
	}

	public function SetQueryString(array $arr): void {
		$this->qs = $arr;
	}

	public function PrepareGet(): void {
		$this->options[CURLOPT_HTTPGET] = true;

		unset($this->options[CURLOPT_POST]);
		unset($this->options[CURLOPT_POSTFIELDS]);
		unset($this->options[CURLOPT_CUSTOMREQUEST]);
	}

	public function PreparePostBody(string $body = null): void {
		$this->options[CURLOPT_POST] = true;
		$this->options[CURLOPT_POSTFIELDS] = $body;

		unset($this->options[CURLOPT_HTTPGET]);
		unset($this->options[CURLOPT_CUSTOMREQUEST]);
	}

	public function PreparePostArray(array $data = null): void {
		if( ArrayHelper::IsAssoc($data) ) {
			$parts = [];

			foreach($data as $name => $value)
				$parts[] = urlencode($name).'='.urlencode($value);

			$data = implode('&', $parts);
		}

		$this->options[CURLOPT_POST] = true;
		$this->options[CURLOPT_POSTFIELDS] = $data;

		unset($this->options[CURLOPT_HTTPGET]);
		unset($this->options[CURLOPT_CUSTOMREQUEST]);
	}

	public function PrepareDelete(): void {
		$this->options[CURLOPT_CUSTOMREQUEST] = 'DELETE';

		unset($this->options[CURLOPT_HTTPGET]);
		unset($this->options[CURLOPT_POST]);
		unset($this->options[CURLOPT_POSTFIELDS]);
	}

	public function DisableSSLVerify(): void {
		$this->options[CURLOPT_SSL_VERIFYHOST] = 0;
		$this->options[CURLOPT_SSL_VERIFYPEER] = 0;
	}

	public function Execute() {
		$url = $this->url;

		if( isset($this->qs) ) {
			if( strpos($url, '?') !== false )
				$url .= '&';
			else
				$url .= '?';

			$parts = [];

			foreach($this->qs as $name => $value)
				$parts[] = urlencode($name).'='.urlencode($value);

			$url .= implode('&', $parts);
		}

		$defaults = [
			CURLOPT_HEADER => 0,
			CURLOPT_URL => $url,
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_COOKIEJAR => 'cookies'.$this->cm.'.txt',
			CURLOPT_COOKIEFILE => 'cookies'.$this->cm.'.txt',
			CURLOPT_USERAGENT => 'PHP cURL'
		];

		if( count($this->headers) > 0 )
			$this->options[CURLOPT_HTTPHEADER] = $this->headers;

		$opts = ($this->options + $defaults);

		curl_setopt_array($this->handle, $opts);

		$ret = curl_exec($this->handle);

		//$info = curl_getinfo($this->handle);

		if( !$ret )
			$this->error = curl_error($this->handle);

		curl_close($this->handle);

		return $ret;
	}

	private function _getIsError(): bool {
		return isset($this->error) && trim($this->error) != '';
	}

	private function _getError(): ?string {
		return $this->error;
	}
}