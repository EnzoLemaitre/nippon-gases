<?php
namespace App;

class Admin
{

    public function __construct()
    {

        add_action('after_setup_theme', [$this, 'removeThumbnailBox']);

        add_action('admin_menu', [$this, 'removeTagsPosts'], 999);

        add_action('admin_menu', [$this, 'replacePostByNews']);

        add_action('init', [$this, 'replacePostByNewsLabel']);

        add_action('admin_menu', [$this, 'removeTagsBox']);

        add_action('do_meta_boxes', [$this, 'keepFeaturedImageOnlyForPosts'], 20);

    }

    /**

     * Remove

     */

    public function removeThumbnailBox()
    {

        //remove_theme_support('post-thumbnails');

    }

    /**

     * Remove tags for postss

     */

    public function removeTagsPosts()
    {

        remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=post_tag');

    }

    /**

     * Remove tags box for posts

     *

     * @return void

     */

    public function removeTagsBox()
    {

        unregister_taxonomy_for_object_type('post_tag', 'post');

    }

    /**

     * Remove featured image box for posts

     */

    public function keepFeaturedImageOnlyForPosts()
    {

        $types = get_post_types(['public' => true], 'names');

        foreach ($types as $type) {

            if ($type !== 'post') {
                remove_meta_box('postimagediv', $type, 'side');
            }

        }

    }

    /**

     * Replace "posts" by "news"

     */

    public function replacePostByNews()
    {

        global $menu;

        global $submenu;

        $menu[5][0] = __('News', TRANSLATE_DOMAIN);

        $submenu['edit.php'][5][0] = __('News', TRANSLATE_DOMAIN);

        $submenu['edit.php'][10][0] = __('Add News', TRANSLATE_DOMAIN);

        $submenu['edit.php'][16][0] = __('News Tags', TRANSLATE_DOMAIN);

    }

    /**

     * Change "posts" labels by "news"

     */

    public function replacePostByNewsLabel()
    {

        global $wp_post_types;

        $labels = &$wp_post_types['post']->labels;

        $labels->name = __('News', TRANSLATE_DOMAIN);

        $labels->singular_name = __('News', TRANSLATE_DOMAIN);

        $labels->add_new = __('Add News', TRANSLATE_DOMAIN);

        $labels->add_new_item = __('Add News', TRANSLATE_DOMAIN);

        $labels->edit_item = __('Edit News', TRANSLATE_DOMAIN);

        $labels->new_item = __('News', TRANSLATE_DOMAIN);

        $labels->view_item = __('View News', TRANSLATE_DOMAIN);

        $labels->search_items = __('Search News', TRANSLATE_DOMAIN);

        $labels->not_found = __('No News found', TRANSLATE_DOMAIN);

        $labels->not_found_in_trash = __('No News found in Trash', TRANSLATE_DOMAIN);

        $labels->all_items = __('All News', TRANSLATE_DOMAIN);

        $labels->menu_name = __('News', TRANSLATE_DOMAIN);

        $labels->name_admin_bar = __('News', TRANSLATE_DOMAIN);

    }

}

new Admin;
