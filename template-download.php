<?php
// Template Name: Download

use Timber\Timber;

// --- CONFIGURATION ---
$api_secret = defined('MULE_CLIENT_SECRET') ? MULE_CLIENT_SECRET : '';
$client_id  = '2cdae861-bdc1-4138-abc1-437045fa1cb0';
$token_url  = 'https://login.microsoftonline.com/152d35f3-e46a-4e14-b95c-f462dab4ce70/oauth2/v2.0/token';
$list_url   = 'https://mule-worker-internal-ng-e-documents.de-c1.cloudhub.io:8082/api/v1/cms/public-doc-list';
$download_url = 'https://mule-worker-internal-ng-e-documents.de-c1.cloudhub.io:8082/api/v1/cms/download-doc';

// --- FONCTION AUTH ---
function get_mule_token_secure($token_url, $client_id, $api_secret) {
    if (empty($api_secret)) return false;
    $token = get_transient('mule_access_token');
    if ($token) return $token;

    $response = wp_remote_post($token_url, [
        'body' => ['grant_type' => 'client_credentials', 'client_id' => $client_id, 'client_secret' => $api_secret, 'scope' => 'api://' . $client_id . '/.default'], 
        'sslverify' => false
    ]);

    if (!is_wp_error($response)) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($body['access_token'])) {
            set_transient('mule_access_token', $body['access_token'], 3500);
            return $body['access_token'];
        }
    }
    return false;
}

// =========================================================
// PARTIE 1 : TÉLÉCHARGEMENT
// =========================================================
if (isset($_GET['doc_id']) && !empty($_GET['doc_id'])) {
    $doc_id = intval($_GET['doc_id']); 
    $token = get_mule_token_secure($token_url, $client_id, $api_secret);

    if ($token) {
        $args = [
            'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json'],
            'body' => json_encode([ "OnBaseID" => $doc_id ]),
            'sslverify' => false,
            'timeout' => 45
        ];

        $response = wp_remote_post($download_url, $args);

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
            $json_body = json_decode(wp_remote_retrieve_body($response), true);

            if (isset($json_body['Document']) && !empty($json_body['Document'])) {
                $pdf_binary = base64_decode($json_body['Document']);
                if (ob_get_level()) { ob_end_clean(); }

                header('Content-Description: File Transfer');
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="document-' . $doc_id . '.pdf"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . strlen($pdf_binary));

                echo $pdf_binary;
                exit; 
            } else {
                wp_die("Erreur API: Le document est vide.");
            }
        } else {
            wp_die('Erreur HTTP lors du téléchargement.');
        }
    }
}

// =========================================================
// PARTIE 2 : LISTE AVEC FILTRES ET RECHERCHE
// =========================================================
$context = Timber::context();
$context['post'] = Timber::get_post();
$downloads_list = [];
$token = get_mule_token_secure($token_url, $client_id, $api_secret);

// Récupération des filtres
$filter_doc_type = isset($_GET['doc_type']) ? sanitize_text_field($_GET['doc_type']) : 'PDM - Policies';
$filter_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
$filter_company  = isset($_GET['company'])  ? sanitize_text_field($_GET['company']) : '';
$filter_lang     = isset($_GET['lang_iso']) ? sanitize_text_field($_GET['lang_iso']) : '';
$filter_search   = isset($_GET['search_text']) ? sanitize_text_field($_GET['search_text']) : '';

if ($token) {
    $locale = get_locale();
    switch (substr($locale, 0, 2)) {
        case 'fr': $cc = 'FRA'; break;
        case 'es': $cc = 'ESP'; break;
        case 'de': $cc = 'DEU'; break;
        case 'pt': $cc = 'POR'; break;
        case 'it': $cc = 'ITA'; break;
        case 'nl': $cc = 'NLD'; break;
        case 'da': $cc = 'DNK'; break;
        case 'sv': $cc = 'SWE'; break;
        case 'no': $cc = 'NOR'; break;
        default:   $cc = 'GBR';
    }

    $api_filters = [
        [ "Operator" => "=", "Relation" => "AND", "Key" => "NG - Is Public", "Value" => "YES" ],
        [ "Operator" => "=", "Relation" => "AND", "Key" => "NG - Country Code", "Value" => $cc ]
    ];

    if (!empty($filter_category)) {
        $api_filters[] = [ "Operator" => "=", "Relation" => "AND", "Key" => "NG - Category Name", "Value" => $filter_category ];
    }
    if (!empty($filter_company)) {
        $api_filters[] = [ "Operator" => "=", "Relation" => "AND", "Key" => "NG - Company Name", "Value" => $filter_company ];
    }
    if (!empty($filter_lang)) {
        $api_filters[] = [ "Operator" => "=", "Relation" => "AND", "Key" => "NG - Language ISO Code", "Value" => $filter_lang ];
    }

    $payload = [
        "DocumentType" => $filter_doc_type, 
        "Filters" => $api_filters,
        "Sorting" => [ "KeywordName" => "NG - Original File Name", "OrderType" => "NG" ]
    ];

    $api_response = wp_remote_post($list_url, [
        'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json'],
        'body' => json_encode($payload), 'sslverify' => false, 'timeout' => 15
    ]);

    if (!is_wp_error($api_response) && wp_remote_retrieve_response_code($api_response) == 200) {
        $raw_data = json_decode(wp_remote_retrieve_body($api_response), true);
        if (is_array($raw_data)) {
            foreach ($raw_data as $doc) {
                $title = isset($doc['Name']) ? $doc['Name'] : 'Document';
                if (isset($doc['keywords']) && is_array($doc['keywords'])) {
                    foreach ($doc['keywords'] as $kw) {
                        if (isset($kw['Key']) && $kw['Key'] === 'NG - Original File Name') {
                            $title = $kw['Value']; break;
                        }
                    }
                }

                // FILTRAGE RECHERCHE (PHP)
                if (!empty($filter_search) && stripos($title, $filter_search) === false) {
                    continue; 
                }
                
                $doc_id = isset($doc['dochandle']) ? $doc['dochandle'] : '';
                if ($doc_id) {
                    $download_link = add_query_arg('doc_id', $doc_id, get_permalink());
                    $downloads_list[] = [
                        'id'    => $doc_id, 
                        'title' => $title, 
                        'date'  => '', 
                        'url'   => $download_link 
                    ];
                }
            }
        }
    }
}

$context['downloads'] = $downloads_list;
$context['filters'] = [
    'doc_type' => $filter_doc_type,
    'category' => $filter_category,
    'company'  => $filter_company,
    'lang'     => $filter_lang,
    'search'   => $filter_search
];

if (empty($context['post']->meta('download'))) { $context['post']->custom['download'] = ['title' => '']; }
Timber::render('pages/download.twig', $context);
?>