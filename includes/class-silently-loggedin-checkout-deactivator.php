<?php

/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 *
 * @package    Silently_Loggedin_Checkout
 * @subpackage Silently_Loggedin_Checkout/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Silently_Loggedin_Checkout
 * @subpackage Silently_Loggedin_Checkout/includes
 * @author     Téo F <teof.wp@tfia.fr>
 */
class Silently_Loggedin_Checkout_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		flush_rewrite_rules();

	}

}
