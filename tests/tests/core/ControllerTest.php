<?
require_once (__DIR__ . '/../../index.php');

use piha\modules\core\CCoreModule;

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
	}
}