<?php
/**
* CCommand
* класс для работы с консолью
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
*/
namespace piha\modules\core\classes;
use piha\CException;
use piha\AClass;

class CCommand extends AClass {

	const METHOD_NAME = 'command';

    public static function getCommandName($command_id) {
        return self::METHOD_NAME . ucfirst($command_id);
    }

	public static function parse($argv) {
		$args = array();
		foreach ($argv as $arg) {
			$arg = trim($arg);
		    if (ereg('^--([^=]+)=(.*)$',$arg,$reg)) {
		        $args[$reg[1]] = $reg[2];
		    } elseif(ereg('^-([a-zA-Z0-9])$',$arg,$reg)) {
		        $args[$reg[1]] = true;
		    } else {
		    	echo "\n\nError param format for '{$arg}'. Please, use '--param=value' or '-p' format\n\n";
		    	exit(0);
		    }
		}
		return $args;
	}

	public function __construct($name, $argv) {
        if (!$argv) {
        	$this->help(null, null, "Command method for command '{$name}' not defined.");
        	exit(0);
        }
        $method = self::getCommandName(array_shift($argv));
        $args = self::parse($argv);
        if (!method_exists($this, $method)) {
        	$this->help($method, $args, "Command method {$method} for command '{$name}' not defined.");
        	exit(0);
        }
        $params = array();
        $f = new \ReflectionMethod(get_class($this), $method);
        $fParams = $f->getParameters();
        foreach ($fParams as $param) {
            if ($args && isset($args[$param->name])) {
                $params[$param->name] = $args[$param->name];
                unset($args[$param->name]);
            } else if ($param->isOptional() && $default = $param->getDefaultValue()) {
            	$params[$param->name] = $default;
            } else {
            	$this->help($method, $args, "Param '{$param->name}' for command '{$method}' is require.");
            	exit(0);
            }
        }
        if ($args) {
        	$this->help($method, $args, "Undefine params: '".implode(', ', array_keys($args))."' for {$class}::{$method}() command.");
        	exit(0);
        }
        $this->before();
        call_user_func_array(array($this, $method), $params);
        $this->after();
	}

	protected function help($method = null, $argv = null, $message = null) {
	    throw new CException($message);
	}

	protected function before() {

	}

	protected function after() {

	}
}
