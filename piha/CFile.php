<?php

namespace piha;

class CFile {

	public static function Copy($src, $dst, $replace = false) {
		if (!file_exists($src)) {
			throw new CException("Path $src not found");
		}
	    if (!file_exists($dst)) {
	    	self::MkDir($dst);
	    }

	    $dir = opendir($src);


	    while(false !== ( $file = readdir($dir)) ) {
	    	$dstFile = $dst . CAlias::ds() . $file;
	    	if (!$replace && file_exists($dstFile)) {
	    		continue;
	    	}

	        if (( $file != '.' ) && ( $file != '..' )) {
	            if ( is_dir($src . CAlias::ds() . $file) ) {
	                self::Copy($src . CAlias::ds() . $file, $dstFile, $replace);
	            }
	            else {
	                copy($src . CAlias::ds() . $file, $dstFile);
	            }
	        }
	    }
	    closedir($dir);
	}

    public static function MkDir($src) {
        mkdir($src,0755,true);
        if (!file_exists($src)) {
            throw new CException("Path $src can`t created");
        }
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
        if (file_exists($src)) {
            throw new CException("Path $src not deleted");
        }
	}
}