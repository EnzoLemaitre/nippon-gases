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

/* ============================================================
 * MULESOFT INTEGRATION - NIPPON GASES DOCUMENTS
 * Added on: 2026-01-22
 * Description: Fetches documents via Mulesoft API with caching
 * ============================================================
 */

/**
 * Fetch documents from Mulesoft API based on country code.
 * Uses WordPress Transients API for caching to improve performance.
 *
 * @param string $country_code The country code (e.g., 'ES', 'GB').
 * @return array The list of documents or an empty array on failure.
 */
function get_nippon_documents_from_mulesoft($country_code) {
    // 1. Check Cache (Cache duration: 1 hour)
    $cache_key = 'docs_mulesoft_' . $country_code;
    $cached_docs = get_transient($cache_key);

    if (false !== $cached_docs) {
        return $cached_docs;
    }

    // 2. API Configuration
    // TODO: Define these constants in wp-config.php for security
    $api_url = 'https://api.nippongases.com/v1/documents'; // Verify endpoint with Mulesoft team
    
    // Check if credentials are defined in wp-config.php, otherwise use safe placeholders
    $client_id     = defined('MULESOFT_CLIENT_ID') ? MULESOFT_CLIENT_ID : 'KEY_NOT_DEFINED';
    $client_secret = defined('MULESOFT_CLIENT_SECRET') ? MULESOFT_CLIENT_SECRET : 'SECRET_NOT_DEFINED';

    $args = [
        'timeout' => 15, // Timeout in seconds
        'headers' => [
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'Accept'        => 'application/json',
        ],
        'body' => [
            'country' => $country_code
        ]
        // Note: If the API requires GET parameters instead of Body, append to URL: ?country=...
    ];

    // 3. Execute Request
    $response = wp_remote_get($api_url, $args);

    // 4. Error Handling
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        // Optional: Log error for debugging
        // error_log('Mulesoft API Error: ' . print_r($response, true));
        return [];
    }

    // 5. Parse Data
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // 6. Save to Cache if data exists (3600 seconds = 1 hour)
    if (!empty($data)) {
        set_transient($cache_key, $data, 3600);
    }

    return $data;
}

/** Shortcode to display the document list.*/
function display_mulesoft_docs_shortcode($atts) {
    // Detect current language (Polylang support)
    $current_lang = function_exists('pll_current_language') ? pll_current_language() : 'es';
    
    // Map Language codes to Country codes expected by Mulesoft
    $country_map = [
        'es' => 'ES', 
        'en' => 'GB', 
        'fr' => 'FR', 
        'pt' => 'PT',
        'de' => 'DE'
    ];
    
    // Default to 'ES' if language is not mapped
    $target_country = isset($country_map[$current_lang]) ? $country_map[$current_lang] : 'ES';

    // Fetch Data
    $documents = get_nippon_documents_from_mulesoft($target_country);

    // No documents found scenario
    if (empty($documents)) {
        return '<div class="nippon-alert">No documents available at the moment.</div>';
    }

    // Build HTML Output
    $output = '<div class="nippon-downloads-grid">';
    
    foreach ($documents as $doc) {
        // Safety checks on array keys (adjust 'title' and 'url' based on real JSON response)
        $title = isset($doc['title']) ? $doc['title'] : 'Untitled Document';
        $url   = isset($doc['url']) ? $doc['url'] : '#';
        $date  = isset($doc['date']) ? $doc['date'] : ''; // Optional date

        $output .= '<div class="doc-item">';
        $output .= '<h4>' . esc_html($title) . '</h4>';
        if ($date) {
            $output .= '<span class="doc-date">' . esc_html($date) . '</span>';
        }
        $output .= '<a href="' . esc_url($url) . '" class="btn btn-download" target="_blank">Download</a>';
        $output .= '</div>';
    }
    
    $output .= '</div>';

    return $output;
}
add_shortcode('nippon_docs', 'display_mulesoft_docs_shortcode');