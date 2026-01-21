<?php
namespace App;

class WordPress {

	public function __construct()
	{
		add_filter('auto_core_update_send_email', [$this, 'disabledEmailWordPressUpdate'], 10, 4);
		add_filter('auto_theme_update_send_email', '__return_false');
		add_filter('auto_plugin_update_send_email', '__return_false');
		add_action('after_switch_theme', [$this, 'protectUploadsDirectory']);
	}

	/**
	 * Disabled auto email WordPress Update
	 * 
	 * @return bool
	 */
	public function disabledEmailWordPressUpdate($send, $type, $core_update, $result)
	{
		if (!empty($type) && $type == 'success') {
			return false;
		}

		return true;
	}

	/**
	 * Protect uploads directory and subdirectories
	 * 
	 * @return void
	 */
	public function protectUploadsDirectory()
	{
		if (function_exists('got_mod_rewrite') && got_mod_rewrite()) {
			$home_path     = get_home_path();
			$htaccess_file = $home_path . '.htaccess';

			$rules = array(
				'# Disable directory listing for /wp-content/uploads/',
				'<IfModule mod_rewrite.c>',
				'  RewriteEngine On',
				'  RewriteCond %{REQUEST_URI} ^/wp-content/uploads/',
				'  RewriteCond %{REQUEST_FILENAME} -d',
				'  RewriteRule ^ - [F,L]',
				'</IfModule>',
			);
			insert_with_markers($htaccess_file, 'MyTheme Uploads Protection', $rules);
		}

		$uploads_dir = WP_CONTENT_DIR . '/uploads';
		if (is_dir($uploads_dir) && is_writable($uploads_dir)) {
			$uploads_ht = trailingslashit($uploads_dir) . '.htaccess';
	
			$content = "Options -Indexes\n";
			if (!file_exists($uploads_ht) || md5_file($uploads_ht) !== md5($content)) {
				file_put_contents($uploads_ht, $content);
			}
		} else {
			add_action('admin_notices', function () use ($uploads_dir) {
				echo '<div class="notice notice-warning"><p>Unable to write in <code>' . esc_html($uploads_dir) . '</code>. Check permissions to protect the directory index.</p></div>';
			});
		}
	}
}

new WordPress();
