<?php
/**
 * Template Name: Search Page
 * Description: Page de recherche globale (News, Solutions, Gases)
 */

use Timber\Timber;

// Récupération de la query de recherche
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

$results = [];
$total_items = 0;

if (!empty($search_query)) {
    // Recherche dans plusieurs post types : news, solutions, gases
    $args = [
        'post_type' => ['news', 'solutions', 'gases'],
        'post_status' => 'publish',
        's' => $search_query,
        'posts_per_page' => -1, // Récupérer tous les résultats pour le tri manuel
        'orderby' => 'relevance',
        'order' => 'DESC'
    ];

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            // Calcul de la pertinence (nombre d'occurrences dans title + content)
            $title = get_the_title();
            $content = get_the_content();
            
            $title_matches = substr_count(strtolower($title), strtolower($search_query));
            $content_matches = substr_count(strtolower($content), strtolower($search_query));
            $relevance = ($title_matches * 3) + $content_matches; // Le titre compte 3x plus
            
            // Création de l'extrait (100 caractères)
            $excerpt = wp_trim_words($content, 15, '...');
            if (strlen($excerpt) > 100) {
                $excerpt = substr($excerpt, 0, 100) . '...';
            }
            
            // Post type label
            $post_type_obj = get_post_type_object(get_post_type());
            $post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : get_post_type();
            
            $results[] = [
                'id' => get_the_ID(),
                'title' => $title,
                'excerpt' => $excerpt,
                'url' => get_permalink(),
                'post_type' => get_post_type(),
                'post_type_label' => $post_type_label,
                'relevance' => $relevance
            ];
        }
        wp_reset_postdata();
        
        // Tri par pertinence (du plus pertinent au moins pertinent)
        usort($results, function($a, $b) {
            return $b['relevance'] - $a['relevance'];
        });
    }
}

$total_items = count($results);

// Pagination
$items_per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$total_pages = ceil($total_items / $items_per_page);
$offset = ($current_page - 1) * $items_per_page;
$paged_results = array_slice($results, $offset, $items_per_page);

// Contexte Timber
$context = Timber::context();
$context['post'] = Timber::get_post();

// Titre de page
$context['page_title'] = function_exists('pll__') ? pll__('Search Results') : 'Search Results';

// Traductions
$context['i18n'] = [
    'search_placeholder' => function_exists('pll__') ? pll__('Search...') : 'Search...',
    'search_button' => function_exists('pll__') ? pll__('Search') : 'Search',
    'results_count' => function_exists('pll__') ? pll__('Results') : 'Results',
    'no_results' => function_exists('pll__') ? pll__('No results found for your search.') : 'No results found for your search.',
    'view_page' => function_exists('pll__') ? pll__('View page') : 'View page',
    'back_arrow' => '« ',
    'category_prefix' => function_exists('pll__') ? pll__('in') : 'in'
];

$context['search_query'] = $search_query;
$context['results'] = $paged_results;
$context['results_count'] = $total_items;
$context['pagination'] = [
    'count' => $total_items,
    'total' => $total_pages,
    'current' => $current_page
];

// Rendu du template Twig
Timber::render('pages/search.twig', $context);
