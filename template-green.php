<?php // Template Name: Green

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/green.twig', $context);