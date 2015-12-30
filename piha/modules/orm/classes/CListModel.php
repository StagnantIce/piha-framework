<?php

namespace piha\modules\orm\classes;

use piha\modules\core\classes\CListData;
use piha\CException;

class CListModel extends CListData {

	private $q = null;
	private $total = 0;

	private static $selfCount = 0;

	public function setQuery(CQuery $q) {
		$this->q = $q;
	}

	public function getModel() {
		if ($this->q) {
			return $this->q->getModel();
		} else if ($this->data) {
			return $this->data[0];
		}
		return false;
	}

	public function getTotal() {
		return $this->total;
	}

	public function getData() {
		if ($this->data) {
			$result = array();
			foreach($this->data as $d) {
				$result[] = $d->toArray();
			}
			$this->total = count($result);
			return $result;
		} else if ($this->q) {
			$this->data = $this->q->limit(($this->getCurrentPage() -1) * $this->getPageSize(), $this->getPageSize())->execute()->all(false, 'ID');
			$this->total = CQuery::getTotal();
		}
		return $this->data;
	}
}