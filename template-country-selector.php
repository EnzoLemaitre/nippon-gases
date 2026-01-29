<?php
/**
 * Template Name: Country Selector
 * Description: Page de sélection pays/langue avec détection automatique
 */

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

// Configuration des pays et leurs langues disponibles
// Format: 'country_code' => ['lang1', 'lang2', ...]
$countries = [
    'ES' => ['es', 'en'], // España
    'IT' => ['it', 'en'], // Italia
    'FR' => ['fr', 'en'], // France
    'DE' => ['de', 'en'], // Deutschland
    'PT' => ['pt', 'en'], // Portugal
    'GB' => ['en'],       // United Kingdom
    'BE' => ['fr', 'nl', 'en'], // België/Belgique
    'NL' => ['nl', 'en'], // Nederland
    'DK' => ['da'],       // Danmark
    'SE' => ['sv'],       // Sverige
    'NO' => ['no']        // Norge
];

// Récupérer les langues actives dans Polylang
$active_languages = [];
if (function_exists('pll_languages_list')) {
    $langs = pll_languages_list(['fields' => 'slug']);
    foreach ($langs as $lang) {
        $active_languages[$lang] = [
            'code' => $lang,
            'name' => strtoupper($lang)
        ];
    }
}

// Filtrer les pays selon les langues disponibles
$available_countries = [];
foreach ($countries as $country_code => $langs) {
    // Vérifier si au moins une langue du pays est active
    $has_active_lang = false;
    foreach ($langs as $lang) {
        if (isset($active_languages[$lang])) {
            $has_active_lang = true;
            break;
        }
    }
    
    if ($has_active_lang) {
        $available_countries[$country_code] = [
            'code' => $country_code,
            'name' => get_country_name($country_code),
            'languages' => array_filter($langs, function($lang) use ($active_languages) {
                return isset($active_languages[$lang]);
            })
        ];
    }
}

// Fonction helper pour les noms de pays
function get_country_name($code) {
    $names = [
        'ES' => 'España',
        'IT' => 'Italia',
        'FR' => 'France',
        'DE' => 'Deutschland',
        'PT' => 'Portugal',
        'GB' => 'United Kingdom',
        'BE' => 'België / Belgique',
        'NL' => 'Nederland',
        'DK' => 'Danmark',
        'SE' => 'Sverige',
        'NO' => 'Norge'
    ];
    return isset($names[$code]) ? $names[$code] : $code;
}

// Préparer les données pour JavaScript
$country_lang_map = [];
foreach ($available_countries as $country) {
    $country_lang_map[$country['code']] = $country['languages'];
}

// URLs des langues pour la redirection
$language_urls = [];
if (function_exists('pll_home_url')) {
    foreach ($active_languages as $lang_code => $lang_data) {
        $language_urls[$lang_code] = pll_home_url($lang_code);
    }
}

$context['countries'] = $available_countries;
$context['country_lang_map'] = json_encode($country_lang_map);
$context['language_urls'] = json_encode($language_urls);
$context['active_languages'] = $active_languages;

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
