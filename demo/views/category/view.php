<?php
use piha\modules\bootstrap3\widgets\CNavWidget;

function renderElements($cat, $c) {
	$items = array();
	foreach($cat->elements as $el) {
		$items[] = array('href' => $c->url('element/view', array('id' => $el->id)), 'label' => $el->name);
	}
	return $items;
}

function renderCategories($categories, $c) {
	$items = array();
	foreach($categories as $cat) {
		$items[] = array('href' => $c->url('category/view', array('id' => $cat->id)), 'label' => $cat->name, 'childs' => renderCategories($cat->childs, $c));
		renderElements($cat, $c);
	}
	return $items;
}
$items = renderCategories($elementCategories, $this);
$nav = new CNavWidget($items);
echo $nav->render();
$items = renderElements($category, $this);
$nav = new CNavWidget($items);
echo $nav->render();