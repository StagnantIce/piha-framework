<?
namespace piha\modules\orm\classes;
use piha\modules\orm\models\CMigrationModel;
use piha\modules\orm\classes\CQuery;
use piha\modules\orm\COrmModule;
use piha\CAlias;
use piha\AClass;
use piha\CException;

class CMigrationCommand extends AClass {

	public function create($name) {
		echo "Execute {$name}";
	}

	public function up() {

	}

	public function down() {

	}
}