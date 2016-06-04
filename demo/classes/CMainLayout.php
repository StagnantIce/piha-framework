<?php

use piha\modules\core\classes\CLayout;

class CMainLayout extends CLayout {

	public function getContext($context) {
		$context['categories'] = CCategoryModel::GetTree();
		return $context;
	}
}