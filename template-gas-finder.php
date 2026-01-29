<?php
/**
 * Template Name: Gas Finder
 * Description: Trouvez le bon gaz pour vos besoins spécifiques
 */

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

// Charger les données JSON
$json_file = get_template_directory() . '/data/gas-finder-data.json';
$gas_data = [];

if (file_exists($json_file)) {
    $raw_data = json_decode(file_get_contents($json_file), true);
    
    // Normaliser les noms de techniques pour éviter les doublons
    foreach ($raw_data as $entry) {
        // Normaliser "TIG" en "TIG (GTAW)"
        if ($entry['technique'] === 'TIG') {
            $entry['technique'] = 'TIG (GTAW)';
        }
        $gas_data[] = $entry;
    }
}

// Extraire les options uniques pour chaque étape
$techniques = [];
$materials = [];
$thicknesses = [];

foreach ($gas_data as $entry) {
    // Techniques
    if (!in_array($entry['technique'], $techniques)) {
        $techniques[] = $entry['technique'];
    }
    
    // Materials (tous, on filtrera côté JS)
    if (!in_array($entry['material'], $materials)) {
        $materials[] = $entry['material'];
    }
    
    // Thickness (tous, on filtrera côté JS)
    if (!in_array($entry['thickness'], $thicknesses)) {
        $thicknesses[] = $entry['thickness'];
    }
}

// Mapper les noms techniques vers des slugs propres
$technique_map = [
    'MIG /MAG (GMAW)' => 'mig-mag',
    'TIG (GTAW)' => 'tig',
    'Plasma welding' => 'plasma-welding',
    'Laser welding' => 'laser-welding',
    'Backing' => 'backing'
];

$techniques_formatted = [];
foreach ($techniques as $tech) {
    $slug = $technique_map[$tech] ?? sanitize_title($tech);
    $techniques_formatted[] = [
        'name' => $tech,
        'slug' => $slug,
        'display' => str_replace(['(GMAW)', '(GTAW)'], '', $tech)
    ];
}

$context['techniques'] = $techniques_formatted;
$context['gas_data'] = json_encode($gas_data);

// Champs ACF
$context['title'] = get_field('title') ?: 'Find the right gas for your specific needs';
$context['subtitle'] = get_field('subtitle') ?: '';

// Traductions
$context['i18n'] = [
    'technique' => 'Technique',
    'material' => 'Material',
    'thickness' => 'Thickness',
    'your_gas' => 'Your gas',
    'back_to_technique' => 'Back to technique',
    'select' => 'Select',
    'recommended_gases' => 'Recommended gases',
    'description' => 'Description',
    'no_results' => 'No gas found for this combination'
];

Timber::render('pages/gas-finder.twig', $context);
