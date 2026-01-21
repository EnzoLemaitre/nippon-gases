<?php // Template Name: Europe
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/europe.twig', $context);
