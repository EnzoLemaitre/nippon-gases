<?php
namespace App;

/**
 * Custom login admin WordPress
 */
class LoginAdmin {
	
	public function __construct() 
	{
		add_filter('login_headerurl', [$this, 'url']);
		add_filter('login_headertext', [$this, 'title']);
		add_filter('login_head', [$this, 'head']);
	}

	// Custom url in logo
	public function url(): string
	{
		return home_url();
	}

	// Change content in link logo
	public function title(string $message): string
	{
		return get_bloginfo('name');
	}

	// Add favicon and custom css
	public function head()
	{
		echo '<link rel="shortcut icon" href="' . get_stylesheet_directory_uri() . '/assets/img/favicon.ico" />';
		echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('template_directory') . '/assets/css/login.css" />';
	}
}

new LoginAdmin;
