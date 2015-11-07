<?php

namespace piha\modules\core\classes;

/**
  * @deprecated
  */
class CFormModel {


    public static function tr($name, $content, $class = '', $id = '') {
        return '
        <tr' . ($id ? ' id="'.$id.'"': '') . ($class ? ' class="'.$class.'"': '') . ' style="width:50%">
            <td style="width:50%"><div style="float:right; margin-top:5px;">'.$name.'</div><div style="clear:both"></div></td>
            <td>'.$content.'</td>
        </tr>';
    }

    public static function checkbox($name, $key, $value, $checked, $type="checkbox", $class = "", $id = "") {
        return self::tr($name, self::hidden('', $key, $value ?: "Y") . "<input type='$type' onclick='$(this).prev().val($(this).is(\":checked\") ? \"Y\" : \"N\")' " . ($checked ? "checked = 'checked'": "") . "/>", $class, $id);
    }

    public static function hidden($name, $key, $value, $type="hidden", $class = "", $id = "") {
        return self::input($name, $key, $value, $type, $class, $id);
    }

    public static function input($name, $key, $value, $type="text", $class = "", $id = "") {
        $input = "<input type='$type' name='$key' value='" . (is_string($value) ? htmlspecialchars($value, ENT_QUOTES): $value) . "'/>";
        if ($name) {
            return self::tr($name, $input, $class, $id);
        } else {
            return $input;
        }
    }

    public static function select($name, $key, $value, $values, $class = "", $id = "") {
        $input = "<select name='$key'>";
        foreach($values as $k => $v) {
            $input .= '<option '. ($value == $k ? 'selected' : '').' value = "'.$k.'">'.$v.'</option>';
        }
        $input .= '</select>';
        if ($name) {
            return self::tr($name, $input, $class, $id);
        } else {
            return $input;
        }
    }

    public static function textarea($name, $key, $value, $class = "", $id = "") {
        $input = "<textarea name='$key'/>" . (is_string($value) ? htmlspecialchars($value, ENT_QUOTES): $value) . "</textarea>";
        if ($name) {
            return self::tr($name, $input, $class, $id);
        } else {
            return $input;
        }
    }

    public static function Start($action, $obj, $replace = array()) {
        $s = '<form action="'.$action.'" method="POST"><table style="width:100%">';
        $modelName = get_class($obj);
        $values = $obj->toArray();
        $names = $modelName::GetFieldNames();
        foreach($names as $key => $label) {
            if (isset($replace[$key])) {
                $s .= $replace[$key];
            } else if ($key == 'ID' && isset($_REQUEST['id'])) {
                $s .= self::hidden("", "id",  intval($_REQUEST['id']));
            } elseif ($type = $modelName::getType($key) and $key != 'ID') {
                switch($type) {
                    case "char":
                        $s .= self::checkbox($label, $key, $values[$key], $values[$key] == 'Y');
                    break;
                    case 'text':
                        $s .= self::textarea($label, $key,  $values[$key]);
                        break;
                    default:
                        $s .= self::input($label, $key,  $values[$key]);
                }
            } elseif ($key != 'ID') {
                $s .= self::input($label, $key,  $values[$key]);
            }
        }
        $s .= '<tr><td colspan="2" style="text-align:right"><input type="submit" value="Save"/></td></tr></table>
        </form>';
        return $s;
    }

}