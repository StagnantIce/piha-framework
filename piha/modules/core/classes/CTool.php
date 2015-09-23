<?php

class CTool {

	public static function in($scalarNeed, $mixedList) {
		if (is_scalar($scalarNeed)) {
			if (is_string($mixedList)) {
				return strpos($mixedList, '' . $scalarNeed) !== false;
			}
			if (is_array($mixedList)) {
				return in_array($scalarNeed, $mixedList);
			}
		}
		throw new CCoreException("Error in function");
	}

	public static function random($length = 8, $chars = 'Aa1') {
        $rnd = '';
        if (self::in('A', $chars)) {
        	$rnd .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if (self::in('a', $chars)) {
        	$rnd .= 'abcdefghijklmnopqrstuvwxyz';
        }
        if (self::in(1, $chars)) {
        	$rnd .= '0123456789';
        }
        if ($rnd == '' || $length == 0) {
            throw new CCoreException("No letters to generate");
        }
        return substr(str_shuffle($rnd), 0, $length);
    }

}