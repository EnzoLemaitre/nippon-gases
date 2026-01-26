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


<?php

// 1. Configuration des identifiants (PROD)
define('MULE_TOKEN_URL', 'https://login.microsoftonline.com/152d35f3-e46a-4e14-b95c-f462dab4ce70/oauth2/v2.0/token');
define('MULE_API_URL', 'https://mule-worker-internal-ng-e-documents.de-c1.cloudhub.io:8082/api/v1/cms/public-doc-list');
define('MULE_CLIENT_ID', '2cdae861-bdc1-4138-abc1-437045fa1cb0'); // ID de Prod [cite: 66]
// Le secret doit être défini dans le fichier wp-config.php pour la sécurité
if (!defined('MULE_CLIENT_SECRET')) {
    define('MULE_CLIENT_SECRET', ''); 
}
define('MULE_SCOPE', 'api://2cdae861-bdc1-4138-abc1-437045fa1cb0/.default');

/**
 * Récupère le Token OAuth 2.0 et le met en cache
 */
function get_mulesoft_token() {
    // On vérifie si on a déjà un token en cache (valide 1h)
    $cached_token = get_transient('mulesoft_access_token');
    if ($cached_token) {
        return $cached_token;
    }

    $response = wp_remote_post(MULE_TOKEN_URL, [
        'body' => [
            'grant_type'    => 'client_credentials',
            'client_id'     => MULE_CLIENT_ID,
            'client_secret' => MULE_CLIENT_SECRET,
            'scope'         => MULE_SCOPE
        ],
        'sslverify' => false // À mettre à true si le certificat est valide sur le serveur WP
    ]);

    if (is_wp_error($response)) {
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['access_token'])) {
        // On sauvegarde le token pour 50 minutes (3000 secondes) pour éviter de spammer Microsoft
        set_transient('mulesoft_access_token', $body['access_token'], 3000);
        return $body['access_token'];
    }

    return false;
}

/**
 * Fonction pour récupérer les documents selon la langue
 */
function get_documents_by_current_language($docType = 'PDM - Policies') {
    $token = get_mulesoft_token();
    if (!$token) return "Erreur d'authentification.";

    // Détection de la langue actuelle de WordPress
    $locale = get_locale(); // Ex: 'fr_FR'
    
    // Mapping Langue -> Code Pays API 
    switch (substr($locale, 0, 2)) {
        case 'fr': $countryCode = 'FRA'; break;
        case 'es': $countryCode = 'ESP'; break;
        case 'de': $countryCode = 'DEU'; break;
        case 'pt': $countryCode = 'POR'; break;
        case 'it': $countryCode = 'ITA'; break;
        case 'nl': $countryCode = 'NLD'; break;
        default:   $countryCode = 'GBR'; // Fallback anglais (United Kingdom)
    }

    // Construction de la requête 
    $payload = [
        "DocumentType" => $docType,
        "Filters" => [
            [
                "Operator" => "=",
                "Relation" => "AND",
                "Key"      => "NG - Is Public",
                "Value"    => "YES"
            ],
            [
                "Operator" => "=",
                "Relation" => "AND",
                "Key"      => "NG - Country Code",
                "Value"    => $countryCode
            ]
        ],
        "Sorting" => [
            "KeywordName" => "NG - Original File Name",
            "OrderType"   => "NG"
        ]
    ];

    $response = wp_remote_post(MULE_API_URL, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json'
        ],
        'body'      => json_encode($payload),
        'sslverify' => false, // Important pour l'URL interne si certificat auto-signé
        'timeout'   => 15 // Laisser un peu de temps au serveur interne
    ]);

    if (is_wp_error($response)) {
        return "Erreur de connexion API: " . $response->get_error_message();
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        return "Erreur API ($code)";
    }

    return json_decode(wp_remote_retrieve_body($response), true);
}

/**
 * Création du Shortcode [liste_documents]
 */
function render_doc_list_shortcode($atts) {
    // Attributs par défaut
    $atts = shortcode_atts(['type' => 'PDM - Policies'], $atts);
    
    $data = get_documents_by_current_language($atts['type']);

    if (is_string($data)) {
        return '<div class="error">' . $data . '</div>';
    }

    if (empty($data) || !is_array($data)) {
        return '<p>Aucun document trouvé pour cette langue.</p>';
    }

    // Affichage HTML simple
    $output = '<ul class="onbase-doc-list">';
    foreach ($data as $doc) {
        // On récupère le nom et l'ID (adapter selon la structure réelle du JSON que tu as reçu tout à l'heure)
        // Supposition basée sur les logs précédents: il y a surement un champ "Name" ou "FileName" et "OnBaseID"
        $name = isset($doc['NG - Original File Name']) ? $doc['NG - Original File Name'] : 'Document sans nom';
        $id = isset($doc['OnBaseID']) ? $doc['OnBaseID'] : '#';
        
        // Lien de téléchargement (On pointera vers une route qui gère le download, ou l'URL directe si disponible)
        $output .= '<li>';
        $output .= '<strong>' . esc_html($name) . '</strong> (ID: ' . esc_html($id) . ')';
        $output .= '</li>';
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode('liste_documents', 'render_doc_list_shortcode');

