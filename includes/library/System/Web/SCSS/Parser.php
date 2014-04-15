<?php

namespace System\Web\SCSS
{
class Parser
{
	static protected $precedence = array(
		"or" => 0,
		"and" => 1,

		'==' => 2,
		'!=' => 2,
		'<=' => 2,
		'>=' => 2,
		'=' => 2,
		'<' => 3,
		'>' => 2,

		'+' => 3,
		'-' => 3,
		'*' => 4,
		'/' => 4,
		'%' => 4,
		);

	static protected $operators = array("+", "-", "*", "/", "%",
		"==", "!=", "<=", ">=", "<", ">", "and", "or");

	static protected $operatorStr;
	static protected $whitePattern;
	static protected $commentMulti;

	static protected $commentSingle = "//";
	static protected $commentMultiLeft = "/*";
	static protected $commentMultiRight = "*/";

	function __construct($sourceName = null)
	{
		$this->sourceName = $sourceName;

		if (empty(self::$operatorStr))
		{
			self::$operatorStr = $this->makeOperatorStr(self::$operators);

			$commentSingle = $this->preg_quote(self::$commentSingle);
			$commentMultiLeft = $this->preg_quote(self::$commentMultiLeft);
			$commentMultiRight = $this->preg_quote(self::$commentMultiRight);
			self::$commentMulti = $commentMultiLeft.'.*?'.$commentMultiRight;
			self::$whitePattern = '/'.$commentSingle.'[^\n]*\s*|('.self::$commentMulti.')\s*|\s+/Ais';
		}
	}

	static protected function makeOperatorStr($operators)
	{
		return '('.implode('|', array_map(array('\System\Web\SCSS\Parser','preg_quote'),
			$operators)).')';
	}

	function parse($buffer)
	{
		$this->count = 0;
		$this->env = null;
		$this->inParens = false;
		$this->pushBlock(null); // root block
		$this->eatWhiteDefault = true;
		$this->insertComments = true;

		$this->buffer = $buffer;

		$this->whitespace();
		while (false !== $this->parseChunk());

		if ($this->count != strlen($this->buffer))
			$this->throwParseError();

		if (!empty($this->env->parent))
			$this->throwParseError("unclosed block");

		$this->env->isRoot = true;
		return $this->env;
	}

	protected function parseChunk()
	{
		$s = $this->seek();

		// the directives
		if (isset($this->buffer[$this->count]) && $this->buffer[$this->count] == "@")
		{
			if ($this->literal("@media") && $this->mediaQueryList($mediaQueryList) && $this->literal("{"))
			{
				$media = $this->pushSpecialBlock("media");
				$media->queryList = $mediaQueryList[2];
				return true;
			} else {
				$this->seek($s);
			}

			if ($this->literal("@mixin") &&
				$this->keyword($mixinName) &&
				($this->argumentDef($args) || true) &&
				$this->literal("{"))
			{
				$mixin = $this->pushSpecialBlock("mixin");
				$mixin->name = $mixinName;
				$mixin->args = $args;
				return true;
			} else {
				$this->seek($s);
			}

			if ($this->literal("@include") &&
				$this->keyword($mixinName) &&
				($this->literal("(") &&
						($this->argValues($argValues) || true) &&
						$this->literal(")") || true) &&
					($this->end() ||
						$this->literal("{") && $hasBlock = true))
			{
				$child = array("include",
					$mixinName, isset($argValues) ? $argValues : null, null);

				if (!empty($hasBlock))
				{
					$include = $this->pushSpecialBlock("include");
					$include->child = $child;
				} else {
					$this->append($child);
				}

				return true;
			} else {
				$this->seek($s);
			}

			if ($this->literal("@import") &&
				$this->valueList($importPath) &&
				$this->end())
			{
				$this->append(array("import", $importPath));
				return true;
			} else {
				$this->seek($s);
			}

			if ($this->literal("@extend") &&
				$this->selectors($selector) &&
				$this->end())
			{
				$this->append(array("extend", $selector));
				return true;
			} else {
				$this->seek($s);
			}

			if ($this->literal("@function") &&
				$this->keyword($fn_name) &&
				$this->argumentDef($args) &&
				$this->literal("{"))
			{
				$func = $this->pushSpecialBlock("function");
				$func->name = $fn_name;
				$func->args = $args;
				return true;
			} else {
				$this->seek($s);
			}

			if ($this->literal("@return") && $this->valueList($retVal) && $this->end()) {
				$this->append(array("return", $retVal));
				return true;
			} else {
				$this->seek($s);
			}

			if ($this->literal("@each") &&
				$this->variable($varName) &&
				$this->literal("in") &&
				$this->valueList($list) &&
				$this->literal("{"))
			{
				$each = $this->pushSpecialBlock("each");
				$each->var = $varName[1];
				$each->list = $list;
				return true;
			} else {
				$this->seek($s);
			}

			if ($this->literal("@while") &&
				$this->expression($cond) &&
				$this->literal("{"))
			{
				$while = $this->pushSpecialBlock("while");
				$while->cond = $cond;
				return true;
			} else {
				$this->seek($s);
			}

			if ($this->literal("@for") &&
				$this->variable($varName) &&
				$this->literal("from") &&
				$this->expression($start) &&
				($this->literal("through") ||
						($forUntil = true && $this->literal("to"))) &&
					$this->expression($end) &&
					$this->literal("{"))
			{
				$for = $this->pushSpecialBlock("for");
				$for->var = $varName[1];
				$for->start = $start;
				$for->end = $end;
				$for->until = isset($forUntil);
				return true;
			} else {
				$this->seek($s);
			}

			if ($this->literal("@if") && $this->valueList($cond) && $this->literal("{"))
			{
				$if = $this->pushSpecialBlock("if");
				$if->cond = $cond;
				$if->cases = array();
				return true;
			} else {
				$this->seek($s);
			}

			if (($this->literal("@debug") || $this->literal("@warn")) &&
				$this->valueList($value) &&
				$this->end())
			{
				$this->append(array("debug", $value, $s));
				return true;
			} else {
				$this->seek($s);
			}

			if ($this->literal("@content") && $this->end())
			{
				$this->append(array("mixin_content"));
				return true;
			} else {
				$this->seek($s);
			}

			$last = $this->last();
			if (!is_null($last) && $last[0] == "if")
			{
				list(, $if) = $last;
				if ($this->literal("@else"))
				{
					if ($this->literal("{"))
					{
						$else = $this->pushSpecialBlock("else");
					}
					elseif ($this->literal("if") && $this->valueList($cond) && $this->literal("{"))
					{
						$else = $this->pushSpecialBlock("elseif");
						$else->cond = $cond;
					}

					if (isset($else))
					{
						$else->dontAppend = true;
						$if->cases[] = $else;
						return true;
					}
				}

				$this->seek($s);
			}

			if ($this->literal("@charset") &&
				$this->valueList($charset) && $this->end())
			{
				$this->append(array("charset", $charset));
				return true;
			} else {
				$this->seek($s);
			}

			// doesn't match built in directive, do generic one
			if ($this->literal("@", false) && $this->keyword($dirName) &&
				($this->openString("{", $dirValue) || true) &&
				$this->literal("{"))
			{
				$directive = $this->pushSpecialBlock("directive");
				$directive->name = $dirName;
				if (isset($dirValue)) $directive->value = $dirValue;
				return true;
			}

			$this->seek($s);
			return false;
		}

		// property shortcut
		// captures most properties before having to parse a selector
		if ($this->keyword($name, false) &&
			$this->literal(": ") &&
			$this->valueList($value) &&
			$this->end())
		{
			$name = array("string", "", array($name));
			$this->append(array("assign", $name, $value));
			return true;
		} else {
			$this->seek($s);
		}

		// variable assigns
		if ($this->variable($name) &&
			$this->literal(":") &&
			$this->valueList($value) && $this->end())
		{
			$defaultVar = false;
			// check for !default
			if ($value[0] == "list")
			{
				$def = end($value[2]);
				if ($def[0] == "keyword" && $def[1] == "!default")
				{
					array_pop($value[2]);
					$value = $this->flattenList($value);
					$defaultVar = true;
				}
			}
			
			$this->append(array("assign", $name, $value, $defaultVar));
			return true;
		} else {
			$this->seek($s);
		}

		// misc
		if ($this->literal("-->"))
			return true;

		// opening css block
		$oldComments = $this->insertComments;
		$this->insertComments = false;
		if ($this->selectors($selectors) && $this->literal("{"))
		{
			$this->pushBlock($selectors);
			$this->insertComments = $oldComments;
			return true;
		} else {
			$this->seek($s);
		}
		$this->insertComments = $oldComments;

		// property assign, or nested assign
		if ($this->propertyName($name) && $this->literal(":"))
		{
			$foundSomething = false;
			if ($this->valueList($value))
			{
				$this->append(array("assign", $name, $value));
				$foundSomething = true;
			}

			if ($this->literal("{"))
			{
				$propBlock = $this->pushSpecialBlock("nestedprop");
				$propBlock->prefix = $name;
				$foundSomething = true;
			}
			elseif ($foundSomething)
			{
				$foundSomething = $this->end();
			}

			if ($foundSomething)
				return true;

			$this->seek($s);
		} else {
			$this->seek($s);
		}

		// closing a block
		if ($this->literal("}"))
		{
			$block = $this->popBlock();
			if (isset($block->type) && $block->type == "include")
			{
				$include = $block->child;
				unset($block->child);
				$include[3] = $block;
				$this->append($include);
			}
			else if (empty($block->dontAppend))
			{
				$type = isset($block->type) ? $block->type : "block";
				$this->append(array($type, $block));
			}
			return true;
		}

		// extra stuff
		if ($this->literal(";") || $this->literal("<!--"))
			return true;

		return false;
	}

	protected function literal($what, $eatWhitespace = null)
	{
		if (is_null($eatWhitespace))
			$eatWhitespace = $this->eatWhiteDefault;

		// this is here mainly prevent notice from { } string accessor
		if ($this->count >= strlen($this->buffer))
			return false;

		// shortcut on single letter
		if (!$eatWhitespace && strlen($what) == 1)
		{
			if ($this->buffer{$this->count} == $what)
			{
				$this->count++;
				return true;
			}
			else
				return false;
		}

		return $this->match($this->preg_quote($what), $m, $eatWhitespace);
	}

	// tree builders

	protected function pushBlock($selectors)
	{
		$b = new \stdclass;
		$b->parent = $this->env; // not sure if we need this yet

		$b->selectors = $selectors;
		$b->children = array();

		$this->env = $b;
		return $b;
	}

	protected function pushSpecialBlock($type)
	{
		$block = $this->pushBlock(null);
		$block->type = $type;
		return $block;
	}

	protected function popBlock()
	{
		if (empty($this->env->parent))
			$this->throwParseError("unexpected }");

		$old = $this->env;
		$this->env = $this->env->parent;
		unset($old->parent);
		return $old;
	}

	protected function append($statement)
	{
		$this->env->children[] = $statement;
	}

	// last child that was appended
	protected function last()
	{
		$i = count($this->env->children) - 1;
		if (isset($this->env->children[$i]))
			return $this->env->children[$i];
	}

	// high level parsers (they return parts of ast)

	protected function mediaQueryList(&$out)
	{
		return $this->genericList($out, "mediaQuery", ",", false);
	}

	protected function mediaQuery(&$out)
	{
		$s = $this->seek();

		$expressions = null;
		$parts = array();

		if (($this->literal("only") && ($only = true) || $this->literal("not") && ($not = true) || true) && $this->keyword($mediaType))
		{
			$prop = array("mediaType");
			if (isset($only)) $prop[] = "only";
			if (isset($not)) $prop[] = "not";
			$prop[] = $mediaType;
			$parts[] = $prop;
		} else {
			$this->seek($s);
		}


		if (!empty($mediaType) && !$this->literal("and"))
		{
			// ~
		} else {
			$this->genericList($expressions, "mediaExpression", "and", false);
			if (is_array($expressions))
				$parts = array_merge($parts, $expressions[2]);
		}

		$out = $parts;
		return true;
	}

	protected function mediaExpression(&$out)
	{
		$s = $this->seek();
		$value = null;
		if ($this->literal("(") &&
			$this->keyword($feature) &&
			($this->literal(":") && $this->expression($value) || true) &&
			$this->literal(")"))
		{
			$out = array("mediaExp", $feature);
			if ($value) $out[] = $value;
			return true;
		}

		$this->seek($s);
		return false;
	}

	protected function argValues(&$out)
	{
		if ($this->genericList($list, "argValue", ",", false)) {
			$out = $list[2];
			return true;
		}
		
		return false;
	}

	protected function argValue(&$out)
	{
		$s = $this->seek();

		$keyword = null;
		if (!$this->variable($keyword) || !$this->literal(":"))
		{
			$this->seek($s);
			$keyword = null;
		}

		if ($this->genericList($value, "expression"))
		{
			$out = array($keyword, $value);
			return true;
		}

		return false;
	}


	protected function valueList(&$out)
	{
		return $this->genericList($out, "commaList");
	}

	protected function commaList(&$out)
	{
		return $this->genericList($out, "expression", ",");
	}

	protected function genericList(&$out, $parseItem, $delim="", $flatten=true)
	{
		$s = $this->seek();
		$items = array();
		while ($this->$parseItem($value))
		{
			$items[] = $value;
			if ($delim &&!$this->literal($delim))
				break;
		}

		if (count($items) == 0)
		{
			$this->seek($s);
			return false;
		}

		if ($flatten && count($items) == 1)
			$out = $items[0];
		else
			$out = array("list", $delim, $items);

		return true;
	}

	protected function expression(&$out)
	{
		$s = $this->seek();

		if ($this->literal("("))
		{
			if ($this->literal(")"))
			{
				$out = array("list", "", array());
				return true;
			}

			if ($this->valueList($out) && $this->literal(')') && $out[0] == "list")
				return true;

			$this->seek($s);
		}

		if ($this->value($lhs))
		{
			$out = $this->expHelper($lhs, 0);
			return true;
		}

		return false;
	}

	protected function expHelper($lhs, $minP)
	{
		$opstr = self::$operatorStr;

		$ss = $this->seek();
		$whiteBefore = isset($this->buffer[$this->count - 1]) && ctype_space($this->buffer[$this->count - 1]);
		while ($this->match($opstr, $m) && self::$precedence[$m[1]] >= $minP) {
			$whiteAfter = isset($this->buffer[$this->count - 1]) && ctype_space($this->buffer[$this->count - 1]);

			$op = $m[1];

			// don't turn negative numbers into expressions
			if ($op == "-" && $whiteBefore && !$whiteAfter)
				break;

			if (!$this->value($rhs))
				break;

			// peek and see if rhs belongs to next operator
			if ($this->peek($opstr, $next) && self::$precedence[$next[1]] > self::$precedence[$op])
				$rhs = $this->expHelper($rhs, self::$precedence[$next[1]]);

			$lhs = array("exp", $op, $lhs, $rhs, $this->inParens, $whiteBefore, $whiteAfter);
			$ss = $this->seek();
			$whiteBefore = isset($this->buffer[$this->count - 1]) && ctype_space($this->buffer[$this->count - 1]);
		}

		$this->seek($ss);
		return $lhs;
	}

	protected function value(&$out)
	{
		$s = $this->seek();

		if ($this->literal("not", false) && $this->whitespace() && $this->value($inner))
		{
			$out = array("unary", "not", $inner, $this->inParens);
			return true;
		} else {
			$this->seek($s);
		}

		if ($this->literal("+") && $this->value($inner))
		{
			$out = array("unary", "+", $inner, $this->inParens);
			return true;
		} else {
			$this->seek($s);
		}

		// negation
		if ($this->literal("-", false) &&
			($this->variable($inner) ||
					$this->unit($inner) ||
					$this->parenValue($inner)))
		{
			$out = array("unary", "-", $inner, $this->inParens);
			return true;
		} else {
			$this->seek($s);
		}

		if ($this->parenValue($out)) return true;
		if ($this->interpolation($out)) return true;
		if ($this->variable($out)) return true;
		if ($this->color($out)) return true;
		if ($this->unit($out)) return true;
		if ($this->string($out)) return true;
		if ($this->func($out)) return true;
		if ($this->progid($out)) return true;

		if ($this->keyword($keyword))
		{
			$out = array("keyword", $keyword);
			return true;
		}

		return false;
	}

	// value wrappen in parentheses
	protected function parenValue(&$out)
	{
		$s = $this->seek();

		$inParens = $this->inParens;
		if ($this->literal("(") &&
			($this->inParens = true) && $this->expression($exp) &&
			$this->literal(")"))
		{
			$out = $exp;
			$this->inParens = $inParens;
			return true;
		} else {
			$this->inParens = $inParens;
			$this->seek($s);
		}

		return false;
	}

	protected function progid(&$out)
	{
		$s = $this->seek();
		if ($this->literal("progid:", false) &&
			$this->openString("(", $fn) &&
			$this->literal("("))
		{
			$this->openString(")", $args, "(");
			if ($this->literal(")"))
			{
				$out = array("string", "", array("progid:", $fn, "(", $args, ")"));
				return true;
			}
		}

		$this->seek($s);
		return false;
	}

	protected function func(&$func)
	{
		$s = $this->seek();

		if ($this->keyword($name, false) &&
			$this->literal("("))
		{
			if ($name != "expression" && false == preg_match("/^(-[a-z]+-)?calc$/", $name))
			{
				$ss = $this->seek();
				
				if ($this->argValues($args) && $this->literal(")"))
				{
					$func = array("fncall", $name, $args);
					return true;
				}
				
				$this->seek($ss);
			}

			if (($this->openString(")", $str, "(") || true ) && $this->literal(")"))
			{
				$args = array();
				
				if (!empty($str))
					$args[] = array(null, array("string", "", array($str)));

				$func = array("fncall", $name, $args);
				return true;
			}
		}

		$this->seek($s);
		return false;
	}

	protected function argumentDef(&$out)
	{
		$s = $this->seek();
		$this->literal("(");

		$args = array();
		while ($this->variable($var))
		{
			$arg = array($var[1], null);

			$ss = $this->seek();
			if ($this->literal(":") && $this->expression($defaultVal))
				$arg[1] = $defaultVal;
			else
				$this->seek($ss);

			$args[] = $arg;
			if (!$this->literal(","))
				break;
		}

		if (!$this->literal(")"))
		{
			$this->seek($s);
			return false;
		}

		$out = $args;
		return true;
	}

	protected function color(&$out)
	{
		$color = array('color');

		if ($this->match('(#([0-9a-f]{6})|#([0-9a-f]{3}))', $m))
		{
			if (isset($m[3]))
			{
				$num = $m[3];
				$width = 16;
			} else {
				$num = $m[2];
				$width = 256;
			}

			$num = hexdec($num);
			foreach (array(3,2,1) as $i)
			{
				$t = $num % $width;
				$num /= $width;

				$color[$i] = $t * (256/$width) + $t * floor(16/$width);
			}

			$out = $color;
			return true;
		}

		return false;
	}

	protected function unit(&$unit)
	{
		if ($this->match('([0-9]*(\.)?[0-9]+)([%a-zA-Z]+)?', $m))
		{
			$unit = array("number", $m[1], empty($m[3]) ? "" : $m[3]);
			return true;
		}
		return false;
	}

	protected function string(&$out)
	{
		$s = $this->seek();
		if ($this->literal('"', false))
			$delim = '"';
		elseif ($this->literal("'", false))
			$delim = "'";
		else
			return false;

		$content = array();

		// look for either ending delim , escape, or string interpolation
		$patt = '([^\n]*?)(#\{|\\\\|' . $this->preg_quote($delim).')';

		$oldWhite = $this->eatWhiteDefault;
		$this->eatWhiteDefault = false;

		while ($this->match($patt, $m, false))
		{
			$content[] = $m[1];
			if ($m[2] == "#{")
			{
				$this->count -= strlen($m[2]);
				if ($this->interpolation($inter, false))
				{
					$content[] = $inter;
				} else {
					$this->count += strlen($m[2]);
					$content[] = "#{"; // ignore it
				}
			}
			elseif ($m[2] == '\\')
			{
				$content[] = $m[2];
				if ($this->literal($delim, false))
					$content[] = $delim;
			} else {
				$this->count -= strlen($delim);
				break; // delim
			}
		}

		$this->eatWhiteDefault = $oldWhite;

		if ($this->literal($delim))
		{
			$out = array("string", $delim, $content);
			return true;
		}

		$this->seek($s);
		return false;
	}

	protected function mixedKeyword(&$out)
	{
		$s = $this->seek();

		$parts = array();

		$oldWhite = $this->eatWhiteDefault;
		$this->eatWhiteDefault = false;

		while (true)
		{
			if ($this->keyword($key))
			{
				$parts[] = $key;
				continue;
			}

			if ($this->interpolation($inter))
			{
				$parts[] = $inter;
				continue;
			}

			break;
		}

		$this->eatWhiteDefault = $oldWhite;

		if (count($parts) == 0)
			return false;

		$out = $parts;
		return true;
	}

	// an unbounded string stopped by $end
	protected function openString($end, &$out, $nestingOpen=null)
	{
		$oldWhite = $this->eatWhiteDefault;
		$this->eatWhiteDefault = false;

		$stop = array("'", '"', "#{", $end);
		$stop = array_map(array($this, "preg_quote"), $stop);
		$stop[] = self::$commentMulti;

		$patt = '(.*?)('.implode("|", $stop).')';

		$nestingLevel = 0;

		$content = array();
		while ($this->match($patt, $m, false))
		{
			if (!empty($m[1]))
			{
				$content[] = $m[1];
				if ($nestingOpen)
					$nestingLevel += substr_count($m[1], $nestingOpen);
			}

			$tok = $m[2];

			$this->count-= strlen($tok);
			if ($tok == $end)
			{
				if ($nestingLevel == 0)
					break;
				else
					$nestingLevel--;
			}

			if (($tok == "'" || $tok == '"') && $this->string($str))
			{
				$content[] = $str;
				continue;
			}

			if ($tok == "#{" && $this->interpolation($inter))
			{
				$content[] = $inter;
				continue;
			}

			$content[] = $tok;
			$this->count+= strlen($tok);
		}

		$this->eatWhiteDefault = $oldWhite;

		if (count($content) == 0)
			return false;

		// trim the end
		if (is_string(end($content)))
			$content[count($content) - 1] = rtrim(end($content));

		$out = array("string", "", $content);
		return true;
	}

	// $lookWhite: save information about whitespace before and after
	protected function interpolation(&$out, $lookWhite=true)
	{
		$oldWhite = $this->eatWhiteDefault;
		$this->eatWhiteDefault = true;

		$s = $this->seek();
		if ($this->literal("#{") && $this->valueList($value) && $this->literal("}", false))
		{
			// TODO: don't error if out of bounds

			if ($lookWhite)
			{
				$left = preg_match('/\s/', $this->buffer[$s - 1]) ? " " : "";
				$right = preg_match('/\s/', $this->buffer[$this->count]) ? " ": "";
			} else {
				$left = $right = false;
			}

			$out = array("interpolate", $value, $left, $right);
			$this->eatWhiteDefault = $oldWhite;
			if ($this->eatWhiteDefault)
				$this->whitespace();
			
			return true;
		}

		$this->seek($s);
		$this->eatWhiteDefault = $oldWhite;
		return false;
	}

	// low level parsers

	// returns an array of parts or a string
	protected function propertyName(&$out)
	{
		$s = $this->seek();
		$parts = array();

		$oldWhite = $this->eatWhiteDefault;
		$this->eatWhiteDefault = false;

		while (true)
		{
			if ($this->interpolation($inter))
				$parts[] = $inter;
			elseif ($this->keyword($text))
				$parts[] = $text;
			elseif (count($parts) == 0 && $this->match('[:.#]', $m, false)) // css hacks
				$parts[] = $m[0];
			else
				break;
		}

		$this->eatWhiteDefault = $oldWhite;
		if (count($parts) == 0)
			return false;

		// match comment hack
		if (preg_match(self::$whitePattern, $this->buffer, $m, null, $this->count))
		{
			if (!empty($m[0]))
			{
				$parts[] = $m[0];
				$this->count += strlen($m[0]);
			}
		}

		$this->whitespace(); // get any extra whitespace

		$out = array("string", "", $parts);
		return true;
	}

	// comma separated list of selectors
	protected function selectors(&$out)
	{
		$s = $this->seek();
		$selectors = array();
		while ($this->selector($sel))
		{
			$selectors[] = $sel;
			if (!$this->literal(","))
				break;
			
			while ($this->literal(",")); // ignore extra
		}

		if (count($selectors) == 0)
		{
			$this->seek($s);
			return false;
		}

		$out = $selectors;
		return true;
	}

	// whitepsace separated list of selectorSingle
	protected function selector(&$out)
	{
		$selector = array();

		while (true)
		{
			if ($this->match('[>+~]+', $m))
			{
				$selector[] = array($m[0]);
			}
			elseif ($this->selectorSingle($part))
			{
				$selector[] = $part;
				$this->whitespace();
			} else {
				break;
			}

		}

		if (count($selector) == 0)
			return false;

		$out = $selector;
		return true;
	}

	// the parts that make up
	// div[yes=no]#something.hello.world:nth-child(-2n+1)
	protected function selectorSingle(&$out)
	{
		$oldWhite = $this->eatWhiteDefault;
		$this->eatWhiteDefault = false;

		$parts = array();

		if ($this->literal("*", false))
			$parts[] = "*";

		while (true)
		{
			// see if we can stop early
			if ($this->match("\s*[{,]", $m))
			{
				$this->count--;
				break;
			}

			$s = $this->seek();
			// self
			if ($this->literal("&", false))
			{
				$parts[] = \System\Web\SCSS\Compiler::$selfSelector;
				continue;
			}

			if ($this->literal(".", false))
			{
				$parts[] = ".";
				continue;
			}

			if ($this->literal("|", false))
			{
				$parts[] = "|";
				continue;
			}

			// for keyframes
			if ($this->unit($unit))
			{
				$parts[] = $unit;
				continue;
			}

			if ($this->keyword($name))
			{
				$parts[] = $name;
				continue;
			}

			if ($this->interpolation($inter))
			{
				$parts[] = $inter;
				continue;
			}

			if ($this->literal("#", false))
			{
				$parts[] = "#";
				continue;
			}

			// a pseudo selector
			if ($this->match("::?", $m) && $this->mixedKeyword($nameParts))
			{
				$parts[] = $m[0];
				foreach ($nameParts as $sub)
					$parts[] = $sub;

				$ss = $this->seek();
				if ($this->literal("(") && ($this->openString(")", $str, "(") || true ) && $this->literal(")"))
				{
					$parts[] = "(";
					if (!empty($str))
						$parts[] = $str;
					$parts[] = ")";
				} else {
					$this->seek($ss);
				}

				continue;
			} else {
				$this->seek($s);
			}

			// attribute selector
			// TODO: replace with open string?
			if ($this->literal("[", false))
			{
				$attrParts = array("[");
				// keyword, string, operator
				while (true)
				{
					if ($this->literal("]", false))
					{
						$this->count--;
						break; // get out early
					}

					if ($this->match('\s+', $m))
					{
						$attrParts[] = " ";
						continue;
					}
					if ($this->string($str))
					{
						$attrParts[] = $str;
						continue;
					}

					if ($this->keyword($word))
					{
						$attrParts[] = $word;
						continue;
					}

					if ($this->interpolation($inter, false))
					{
						$attrParts[] = $inter;
						continue;
					}

					// operator, handles attr namespace too
					if ($this->match('[|-~\$\*\^=]+', $m))
					{
						$attrParts[] = $m[0];
						continue;
					}

					break;
				}

				if ($this->literal("]", false))
				{
					$attrParts[] = "]";
					foreach ($attrParts as $part)
						$parts[] = $part;

					continue;
				}
				$this->seek($s);
				// should just break here?
			}

			break;
		}

		$this->eatWhiteDefault = $oldWhite;

		if (count($parts) == 0)
			return false;

		$out = $parts;
		return true;
	}

	protected function variable(&$out)
	{
		$s = $this->seek();
		if ($this->literal("$", false) && $this->keyword($name))
		{
			$out = array("var", $name);
			return true;
		}
		
		$this->seek($s);
		return false;
	}

	protected function keyword(&$word, $eatWhitespace = null)
	{
		if ($this->match('([\w_\-\*!"\'\\\\][\w\-_"\'\\\\]*)', $m, $eatWhitespace))
		{
			$word = $m[1];
			return true;
		}
		
		return false;
	}

	// consume an end of statement delimiter
	protected function end()
	{
		if ($this->literal(';'))
			return true;
		elseif ($this->count == strlen($this->buffer) || $this->buffer{$this->count} == '}') // if there is end of file or a closing block next then we don't need a ;
			return true;
		
		return false;
	}

	// advance counter to next occurrence of $what
	// $until - don't include $what in advance
	// $allowNewline, if string, will be used as valid char set
	protected function to($what, &$out, $until = false, $allowNewline = false)
	{
		if (is_string($allowNewline))
			$validChars = $allowNewline;
		else
			$validChars = $allowNewline ? "." : "[^\n]";
		
		if (!$this->match('('.$validChars.'*?)'.$this->preg_quote($what), $m, !$until))
			return false;
		
		if ($until)
			$this->count -= strlen($what); // give back $what
		
		$out = $m[1];
		return true;
	}

	protected function throwParseError($msg = "parse error", $count = null)
	{
		$count = is_null($count) ? $this->count : $count;

		$line = $this->getLineNo($count);

		if (!empty($this->sourceName))
			$loc = "$this->sourceName on line $line";
		else
			$loc = "line: $line";

		if ($this->peek("(.*?)(\n|$)", $m, $count))
			throw new \exception("$msg: failed at `$m[1]` $loc");
		else
			throw new \exception("$msg: $loc");
	}

	public function getLineNo($pos)
	{
		return 1 + substr_count(substr($this->buffer, 0, $pos), "\n");
	}

	// try to match something on head of buffer
	protected function match($regex, &$out, $eatWhitespace = null)
	{
		if (is_null($eatWhitespace))
			$eatWhitespace = $this->eatWhiteDefault;

		$r = '/'.$regex.'/Ais';
		if (preg_match($r, $this->buffer, $out, null, $this->count))
		{
			$this->count += strlen($out[0]);
			if ($eatWhitespace)
				$this->whitespace();
			
			return true;
		}
		
		return false;
	}

	// match some whitespace
	protected function whitespace()
	{
		$gotWhite = false;
		while (preg_match(self::$whitePattern, $this->buffer, $m, null, $this->count))
		{
			if ($this->insertComments)
			{
				if (isset($m[1]) && empty($this->commentsSeen[$this->count]))
				{
					$this->append(array("comment", $m[1]));
					$this->commentsSeen[$this->count] = true;
				}
			}
			
			$this->count += strlen($m[0]);
			$gotWhite = true;
		}
		
		return $gotWhite;
	}

	protected function peek($regex, &$out, $from=null)
	{
		if (is_null($from))
			$from = $this->count;

		$r = '/'.$regex.'/Ais';
		$result = preg_match($r, $this->buffer, $out, null, $from);

		return $result;
	}

	protected function seek($where = null)
	{
		if ($where === null)
			return $this->count;
		else
			$this->count = $where;
		return true;
	}

	static function preg_quote($what)
	{
		return preg_quote($what, '/');
	}

	protected function show()
	{
		if ($this->peek("(.*?)(\n|$)", $m, $this->count))
			return $m[1];
		
		return "";
	}

	// turn list of length 1 into value type
	protected function flattenList($value)
	{
		if ($value[0] == "list" && count($value[2]) == 1)
			return $this->flattenList($value[2][0]);
		
		return $value;
	}
}
}