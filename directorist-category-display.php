<?php

/*
Plugin Name: Category Display - Directorist
Description: A plugin that displays posts by category for the Directorist WordPress plugin.
Version: 1.0.0
Author: Kim Welch
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit;
}

class Directorist_Category_Display
{

    /**
     * Initialize the plugin
     */
    public function __construct()
    {
        // Directorist Category Display Shortcode
        add_shortcode('directorist-category-display', array($this, 'category_display_shortcode'));

        // Check if Directorist plugin is active on plugin activation
        register_activation_hook(__FILE__, array($this, 'check_directorist_plugin_active'));

        // Custom Style
        add_action('wp_head', array($this, 'custom_style'));
    }

    /**
     * Check if Directorist plugin is active
     */
    public function check_directorist_plugin_active()
    {
        if (!is_plugin_active('directorist/directorist-base.php')) {
            // If Directorist plugin is not active, deactivate this plugin and show error message
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die('Category Display - Directorist requires the Directorist plugin to be active.');
        }
    }

    /**
     * Shortcode to display posts by category
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML markup for posts.
     */
    public function category_display_shortcode($atts)
    {
        ob_start();
        $categories = $this->directorist_prepare_category_items();

        if ($categories && count($categories) > 0) {
?>
            <div class="category-block-wrapper">
                <?php
                foreach ($categories as $key => $category) {
                    if ($category && count($category) > 0) {
                ?>
                        <div class="category-block">
                            <?php
                            foreach ($category as $cat_key => $cat_obj) {
                                $cat_obj->link = get_term_link($cat_obj);
                                if ($cat_key === 'obj') {
                            ?>
                                    <a href="<?php echo $cat_obj->link; ?>" class="parent-category"><?php echo $cat_obj->name; ?></a>
                                <?php
                                } else {
                                ?>
                                    <a href="<?php echo $cat_obj->link; ?>" class="child-category"><?php echo $cat_obj->name; ?></a>
                            <?php
                                }
                            }
                            ?>
                        </div>
                <?php
                    }
                }
                ?>
            </div>
        <?php
        }

        return ob_get_clean();
    }

    /**
     * Directorist - Prepare Category Items
     */

    public function directorist_prepare_category_items()
    {
        $categories = [];
        $parent_args = array(
            'taxonomy' => ATBDP_CATEGORY,
            'hide_empty' => false,
            'parent' => 0
        );

        $parents = get_terms($parent_args);

        if (!$parents || count($parents) < 1) return;

        foreach ($parents as $parent) {

            $categories[$parent->slug]['obj'] = $parent;
            $child_args = array(
                'taxonomy' => ATBDP_CATEGORY,
                'hide_empty' => false,
                'parent' => $parent->term_id
            );

            $children = get_terms($child_args);
            if ($children && count($children) > 0) {
                foreach ($children as $child) {
                    $categories[$parent->slug][$child->slug] = $child;
                }
            }
        }

        return $categories;
    }

    /**
     * Directorist - Custom Style
     */
    public function custom_style()
    {
        ?>
        <style>
            .parent-category,
            .child-category {
                display: block;
            }

            .parent-category {
                font-family: "Fjalla One", "Source Sans Pro", -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .5px;
                background-color: rgba(0, 0, 0, .03);
                padding: 10px 20px;
                margin-bottom: 10px;
                font-size: 1rem;
                line-height: 1.5;
                color: #2a3c48;
            }

            .child-category {
                color: #db633f;
                text-decoration: underline;
                background-color: transparent;
                padding: 0px 20px;
                line-height: 18px;
                font-size: 0.8rem;
            }

            .category-block-wrapper {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
            }

            .category-block {
                width: 32%;
                margin-right: 1%;
                margin-bottom: 20px
            }
        </style>
<?php
    }
}

new Directorist_Category_Display();
