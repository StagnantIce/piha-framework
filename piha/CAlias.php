<?php

/**
* CAlias
* класс для хранения путей к папкам
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
*/

namespace piha;

class CAlias extends AClass {

	/** @var string $ds разделитель между папок */
	private static $ds = '';

	/** @var array $aliases сохраненные пути */
	private static $aliases = array();

	/**
	 * Получить разделитель для папок
	 * @param integer $number количество повторений
	 * @return string
	 */
	public static function ds($number=1) {
		self::$ds = self::$ds ?: (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '\\' : '/');
		return str_repeat(self::$ds, $number);
	}

	/**
	 * Получить путь до папки
	 * @param string|array $mixed путь до папки, который может содержать алиасы
	 * @return string
	 */
	public static function GetPath($mixed) {
		$folders = is_array($mixed) ? $mixed : explode(self::ds(), $mixed);
		$paths = array();
		foreach($folders as $name) {
			$paths[] = isset(self::$aliases[$name]) ? self::$aliases[$name] : $name;
		}
		return self::trim(implode(self::ds(), $paths));
	}

	/**
	 * Получить путь до папки по алиасу
	 * @param string $name имя алиаса
	 * @return string
	 */
	public static function GetAlias($name) {
		if (!isset(self::$aliases[$name])) {
			throw new CException("Alias name {$name} not found");
		}
		return self::$aliases[$name];
	}

	/**
	 * Создать новый алиас
	 * @param string $name имя алиаса
	 * @param string|array $mixed путь до алиаса
	 * @return null
	 */
	public static function SetAlias($name, $mixed) {
		$path = self::GetPath($mixed);
		if (!file_exists($path)) {
			throw new CException("Path {$path} not found.");
		}
		self::$aliases['@'.ltrim($name, '@')] = $path;
	}

	/**
	 * Подключить файл согласно алиасу
	 * @param string $name имя файла
	 * @param string|array $path путь до файла
	 * @return mixed
	 */
	public static function requireFile($name, $path=null) {
		$file = self::file($name, $path);
		if (!file_exists($file)) {
			throw new CException("File {$file} not found.");
		}
		return require($file);
	}

	/**
	 * Подключить файл согласно алиасу
	 * @param string $name имя файла
	 * @param string|array $path путь до файла
	 * @return mixed
	 */
	public static function includeFile($name, $path=null) {
		$file = self::file($name, $path);
		if (file_exists($file)) {
			return require($file);
		}
	}

	/**
	 * Получить путь до файла
	 * @param string $name имя файла
	 * @param string|array $mixed путь до файла
	 * @return string
	 */
	public static function file($name, $mixed=null) {
		$mixed  = $mixed ? self::GetPath($mixed) . self::ds() : '';
		return self::trim($mixed . $name);
	}

	/**
	 * Убрать лишние слеши в пути файла
	 * @param string $path путь
	 * @return string
	 */
	public static function trim($path) {
		return str_replace(self::ds(2), self::ds(), $path);
	}
}