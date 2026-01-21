<?php // Template Name: Need help
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/needHelp.twig', $context);
