<?
namespace piha\modules\permission\classes;
use piha\modules\core\classes\CCommand;
use piha\CException;
use piha\modules\permission\CPermissionModule;

class CPermissionCommand extends CCommand {

	public function commandAdmin($password, $login='admin') {
		$class = CPermissionModule::Config('modelClass');
		if (!$class) {
			throw new CException("Config 'modelClass' for user model not defined.");
		}
		$user = $class::GetOrCreate(array('LOGIN' => $login));
		$user->password = \Piha::user()->hashPassword($password);
		$user->save();
		\Piha::permission()->addRole('admin');
		\Piha::permission()->assign($user->id, 'admin');
	}
}