<?php // Template Name: Distributor

$context = Timber::context();

$args = [
    'post_type' => 'distributors',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
];

$distributors_posts = Timber::get_posts($args);
$distributors = [];
$addresses = [];

foreach ($distributors_posts as $post) {
    $categories_list = $post->terms('distributors-cat');
    $cat_slugs = [];
    
    if ($categories_list) {
        foreach ($categories_list as $cat) {
            $cat_slugs[] = $cat->slug;
        }
    }
    
    $distributors[] = [
        'id' => $post->ID,
        'title' => $post->title,
        'address' => get_field('distributor_address', $post->ID),
        'phone' => get_field('distributor_phone', $post->ID),
        'email' => get_field('distributor_email', $post->ID),
        'fax' => get_field('distributor_fax', $post->ID),
        'categories' => $categories_list,
        'category_slugs' => $cat_slugs
    ];

	$address = get_field('distributor_address', $post->ID);
    if (!$address) continue;

    $addresses[] = [
        'title' => $post->title,
        'address' => $address,
    ];
}

$categories = Timber::get_terms([
    'taxonomy' => 'distributors-cat',
    'hide_empty' => false
]);

$context['distributors'] = $distributors;
$context['categories'] = $categories;

?>

<script>
  window.distributors = <?php echo json_encode($distributors); ?>;
</script>

<?php
Timber::render('pages/distributor.twig', $context);
