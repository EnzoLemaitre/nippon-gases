<?php // Template Name: Iberia
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/iberia.twig', $context);
