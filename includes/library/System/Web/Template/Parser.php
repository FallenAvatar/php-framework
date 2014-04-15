<?php


namespace System\Web\Template
{
class Parser
{
	protected static $whitespace = array(' ', "\t", "\r", "\n", "\0");
	protected static $tagstart = array('<');
	protected static $tagend = array('>', '/');
	
	public static function Parse($fileName)
	{
		if( !is_file($fileName) )
			throw new \Exception("Can not find file '$file'.");
		
		$file = new \System\IO\FileEnumerator($fileName);
		
		$pageInfo = new \System\Web\Template\PageInfo();
		
		$file->ReadWhile(self::$whitespace);
		
		if( $file->Current != '<' )
			throw new \Exception('Not valid HTML! Must start with a tag.');
		
		$file->Advance();
		if( $file->Current != '@' )		// Header Definition
			throw new \Exception('Did not find Header Definition in ['.$fileName.']. The Header must be the first thing in the file.');
		
		self::ParseTemplateHeader($file,$pageInfo);
		$file->ReadWhile(self::$whitespace);

		if( $file->Current != '<' )
			throw new \Exception('Not valid HTML! Must start with a tag.');
		
		$file->Advance();
		
		if( $file->Current == '@' )	// Register block for control registrations
		{
			self::ParseRegisterBlocks($file, $pageInfo);
			$file->ReadWhile(self::$whitespace);
			
			if( $file->Current != '<' )
				throw new \Exception('Not valid HTML! Must start with a tag.');
			
			$file->Advance();	
		}
		
		if( $file->Current == '!' )		// DOCTYPE definition
			self::ParseDocType($file,$pageInfo);
		else
			$file->Seek(-1);
		
		$file->ReadWhile(self::$whitespace);
		
		if( $file->Current != '<' )
			throw new \Exception('Not valid HTML! Must start with a tag.');
		
		while( !$file->IsEOF() )
			$pageInfo->Nodes[] = self::ParseNode($file,$pageInfo);
		
		return $pageInfo;
	}
	
	protected static function ParseTemplateHeader(\System\IO\FileEnumerator &$file, \System\Web\Template\PageInfo &$pageInfo)
	{
		$file->Advance();
		$pageInfo->PageType = $file->ReadUntil(array_merge(self::$whitespace,self::$tagend));
		
		$valid_page_types = array('Page', 'Control', 'Layout');
		
		if( !in_array($pageInfo->PageType,$valid_page_types) )
			throw new \Exception('Invalid PageType ['.$pageInfo->PageType.'].');
		
		while( $file->Current != '>' )
		{
			$file->ReadWhile(self::$whitespace);
			$name=$file->ReadUntil(array('='));
			$file->ReadWhile(self::$whitespace);
			$file->Advance();
			$file->ReadWhile(self::$whitespace);
			$quote=$file->Current;
			$file->Advance();
			$value=$file->ReadUntil(array($quote));
			$file->Advance();
			$file->ReadWhile(self::$whitespace);
			
			$pageInfo->TemplateHeader[$name]=$value;
		}
		
		$file->Advance();
	}
	
	protected static function ParseRegisterBlocks(\System\IO\FileEnumerator &$file, \System\Web\Template\PageInfo &$pageInfo)
	{
		while( $file->Current == '@' )
		{
			$file->Advance();
			$register = $file->ReadUntil(array_merge(self::$whitespace,self::$tagend));
			
			if( $register != 'Register' )
				throw new \Exception('Only register blocks are allowed after a Page, Control, or Layout block. Found ['.$register.'].');
			
			$attrs = array();
			
			while( $file->Current != '>' )
			{
				$file->ReadWhile(self::$whitespace);
				$name=$file->ReadUntil(array('='));
				$file->ReadWhile(self::$whitespace);
				$file->Advance();
				$file->ReadWhile(self::$whitespace);
				$quote=$file->Current;
				$file->Advance();
				$value=$file->ReadUntil(array($quote));
				$file->Advance();
				$file->ReadWhile(self::$whitespace);
				
				$attrs[$name]=$value;
			}
			
			
			
			$file->Advance();
			
			$file->ReadWhile(self::$whitespace);
			
			if( $file->Current != '<' )
				throw new \Exception('Not valid HTML! Must start with a tag.');
			
			$file->Advance();	
		}
	}
	
	protected static function ParseDocType(\System\IO\FileEnumerator &$file, \System\Web\Template\PageInfo &$pageInfo)
	{
		$file->Advance();
		$doctype = $file->ReadUntil(self::$whitespace);
		if( strtolower($doctype) != 'doctype' )
			throw new \Exception('Invalid DOCTYPE!');

		$file->ReadWhile(self::$whitespace);
		$html = $file->ReadUntil(array_merge(self::$whitespace,self::$tagend));
		if( strtolower($html) != 'html' )
			throw new \Exception('Invalid DOCTYPE!');

		$system='html5';
		
		$file->ReadWhile(self::$whitespace);
		if( $file->Current != '>' )
		{
			$str = strtolower($file->ReadUntil(self::$whitespace));
			$system='';
			
			if( $str == 'system' )
			{
				$system='html5legacy';
				$file->ReadWhile(self::$whitespace);
				$quote = $file->Current;
				if( $quote != "'" && $quote != '"' )
					throw new \Exception('Invalid DOCTYPE!');
				
				$legacy = $file->ReadUntil(array($quote));
				if( $legacy != 'about:legacy-compat' )				// NOT case-INsesitive
					throw new \Exception('Invalid DOCTYPE!');
				
				$file->Advance();
				$file->ReadWhile(self::$whitespace);
				
				if( $file->Current != '>' )
					throw new \Exception('Invalid DOCTYPE!');
				
				$file->Advance();
				$file->ReadWhile(self::$whitespace);
			}
			else if( $str == 'public' )
			{
				$system='obsolete permitted';
				
				$file->ReadWhile(self::$whitespace);
				
				$doctypes = array(
					array('publicID' => '-//W3C//DTD HTML 4.0//EN', 'systemID' => '', 'system' => 'html40'),
					array('publicID' => '-//W3C//DTD HTML 4.0//EN', 'systemID' => 'http://www.w3.org/TR/REC-html40/strict.dtd', 'system' => 'html40strict'),
					array('publicID' => '-//W3C//DTD HTML 4.01//EN', 'systemID' => '', 'system' => 'html401'),
					array('publicID' => '-//W3C//DTD HTML 4.01 Frameset//EN', 'systemID' => 'http://www.w3.org/TR/html4/frameset.dtd', 'system' => 'html40frameset'),
					array('publicID' => '-//W3C//DTD HTML 4.01 Transitional//EN', 'systemID' => 'http://www.w3.org/TR/html4/loose.dtd', 'system' => 'html401transitional'),
					array('publicID' => '-//W3C//DTD HTML 4.01//EN', 'systemID' => 'http://www.w3.org/TR/html4/strict.dtd', 'system' => 'html401strict'),
					array('publicID' => '-//W3C//DTD XHTML 1.0 Frameset//EN', 'systemID' => 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd', 'system' => 'xhtml10frameset'),
					array('publicID' => '-//W3C//DTD XHTML 1.0 Transitional//EN', 'systemID' => 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd', 'system' => 'xhtml10transitional'),
					array('publicID' => '-//W3C//DTD XHTML 1.0 Strict//EN', 'systemID' => 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd', 'system' => 'xhtml10strict'),
					array('publicID' => '-//W3C//DTD XHTML 1.1//EN', 'systemID' => 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd', 'system' => 'xhtml11')
					);
				
				$quote = $file->Current;
				if( $quote != "'" && $quote != '"' )
					throw new \Exception('Invalid DOCTYPE!');
				$publicID = $file->ReadUntil(array($quote));
				$systemID = '';
				
				$file->Advance();
				$file->ReadWhile(self::$whitespace);
				
				if( $file->Current != '>' )
				{
					$quote = $file->Current;
					if( $quote != "'" && $quote != '"' )
						throw new \Exception('Invalid DOCTYPE!');
					$systemID = $file->ReadUntil(array($quote));
					$file->Advance();
				}
				
				if( $file->Current != '>' )
					throw new \Exception('Invalid DOCTYPE!');
				
				foreach( $doctypes as $dt )
				{
					if( strtolower($dt['publicID']) == strtolower($publicID) && strtolower($dt['systemID']) == strtolower($systemID) )
					{
						$system = $dt['system'];
						break;
					}
				}
				
				if( $system == 'obsolete permitted' )
					throw new \Exception('Invalid DOCTYPE!');
			}
			else
				throw new \Exception('Invalid DOCTYPE!');
		}
		
		$file->Advance();
		
		$pageInfo->DocType=$system;
	}
	
	protected static function ParseNode(\System\IO\FileEnumerator &$file, \System\Web\Template\PageInfo &$pageInfo)
	{
		global $app;
		if( $file->Current != '<' )
		{
			$text = trim($file->ReadUntil(self::$tagstart));
			if( isset($text) && $text != '' )
				return new \System\Web\Template\TextNode($text);
		}
		
		$file->Advance();
		
		if( $file->Current == '/' )
		{
			$file->Advance();
			$file->ReadUntil(self::$tagend);
			$file->Advance();
			return null;
		}
		
		$tagName = $file->ReadUntil(array_merge(self::$whitespace,self::$tagend));
		
		if( !isset($tagName) || $tagName == '' )
			throw new Exception('Invalid HTML');
		
		$file->ReadWhile(self::$whitespace);
		
		$ret = $app->GetTag($tagName);
		
		while(true)
		{
			$attr=self::ParseAttribute($file);
			
			if( !isset($attr) )
				break;
			
			if( strtolower($attr->Name) == 'id' )
				$pageInfo->Controls[$attr->Value] = &$ret;
			
			$ret->Attributes[]=$attr;
		}
		
		
		$file->ReadWhile(self::$whitespace);
		
		if( $file->Current == '/' )
		{
			$file->Advance();
			if( $file->Current == '>' )
			{
				$file->Advance();
				return $ret;
			}
			else
				throw new \Exception('Invalid HTML');
		}
		
		$file->Advance();
		
		while( true )
		{
			$child=self::ParseNode($file,$pageInfo);
			
			if( !isset($child) )
				break;
			
			$ret->Children[] = $child;
		}
		
		return $ret;
	}
	
	protected static function ParseAttribute(\System\IO\FileEnumerator &$file)
	{
		$file->ReadWhile(self::$whitespace);
		$delims = array('=',"\'",'"');
		$valueend = self::$tagend;
		$name = $file->ReadUntil(array_merge($delims,self::$whitespace,self::$tagend));
		
		if( !isset($name) || $name == '' )
			return null;
		
		$file->ReadWhile(self::$whitespace);
		
		$value='';
		
		if( $file->Current != '=' )
			return new \System\Web\Template\Attribute($name);
		else
			$file->Advance();
		
		$file->ReadWhile(self::$whitespace);
		
		if( in_array($file->Current, $delims) )
		{
			$valueend = array( $file->Current );
			$file->Advance();
		}
		
		$value = $file->ReadUntil($valueend);
		
		if( in_array($file->Current, $delims) )
			$file->Advance();
		
		return new \System\Web\Template\Attribute($name,$value);
	}
}
}