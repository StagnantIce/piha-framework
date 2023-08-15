<?php

use piha\modules\bootstrap3\widgets\CGridWidget;
use piha\modules\bootstrap3\widgets\CHtml;

$modelClass = $list->getModel();
CHtml::create()->h2()->html($modelClass::label())->render();

CHtml::create()
	->p()
		->button($this->url('create'), CHtml::BUTTON_PRIMARY)->html('Добавить')->render();

foreach($modelClass::GetFieldKeys() as $column) {
	$columns[] = array('id' => $column);
}
$columns[] = array(
	'label' => 'Управление',
	'value' => function($data) {
		return CHtml::create()
			->button(\Piha::controller()->url('delete', array('id' => $data->id)), CHtml::BUTTON_DANGER)
				->icon(array(CHtml::ICON_REMOVE, CHtml::ICON_WHITE))
			->end()->end()
			->html(' ')
			->button(\Piha::controller()->url('update', array('id' => $data->id)), CHtml::BUTTON_SUCCESS)
				->icon(array(CHtml::ICON_PENCIL, CHtml::ICON_WHITE))
			->render(true);
	}
);

$grid = new CGridWidget($list, $columns);
$grid->render();
$grid->renderPaginator();
