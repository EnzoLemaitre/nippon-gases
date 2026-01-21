<?php // Template Name: About
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/about.twig', $context);
