<?php
namespace App;

class Autoloader {

	static function autoload() 
	{
		$path = get_template_directory() . '/class';
		$files = scandir($path);
		foreach ($files as $file) :
			if ($file != '.' && $file != '..') :
				require_once $path . '/' . $file;
			endif;
		endforeach;
	}
}
