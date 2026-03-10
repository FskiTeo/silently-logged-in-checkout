<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://tfia.fr
 * @since             1.0.0
 * @package           Silently_Loggedin_Checkout
 *
 * @wordpress-plugin
 * Plugin Name:       Silently Logged In Checkout
 * Description:       This plugin allows a silently logged in checkout by prompting user email and creating an account silently before checkout.
 * Version:           1.0.0
 * Author:            Téo F
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       silently-loggedin-checkout
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'SILENTLY_LOGGEDIN_CHECKOUT_VERSION', '1.0.0' );
define( 'SILENTLY_LOGGEDIN_CHECKOUT_PLUGIN_FILE', __FILE__ );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-silently-loggedin-checkout-activator.php
 */
function activate_silently_loggedin_checkout() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-silently-loggedin-checkout-activator.php';
	Silently_Loggedin_Checkout_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-silently-loggedin-checkout-deactivator.php
 */
function deactivate_silently_loggedin_checkout() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-silently-loggedin-checkout-deactivator.php';
	Silently_Loggedin_Checkout_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_silently_loggedin_checkout' );
register_deactivation_hook( __FILE__, 'deactivate_silently_loggedin_checkout' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-silently-loggedin-checkout.php';

/**
 * Begins execution of the plugin.
 * Hooked to plugins_loaded so WooCommerce is guaranteed to be loaded first.
 *
 * @since    1.0.0
 */
function run_silently_loggedin_checkout() {

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p>' .
				esc_html__( 'Silently Logged In Checkout nécessite que WooCommerce soit installé et actif.', 'silently-loggedin-checkout' ) .
				'</p></div>';
		} );
		return;
	}

	$plugin = new Silently_Loggedin_Checkout();
	$plugin->run();

}
add_action( 'plugins_loaded', 'run_silently_loggedin_checkout' );
