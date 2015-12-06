<?
use piha\modules\bootstrap3\widgets\CNavWidget;
$nav = new CNavWidget(array(
    array('href' => $this->url('category/index'), 'label' => CCategoryModel::label())
));

$nav->render();

