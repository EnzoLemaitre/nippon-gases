<?php // Template Name: Customer Portal
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/customerPortal.twig', $context);
