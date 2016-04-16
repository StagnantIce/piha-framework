<?
use piha\modules\bootstrap3\widgets\CNavWidget;

function renderCats($categories, $c) {
	$items = array();

	foreach($categories as $cat) {
		$items[] = array('href' => $c->url('category/view', array('id' => $cat->id)), 'label' => $cat->name, 'childs' => renderCats($cat->childs, $c));
	}
	return $items;
}

$nav = new CNavWidget(renderCats($categories, $this));

$nav->render();

