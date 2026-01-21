<?php // Template Name: Contact us

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/contact.twig', $context);
