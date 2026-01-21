<?php
namespace App;

use Dotenv\Dotenv;

class Tools {

	public function __construct()
	{
		$this->loadEnvVars();
	}

	/**
	 * Load environment variables
	 * 
	 * @return void
	 */
	private function loadEnvVars(): void
	{
		$theme_root = get_template_directory();
		$dotenv = Dotenv::createImmutable($theme_root);
		$dotenv->safeLoad();
	}
}

new Tools();
