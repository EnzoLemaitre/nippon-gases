<?php // Template Name: Our gases
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();
$context['gases'] = (new WP_Query(['post_type' => 'gases', 'posts_per_page' => -1]))->posts;

Timber::render('pages/gases.twig', $context);
