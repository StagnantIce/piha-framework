<?

use piha\modules\bootstrap3\widgets\CHtml;
use piha\modules\bootstrap3\widgets\CForm;

echo $form->start();

foreach($model::GetFieldKeys() as $column) {
	echo $form->textGroup(array('name' => $column));
}

echo $form->submit(array('value' => $model->isNew() ? 'Добавить' : 'Сохранить'));

echo $form->end();
