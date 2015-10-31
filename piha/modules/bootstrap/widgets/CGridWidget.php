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
		echo '<table class="table table-striped table-bordered">';
		echo '<thead>';
		echo '<tr>';
		foreach($this->columns as $column) {
			echo '<th>';
			if (is_array($column) && isset($column['id'])) {
				echo CTool::fromArray($column, 'label', $column['id']);
			} else if(is_string($column)) {
				echo $column;
			} else {
				throw new CException("Error GridView columns.");
			}
			echo '</th>';
		}
		echo '</tr>';
		echo '<tbody>';
		foreach($this->listData->getData() as $row) {
			echo '<tr>';
			foreach($this->columns as $column) {
				echo '<td>';
				if (is_array($column) && isset($column['id'])) {
					echo CTool::fromArray($row, $column['id'], '');
				} else if(is_string($column)) {
					echo CTool::fromArray($row, $column, '');
				} else {
					throw new CException("Error GridView data.");
				}
				echo '</td>';
			}
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
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