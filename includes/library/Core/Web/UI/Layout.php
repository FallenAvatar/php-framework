<?php declare(strict_types=1);

namespace Core\Web\UI;

class Layout extends \Core\Web\BaseObject {
	protected string $Name;
	public function _getName(): string { return $this->Name; }

	protected string $Path;
	public function _getPath(): string { return $this->Path; }

	protected \Core\Web\UI\Page $Page;
	protected $content;

	public function __construct(string $name) {
		parent::__construct();

		if( !is_file($this->App->Dirs->Layouts.$name.'.phtml') )
			throw new \Core\Exception("Layout file for [".$name."] (".$this->App->Dirs->Layouts.$name.'.phtml'.") not found.");

		$this->Name = $name;
		$this->Path = $this->App->Dirs->Layouts.$name.'.phtml';
	}

	public function SetPage(\Core\Web\UI\Page $page) {
		$this->Page = $page;
	}

	public function GetOption(string $name, $default='') {
		return $this->Page->GetOption($name,$default);
	}

	public function GetStyleSheets(): string {
		$ret="\n";

		foreach( $this->Page->StyleSheets as $stylesheet )
			$ret .= "\t".'<link href="'.$stylesheet['path'].'" rel="'.$stylesheet['rel'].'"'.((isset($stylesheet['media'])) ? ' media="'.$stylesheet['media'].'"' : '').' type="text/css" />'."\n";

		return $ret;
	}

	public function GetJSFiles(): string {
		$ret = "\n";

		foreach( $this->Page->JSFiles as $js )
		{
			$cond = $js['cond'];
			$ret .= "\t";

			if( isset($cond) && trim($cond) != '' )
				$ret .= '<!--['.$cond.']>';

			$ret .= '<script src="'.$js['path'].'" type="text/javascript"></script>';

			if( isset($cond) && trim($cond) != '' )
				$ret .= '<![endif]-->';

			$ret .= "\n";
		}

		return $ret;
	}

	public function GetStyles(): string {
		if( count($this->Page->Styles) == 0 )
			return "";

		$ret = "\t<style type=\"text/css\">\n";

		foreach( $this->Page->Styles as $k => $v ) {
			$ret .= "\t\t".$k."{";

			foreach( $v as $ik => $iv )
				$ret .= $ik.":".$iv.";";

			$ret .= "}\n";
		}

		$ret .= "\t</style>\n";

		return $ret;
	}

	public function Render($content): void {
		$this->content = $content;

		ob_start();
		require_once($this->Path);
		ob_end_flush();
	}

	public function GetContent(string $name = 'default'): ?string {
		if( !isset($this->content[$name]) )
			return null;

		return $this->content[$name];
	}
}