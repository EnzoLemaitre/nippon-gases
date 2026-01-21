<?php
/* Template Name: Solution Diving
 * Template Post Type: solutions
*/

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/solution-diving.twig', $context);
