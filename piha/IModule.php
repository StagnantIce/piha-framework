<?php

namespace piha;

interface IModule {
	/**
	  * Return absolute module path
	  * @return string equal __DIR__
	  */
	function GetDir();

	/**
	  * Return paths with module classes
	  * @return Array of paths
	  */
	function GetDirPaths();
}