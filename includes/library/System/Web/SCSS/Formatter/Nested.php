<?php

namespace System\Web\SCSS\Formatter
{
class Nested extends \System\Web\SCSS\Formatter
{
	public $close = " }";

	// adjust the depths of all children, depth first
	public function adjustAllChildren($block)
	{
		// flatten empty nested blocks
		$children = array();
		foreach ($block->children as $i => $child)
		{
			if (empty($child->lines) && empty($child->children))
			{
				if (isset($block->children[$i + 1]))
					$block->children[$i + 1]->depth = $child->depth;
				
				continue;
			}
			
			$children[] = $child;
		}
		
		$block->children = $children;

		// make relative to parent
		foreach ($block->children as $child)
		{
			$this->adjustAllChildren($child);
			$child->depth = $child->depth - $block->depth;
		}
	}

	public function block($block)
	{
		if ($block->type == "root")
			$this->adjustAllChildren($block);

		$inner = $pre = $this->indentStr($block->depth - 1);
		if (!empty($block->selectors))
		{
			echo $pre .
				implode($this->tagSeparator, $block->selectors) .
				$this->open . $this->break;
			$this->indentLevel++;
			$inner = $this->indentStr($block->depth - 1);
		}

		if (!empty($block->lines))
		{
			$glue = $this->break.$inner;
			echo $inner . implode($glue, $block->lines);
			if (!empty($block->children))
				echo $this->break;
		}

		foreach ($block->children as $i => $child)
		{
			// echo "*** block: ".$block->depth." child: ".$child->depth."\n";
			$this->block($child);
			if ($i < count($block->children) - 1)
			{
				echo $this->break;

				if (isset($block->children[$i + 1]))
				{
					$next = $block->children[$i + 1];
					if ($next->depth == max($block->depth, 1) && $child->depth >= $next->depth)
						echo $this->break;
				}
			}
		}

		if (!empty($block->selectors))
		{
			$this->indentLevel--;
			echo $this->close;
		}

		if ($block->type == "root")
			echo $this->break;
	}
}
}