<?php

namespace piha;

interface IModule {
	/**
	  * Return absolute module path
	  * @return string equal __DIR__
	  */
	public static function GetDir();
}