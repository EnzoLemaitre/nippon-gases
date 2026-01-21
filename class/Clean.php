<?php
namespace App;

/**
 * Clean default wordpress configuration
 */
class Clean {
	
	public $activeNews = true;

	public function __construct()
	{
		add_action('admin_head', [$this, 'removeHelpTab']);
		add_filter('emoji_svg_url', [$this, 'removeSvgUrl']);
		add_action('wp_enqueue_scripts', [$this, 'removeBlockLibrary']);
		add_filter('admin_footer_text', [$this, 'adminFooterText'], 11);
		add_filter('admin_bar_menu', [$this, 'removeWordPressLogo'], 999);
		add_filter('admin_menu', [$this, 'removeMenuItems']);
		add_filter('admin_menu', [$this, 'removeDashboardWidget']);
		add_filter('admin_menu', [$this, 'removeAppearanceSubMenu']);
		add_action('wp_before_admin_bar_render', [$this, 'adminBar']);
		add_filter('intermediate_image_sizes_advanced', [$this, 'removeDefaultImageSizes']);
		add_action('template_redirect', [$this, 'disableAuthorPage']);
		add_filter('wp_revisions_to_keep', [$this, 'fixeMaxRevisions'], 10, 2);
		add_filter('xmlrpc_enabled', '__return_false');
		add_action('admin_menu', [$this, 'disableThemePluginEditors'], 999);

		$this->removePost();
		$this->removeEmailAlertFatal();
		$this->removeComments();

		$this->_cleanHeader();
	}

	/**
	 * Remove post type
	 * 
	 * @return void
	 */
	private function removePost()
	{
		if (!$this->activeNews) {
			add_action('wp_dashboard_setup', [$this, 'removeDraftWidget'], 999);
			add_action('admin_bar_menu', [$this, 'removeDefaultPostTypeMenuBar'], 999);
			add_action('admin_menu', [$this, 'removeDefaultPostType'], 999);
		}
	}

	public function removeDefaultPostType()
	{
		remove_menu_page('edit.php');
	}

	public function removeDraftWidget()
	{
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	}

	public function removeDefaultPostTypeMenuBar($wp_admin_bar)
	{
		$wp_admin_bar->remove_node('new-post');
	}

	public function removeSvgUrl() {
		return false;
	}

	/**
	 * Delete the theme and plugin editors
	 * 
	 * @return void
	 */
	public function disableThemePluginEditors(): void
	{
		remove_menu_page('theme-editor.php');
		remove_menu_page('plugin-editor.php');
	}

	private function _cleanHeader()
	{
		remove_action('wp_head', 'wp_generator');
		add_filter('the_generator', '__return_empty_string');
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'wp_shortlink_wp_head');
		remove_action('wp_head', 'rest_output_link_wp_head', 10);

		// WPML
		global $sitepress;
		remove_action('wp_head', [$sitepress, 'meta_generator_tag']);

		// REMOVE WP EMOJI
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('admin_print_styles', 'print_emoji_styles');
	}

	// Removes the Help tab in the WP Admin
	public function removeHelpTab()
	{
		$screen = get_current_screen();
		$screen->remove_help_tabs();
	}

	public function removeBlockLibrary()
	{
		wp_dequeue_style('wp-block-library');
	}

	public function adminFooterText()
	{
		return '';
	}

	public function removeWordPressLogo($adminBar)
	{
		$adminBar->remove_node('wp-logo');
	}

	public function adminBar()
	{
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('comments');
		$wp_admin_bar->remove_menu('wp-logo');
		$wp_admin_bar->remove_node('new-content');
		$wp_admin_bar->remove_node('customize');
	}

	public function removeAppearanceSubMenu()
	{
		global $submenu;
		unset($submenu['themes.php'][6]);
		unset($submenu['themes.php'][7]);
		remove_action('admin_menu', '_add_themes_utility_last', 101);
	}

	public function removeDashboardWidget()
	{
		remove_action('welcome_panel', 'wp_welcome_panel');
		remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
		remove_meta_box('dashboard_activity', 'dashboard', 'normal');
		remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
		remove_meta_box('dashboard_primary', 'dashboard', 'core');
	}

	// Remove menu admin
	public function removeMenuItems()
	{
		// remove_menu_page('tools.php');
		remove_menu_page('edit-comments.php');
		//remove_menu_page('upload.php');
	}

	/**
	 * Remove default image sizes
	 */
	public function removeDefaultImageSizes(array $sizes): array
	{
		unset($sizes['thumbnail']);
		unset($sizes['medium']);
		unset($sizes['medium_large']);
		unset($sizes['large']);
		return $sizes;
	}

	/**
	 * Disable author page
	 */
	public function disableAuthorPage()
	{
		global $wp_query;
		if (is_author()) {
			$wp_query->set_404();
			status_header(404);
			nocache_headers();
		}
	}

	/**
	 * Fixe max numbe of revisions
	 * 
	 * @return int
	 */
	public function fixeMaxRevisions(): int
	{
		return 5;
	}
	
	/**
	 * Remove email alert fatal
	 * 
	 * @return void
	 */
	private function removeEmailAlertFatal(): void
	{
		if (defined('WP_DEBUG') && WP_DEBUG) {
			add_filter('wp_fatal_error_handler_enabled', '__return_false');
		}
	}

	/**
	 * Remove all comments
	 * 
	 * @return void
	 */
	public function removeComments(): void
	{
		add_action('admin_init', function () {
			foreach (get_post_types() as $post_type) {
				remove_post_type_support($post_type, 'comments');
				remove_post_type_support($post_type, 'trackbacks');
			}
		});

		add_action('init', function () {
			if ($_SERVER['REQUEST_URI'] === '/wp-comments-post.php') {
				wp_die('Comments are disabled.', 'Erreur', ['response' => 403]);
			}
		});

		add_filter('comments_open', '__return_false', 20, 2);
		add_filter('pings_open', '__return_false', 20, 2);
		add_filter('comments_array', '__return_empty_array', 10, 2);

		add_action('admin_menu', function () { remove_menu_page('edit-comments.php'); });
		add_action('wp_dashboard_setup', function () { remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); });

		add_action('add_meta_boxes', function () {
			foreach (get_post_types() as $post_type) {
				remove_meta_box('commentstatusdiv', $post_type, 'normal');
				remove_meta_box('commentsdiv', $post_type, 'normal');
			}
		}, 99);

		add_action('add_meta_boxes', function () {
			remove_meta_box('commentstatusdiv', 'post', 'normal');
			remove_meta_box('commentsdiv', 'post', 'normal');
			remove_meta_box('commentstatusdiv', 'page', 'normal');
			remove_meta_box('commentsdiv', 'page', 'normal');
		}, 99);

		add_filter('manage_posts_columns', function ($columns) {
			unset($columns['comments']);
			return $columns;
		});

		add_filter('manage_pages_columns', function ($columns) {
			unset($columns['comments']);
			return $columns;
		});
	}
}

new Clean;
