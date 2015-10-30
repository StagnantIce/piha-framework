<?php

namespace piha\modules\core\classes;


class CRequest {
    public $location = '';
    public $path = '';
    public $host = '';
    public $method = '';

    /**
      * Получить из массива $_GET значение по имени ключа
      * @param string $key имя ключа
      * @param string $default значение по умолчанию в случае отсутствия параметра
      * @return mixed значение параметра
      */
    public function get($key, $default = null) {
        return CTool::fromArray($_GET, $key, $default);
    }

    /**
      * Получить из массива $_POST значение по имени ключа
      * @param string $key имя ключа
      * @param string $default значение по умолчанию в случае отсутствия параметра
      * @return mixed значение параметра
      */
    public function post($key, $default = null) {
        return CTool::fromArray($_POST, $key, $default);
    }

	public function __construct() {
        $this->location = $_SERVER['REQUEST_URI'];
        $url = parse_url($this->location);
        $this->path = $url['path'];
        $shema = isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] ? 'https': 'http';
        $this->host = $shema . '://' . $_SERVER['SERVER_NAME'];
        $this->method = $_SERVER['REQUEST_METHOD'];
	}

	public function url(Array $addParams = null, Array $removeParams = null) {
        $url = parse_url($this->location);
        $params = $_GET;
        if ($addParams) {
        	$params = array_replace_recursive($params, $addParams);
        }
        if ($removeParams) {
        	$params = array_diff_key($params,array_flip($removeParams));
        }
		return '/' . $this->path . $_GET ? '?' . http_build_query($params): '';
	}
}