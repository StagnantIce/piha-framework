<?php

namespace piha\modules\bootstrap3\widgets;


class CNavWidget {

	private $_items = array();
	private $_classes = 'nav nav-sidebar';
	public function __construct($items) {
		$this->_items = $items;
	}

	public function render($classes = false) {
		echo '<ul class="'.($classes ?: $this->_classes).'">';
		foreach($this->_items as  $options) {
			echo '<li style="list-style: none;"><a '.( isset($options['active']) && $options['active'] ? 'class = "active"' : '') . ' href="'.$options['href'].'">'.$options['label'];
			if (isset($options['childs'])) {
				$nav = new self($options['childs']);
				$nav->render('sub-menu');
			}
			echo '</a></li>';
		}
		echo '</ul>';
	}

}