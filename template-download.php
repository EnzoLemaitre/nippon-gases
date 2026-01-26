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

$downloads_list = [];

// --- 2. RECUPERATION TOKEN & DONNÉES ---
if (!empty($api_secret)) {
    $access_token = get_transient('mule_access_token');

    if (false === $access_token) {
        $response = wp_remote_post($token_url, [
            'body' => [
                'grant_type' => 'client_credentials', 'client_id' => $client_id, 'client_secret' => $api_secret, 'scope' => 'api://' . $client_id . '/.default'
            ],
            'sslverify' => false
        ]);
        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['access_token'])) set_transient('mule_access_token', $body['access_token'], 3500);
            $access_token = $body['access_token'] ?? false;
        }
    }

    if ($access_token) {
        // Détection langue
        $locale = get_locale();
        switch (substr($locale, 0, 2)) {
            case 'fr': $cc = 'FRA'; break;
            case 'es': $cc = 'ESP'; break;
            case 'de': $cc = 'DEU'; break;
            case 'pt': $cc = 'POR'; break;
            default:   $cc = 'GBR';
        }

        // Appel API
        $payload = [
            "DocumentType" => "PDM - Policies",
            "Filters" => [
                [ "Operator" => "=", "Relation" => "AND", "Key" => "NG - Is Public", "Value" => "YES" ],
                [ "Operator" => "=", "Relation" => "AND", "Key" => "NG - Country Code", "Value" => $cc ]
            ],
            "Sorting" => [ "KeywordName" => "NG - Original File Name", "OrderType" => "NG" ]
        ];

        $api_response = wp_remote_post($list_url, [
            'headers' => ['Authorization' => 'Bearer ' . $access_token, 'Content-Type' => 'application/json'],
            'body' => json_encode($payload), 'sslverify' => false, 'timeout' => 15
        ]);

        if (!is_wp_error($api_response) && wp_remote_retrieve_response_code($api_response) == 200) {
            $raw_data = json_decode(wp_remote_retrieve_body($api_response), true);
            
            if (is_array($raw_data)) {
                foreach ($raw_data as $doc) {
                    // MAPPING pour correspondre à ton TWIG (id, title, date)
                    $date_raw = isset($doc['DocumentDate']) ? $doc['DocumentDate'] : '';
                    // On essaie de formater la date proprement (simplement)
                    $date_clean = $date_raw ? date('d/m/Y', strtotime($date_raw)) : '';

                    $downloads_list[] = [
                        'id'    => isset($doc['OnBaseID']) ? $doc['OnBaseID'] : uniqid(),
                        'title' => isset($doc['NG - Original File Name']) ? $doc['NG - Original File Name'] : 'Document sans titre',
                        'date'  => $date_clean,
                        // Ajout de champs techniques si besoin
                        'url'   => '#' 
                    ];
                }
            }
        }
    }
}

// --- 3. ENVOI A TWIG (CORRECTION DU NOM) ---
$context['downloads'] = $downloads_list;

// --- 4. DEBUG VISUEL (IMPORTANT : A LAISSER POUR LE TEST) ---
// Si la liste est vide, on force un message visible
if (empty($downloads_list)) {
    echo '<div style="background:red; color:white; padding:20px; text-align:center; font-weight:bold;">';
    echo 'DEBUG: API connectée mais liste vide ou erreur.<br>';
    echo 'Secret present ? ' . (!empty($api_secret) ? 'OUI' : 'NON') . '<br>';
    echo 'Token present ? ' . (!empty($access_token) ? 'OUI' : 'NON');
    echo '</div>';
}

Timber::render('pages/download.twig', $context);