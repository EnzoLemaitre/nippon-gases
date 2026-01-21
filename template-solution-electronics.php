<?php
/* Template Name: Solution Electronics
 * Template Post Type: solutions
*/

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/solution-electronics.twig', $context);