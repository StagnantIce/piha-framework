<?php


namespace piha\modules\core\classes;

class CListData {

	private $id = 0;
	protected $data = array();
	private static $selfCount = 0;
	public $pageSize = 10;

	public function __construct() {
		self::$selfCount++;
		$this->id = self::$selfCount;
	}

	public function setData(Array $data) {
		$this->data = $data;
	}

	public function getData() {
		return array_slice($this->data, ($this->getCurrentPage() -1) * $this->getPageSize(), $this->getPageSize());
	}

	public function getTotal() {
		return count($this->data);
	}

	public function getPageCount() {
		return ceil($this->getTotal() / $this->getPageSize());
	}

	public function getCurrentPage() {
		return intval(\Piha::request()->get('currentPage' . $this->id, 1));
	}

	public function getPageSize() {
		return intval(\Piha::request()->get('pageSize' . $this->id, $this->pageSize));
	}

	public function nextUrl() {
		if ($this->getCurrentPage() < $this->getPageCount()) {
			return \Piha::request()->url(array('currentPage'. $this->id => $this->getCurrentPage() + 1));
		}
		return false;
	}

	public function prevUrl() {
		if ($this->getCurrentPage() > 1) {
			return \Piha::request()->url(array('currentPage'. $this->id => $this->getCurrentPage() - 1));
		}
		return false;
	}

	public function nearUrl($count = 10) {
		$urls = array();
		$page = $this->getCurrentPage();
		$pageCount = $this->getPageCount();
		// find page start
		while ($page > $this->getCurrentPage() - $count / 2 + 1 && $page > 1) {
			$page--;
		}

		while($page > $pageCount - $count && $page > 1) {
			$page--;
		}

		for($i = $page; $i <= min($page + $count, $pageCount); $i++) {
			$urls[$i] = \Piha::request()->url(array('currentPage'. $this->id => $i));
		}

		if (count($urls) == 1) {
			return array();
		}

		return $urls;
	}
}