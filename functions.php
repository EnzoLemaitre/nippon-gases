<?php

use App\Autoloader;
use App\Config;
use Dotenv\Dotenv;
use Timber\Timber;

// Load composer
require 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Autoloader
require 'class/Autoloader.php';

Autoloader::autoload();

// Use Timber
Timber::init();

// Config of theme
define('TRANSLATE_DOMAIN', 'ng');
define('THEME_OPTIONS', ['modeTheme' => false, 'backToTop' => false]);
$Config = new Config;

$Config->cssFiles = [
    '/assets/css/main.css',
];

// ACF language not the same for different languages polylang
add_filter('acf/settings/current_language', function ($lang) {
    if (function_exists('pll_current_language')) {
        return pll_current_language();
    }

    return $lang;
});



// Add a field icon for the distributors-cat taxonomy
add_action('acf/init', 'add_distributor_category_icon_field');
function add_distributor_category_icon_field() {
    
    if( function_exists('acf_add_local_field_group') ):
    
    acf_add_local_field_group(array(
        'key' => 'group_distributor_cat_icon',
        'title' => __('Category Icon', TRANSLATE_DOMAIN),
        'fields' => array(
            array(
                'key' => 'field_distributor_cat_icon',
                'label' => __('Icon', TRANSLATE_DOMAIN),
                'name' => 'category_icon',
                'type' => 'image',
                'instructions' => __('Upload an icon for this category', TRANSLATE_DOMAIN),
                'required' => 0,
                'return_format' => 'array',
                'preview_size' => 'thumbnail',
                'library' => 'all',
                'mime_types' => 'png',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'taxonomy',
                    'operator' => '==',
                    'value' => 'distributors-cat',
                ),
            ),
        ),
    ));
    
    endif;
}