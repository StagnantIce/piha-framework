<?php
/**
* CLayout
* класс для работы с шаблонами
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
*/

namespace piha\modules\core\classes;
use piha\CAlias;
use piha\CException;


class CLayout extends CView {

	public $name;
	public $title = '';

	public function __construct($file, Array $context = null, $controller = null) {
		$this->name = $file;
		parent::__construct('/'. $file, $context, $controller);
	}

	public function title($title = '') {
		CHtml::create()->title()->html($title ?: $this->title)->render();
	}
}
