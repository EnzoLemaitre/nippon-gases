<?php
namespace App;

use App\CustomPostType;
use App\Template;

class Config
{
    private $template;
    public $cssFiles = [];

    public function __construct()
    {
        $this->template = new Template(TRANSLATE_DOMAIN);
        $this->menus();
        $this->scripts();
        $this->styles();
        // $this->imagesFormat();
        $this->customPostType();
    }

    /**
     * Add menus to theme
     *
     * @return void
     */
    public function menus(): void
    {
        add_action('init', function () {
            $this->template->menus([
                'header'             => __('Header Left', TRANSLATE_DOMAIN),
                'headerRight'        => __('Header Right', TRANSLATE_DOMAIN),
                'footer'             => __('Footer', TRANSLATE_DOMAIN),
                'footerBottom'       => __('Footer bottom', TRANSLATE_DOMAIN),
                'footerNippon'       => __('Footer Nippon', TRANSLATE_DOMAIN),
                'footerNeedHelp'     => __('Footer Need help', TRANSLATE_DOMAIN),
                'footerCareers'      => __('Footer Careers', TRANSLATE_DOMAIN),
                'footerSolutions'    => __('Footer Solutions', TRANSLATE_DOMAIN),
                'footerSubsidiaries' => __('Footer Subsidiaries', TRANSLATE_DOMAIN),
            ]);
        });
    }

    /**
     * Image formats
     *
     * @return void
     */
    public function imagesFormat(): void
    {
        add_action('after_setup_theme', function () {
            $this->template->imagesFormat([
                // ['partners', 345, 345],
            ]);
        });
    }

    /**
     * Custom post type
     *
     * @return void
     */
    public function customPostType(): void
    {
        add_action('init', function () {
            $postType = new CustomPostType;
            $postType->createPostType([
                'typeName'     => 'solutions',
                'name'         => __('Solutions', TRANSLATE_DOMAIN),
                'singular'     => __('Solution', TRANSLATE_DOMAIN),
                'all'          => __('All solutions', TRANSLATE_DOMAIN),
                'add'          => __('Add a solution', TRANSLATE_DOMAIN),
                'new'          => __('New solution', TRANSLATE_DOMAIN),
                'edit'         => __('Edit solution', TRANSLATE_DOMAIN),
                'view'         => __('View a solution', TRANSLATE_DOMAIN),
                'slug'         => 'solutions',
                'visibleFront' => true,
                'icon'         => 'dashicons-database',
            ]);

            $postType->createPostType([
                'typeName'     => 'gases',
                'name'         => __('Gases', TRANSLATE_DOMAIN),
                'singular'     => __('Gas', TRANSLATE_DOMAIN),
                'all'          => __('All gases', TRANSLATE_DOMAIN),
                'add'          => __('Add a gas', TRANSLATE_DOMAIN),
                'new'          => __('New gas', TRANSLATE_DOMAIN),
                'edit'         => __('Edit gas', TRANSLATE_DOMAIN),
                'view'         => __('View a gas', TRANSLATE_DOMAIN),
                'slug'         => 'gases',
                'visibleFront' => true,
                'icon'         => 'dashicons-image-filter',
            ]);

            $postType->createPostType([
                'typeName'     => 'distributors',
                'name'         => __('Distributors', TRANSLATE_DOMAIN),
                'singular'     => __('Distributor', TRANSLATE_DOMAIN),
                'all'          => __('All distributors', TRANSLATE_DOMAIN),
                'add'          => __('Add a distributor', TRANSLATE_DOMAIN),
                'new'          => __('New distributor', TRANSLATE_DOMAIN),
                'edit'         => __('Edit distributor', TRANSLATE_DOMAIN),
                'view'         => __('View a distributor', TRANSLATE_DOMAIN),
                'slug'         => 'distributors',
                'visibleFront' => false,
                'icon'         => 'dashicons-store',
            ]);

             $postType->createTaxo([
             	'name'         => 'distributors-cat',
             	'postType'     => 'distributors',
             	'label'        => __('Categories', TRANSLATE_DOMAIN),
             	'new'          => __('Add a category', TRANSLATE_DOMAIN),
             	'slug'         => __('distributors-cat', TRANSLATE_DOMAIN),
             	'visibleFront' => false,
             	'meta_box_cb'  => true
             ]);

            // $postType->createTaxo([
            // 	'name'         => 'realizations-cat',
            // 	'postType'     => 'realizations',
            // 	'label'        => __('Categories', TRANSLATE_DOMAIN),
            // 	'new'          => __('Add a category', TRANSLATE_DOMAIN),
            // 	'slug'         => __('realizations-cat', TRANSLATE_DOMAIN),
            // 	'visibleFront' => true,
            // 	'meta_box_cb'  => true
            // ]);
        });
    }

    /**
     * Add scripts to enqueue
     *
     * @return void
     */
    public function scripts(): void
    {
        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_script('main-js', get_template_directory_uri() . '/assets/js/main.js', [], null, true);
            // Components back to top
            if (! empty(THEME_OPTIONS['backToTop'])) {
                wp_enqueue_script('backToTop-js', get_template_directory_uri() . '/assets/js/components/backToTop.js', [], null, true);
            }
            // Components theme mode (dark/light)
            if (! empty(THEME_OPTIONS['modeTheme'])) {
                wp_enqueue_script('switchThemeMode-js', get_template_directory_uri() . '/assets/js/components/switchThemeMode.js', [], null, true);
                wp_localize_script('switchThemeMode-js', 'themeLabels', [
                    'toDark'  => __('Switch to dark mode', TRANSLATE_DOMAIN),
                    'toLight' => __('Switch to light mode', TRANSLATE_DOMAIN),
                ]);
            }
        });
    }

    /**
     * Add styles to enqueue and add versioning based on file modification time
     *
     * @return void
     */
    public function styles(): void
    {
        add_action('wp_enqueue_scripts', function () {
            if (! empty($this->cssFiles)) {
                foreach ($this->cssFiles as $file) {
                    $handle  = basename($file);
                    $version = filemtime(get_template_directory() . $file);
                    wp_enqueue_style($handle, get_template_directory_uri() . '/' . $file, [], $version);
                }
            }
        });
    }
}
