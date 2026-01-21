<?php 
namespace App;

/**	
 * Config template options
 */
class Template {
	public $textDomain;

	public function __construct(string $textDomain) 
	{
		$this->textDomain = $textDomain;

		add_action('after_setup_theme', [$this, 'title']);
		add_action('init', [$this, 'textDomain']);

		add_theme_support('post-thumbnails');
		$this->bigImageSize();
	}


	// Active menu
	public function menus(array $menus)
	{
		register_nav_menus($menus);
	}


	// Translate theme domain
	public function textDomain()
	{
		global $l10n, $wp_textdomain_registry;

		$locale = get_locale();
		$path = get_template_directory() . '/languages';

		$wp_textdomain_registry->set($this->textDomain, $locale, $path);
		if (isset($l10n[$this->textDomain])) unset($l10n[$this->textDomain]);

		$result = load_theme_textdomain($this->textDomain, $path);

		if ($result) return;

		$locale = apply_filters('theme_locale', get_locale(), $this->textDomain);
		die("Could not find $path/$locale.mo.");
	}


	// Active meta title
	public function title()
	{
		add_theme_support('title-tag');
	}

	
	public function imagesFormat(array $format)
	{
		foreach ($format as $value) :
			add_image_size($value[0], $value[1], $value[2], true);
		endforeach;
	}

	/**
	 * Image create format for retina
	 */
	public static function retinaFormat (string $img, int $width, int $height, int $retina)
	{
		// Edit filename
		$filename = basename($img);
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		$filename = str_replace('.' . $ext, '@' . $retina . 'x.' . $ext, $filename);

		// Clean home url if wpml
		$homeUrl = get_home_url();
		if (defined('ICL_LANGUAGE_CODE') && !empty(ICL_LANGUAGE_CODE)) {
			$homeUrl = str_replace('/' . ICL_LANGUAGE_CODE . '/', '', $homeUrl);
		}

		// New file
		$url = str_replace(basename($img), $filename, $img);
		$newFile = str_replace($homeUrl, '', $url);
		$newFile = $_SERVER['DOCUMENT_ROOT'] . $newFile;

		// Path original file
		$img = str_replace($homeUrl, '', $img);
		$img = $_SERVER['DOCUMENT_ROOT'] . $img;

		// Check exist file
		if (!file_exists($newFile)) {
			copy($img, $newFile);

			// Resize image
			$editor = wp_get_image_editor($newFile);
			$editor->resize($width, $height, true);
			$editor->save($newFile);
		}

		return $url;
	}

	/**
	 * Available big image sizes
	 * 
	 * @return void
	 */
	public function bigImageSize():void
	{
		add_filter('big_image_size_threshold', '__return_false');
	}
}
