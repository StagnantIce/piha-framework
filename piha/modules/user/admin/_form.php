<?php

use piha\modules\bootstrap3\widgets\CHtml;
use piha\modules\bootstrap3\widgets\CForm;
use piha\modules\orm\classes\CModel;
use piha\modules\orm\classes\CMigration;

echo $form->start();

foreach($model::m()->GetColumns() as $key => $column) {
	if (isset($column['object'])) {
		$relationModel = $column['object'];
		echo $form->selectGroup(array('name' => $key, 'options' => $relationModel::StaticGetArray('ID', 'NAME')));
	} else {
		switch($column['type']):
			case 'pk':
			break;
			case CMigration::TYPE_TEXT:
				echo $form->textareaGroup(array('name' => $key));
			break;
			default:
				echo $form->textGroup(array('name' => $key));
			break;
			case 'boolean':
				echo $form->checkboxGroup(array('name' => $key));
			break;
		endswitch;
	}
}

foreach($model->getRelations() as $type => $relation) {
	foreach($relation as $name => $classes) {
		switch($type) {
			case CModel::TYPE_MANY:
				$relationModel = end($classes);

				echo $form->selectGroup(array('name' => $name, 'options' => $relationModel::StaticGetArray('ID', 'NAME'), 'multiple' => 'multiple', 'label' => $relationModel::label()));
				break;
		}
	}
}

echo $form->submit(array('value' => $model->isNew() ? 'Добавить' : 'Сохранить'));

echo $form->end();
