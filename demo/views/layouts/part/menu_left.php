<?
use piha\modules\bootstrap3\widgets\CNavWidget;

$items = array();

function render($categories, $c) {
	foreach($categories as $cat) {
		$items[] = array('href' => $c->url('category/view', array('id' => $cat->id)), 'label' => $cat->name, 'childs' => render($cat->childs, $c));
	}
	return $items;
}

$nav = new CNavWidget(render($categories, $this));

$nav->render();

