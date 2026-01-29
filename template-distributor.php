<?php // Template Name: Distributor

    $context = Timber::context();

    $args = [
    'post_type'      => 'distributors',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
    ];

    $distributors_posts = Timber::get_posts($args);
    $distributors       = [];
    $addresses          = [];

    foreach ($distributors_posts as $post) {
    $categories_list = $post->terms('distributors-cat');
    $cat_slugs       = [];

    if ($categories_list) {
        foreach ($categories_list as $cat) {
            $cat_slugs[] = $cat->slug;
        }
    }

    $category_icons = [];

    if ($categories_list) {
        foreach ($categories_list as $cat) {
            $icon = get_field('category_icon', 'term_' . $cat->term_id);
            if ($icon) {
                $category_icons[$cat->slug] = $icon;
            }
        }
    }

    $distributors[] = [
        'id'             => $post->ID,
        'title'          => $post->title,
        'address'        => get_field('distributor_address', $post->ID),
        'phone'          => get_field('distributor_phone', $post->ID),
        'email'          => get_field('distributor_email', $post->ID),
        'fax'            => get_field('distributor_fax', $post->ID),
        'categories'     => $categories_list,
        'category_slugs' => $cat_slugs,
        'category_icon'  => $category_icons,
    ];

    $address = get_field('distributor_address', $post->ID);
    if (! $address) {
        continue;
    }

    $addresses[] = [
        'title'   => $post->title,
        'address' => $address,
    ];
    }

    $categories = Timber::get_terms([
    'taxonomy'   => 'distributors-cat',
    'hide_empty' => false,
    ]);

    $context['distributors'] = $distributors;
    $context['categories']   = $categories;

?>

<script>
  window.distributors = <?php echo json_encode($distributors); ?>;
</script>

<?php

$context['GOOGLE_MAPS_API'] = getenv('GOOGLE_MAPS_API');
error_log("GOOGLE_MAPS_API value: " . ($context['GOOGLE_MAPS_API'] ?: 'VIDE'));

$client_id = getenv('ONBASE_CLIENT_ID');
$client_secret = getenv('ONBASE_CLIENT_SECRET');
$scope = getenv('ONBASE_SCOPE');

Timber::render('pages/distributor.twig', $context);
