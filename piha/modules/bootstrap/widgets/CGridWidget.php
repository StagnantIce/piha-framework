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
						->th(function($column){
							if (is_array($column) && isset($column['id'])) {
								return array('text' => CTool::fromArray($column, 'label', $column['id']));
							} else if(is_string($column)) {
								return array('text' => $column);
							} else {
								throw new CException("Error GridView columns.");
							}
						})
						->end()
					->endEach()
					->end()
					->tbody()
					->each($this->listData->getData())
						->tr()
						->each($this->columns)
							->td(function($column) {
								if (is_array($column) && isset($column['id'])) {
									return array('text' => CTool::fromArray($row, $column['id'], ''));
								} else if(is_string($column)) {
									return array('text' => CTool::fromArray($row, $column, ''));
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