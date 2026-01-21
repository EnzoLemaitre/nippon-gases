<?php
use Timber\Timber;
use Timber\PostQuery;

$context = Timber::context();
$context['categories'] = get_categories();
$context['years'] = getYears();

Timber::render('pages/index.twig', $context);

function getYears() {
	global $wpdb;
	$years = $wpdb->get_col("SELECT DISTINCT YEAR(post_date) 
		FROM $wpdb->posts 
		WHERE post_type = 'post' 
		AND post_status = 'publish'
		ORDER BY YEAR(post_date) DESC");
	return $years;
}