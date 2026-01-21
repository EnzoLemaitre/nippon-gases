<?php // Template Name: Solutions
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();
$solutions = (new WP_Query(['post_type' => 'solutions', 'posts_per_page' => -1]))->posts;
$context['solutions'] = excludeUsageTemplate($solutions);



Timber::render('pages/solutions.twig', $context);


// Exclude usage template solution
function excludeUsageTemplate(array $allSolutions = [])
{
	$solutions = [];
	foreach ($allSolutions as $solution) {
		if (get_post_meta($solution->ID, '_wp_page_template', true) != 'template-solution-usage.php') {
			$solutions[] = $solution;
		}
	}

	return $solutions;
}
