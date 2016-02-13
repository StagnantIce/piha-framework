<?
  	use piha\modules\bootstrap3\widgets\CNavbarWidget;
  	if ($user = \Piha::user()->getModel()) {
  		$urls = array(
  			$this->url('/auth/logout') => $user->login . ' (Выход)'
  		);
  		if (\Piha::user()->hasPermission('admin')) {
  			$urls[$this->url('admin/category/index')] = 'Админка';
  		}

 	  	$nav = new CNavbarWidget($urls);
	  	$nav->render();
	} else {
	  	$nav = new CNavbarWidget(array(
	  		$this->url('/auth/login') => 'Авторизация',
	  		$this->url('/auth/reg') => 'Регистрация'
	  	));
	  	$nav->render();
	}

  ?>