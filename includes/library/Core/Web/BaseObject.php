<?php

declare(strict_types=1);

namespace Core\Web;

class BaseObject extends \Core\Obj {
	public static function isJsonRequest(): bool {
		$reqWithXHR = $ctJSON = false;

		if( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || \startsWith($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest')) ) {
			$reqWithXHR = true;
		}

		if( isset($_SERVER['CONTENT_TYPE']) && ($_SERVER['CONTENT_TYPE'] == 'application/json' || \startsWith($_SERVER['CONTENT_TYPE'], 'application/json')) ) {
			$ctJSON = true;
		}

		return $reqWithXHR && $ctJSON;
	}

	public static function getJsonBody(): ?array {
		$ret = $inStream = null;

		try {
			$inStream = fopen('php://input', 'r');
			$json_string = fgets($inStream);
			$ret = json_decode($json_string, true);
		} catch(\Exception $e) {
			$ret = null;
		}

		if( isset($inStream) )
			fclose($inStream);

		return $ret;
	}

	protected $App;

	protected $Get;
	protected $Post;
	protected $Session;
	protected $Cookies;
	protected $Files;
	protected $Server;

	protected $OutHeaders;

	public function __construct() {
		global $_GET, $_POST, $_SESSION, $_COOKIE, $_FILES, $_SERVER;
		$this->App = \Core\Application::Get();

		$this->Get = $_GET;
		$this->Post = $_POST;
		$this->Session = $_SESSION;
		$this->Cookies = $_COOKIE;
		$this->Files = $_FILES;
		$this->Server = $_SERVER;

		$this->OutHeaders = [];

		if( static::isJsonRequest() )
			$this->Post = static::getJsonBody();
	}

	public function GetValue(string $name) {
		if( isset($this->Post[$name]) )
			return $this->Post[$name];

		return $this->Get[$name];
	}

	public function Redirect(string $url) {
		$this->Header('Location', $url, true);
		exit();
	}

	public function Header(string $name, $value, bool $overwrite = false) {
		if( !$overwrite && in_array($name, $this->OutHeaders) )
			return;

		$this->OutHeaders[] = $name;
		header($name.': '.$value);
	}

	// Helper Functions
	public function GetMethod(): ?string {
		if( !isset($this->Server['REQUEST_METHOD']) )
			return null;

		return strtoupper($this->Server['REQUEST_METHOD']);
	}

	public function IsPost(): bool {
		return ($this->GetMethod() == 'POST');
	}

	public function IsGet(): bool {
		return ($this->GetMethod() == 'GET');
	}

	public function IsPut(): bool {
		return ($this->GetMethod() == 'PUT');
	}

	public function IsDelete(): bool {
		return ($this->GetMethod() == 'DELETE');
	}
}