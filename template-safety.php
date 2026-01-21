<?php // Template Name: Safety
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/safety.twig', $context);
