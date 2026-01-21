<?php
namespace App;

class Tinymce {

	public function  __construct()
	{
		add_filter('acf/fields/wysiwyg/toolbars' , [$this, 'toolbars']);
		add_action('init', [$this, 'addCssTinymce']);
		add_filter('tiny_mce_before_init', [$this, 'configTinymce']);
		add_filter('mce_buttons', [$this, 'configTinymceBtn']);
		add_filter('tiny_mce_before_init', [$this, 'customColorTinymce']);
		add_filter('tiny_mce_plugins', [$this, 'removeCustomColors']);
	}

	// Configure tinymce for acf plugin
	public function toolbars(array $toolbars): array
	{
		// Basic toolbar
		$toolbars['Basic'][1] = ['bold', 'italic', 'underline', 'bullist', 'link', 'unlink', 'removeformat'/*, 'forecolor'*/];

		// Edit the "Full" toolbar and remove 'code'
		if (($key = array_search('code' , $toolbars['Full' ][2])) !== false) :
			unset($toolbars['Full'][2][$key]);
		endif;

		$toolbars['Full'][2] = ['bullist', 'pastetext'];

		return $toolbars;
	}

	// Add css into content field admin
	public function addCssTinymce()
	{
		add_editor_style('assets/css/main.css');
	}

	// Configure Tinymce format
	public function configTinymce(array $args): array
	{
		$args['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3';

	    return $args;
	}

	// Configure Tinymce buttons
	public function configTinymceBtn($args): array
	{
		$buttons = [
			'formatselect',
			'bold',
			'italic',
			'underline',
			'bullist',
			'numlist',
			'removeformat',
			'link',
			'unlink',
			//'forecolor'
			/*'alignleft',
			'aligncenter',
			'alignright',
			'alignjustify',*/
		];

	    return $buttons;
	}

	/**
	 * Customize the default color palette for TinyMce editor
	 */
	public function customColorTinymce ($options): array
	{
		$options['textcolor_map'] = json_encode(
			[
				'000000', 'primary',
				'000000', 'poncutal',
			]
		);
		return $options;
	}

	// Remove custom color picker
	public function removeCustomColors(array $plugins)
	{
		foreach ($plugins as $key => $pluginName) :
			if ('colorpicker' === $pluginName) :
				unset($plugins[ $key ]);
				return $plugins;
			endif;
		endforeach;
	
		return $plugins;
	}
}

new Tinymce;
