<?php

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 *
 * @package    Silently_Loggedin_Checkout
 * @subpackage Silently_Loggedin_Checkout/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Silently_Loggedin_Checkout
 * @subpackage Silently_Loggedin_Checkout/includes
 * @author     Téo F <teof.wp@tfia.fr>
 */
class Silently_Loggedin_Checkout_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( plugin_basename( SILENTLY_LOGGEDIN_CHECKOUT_PLUGIN_FILE ) );
			wp_die(
				'<p>' . esc_html__( 'Silently Logged In Checkout nécessite que WooCommerce soit installé et actif.', 'silently-loggedin-checkout' ) . '</p>',
				esc_html__( 'Erreur d\'activation du plugin', 'silently-loggedin-checkout' ),
				array( 'back_link' => true )
			);
		}

	}

}
