<?php

declare(strict_types=1);

namespace Core;

class Application extends \Core\Obj {
	public static array $HttpErrorCodeText = [
		100 => "Continue",
		101 => "Switching Protocols",
		200 => "OK",
		201 => "Created",
		202 => "Accepted",
		203 => "Non-Authoritative Information",
		204 => "No Content",
		205 => "Reset Content",
		206 => "Partial Content",
		300 => "Multiple Choices",
		301 => "Moved Permanently",
		302 => "Found",
		303 => "See Other",
		304 => "Not Modified",
		305 => "Use Proxy",
		306 => "(Unused)",
		307 => "Temporary Redirect",
		400 => "Bad Request",
		401 => "Unauthorized",
		402 => "Payment Required",
		403 => "Forbidden",
		404 => "Not Found",
		405 => "Method Not Allowed",
		406 => "Not Acceptable",
		407 => "Proxy Authentication Required",
		408 => "Request Timeout",
		409 => "Conflict",
		410 => "Gone",
		411 => "Length Required",
		412 => "Precondition Failed",
		413 => "Request Entity Too Large",
		414 => "Request-URI Too Long",
		415 => "Unsupported Media Type",
		416 => "Requested Range Not Satisfiable",
		417 => "Expectation Failed",
		500 => "Internal Server Error",
		501 => "Not Implemented",
		502 => "Bad Gateway",
		503 => "Service Unavailable",
		504 => "Gateway Timeout",
		505 => "HTTP Version Not Supported"
	];

	protected static ?Application $s_inst = null;

	public static function Get(): Application {
		return self::$s_inst;
	}

	public static function Run(): void {
		record_timing(__CLASS__.'::'.__FUNCTION__.' - Start');

		$class = '';
		if( \Core\Autoload\StandardAutoloader::Get()->CanLoadClass("\\Site\\Application") )
			$class = "\\Site\\Application";
		else
			$class = "\\Core\\Application";

		self::$s_inst = new $class();

		if( !(self::$s_inst instanceof \Core\Application) )
			throw new \Exception('Application class ['.$class.'] found, but it does not entend \Core\Application.');

		self::$s_inst->_init();
		self::$s_inst->_run();

		exit();
	}

	public function UrlToRelative($url): string {
		if( $url instanceof \Core\Web\URI )
			$url = $url . '';

		$root = $this->Urls->WebRoot;
		$absroot = $this->Urls->AbsoluteWebRoot;

		if( startsWith($url, $absroot) )
			$url = substr($url, strlen($absroot)-1);

		if( startsWith($url, $root) )
			$url = substr($url, strlen($root)-1);

		return $url;
	}

	public function ResolveUrl(string $path): string {
		return str_replace('~/', $this->Urls->AbsoluteWebRoot, $path);
	}

	protected ?Configuration\Config $Config = null;
	public function _getConfig() {
		return $this->Config;
	}

	protected $moduleLoader = null;

	protected DynObject $Dirs;
	public function _getDirs(): DynObject {
		return $this->Dirs;
	}
	protected DynObject $Urls;
	public function _getUrls(): DynObject {
		return $this->Urls;
	}
	protected array $Modules;
	public function _getModules(): array {
		return $this->Modules;
	}

	protected ?Web\Request $Request;
	public function _getRequest(): ?Web\Request {
		return $this->Request;
	}
	protected $LastError;
	public function _getLastError() {
		return $this->LastError;
	}

	protected function __construct() {
		$this->Dirs = new DynObject([], false, true);
		$this->Urls = new DynObject([], false, true);
		$this->Modules = [];

		$this->BuildDirs();
		$this->BuildUrls();
	}

	protected function BuildDirs(): void {
		record_timing(__CLASS__.'::'.__FUNCTION__.' - Start');

		$this->AddDir('Root', $this->GetRootDir());
		$this->AddDir('Includes', $this->Dirs->Root.'includes'.DS);
		$this->AddDir('Library', $this->Dirs->Includes.'library'.DS);
		$this->AddDir('Configs', $this->Dirs->Includes.'configs'.DS);
		$this->AddDir('Layouts', $this->Dirs->Includes.'layouts'.DS);
		$this->AddDir('Modules', $this->Dirs->Includes.'modules'.DS);
		$this->AddDir('Data', $this->Dirs->Includes.'data'.DS);
		$this->AddDir('Vendor', $this->Dirs->Includes.'vendor'.DS);
		$this->AddDir('Cache', $this->Dirs->Data.'cache'.DS);
		$this->AddDir('DocumentRoot', realpath(((isset($_SERVER['SUBDOMAIN_DOCUMENT_ROOT'])) ? $_SERVER['SUBDOMAIN_DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'])));
		$this->AddDir('WebRoot', str_replace($this->Dirs->DocumentRoot, '', $this->Dirs->Root));

		record_timing(__CLASS__.'::'.__FUNCTION__.' - End');
	}

	protected function AddDir(string $name, string $path): void {
		$this->Dirs->$name = $path;
	}

	protected function BuildUrls(): void {
		record_timing(__CLASS__.'::'.__FUNCTION__.' - Start');

		$host = $_SERVER['HTTP_HOST'];
		$secure = isset($_SERVER['SERVER_PORT_SECURE']) && $_SERVER['SERVER_PORT_SECURE'] == '1';
		$port = $_SERVER['SERVER_PORT'];

		$root_url = 'http'.($secure ? 's' : '').'://'.$host.($secure ? ($port == '443' ? '' : ':'.$port) : ($port == '80' ? '' : ':'.$port)).'/';

		$this->AddUrl('Root', $root_url);

		$webroot = $this->Dirs->WebRoot;
		if( DS != '/' )
			$webroot = \str_replace(DS, '/', $webroot);

		$this->AddUrl('WebRoot', $webroot);
		$this->AddUrl('DocumentRoot', \Core\Web\URI::Append($root_url, $webroot));

		record_timing(__CLASS__.'::'.__FUNCTION__.' - End');
	}

	protected function AddUrl(string $name, string $path): void {
		$this->Urls->$name = $path;
	}

	protected function GetRootDir(): string {
		return realpath(dirname(__FILE__).DS.'..'.DS.'..'.DS.'..'.DS).DS;
	}

	public function ErrorHandler(int $errno, string $errstr, ?string $errfile, ?int $errline, ?array $errcontext) {
		// Ignore notices except "Undefined variable" errors
		if( $errno == E_NOTICE && strcmp(substr($errstr, 0, 19), 'Undefined variable:') != 0 )
			return true;

		// Call Exception Handler
		throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
	}

	public function ExceptionHandler(\Throwable $ex) {
		error_log('\Core\Application::ExceptionHandler - '.json_encode(\Core\Exception::ToJsonObject($ex)), 0, \Core\IO\Path::Combine($this->Dirs->Includes, 'error_log'));

		$this->LastError = $ex;
		throw $ex;
		//$this->ErrorPageHandler(500);
	}

	public function ErrorPageHandler(int $errorCode): void {
		$txt = '';
		if( isset(self::$HttpErrorCodeText[$errorCode]) )
			$txt = ' '.self::$HttpErrorCodeText[$errorCode];

		ob_end_clean();
		header('HTTP/1.1 '.$errorCode.$txt);

		$errorPath = $this->Dirs->Root.DS.'error'.DS.$errorCode.'.phtml';

		if( \Core\IO\File::Exists($errorPath) ) {
			// Print pretty error
			$handler = new \Core\Handlers\PageHandler();
			$handler->ExecuteErrorRequest($this, $errorPath, $errorCode);
		}

		exit(0);
	}

	protected function _init(): void {
		record_timing(__CLASS__.'::'.__FUNCTION__.' - Start');

		set_error_handler([$this,'ErrorHandler']);
		set_exception_handler([$this,'ExceptionHandler']);

		session_start();

		$this->Request = new \Core\Web\Request();

		$webroot =  $this->Request->Url->Scheme.'://'.$this->Request->Url->Host;
		$webroot .= ($this->Request->Url->Port == '80' || $this->Request->Url->Port == '443' || !isset($this->Request->Url->Port)) ? '' : ':'.$this->Request->Url->Port;
		$webroot .= $this->Urls->WebRoot;

		$this->AddUrl('AbsoluteWebRoot', $webroot);

		record_timing(__CLASS__.'::'.__FUNCTION__.' - Module Manager Init - Start');
		\Core\Module\Manager::Init($this);
		record_timing(__CLASS__.'::'.__FUNCTION__.' - Module Manager Init - End');

		record_timing(__CLASS__.'::'.__FUNCTION__.' - Start Loading Configs');
		$configFiles = $this->_loadConfig();

		foreach( \Core\Module\Manager::GetConfigs() as $config )
			$configFiles[] = $config;

		$configFiles[] = $this->_loadSiteConfig();

		$configStack = new \Core\Configuration\ConfigStack($configFiles);
		$this->Config = $configStack->GetMergedConfig();

		record_timing(__CLASS__.'::'.__FUNCTION__.' - End Loading Configs');

		$this->_fixPhp();

		record_timing(__CLASS__.'::'.__FUNCTION__.' - Init Modules - Start');
		\Core\Module\Manager::InitModules();
		record_timing(__CLASS__.'::'.__FUNCTION__.' - Init Modules - End');

		record_timing(__CLASS__.'::RunMigrations - Start');
		//$this->RunMigrations();
		record_timing(__CLASS__.'::RunMigrations - End');

		record_timing(__CLASS__.'::OnInit - Start');
		$this->OnInit();
		record_timing(__CLASS__.'::OnInit - End');

		record_timing(__CLASS__.'::'.__FUNCTION__.' - End');
	}

	protected function _loadConfig(): array {
		record_timing(__CLASS__.'::'.__FUNCTION__.' - Start');
		$files = [];
		$files[] = $this->Dirs->Configs.'core.json';

		$d = dir($this->Dirs->Configs);

		while( false !== ($entry = $d->read()) ) {
			$filePath = \Core\IO\Path::Combine($this->Dirs->Configs, $entry);
			if( !is_file($filePath) )
				continue;

			if( substr($filePath, -5) != '.json' )
				continue;

			if( $entry == 'core.json' || $entry == 'site.json' )
				continue;

			$files[] = $filePath;
		}

		record_timing(__CLASS__.'::'.__FUNCTION__.' - End');
		return $files;
	}

	protected function _loadSiteConfig(): string {
		return $this->Dirs->Configs.'site.json';
	}

	protected function _fixPhp(): void {
		record_timing(__CLASS__.'::'.__FUNCTION__.' - Start');

		if( isset($this->Config->PHP) ) {
			if( isset($this->Config->PHP->functions) ) {
				$functions = $this->Config->PHP->functions;
				foreach($functions as $f) {
					$func = $f['name'];
					$params_to_pass = $f['args'];

					call_user_func_array($func, $params_to_pass);
				}
			}

			if( isset($this->Config->PHP->ini) ) {
				$values = $this->Config->PHP->ini->ToArray();
				foreach($values as $name => $value) {
					ini_set($name, $value);
				}
			}
		}

		record_timing(__CLASS__.'::'.__FUNCTION__.' - End');
	}

	protected function RunMigrations(): void {
		$files = [];
		$migrations = [];
		$dp = \Core\IO\Path::Combine($this->Dirs->Data,'migrations');
		$d = \dir($dp);

		while( false !== ($f = $d->read()) ) {
			if( $f == '.' || $f == '..' || !is_file(\Core\IO\Path::Combine($dp,$f)) )
				continue;

			if( substr($f, -4) != '.php' )
				continue;

			$cn = '\\Data\\Migrations\\'.substr($f,0,-4);
			require_once(\Core\IO\Path::Combine($dp,$f));
			$migrations[] = new $cn();
		}

		$d->close();

		\usort($migrations, function($a, $b) {
			return $a->SchemaVersion - $b->SchemaVersion;
		});

		$db = \Core\Data\Database::Get();

		$curr_ver = $db->ExecuteScalar('SELECT '.$db->DelimColumn('value').' FROM '.$db->DelimTable('settings').' WHERE name = '.$db->DelimParameter('name'),['name' => 'schema_version']);

		foreach( $migrations as $m ) {
			if( $m->SchemaVersion <= $curr_ver )
				continue;

			$m->RunSteps();
		}
	}

	protected function LoadModules(): void {
		record_timing(__CLASS__.'::'.__FUNCTION__.' - Start');
		// TODO: Load Modules
		$modsDir = dir($this->Dirs->Modules);

		while( false !== ($entry = $modsDir->read()) ) {
			if( $entry == '.' || $entry == '..' )
				continue;

			$ePath = \Core\IO\Path::Combine($this->Dirs->Modules, $entry);

			if( !is_dir($ePath) )
				continue;

			$configPath =  \Core\IO\Path::Combine($ePath, 'module.json');

			if( !is_file($configPath) ) {
				// TODO: Log
				continue;
			}

			$modConfig = null;

			try {
				$configJson = json_decode(file_get_contents($configPath), true);
				$modConfig = new \Core\Configuration\Config($configJson);
			} catch(\Exception $e) {
				// TODO: Log
				continue;
			}

			\Core\Module\Manager::LoadModule($modConfig, $ePath);
		}

		$allMods = \Core\Module\Manager::GetModules();

		record_timing(__CLASS__.'::'.__FUNCTION__.' - End');
	}

	protected function _run(): void {

		$handler_name = \Core\Handlers\HandlerFactory::ProcessRequest();

		$this->OnAfterHandlerRun($handler_name);

		record_timing('end');

		if( $this->Config->Core->debug )
			$this->printTimings();
	}

	protected function printTimings(): void {
		$pad_len = 24;
		$ts = get_timings();

		foreach( $ts as $t ) {
			if( (strlen($t['name'])+4) > $pad_len )
				$pad_len = strlen($t['name'])+4;
		}

		$hdrs = \headers_list();
		$ct = null;
		$comments = null;

		foreach( $hdrs as $hdr ) {
			$i = strpos($hdr, ':');
			if( $i <= 0 )
				continue;

			$name = strtolower(substr($hdr, 0, $i));
			if( $name != 'content-type' )
				continue;

			$ct = strtolower(trim(substr($hdr, $i+1)));

			$ct = explode(';', $ct);
			$ct = $ct[0];
			break;
		}

		if( $ct == 'text/css' || $ct == 'application/javascript' ) {
			$comments = ['/*', '*/'];
		} else if( $ct == 'text/html' ) {
			$comments = ['<!--', '-->'];
		} else {
			return;
		}

		echo $comments[0];
?>
Timings:

<?php
		foreach( $ts as $t ) {
			$name = $t['name'];
			$et = number_format($t['elapsed_total'], 6);
			$el = number_format($t['elapsed_last'], 6);

			$warn = '';
			if( $el > 0.001 )
				$warn .= '!';
			if( $el > 0.01 )
				$warn .= '!';
			if( $el > 0.1 )
				$warn .= '!!';
			if( $el > 1 )
				$warn .= '!!!!';
?>
<?=str_pad($name, $pad_len)?>: <?=$et?> (<?=$el?>) <?=$warn?>

<?php
		}

		echo $comments[1];
	}

	protected function OnInit(): void {}
	protected function OnAfterHandlerRun(string $handler_name): void {}
}