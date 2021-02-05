<?php

declare(strict_types=1);

namespace Core\Web\UI;

class Page extends \Core\Web\BaseObject {
	protected string $AbsolutePath;
	protected string $Layout;
	protected string $Path;

	protected string $Title;
	public function _getTitle(): string { return $this->Title; }

	protected array $StyleSheets;
	public function _getStyleSheets(): array { return $this->StyleSheets; }

	protected array $JSFiles;
	public function _getJSFiles(): array { return $this->JSFiles; }

	protected array $Styles;
	public function _getStyles(): array { return $this->Styles; }

	protected array $Options;
	public function _getOptions(): array { return $this->Options; }

	protected array $render_regions;
	protected array $render_content;

	public function __construct() {
		parent::__construct();

		$this->Title = '';

		$this->StyleSheets = [];
		$this->JSFiles = [];
		$this->Styles = [];
		$this->Options = [];

		$this->Layout = 'default';
	}

	public function SetPath(string $abs_path, string $web_path) {
		$this->AbsolutePath = $abs_path;
		$this->Path = $web_path;
	}

	protected function SetLayout(?string $name = null): void {
		if( isset($name) && trim($name) != '' && $name != '_none' && $name != 'default' )
			$this->Layout = $name;
		else
			$this->Layout = 'default';
	}

	public function SetOption(string $name, $value): void {
		$this->Options[$name]=$value;
	}

	public function GetOption(string $name, $default='') {
		if( !isset($this->Options[$name]) )
			return $default;

		return $this->Options[$name];
	}

	public function AddStyleSheet(string $path, string $rel='StyleSheet', string $media=null): void {
		$this->StyleSheets[] = [
			'path' => $path,
			'rel' => $rel,
			'media' => $media
		];
	}

	public function AddJSFile(string $path, string $cond=''): void {
		$this->JSFiles[] = [
			'cond' => $cond,
			'path' => $path
		];
	}

	public function AddStyle(string $selector, array $arrStyles): void {
		if( isset($this->Styles[$selector]) )
			$this->Styles[$selector] = array_merge($this->Styles[$selector],$arrStyles);
		else
			$this->Styles[$selector] = $arrStyles;
	}

	public function Init(): void {
		$this->OnInit();
	}

	public function Load(): void {
		$this->OnLoad();
	}

	public function Render(): void {
		$this->OnPreRender();

		$content = $this->RenderContent();

		if( !isset($this->Layout) || $this->Layout == '' || $this->Layout == '_none' ) {
			foreach($content as $area)
				echo $area;
		} else {
			$l = new Layout($this->Layout);
			$l->SetPage($this);

			$l->Render($content);
		}

		$this->OnPostRender();
	}

	protected function RenderContent(): array {
		$this->render_content = [];
		$this->render_regions = [];

		$this->StartRegion('default');

		require_once($this->AbsolutePath);

		$this->EndRegion();

		return $this->render_content;
	}

	protected function StartRegion(string $name): void {
		array_push($this->render_regions, $name);
		ob_start();
	}

	protected function EndRegion(): void {
		$content = ob_get_clean();

		$name = array_pop($this->render_regions);

		if( !isset($this->render_content[$name]) )
			$this->render_content[$name] = '';

		$this->render_content[$name] .= $content;
	}

	// "Virtual" functions for specific page classes to override
	protected function OnInit(): void {}
	protected function OnLoad(): void {}
	protected function OnPreRender(): void {}
	protected function OnPostRender(): void {}
}