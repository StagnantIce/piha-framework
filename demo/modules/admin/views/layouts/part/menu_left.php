<?
use piha\modules\bootstrap3\widgets\CNavWidget;
$nav = new CNavWidget(array(
    array('href' => $this->url('category/index'), 'label' => CCategoryModel::label()),
    array('href' => $this->url('element/index'), 'label' => CElementModel::label()),
    array('href' => $this->url('elementType/index'), 'label' => CElementTypeModel::label()),
    array('href' => $this->url('user/index'), 'label' => CUserModel::label())
));

$nav->render();
