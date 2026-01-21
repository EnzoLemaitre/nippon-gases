<?php // Template Name: Innovation
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/innovation.twig', $context);
