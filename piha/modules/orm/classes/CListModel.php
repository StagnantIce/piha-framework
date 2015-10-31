<?php

namespace piha\modules\orm\classes;

use piha\modules\core\classes\CListData;

class CListModel extends CListData {

	private $q = null;

	private static $selfCount = 0;

	public function setQuery(CQuery $q) {
		$this->q = $q;
	}

	public function getModel() {
		return $this->q->getModel();
	}

	public function getData() {
		$this->data = $this->data ?: $this->q->limit(($this->getCurrentPage() -1) * $this->getPageSize(), $this->getPageSize())->execute()->all(false, 'ID');
		$this->total = CQuery::getTotal();
		$this->pageCount = floor($this->total / $this->getPageSize());
		return $this->data;
	}
}