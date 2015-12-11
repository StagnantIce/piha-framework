<?php

namespace piha\modules\bootstrap\widgets;

use piha\CException;
use piha\modules\core\classes\CTool;
use piha\modules\core\classes\CListData;
use piha\modules\core\classes\CView;
use piha\modules\orm\classes\CListModel;

class CGridWidget {

	private $listData;
	private $columns;
	private $model;
	private $htmlOptions = array(
		'table' => array(
			'class' => 'table table-bordered table-striped'
		)
	);

	public function __construct(CListData $l, Array $columns = null, $htmlOptions = array()) {
		$this->listData = $l;
		$this->columns = $columns ?: array();
		if ($this->listData instanceof CListModel) {
			$this->model = $this->listData->getModel();
			if ($model = $this->model) {
				foreach($this->columns as &$column) {
					if (!isset($column['label'])) {
						$column['label'] = $model::getLabel($column['id']);
					}
				}
			}
		}
		$this->htmlOptions = array_replace($this->htmlOptions, $htmlOptions);
	}

	public function render() {
		$view = new CView('grid_view', array(
				'columns' => $this->columns,
				'listData' => $this->listData,
				'model' => $this->model,
				'htmlOptions' => $this->htmlOptions
			)
		);
		$view->setAlias(array(__DIR__, '..', 'views'));
		echo $view->render();
	}

	public function renderPaginator() {
		$view = new CView('paginator_view', array(
				'listData' => $this->listData
			)
		);
		$view->setAlias(array(__DIR__, '..', 'views'));
		echo $view->render();
	}
}