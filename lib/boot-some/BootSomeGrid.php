<?php
/*
BootSome is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/boot-some/blob/master/LICENSE
*/
declare(strict_types=1);

class BootSomeGrid extends \TRP\HealDocument\Plugin {
	public static function grid($parent, int $columns = 0, bool $dense = false){
		$grid = new self($parent, $dense);
		$grid->columns($columns);
		return $grid;
	}

	public static function grid_horizontal($parent, int $rows = 0, bool $dense = false){
		$grid = new self($parent, $dense);
		$grid->at(['class'=>'bootsome-grid-horizontal'],true);
		$grid->rows($rows);
		return $grid;
	}

	public static function grid_span(
		\TRP\HealDocument\Component $element,
		int $columns = 0,
		int $rows = 0
	){
		if($columns >= 1){
			$element->at(['style'=>"--bootsome-colspan:$columns;"],true);
		}
		if($rows >= 1){
			$element->at(['style'=>"--bootsome-rowspan:$rows;"],true);
		}
		return $element;
	}

	public static function grid_place(
		\TRP\HealDocument\Component $element,
		int|string|null $column = null,
		int|string|null $row = null,
		int|string|null $column_end = null,
		int|string|null $row_end = null,
		int $column_span = 0,
		int $row_span = 0
	){
		$style = (isset($column) ? "--bootsome-column:$column;" : '').(isset($row) ? "--bootsome-row:$row;" : '');
		if(isset($column_end)){
			$style .= "--bootsome-column-end:$column_end;";
		} elseif($column_span >= 1){
			$style .= "--bootsome-column-end:span $column_span;";
		}
		if(isset($row_end)){
			$style .= "--bootsome-row-end:$row_end;";
		} elseif($row_span >= 1){
			$style .= "--bootsome-row-end:span $row_span;";
		}
		if(!empty($style)){
			$element->at(['style'=>$style],true);
		}
		return $element;
	}

	public function __construct($parent, bool $dense = false){
		$this->primary_element = $parent->el('div',['class'=>'bootsome-grid'.($dense ? ' bootsome-grid-dense':'')]);
	}

	public function gutter($gutter_class){
		$this->primary_element->at(['class'=>$gutter_class],true);
		return $this;
	}

	public function columns(int $columns, ?string $breakpoint = null){
		if($columns >= 1){
			$this->breakpoint_property('columns', $columns, $breakpoint);
		}
		return $this;
	}

	public function rows(int $rows, ?string $breakpoint = null){
		if($rows >= 1){
			$this->breakpoint_property('rows', $rows, $breakpoint);
		}
		return $this;
	}

	private function breakpoint_property($property, $value, $breakpoint){
		if(in_array($breakpoint,['sm','md','lg','xl','xxl'])){
			$property .= '-'.$breakpoint;
		}
		$this->primary_element->at(['style'=>"--bootsome-$property:$value;"],true);
	}

	public function span(int $columns = 0, int $rows = 0): \TRP\HealDocument\Component {
		return self::grid_span($this->primary_element->el('div'), $columns, $rows);
	}

	public function place(
		int|string|null $column = null,
		int|string|null $row = null,
		int|string|null $column_end = null,
		int|string|null $row_end = null,
		int $column_span = 0,
		int $row_span = 0
	): \TRP\HealDocument\Component {
		return self::grid_place($this->primary_element->el('div'), $column, $row, $column_end, $row_end, $column_span, $row_span);
	}
}
