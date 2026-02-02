<?php
/**
 * Template Name: Country Selector
 * Description: Sélecteur de pays/langue pour multisite WordPress
 */

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

// Détection de l'environnement (preprod vs prod)
$current_domain = $_SERVER['HTTP_HOST'];
$is_preprod = (strpos($current_domain, 'wptest') !== false);
// Utilise le domaine actuel pour construire les URLs (supporte wp.nippongases.com et nippongases.com)
$base_url = 'https://' . $current_domain;

// Configuration des pays avec leurs sites et langues
// Format: 'country_code' => ['url' => 'site_url', 'languages' => ['lang1', 'lang2']]
$countries_config = [
    'ES' => [
        'name' => 'España',
        'url' => $base_url . '/es/',
        'languages' => ['es' => 'Español', 'en' => 'English']
    ],
    'IT' => [
        'name' => 'Italia',
        'url' => $base_url . '/it/',
        'languages' => ['it' => 'Italiano', 'en' => 'English']
    ],
    'FR' => [
        'name' => 'France',
        'url' => $base_url . '/fr/',
        'languages' => ['fr' => 'Français', 'en' => 'English']
    ],
    'DE' => [
        'name' => 'Deutschland',
        'url' => $base_url . '/de/',
        'languages' => ['de' => 'Deutsch', 'en' => 'English']
    ],
    'PT' => [
        'name' => 'Portugal',
        'url' => $base_url . '/pt/',
        'languages' => ['pt' => 'Português', 'en' => 'English']
    ],
    'GB' => [
        'name' => 'United Kingdom',
        'url' => $base_url . '/gb/',
        'languages' => ['en' => 'English']
    ],
    'BE' => [
        'name' => 'België / Belgique',
        'url' => $base_url . '/be/',
        'languages' => ['fr' => 'Français', 'nl' => 'Nederlands', 'en' => 'English']
    ],
    'NL' => [
        'name' => 'Nederland',
        'url' => $base_url . '/nl/',
        'languages' => ['nl' => 'Nederlands', 'en' => 'English']
    ],
    'DK' => [
        'name' => 'Danmark',
        'url' => $base_url . '/dk/',
        'languages' => ['da' => 'Dansk']
    ],
    'SE' => [
        'name' => 'Sverige',
        'url' => $base_url . '/se/',
        'languages' => ['sv' => 'Svenska']
    ],
    'NO' => [
        'name' => 'Norge',
        'url' => $base_url . '/no/',
        'languages' => ['no' => 'Norsk']
    ]
];

// Préparer les données pour le template
$countries = [];
foreach ($countries_config as $code => $config) {
    $countries[] = [
        'code' => $code,
        'name' => $config['name'],
        'languages' => $config['languages']
    ];
}

// Préparer le mapping pays -> langues pour JavaScript
$country_lang_map = [];
foreach ($countries_config as $code => $config) {
    $country_lang_map[$code] = array_keys($config['languages']);
}

// Préparer les URLs de redirection par langue
// Format: "es" => "https://nippongases.com/es/"
$language_urls = [];
foreach ($countries_config as $country_code => $config) {
    foreach ($config['languages'] as $lang_code => $lang_name) {
        // Pour les pays avec une seule langue, rediriger directement
        if (count($config['languages']) === 1) {
            $language_urls[$lang_code] = $config['url'];
        } else {
            // Pour les pays multilingues, construire l'URL avec la langue
            // Par exemple: /es/ pour espagnol, /es/en/ pour anglais en Espagne
            if ($lang_code === array_key_first($config['languages'])) {
                // Langue principale = URL de base du pays
                $language_urls[$country_code . '_' . $lang_code] = $config['url'];
            } else {
                // Autres langues = URL + code langue
                $language_urls[$country_code . '_' . $lang_code] = $config['url'] . $lang_code . '/';
            }
        }
    }
}

$context['countries'] = $countries;
$context['country_lang_map'] = json_encode($country_lang_map);
$context['language_urls'] = json_encode($language_urls);
$context['countries_config'] = json_encode($countries_config);

// Champs ACF
$context['logo'] = get_field('logo');
$context['title'] = get_field('title') ?: 'Choose an another country and language';
$context['subtitle'] = get_field('subtitle') ?: 'Our solutions are country-specific and will differ based on selected location';

// Traductions
$context['i18n'] = [
    'choose_country' => 'Choose a Country',
    'choose_language' => 'Choose a Language',
    'select_button' => 'Select',
    'error_message' => 'Please select a Country before a language',
    'select_country_first' => 'Select a country first'
];

Timber::render('pages/country-selector.twig', $context);
