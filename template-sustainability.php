<?php // Template Name: Sustainability

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/sustainability.twig', $context);

