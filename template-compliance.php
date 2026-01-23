<?php // Template Name: Compliance

use Timber\Timber;



$context = Timber::context();

$context['post'] = Timber::get_post();



Timber::render('pages/compliance.twig', $context);

