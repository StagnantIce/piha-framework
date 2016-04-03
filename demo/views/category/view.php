<?
use piha\modules\bootstrap3\widgets\CNavWidget;

$items = array();

function renderElements($cat) {
	foreach($cat->elements as $el) {
		$items[] = array('href' => $c->url('element/view', array('id' => $el->id)), 'label' => $el->name);
	}
}

function render($categories, $c) {
	foreach($categories as $cat) {
		$items[] = array('href' => $c->url('category/view', array('id' => $cat->id)), 'label' => $cat->name, 'childs' => render($cat->childs, $c));
		renderElements($cat);
	}
	return $items;
}

$nav = new CNavWidget(render($categories, $this));
renderElements($category);

$nav->render();
