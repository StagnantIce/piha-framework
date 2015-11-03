<?php

namespace piha\modules\bootstrap\widgets;

use piha\CException;
use piha\modules\core\classes\CTool;
use piha\modules\core\classes\CListData;
use piha\modules\orm\classes\CListModel;

class CGridWidget {

	private $listData;
	private $columns;

	public function __construct(CListData $l, Array $columns = null) {
		$this->listData = $l;
		$this->columns = $columns ?: array();
		if ($this->listData instanceof CListModel) {
			$model = $this->listData->getModel();
			if ($model) {
				foreach($this->columns as &$column) {
					if (!isset($column['label'])) {
						$column['label'] = $model::getLabel($column['id']);
					}
				}
			}
		}
	}

	public function render() {
		$columns = $this->columns;
		$h = CHtml::create()
			->table()
				->thead()
					->tr()
					->each($this->columns)
						->th()
							->text(function($column){
								if (is_array($column)) {
									if (isset($column['label'])) {
										return $column['label'];
									}
									if (isset($column['id'])) {
										return $column['id'];
									}
									throw new CException("GridView column expect label or id.");
								} else if(is_string($column)) {
									return $column;
								} else {
									throw new CException("GridView column must be string or array.");
								}
							})
						->end()
					->endEach()
					->end()
				->end()
					->tbody()
					->each($this->listData->getData())
						->tr()
						->each($this->columns)
							->td()
								->text(function($row, $column) {
									if (is_array($column)) {
										if (isset($column['id'])) {
										    return CTool::fromArray($row, $column['id'], '');
										} else if (isset($column['value']) && $value = $column['value']) {
											return CHtml::extractValue($column['value'], array('row' => $row, 'column' => $column));
										}
									} else if(is_string($column)) {
										return CTool::fromArray($row, $column, '');
									} else {
										throw new CException("Error GridView data.");
									}
								})
							->end()
						->endEach()
						->end()
					->endEach()
			->render();
	}

	public function renderPaginator() {
		if ($this->listData->getPageCount() > 0) {
			echo '<div class="pagination">';
			echo '<ul>';
			if ($this->listData->prevUrl()) {
				echo '<li><a href="'.$this->listData->prevUrl().'">Prev</a></li>';
			}
			foreach($this->listData->nearUrl() as $number => $url) {
				if ($this->listData->getCurrentPage() == $number) {
					echo '<li class="active"><a href="'.$url.'">'.$number.'</a></li>';
				} else {
					echo '<li><a href="'.$url.'">'.$number.'</a></li>';
				}
			}
			if ($this->listData->nextUrl()) {
				echo '<li><a href="'.$this->listData->nextUrl().'">Next</a></li>';
			}
			echo '</ul>';
			echo '</div>';
		}
	}
}