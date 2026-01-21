<?php // Template Name: Why Nippon
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/why.twig', $context);
