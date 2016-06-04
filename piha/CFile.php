<?php

namespace piha;

class CFile {

	public static function Copy($src, $dst, $replace = false) {
		if (!file_exists($src)) {
			throw new CException("Path $src not found");
		}
	    if (!file_exists($dst)) {
	    	mkdir($dst);
	    }

	    $dir = opendir($src);


	    while(false !== ( $file = readdir($dir)) ) {
	    	$dstFile = $dst . CAlias::ds() . $file;
	    	if (!$replace && file_exists($dstFile)) {
	    		continue;
	    	}

	        if (( $file != '.' ) && ( $file != '..' )) {
	            if ( is_dir($src . CAlias::ds() . $file) ) {
	                self::Copy($src . CAlias::ds() . $file, $dstFile);
	            }
	            else {
	                copy($src . CAlias::ds() . $file, $dstFile);
	            }
	        }
	    }
	    closedir($dir);
	}

	public static function Delete($src) {
		if (!file_exists($src)) {
			throw new CException("Path $src not found");
		}
		if (is_dir($src)) {
			rmdir($src);
		} else {
			unlink($src);
		}
	}

}