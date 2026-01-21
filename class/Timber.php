<?php
namespace App;

use App\Wpml;
use Twig\Environment;
use Twig\TwigFunction;
use Timber\ImageHelper;
use Symfony\Component\VarDumper\VarDumper;

class Timber {
	public function __construct()
	{
		$this->dump();
		$this->constant();
		$this->breadcrumb();
		$this->image();
		$this->link();
		$this->options();
		$this->mergeMenu();
	}

	/**
	 * Config dump
	 * 
	 * @return void
	 */
	private function dump(): void
	{
		add_filter('timber/twig', function (Environment $twig) {
			$twig->addFunction(new \Twig\TwigFunction('dump', function($var) {
				VarDumper::dump($var);
			}));

			return $twig;
		});
	}

	/**
	 * WPML Selector languages
	 */
	private function languageSelector()
	{
		add_filter('timber/twig', function(Environment $twig) {
			$twig->addFunction(new TwigFunction('languageselector', function() {
				return Wpml::languageSelector();
			}));

			return $twig;
		});
	}
	
	/**
	 * Merge menu to have all items in one menu
	 */
	private function mergeMenu()
	{
		add_filter('timber/twig', function(Environment $twig) {
			$twig->addFunction(new TwigFunction('mergeMenu', function() {
				$locations = get_nav_menu_locations();
				$all_items = [];

				foreach (['header', 'headerRight'] as $location) {
					if (!isset($locations[$location])) continue;

					$menu_id = $locations[$location];
					$items = wp_get_nav_menu_items($menu_id);

					if ($items) {
						foreach ($items as $item) {
							$all_items[$item->ID] = $item;
							$all_items[$item->ID]->children = [];
						}
					}
				}

				$tree = [];
				foreach ($all_items as $item) {
					if ($item->menu_item_parent == 0) {
						$tree[$item->ID] = $item;
					} else {
						if (isset($all_items[$item->menu_item_parent])) {
							$all_items[$item->menu_item_parent]->children[$item->ID] = $item;
						}
					}
				}

				return $tree;
			}));

			return $twig;
		});
	}

	/**
	 * Image format srcset, retina and convert to webp
	 * Need to load image with dimensions * 2
	 */
	private function image ()
	{
		add_filter('timber/twig', function(Environment $twig) {
			$twig->addFunction(new TwigFunction('image', function(object $img)
			{
				$webp = ImageHelper::img_to_webp($img->src, 100);

				$width = []; $height = [];
				for ($i = 1; $i <= 2; $i++) {
					$width[$i] = round($img->width / ($i));
					$height[$i] = round($img->height / $i);
				}
				sort($width); sort($height);

				$content = '<picture>';
					$content .= '<source srcset="';
						$content .= Template::retinaFormat($webp, $width[0], $height[0], 1) . ' 1x, ';
						$content .= $webp . ' 2x';
					$content .= '" type="image/webp">';
					$content .= '<source srcset="';
						$content .= Template::retinaFormat($img->src, $width[0], $height[0], 1) . ' 1x, ';
						$content .= $img->src . ' 2x';
					$content .= '" type="image/jpeg">';
					$content .= '<img src="' . $img->src . '" alt="' . $img->alt . '" width="' . $width[0] . '" height="' . $height[0] . '">';
				$content .= '</picture>';

				return $content;
			}));

			return $twig;
		});
	}

	/**
	 * Constante to use in twig
	 * 
	 * @return void
	 */
	private function constant(): void
	{
		add_filter('timber/twig', function (Environment $twig) {
			$twig->addFunction(new TwigFunction('constant', function($name) {
				return constant($name);
			}));

			return $twig;
		});
	}

	/**
	 * Options to use in twig
	 * 
	 * @return void
	 */
	private function options(): void
	{
		add_filter('timber/context', function ($context) {
			// Récupérer le post_id correct pour la langue actuelle
			$post_id = 'options';
			if (function_exists('pll_current_language')) {
				$current_lang = pll_current_language();
				$post_id = 'options_' . $current_lang;
			}

			$fields = get_fields($post_id);

			if (!empty($fields['options'])) {
				$context['options'] = $fields['options'];
			}

			return $context;
		});
	}

	/**
	 * Link acf to use in twig
	 * 
	 * @return void
	 */
	private function link(): void
	{
		add_filter('timber/twig', function (Environment $twig) {
			$twig->addFunction(new TwigFunction('link', function($link, $class = '', $icon = '') {
				$result = '';
				if (!empty($link)) {
					$target = $link['target'] ? ' target="_blank"' : '';
					$class = $class ? ' class="' . $class . '"' : '';
					$result = '<a href="' . $link['url'] . '"' . $class . $target .  '>';
						$result .= $link['title'];
						if (!empty($icon)) $result .= $icon;
					$result .= '</a>';
				}

				return $result;
			}));

			return $twig;
		});
	}

	/**
	 * Breadcrumb with schema.org
	 */
	private function breadcrumb(): void
	{
		add_filter('timber/twig', function (Environment $twig) {
			$twig->addFunction(new TwigFunction('breadcrumb', function($breadcrumbTitle = []) {
				global $post;
				$breadcrumbs = [];
				$position = 1;

				$breadcrumbs[] = ['name' => __('Home', 'ng'), 'url'  => home_url('/')];

				if (is_singular('gases')) {
					$template = 'template-gases.php';
					$pages = get_pages(['meta_key' => '_wp_page_template', 'meta_value' => $template]);
					if (!empty($pages)) {
						$gasesPage = $pages[0]->ID;
						if (!empty(get_field('breadcrumb', $gasesPage)['title'])) {
							$breadcrumbs[] = ['name' => get_field('breadcrumb', $gasesPage)['title'], 'url' => get_permalink($gasesPage)];
						} else {
							$breadcrumbs[] = ['name' => __('Our gases', 'ng'), 'url' => get_permalink($gasesPage)];
						}
					}
					$breadcrumbs[] = ['name' => get_the_title(), 'url' => get_permalink()];
				} elseif (is_singular('solutions')) {
					$template = 'template-solutions.php';
					$pages = get_pages(['meta_key' => '_wp_page_template', 'meta_value' => $template]);
					if (!empty($pages)) {
						$solutionsPage = $pages[0]->ID;
						if (!empty(get_field('breadcrumb', $solutionsPage)['title'])) {
							$breadcrumbs[] = ['name' => get_field('breadcrumb', $solutionsPage)['title'], 'url' => get_permalink($solutionsPage)];
						} else {
							$breadcrumbs[] = ['name' => __('Solutions', 'ng'), 'url' => get_permalink($solutionsPage)];
						}
					}
					$breadcrumbs[] = ['name' => get_the_title(), 'url' => get_permalink()];
				} elseif (is_single()) {
					// Get id of index page
					$idNews = get_option('page_for_posts');
					$url = get_permalink($idNews);
					$breadcrumbs[] = ['name' => get_the_title($idNews), 'url' => $url];

					$category = get_the_category();
					if (!empty($category)) {
						$cat = $category[0];
						$ancestors = array_reverse(get_ancestors($cat->term_id, 'category'));
						foreach ($ancestors as $ancestor) {
							$cat_obj = get_category($ancestor);
							$breadcrumbs[] = [
								'name' => $cat_obj->name,
								'url'  => get_category_link($cat_obj->term_id)
							];
						}
						$breadcrumbs[] = [
							'name' => $cat->name,
							'url'  => get_category_link($cat->term_id)
						];
					}

					$breadcrumbs[] = [
						'name' => get_the_title(),
						'url'  => get_permalink()
					];

				} elseif (is_page() && !is_front_page()) {
					$parents = array_reverse(get_post_ancestors($post->ID));
					foreach ($parents as $parent_id) {
						$breadcrumbs[] = [
							'name' => get_the_title($parent_id),
							'url'  => get_permalink($parent_id)
						];
					}
					$breadcrumbs[] = [
						'name' => get_the_title(),
						'url'  => get_permalink()
					];
				} elseif (is_category()) {
					// Get id of index page
					$idNews = get_option('page_for_posts');
					$url = get_permalink($idNews);
					$breadcrumbs[] = ['name' => get_the_title($idNews), 'url' => $url];

					$current_cat = get_queried_object();
					$ancestors = array_reverse(get_ancestors($current_cat->term_id, 'category'));
					foreach ($ancestors as $ancestor) {
						$cat_obj = get_category($ancestor);
						$breadcrumbs[] = [
							'name' => $cat_obj->name,
							'url'  => get_category_link($cat_obj->term_id)
						];
					}
					$breadcrumbs[] = [
						'name' => single_cat_title('', false),
						'url'  => get_category_link($current_cat->term_id)
					];

				} elseif (is_search()) {
					$breadcrumbs[] = ['name' => 'Recherche : ' . get_search_query(), 'url' => ''];
				} elseif (is_404()) {
					$breadcrumbs[] = ['name' => 'Erreur 404', 'url' => ''];
				} elseif (is_home()) {
					$breadcrumbs[] = ['name' => get_the_title(), 'url' => ''];
				}

				$content = '<nav class="breadcrumb" aria-label="' . __('Breadcrumb', 'ng') . '"><ol>';
				foreach ($breadcrumbs as $index => $crumb) {
					$is_last = ($index === array_key_last($breadcrumbs));
					$content .= '<li>';
					if (!$is_last && !empty($crumb['url'])) {
						$content .= '<a href="' . esc_url($crumb['url']) . '">' . esc_html($crumb['name']) . '</a>';
					} else {
						$name = (!empty($breadcrumbTitle['title'])) ? $breadcrumbTitle['title'] : $crumb['name'];
						$content .= '<span aria-current="page">' . esc_html($name) . '</span>';
					}
					$content .= '</li>';
				}
				$content .= '</ol></nav>';

				$schema = [
					"@context" => "https://schema.org",
					"@type" => "BreadcrumbList",
					"itemListElement" => []
				];

				foreach ($breadcrumbs as $index => $crumb) {
					$schema["itemListElement"][] = [
						"@type" => "ListItem",
						"position" => $index + 1,
						"name" => $crumb["name"],
						"item" => !empty($crumb["url"]) ? esc_url($crumb["url"]) : ''
					];
				}

				$content .= '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
				return $content;
			}));

			return $twig;
		});
	}
}

new Timber;
