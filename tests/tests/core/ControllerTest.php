<?
require_once (__DIR__ . '/../../index.php');

use piha\modules\core\CCoreModule;
use piha\modules\core\classes\CRequest;
use piha\modules\core\classes\CRouter;

class ControllerTest extends PHPUnit_Framework_TestCase {

	public function testRun() {
		$controller = new AuthController('login');
		$this->assertEquals('auth', $controller->id);
		$this->assertEquals('login', $controller->action_id);
		$this->assertEquals(AuthController::getActionName('login'), 'actionLogin');

		CCoreModule::SetConfig('prettyUrl', true);
		$out = $controller->runAction(true);
		$this->assertEquals($controller->url(), '/auth/login/');

		$this->assertContains('Login!!!', $out);
		$this->assertContains('<html', $out);

		$out = $controller->part('part/menu', null, true);
		$this->assertContains('nav', $out);

		$controller->flash('error', 'test');
		$this->assertEquals($controller->flash('error'), 'test');
		$this->assertEquals($controller->flash('error'), false);
	}

	public function testRouter() {
		$_SERVER['REQUEST_URI'] = '/auth/login/';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['SERVER_NAME'] = 'localhost';
		$request = new CRequest();
		$router = new CRouter($request);
		$this->assertEquals($router->getController()->id, 'auth');
		CCoreModule::SetConfig('prettyUrl', true);
		$this->assertEquals($router->buildUrl('/auth/login/', array('p' => 1)), '/auth/login/?p=1');
		$this->assertEquals($request->url(array('p' => 1)), '/auth/login/?p=1');
	}

	public function testPiha() {
		$this->assertEquals(Piha::controller(), null);
		$this->assertNotEquals(Piha::app(), null);
	}
}