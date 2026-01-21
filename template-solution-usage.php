<?php
/* Template Name: Solution Usage
 * Template Post Type: solutions
*/

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/solution-usage.twig', $context);
