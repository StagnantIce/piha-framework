<?php

namespace piha\modules\core\classes;

/**
* класс CCoreException
* класс для работы с исключениями
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
* @namespace
*/

class CCoreException extends \Exception {
    private $backtrace='';
    public function __construct($message, $code = 0, Exception $previous = null) {
        $this->backtrace = self::GetBacktrace(1);
        parent::__construct($message, $code, $previous);
    }

    /**
     * Фильтрация backtrace
     * @param int $ignore - с какой позиции читать backtrace
     * @param int $rows - сколько строк читать из backtrace
     * @return Array backtrace
     */
    public static function GetBacktrace($ignore = 3, $rows = 20) {
        $new_backtrace = array();
        $trace = '';
        foreach (debug_backtrace() as $k => $v) {
            if ($k < $ignore || $k > $ignore + $rows) {
                continue;
            }
            $new_backtrace[$k - $ignore + 1] = $v;
        }
        return $new_backtrace;
    }

    /**
     * Генерация подробного описания исключения для дебага
     * @param array $backtrace результат выполнения функции debug_backtrace() или уровень для ее фильтрации
     * @param boolean $html возвращать html теги или только текст
     * @return string html или текст подробно описывающий выброшенное исключение для дебага
     */
    public static function ErrorHandler($backtrace=1, $html=true) {
        $trace = '';
        if (PIHA_CONSOLE) {
            $html = false;
        }
        $backtrace = is_numeric($backtrace) ? self::GetBacktrace($backtrace) : $backtrace;
        foreach ($backtrace as $k => $v) {
            array_walk($v['args'], function (&$item, $key) {
                if (is_scalar($item) && !is_string($item)) { // Nesting level too deep fix
                    $item = var_export($item, true);
                } else {
                    if (is_array($item) && count($item) > 10) {
                        $item = array_slice($item, 0, 10);
                        $item[] = count($item) .' elements';
                    }
                    $item = print_r($item, true);
                }
            });
            $handle = fopen($v['file'], "r");
            if (isset($_SERVER['DOCUMENT_ROOT']) && strpos($v['file'], $_SERVER['DOCUMENT_ROOT']) !== false) {
                $v['file'] = substr($v['file'], strlen($_SERVER['DOCUMENT_ROOT']), strlen($v['file']));
            }
            $line = 1;
            $trace .= '#' .($k) . ' '. (isset($v['class']) ? $v['class'] . '->' : '') . $v['function'] . '(' . implode(', ', $v['args']) . ')' . ($html ? "<br/>\n<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\t" : "\nfile:\t") . $v['file'] . ':' . $v['line'] . ($html ? "</b><br/>\n" : "\n");
            $trace .= $html ? '<table style="border:1px #EEEEEE solid;padding:0;margin:0;width:100%" cellspacing=0>' : '';
            while ($line < $v['line'] + 3 && !feof($handle)) {
                $buffer = fgets($handle);
                if ($line > $v['line'] - 3) {
                    $trace .= $html ? '<tr><td style="background:#FFFFEE; color: #999999; padding: 0 5px; width:20px; border-right:1px solid #CCCCCC">'.$line.'</td>' : '';
                    if ($line == $v['line']) {
                        $trace .= $html ? '<td style="background:#FCE3E3">' :  '*';
                    } else {
                        $trace .= $html ? '<td style="background:#FFFFEE">' : '';
                    }
                    $trace .= $html ? htmlspecialchars($buffer, ENT_QUOTES).'<br/>' : $buffer."\n";
                    $trace .= $html ? '</td></tr>': '';
                }
                $line++;
            }
            fclose($handle);
            $trace .= $html ? '</table>' : '';
        }

        return $trace;
    }

    /**
     * Вывод исключения на странице и прекращение работы
     */
    public function __toString() {
        ob_end_clean();
        ob_start();

        echo '<h2>' . $this->getMessage() . '</h2><br/>';
        echo self::ErrorHandler($this->backtrace);
        exit();
    }
}