<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://http://localhost/
 * @since             1.0.0
 * @package           Guests_Posts
 *
 * @wordpress-plugin
 * Plugin Name:       guests-posts
 * Plugin URI:        https://http://localhost/
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            rk
 * Author URI:        https://http://localhost//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       guests-posts
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('GUESTS_POSTS_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-guests-posts-activator.php
 */
function activate_guests_posts()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-guests-posts-activator.php';
	Guests_Posts_Activator::activate();
	create_custom_table();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-guests-posts-deactivator.php
 */
function deactivate_guests_posts()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-guests-posts-deactivator.php';
	Guests_Posts_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_guests_posts');
register_deactivation_hook(__FILE__, 'deactivate_guests_posts');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-guests-posts.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_guests_posts()
{

	$plugin = new Guests_Posts();
	$plugin->run();
}
run_guests_posts();

function create_custom_table()
{
	global $wpdb;

	$table_name = $wpdb->prefix . 'guest_post_data';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        author_name varchar(255) NOT NULL,
        author_email varchar(255) NOT NULL,
        PRIMARY KEY  (id),
        KEY post_id (post_id)
    ) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta($sql);
}
