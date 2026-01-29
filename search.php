<?php
/**
 * Search Results Template
 * Template WordPress natif pour les résultats de recherche
 */

use Timber\Timber;

// Récupération de la query de recherche (WordPress natif)
$search_query = get_search_query();

// Arguments de recherche
$args = [
    'post_type' => ['post', 'news', 'solutions', 'gases'],
    'post_status' => 'publish',
    's' => $search_query,
    'posts_per_page' => 10,
    'paged' => get_query_var('paged') ? get_query_var('paged') : 1
];

// Query WordPress
$query = new WP_Query($args);
$posts = Timber::get_posts($query);

// Contexte Timber
$context = Timber::context();
$context['posts'] = $posts;
$context['search_query'] = $search_query;
$context['pagination'] = Timber::get_pagination();

// Titre de la page
$context['page_title'] = sprintf(
    function_exists('pll__') ? pll__('Search Results') : 'Search Results'
);

// Traductions
$context['i18n'] = [
    'search_placeholder' => function_exists('pll__') ? pll__('Search...') : 'Search...',
    'search_button' => function_exists('pll__') ? pll__('Search') : 'Search',
    'results_for' => function_exists('pll__') ? pll__('Results for') : 'Results for',
    'no_results' => function_exists('pll__') ? pll__('No results found. Try different keywords.') : 'No results found. Try different keywords.',
    'view_page' => function_exists('pll__') ? pll__('View') : 'View',
    'found_results' => function_exists('pll__') ? pll__('results found') : 'results found'
];

$context['found_posts'] = $query->found_posts;

// Rendu du template
Timber::render('search.twig', $context);
