<?php
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

// Get url of page with template solutions
$template = 'template-solutions.php';
$pages = get_pages(['meta_key' => '_wp_page_template', 'meta_value' => $template]);
if (!empty($pages)) $context['solutionsUrl'] = get_permalink($pages[0]->ID);

// Get url of page with template contact
$template = 'template-contact.php';
$pages = get_pages(['meta_key' => '_wp_page_template', 'meta_value' => $template]);
if (!empty($pages)) $context['contactUrl'] = get_permalink($pages[0]->ID);

// Contact alert content of about template
$template = 'template-about.php';
$pages = get_pages(['meta_key' => '_wp_page_template', 'meta_value' => $template]);
if (!empty($pages)) $context['contactAlert'] = get_field('contactAlert', $pages[0]->ID);

Timber::render('pages/404.twig', $context);
