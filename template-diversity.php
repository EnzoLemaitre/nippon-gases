<?php // Template Name: Diversity
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/diversity.twig', $context);
