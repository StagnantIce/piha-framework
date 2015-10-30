<?php


namespace piha\modules\core\classes;

class CListData {

	private $id = 0;
	protected $data = array();
	private static $selfCount = 0;
	protected $pageCount = 0;
	protected $total = 0;

	public function __construct() {
		self::$selfCount++;
		$this->id = self::$selfCount;
	}

	public function setData(Array $data) {
		$this->data = $data;
	}

	public function getData() {
		return $this->data;
	}

	public function getTotal() {
		return $this->total;
	}

	public function getPageCount() {
		return $this->pageCount;
	}

	public function getCurrentPage() {
		return \Piha::request()->get('currentPage' . $this->id, 1);
	}

	public function getPageSize() {
		return \Piha::request()->get('pageSize' . $this->id, 20);
	}

	public function nextUrl() {
		if ($this->getCurrentPage() < $this->pageCount) {
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
		// find page start
		$page = $this->getCurrentPage();
		while ($page > $this->getCurrentPage() - $count / 2 + 1 && $page > 1) {
			$page--;
		}

		while($page >  $this->pageCount - $count) {
			$page--;
		}

		for($i = $page; $i < min($page + $count, $this->pageCount); $i++) {
			$urls[$i] = \Piha::request()->url(array('currentPage'. $this->id => $i));
		}

		return $urls;
	}
}