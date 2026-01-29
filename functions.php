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

// Chaînes Polylang pour la page Download
add_action('init', function() {
    if (function_exists('pll_register_string')) {
        $group = 'Download Page';
        
        // Titres et labels
        pll_register_string('download_page_title', 'What do you want to download?', $group);
        pll_register_string('download_choose_type', 'Choose a document type', $group);
        pll_register_string('download_choose_language', 'Choose a language', $group);
        pll_register_string('download_choose_category', 'Choose a category', $group);
        pll_register_string('download_all_languages', 'All languages', $group);
        pll_register_string('download_all_categories', 'All categories', $group);
        pll_register_string('download_search_button', 'Search', $group);
        pll_register_string('download_search_placeholder', 'Search a document, a solution category...', $group);
        pll_register_string('download_all_results', 'All results', $group);
        
        // Messages
        pll_register_string('download_select_type_msg', 'Please select a document type to view available documents.', $group);
        pll_register_string('download_no_results', 'No documents found matching your criteria.', $group);
        
        // Types de documents
        pll_register_string('download_type_policies', 'Policies', $group);
        pll_register_string('download_type_certifications', 'Certifications', $group);
        pll_register_string('download_type_general_terms', 'General Terms', $group);
        pll_register_string('download_type_instructions', 'Instructions For Use', $group);
        pll_register_string('download_type_privacy', 'Privacy Indications', $group);
        pll_register_string('download_type_risk', 'Risk Evaluation Sheets', $group);
        pll_register_string('download_type_safety', 'Safety Data Sheets', $group);
        
        // Catégories (corrigées)
        pll_register_string('download_cat_quality', 'Quality', $group);
        pll_register_string('download_cat_environment', 'Environment', $group);
        pll_register_string('download_cat_safety', 'Safety', $group);
        pll_register_string('download_cat_finance', 'Finance', $group);
        pll_register_string('download_cat_legal', 'Legal', $group);
        pll_register_string('download_cat_hr', 'Human Resources', $group);
        pll_register_string('download_cat_food_safety', 'Food Safety', $group);
        pll_register_string('download_cat_info_security', 'Information Security', $group);
    }
});

// Chaînes Polylang pour la page Search
add_action('init', function() {
    if (function_exists('pll_register_string')) {
        $group = 'Search Page';
        
        pll_register_string('search_page_title', 'Search Results', $group);
        pll_register_string('search_placeholder', 'Search...', $group);
        pll_register_string('search_button', 'Search', $group);
        pll_register_string('search_results_for', 'Results for', $group);
        pll_register_string('search_no_results', 'No results found. Try different keywords.', $group);
        pll_register_string('search_view_page', 'View', $group);
        pll_register_string('search_found_results', 'results found', $group);
    }
});

add_action('init', function() {
    if (function_exists('pll_register_string')) {
        pll_register_string('Téléchargement', 'Rechercher un document...', 'Nippon Gases', false);
        pll_register_string('Téléchargement', 'Type de document', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Compagnie', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Catégorie', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Langue', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Tous les résultats', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Réinitialiser les filtres', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Aucun document trouvé.', 'Nippon Gases');
    }
});

add_action('init', function() {
    if (function_exists('pll_register_string')) {
        pll_register_string('Téléchargement', 'Rechercher un document...', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Type de document', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Compagnie', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Catégorie', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Langue', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Tous les résultats', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Réinitialiser les filtres', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Aucun document trouvé.', 'Nippon Gases');
        pll_register_string('Téléchargement', '-- Toutes --', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Politiques', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Certifications', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Fiches de Données de Sécurité', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Instructions d\'utilisation', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Qualité', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Finance', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Juridique', 'Nippon Gases');
        pll_register_string('Téléchargement', 'Ressources Humaines', 'Nippon Gases');
    }
});

add_action('init', function() { 
    if (function_exists('pll_register_string')) { 
        $group = 'Page Accueil';
        pll_register_string('home_solutions', 'Our solutions', $group);
        pll_register_string('home_gases', 'Our gases', $group);
        pll_register_string('home_news', 'Latest Company News', $group);
        pll_register_string('home_see_all', 'See all', $group);
    }
});

function enqueue_download_styles() {
    if (is_page('download') || is_page('telechargements')) {
        wp_enqueue_style(
            'download-page-style',
            get_template_directory_uri() . '/assets/css/download.css',
            array(),
            filemtime(get_template_directory() . '/assets/css/download.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_download_styles');

/**
 * AJAX Handler pour télécharger les documents OnBase
 */
add_action('wp_ajax_download_onbase_document', 'handle_onbase_document_download');
add_action('wp_ajax_nopriv_download_onbase_document', 'handle_onbase_document_download');

function handle_onbase_document_download() {
    error_log("=== DOWNLOAD HANDLER CALLED ===");
    error_log("GET params: " . print_r($_GET, true));

    if (!isset($_GET['doc_id'])) {
        error_log("ERROR: doc_id missing");
        wp_die('Document ID missing');
    }

    $document_id = sanitize_text_field($_GET['doc_id']);
    error_log("Document ID: " . $document_id);

    $client_id = $_ENV['ONBASE_CLIENT_ID'] ?? '';
    $client_secret = $_ENV['ONBASE_CLIENT_SECRET'] ?? '';
    $scope = $_ENV['ONBASE_SCOPE'] ?? '';

    if (empty($client_id) || empty($client_secret)) {
        error_log("ERROR: API configuration missing");
        wp_die('API configuration error');
    }

    // 1. Obtenir le token OAuth
    $token_url = "https://login.microsoftonline.com/152d35f3-e46a-4e14-b95c-f462dab4ce70/oauth2/v2.0/token";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        "grant_type" => "client_credentials",
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "scope" => $scope,
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $token_response = curl_exec($ch);
    $token_json = json_decode($token_response, true);
    curl_close($ch);

    if (!isset($token_json["access_token"])) {
        error_log("Erreur token pour téléchargement: " . $token_response);
        wp_die('Authentication error');
    }

    $access_token = $token_json["access_token"];

    // 2. Télécharger le document depuis l'API OnBase
    $download_url = "https://mule-worker-internal-ng-e-documents.de-c1.cloudhub.io:8082/api/v1/cms/download-doc";
    
    $payload = json_encode(["OnBaseID" => intval($document_id)]);
    error_log("Payload download: " . $payload);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $download_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$access_token}",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $file_content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    error_log("HTTP Code: " . $http_code);
    error_log("Content-Type: " . $content_type);
    error_log("Content length: " . strlen($file_content));
    error_log("First 500 chars: " . substr($file_content, 0, 500));

    // Décoder le JSON et extraire le document base64
    $response_json = json_decode($file_content, true);
    if (isset($response_json['Document'])) {
        $file_content = base64_decode($response_json['Document']);
        error_log("PDF décodé, taille: " . strlen($file_content));
    }

    if ($http_code !== 200 || empty($file_content)) {
        error_log("Erreur téléchargement document ID {$document_id}: HTTP {$http_code}");
        error_log("Réponse API: " . substr($file_content, 0, 500));
        wp_die('Document download error');
    }

    // 3. Envoyer le fichier au navigateur
    $filename = "document_{$document_id}.pdf";

    // Vider complètement tous les buffers de sortie
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($file_content));
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    echo $file_content;
    exit;
}

// Allow search parameter on custom search page
add_filter('query_vars', function($vars) {
    $vars[] = 's';
    return $vars;
});
