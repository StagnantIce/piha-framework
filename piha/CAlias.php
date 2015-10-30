<?

/**
* CAlias
* класс для хранения путей к папкам
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
*/

namespace piha;

class CAlias {

	/** @var static array Массив объектов модулей */
	private static $ds = '';
	private static $aliases = array();

	public static function path($mixed=null, $folders=null) {
		if (!$mixed) {
			return '';
		}
		if ($folders == null) {
			if (is_array($mixed)) {
				$folders = $mixed;
				$mixed = '';
			} else if (!isset(self::$aliases[$mixed])) {
				throw new CException("Alias name {$mixed} not found");
			} else {
				return self::$aliases[$mixed];
			}
		}
		self::$ds = '/';
		self::$ds = self::$ds ?: (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '\\' : '/');
		$folders = is_array($folders) ? $folders : explode(self::$ds, $folders);
		$paths = array();
		foreach($folders as $name) {
			$paths[] = isset(self::$aliases[$name]) ? self::$aliases[$name] : $name;
		}
		$path = self::trim(implode(self::$ds, $paths));
		if ($mixed && strpos($mixed, '@') !== false) {
			if (!file_exists($path)) {
				throw new CException("Path {$path} not found.");
			}
			self::$aliases[$mixed] = $path;
		}
		return $path;
	}

	public static function requireFile($name, $path=null) {
		$file = self::file($name, $path);
		if (!file_exists($file)) {
			throw new CException("File {$file} not found.");
		}
		return require($file);
	}

	public static function includeFile($name, $path=null) {
		$file = self::file($name, $path);
		if (file_exists($file)) {
			return require($file);
		}
	}

	public static function file($name, $path=null) {
		return self::trim(self::path($path) . self::$ds . $name);
	}

	public static function trim($path) {
		return str_replace(self::$ds.self::$ds, self::$ds, $path);
	}
}