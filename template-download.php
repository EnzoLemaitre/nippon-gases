<?php
// Template Name: Download

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

// --- 1. CONFIGURATION API ---
$api_secret = defined('MULE_CLIENT_SECRET') ? MULE_CLIENT_SECRET : '';
$client_id  = '2cdae861-bdc1-4138-abc1-437045fa1cb0';
$token_url  = 'https://login.microsoftonline.com/152d35f3-e46a-4e14-b95c-f462dab4ce70/oauth2/v2.0/token';
$list_url   = 'https://mule-worker-internal-ng-e-documents.de-c1.cloudhub.io:8082/api/v1/cms/public-doc-list';

$documents_list = []; // Liste vide par défaut

if (empty($api_secret)) {
    // Si pas de secret, on ne fait rien (ou on log une erreur)
    $context['api_error'] = "Configuration serveur manquante (Secret).";
} else {

    // --- 2. OBTENIR LE TOKEN (CACHE 1H) ---
    $access_token = get_transient('mule_access_token');

    if (false === $access_token) {
        $response = wp_remote_post($token_url, [
            'body' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $client_id,
                'client_secret' => $api_secret,
                'scope'         => 'api://' . $client_id . '/.default'
            ],
            'sslverify' => false,
            'timeout'   => 15
        ]);

        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['access_token'])) {
                $access_token = $body['access_token'];
                set_transient('mule_access_token', $access_token, 3500);
            }
        }
    }

    if ($access_token) {
        // --- 3. DÉTECTION DE LA LANGUE ---
        $locale = get_locale(); // Ex: fr_FR
        switch (substr($locale, 0, 2)) {
            case 'fr': $country_code = 'FRA'; break;
            case 'es': $country_code = 'ESP'; break;
            case 'de': $country_code = 'DEU'; break;
            case 'pt': $country_code = 'POR'; break;
            case 'it': $country_code = 'ITA'; break;
            default:   $country_code = 'GBR'; // Fallback
        }

        // --- 4. APPEL API (LISTE) ---
        // On demande "Policies" par