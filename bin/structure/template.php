<?php // Template Name: {{name}}
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/{{name}}.twig', $context);
