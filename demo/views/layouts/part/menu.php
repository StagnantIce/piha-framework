<?
  	use piha\modules\bootstrap3\widgets\CNavbarWidget;
  	if ($user = \Piha::user()->getModel()) {
 	  	$nav = new CNavbarWidget(array(
	  		$this->url('auth/logout') => $user->username . ' (Выход)'
	  	));
	  	$nav->render();
	} else {
	  	$nav = new CNavbarWidget(array(
	  		$this->url('auth/login') => 'Авторизация',
	  		$this->url('auth/reg') => 'Регистрация'
	  	));
	  	$nav->render();
	}

  ?>