<?

namespace piha\modules\bootstrap3\widgets;


class CNavWidget {

	private $_items = array();
	public function __construct($items) {
		$this->_items = $items;
	}

	public function render() {
		echo '<ul class="nav nav-sidebar">';
		foreach($this->_items as  $options) {
			echo '<li><a '.( isset($options['active']) && $options['active'] ? 'class = "active"' : '') . ' href="'.$options['href'].'">'.$options['label'].'</a></li>';
		}
		echo '</ul>';
	}

}