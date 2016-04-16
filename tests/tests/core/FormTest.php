<?
require_once (__DIR__ . '/../../index.php');

use piha\modules\core\classes\CForm;

class FormTest extends PHPUnit_Framework_TestCase {

	public function testForm() {
		$form = CForm::post(array('name' => 'Form'));
		$this->assertEquals($form->start(), '<form action="" name="Form" method="POST">');
		foreach(array('text', 'email', 'search', 'date', 'datetime', 'url', 'month', 'number', 'range', 'tel', 'time', 'week', 'color', 'button', 'radio', 'submit', 'password') as $type)
		$this->assertEquals($form->$type(), '<input type="'.$type.'"/>');
	    $this->assertEquals($form->checkbox(), '<input type="checkbox" value="1"/>');
		$this->assertEquals($form->datetimeLocal(), '<input type="datetime-local"/>');
		$this->assertEquals($form->textarea(array('name' => 'name')), '<textarea name="Form[name]"></textarea>');
		$this->assertEquals($form->select(array('options' => array(1 => 'text'))), '<select><option value="1">text</option></select>');
		$this->assertEquals($form->end(), '</form>');

		$_POST['Form'] = array('name' => 'text');
		$form = CForm::post(array('name' => 'Form'));
		$this->assertEquals($form->textarea(array('name' => 'name')), '<textarea name="Form[name]">text</textarea>');
		$this->assertEquals($form->getValue('name'), 'text');
	}
}