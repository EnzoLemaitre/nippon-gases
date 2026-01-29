<?php
/**
 * Template Name: Search Landing
 * Description: Page d'accueil de la recherche (juste la barre de recherche)
 */

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

// Titre de la page
$context['page_title'] = function_exists('pll__') ? pll__('Search') : 'Search';

// Traductions
$context['i18n'] = [
    'search_placeholder' => function_exists('pll__') ? pll__('Search...') : 'Search...',
    'search_button' => function_exists('pll__') ? pll__('Search') : 'Search'
];

Timber::render('search-landing.twig', $context);
