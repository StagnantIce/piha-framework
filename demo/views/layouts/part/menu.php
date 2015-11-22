<?
  	use piha\modules\bootstrap3\widgets\CNavbarWidget;
  	$nav = new CNavbarWidget(array(
  		$this->url('auth/login') => 'Авторизация',
  		$this->url('auth/reg') => 'Регистрация'
  	));
  	$nav->render();

  ?>