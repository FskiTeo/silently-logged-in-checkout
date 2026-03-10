<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://tfia.fr
 * @since      1.0.0
 *
 * @package    Silently_Loggedin_Checkout
 * @subpackage Silently_Loggedin_Checkout/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Silently_Loggedin_Checkout
 * @subpackage Silently_Loggedin_Checkout/admin
 * @author     Téo F <teof.wp@tfia.fr>
 */
class Silently_Loggedin_Checkout_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Silently_Loggedin_Checkout_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Silently_Loggedin_Checkout_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/silently-loggedin-checkout-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Silently_Loggedin_Checkout_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Silently_Loggedin_Checkout_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/silently-loggedin-checkout-admin.js', array( 'jquery' ), $this->version, false );

	}

	// =========================================================================
	// Settings page
	// =========================================================================

	/**
	 * Register the SLC settings page under WooCommerce in the admin menu.
	 */
	public function register_settings_page() {
		add_submenu_page(
			'woocommerce',
			__( 'Silently Loggedin Checkout – Réglages', 'silently-loggedin-checkout' ),
			__( 'SLC – Réglages', 'silently-loggedin-checkout' ),
			'manage_woocommerce',
			'slc-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register the settings fields and their sanitisation callbacks.
	 */
	public function register_settings() {
		register_setting(
			'slc_settings_group',
			'slc_email_prompt_page_id',
			array( 'sanitize_callback' => 'absint' )
		);
		register_setting(
			'slc_settings_group',
			'slc_otp_verify_page_id',
			array( 'sanitize_callback' => 'absint' )
		);
		register_setting(
			'slc_settings_group',
			'slc_logged_in_redirect_page_id',
			array( 'sanitize_callback' => 'absint' )
		);
	}

	/**
	 * Render the settings page HTML.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$email_page_id          = (int) get_option( 'slc_email_prompt_page_id' );
		$otp_page_id            = (int) get_option( 'slc_otp_verify_page_id' );
		$logged_in_redirect_id  = (int) get_option( 'slc_logged_in_redirect_page_id' );

		$pages = get_pages( array( 'post_status' => 'publish' ) );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Silently Loggedin Checkout – Réglages', 'silently-loggedin-checkout' ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'slc_settings_group' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="slc_email_prompt_page_id">
								<?php esc_html_e( 'Page de saisie de l\'e-mail', 'silently-loggedin-checkout' ); ?>
							</label>
						</th>
						<td>
							<select id="slc_email_prompt_page_id" name="slc_email_prompt_page_id">
								<option value="0"><?php esc_html_e( '— Choisir une page —', 'silently-loggedin-checkout' ); ?></option>
								<?php foreach ( $pages as $page ) : ?>
									<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $email_page_id, $page->ID ); ?>>
										<?php echo esc_html( $page->post_title ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Page contenant le code court [slc_email_prompt]. Les invités y seront redirigés lorsqu\'ils tentent d\'accéder à la caisse.', 'silently-loggedin-checkout' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="slc_otp_verify_page_id">
								<?php esc_html_e( 'Page de vérification OTP', 'silently-loggedin-checkout' ); ?>
							</label>
						</th>
						<td>
							<select id="slc_otp_verify_page_id" name="slc_otp_verify_page_id">
								<option value="0"><?php esc_html_e( '— Choisir une page —', 'silently-loggedin-checkout' ); ?></option>
								<?php foreach ( $pages as $page ) : ?>
									<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $otp_page_id, $page->ID ); ?>>
										<?php echo esc_html( $page->post_title ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Page contenant le code court [slc_otp_verify]. Les utilisateurs existants y seront redirigés pour saisir leur code.', 'silently-loggedin-checkout' ); ?>
							</p>
						</td>
					</tr>
				<tr>
					<th scope="row">
						<label for="slc_logged_in_redirect_page_id">
							<?php esc_html_e( 'Redirection si déjà connecté', 'silently-loggedin-checkout' ); ?>
						</label>
					</th>
					<td>
						<select id="slc_logged_in_redirect_page_id" name="slc_logged_in_redirect_page_id">
							<option value="0"><?php esc_html_e( '— Page par défaut (caisse WooCommerce) —', 'silently-loggedin-checkout' ); ?></option>
							<?php foreach ( $pages as $page ) : ?>
								<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $logged_in_redirect_id, $page->ID ); ?>>
									<?php echo esc_html( $page->post_title ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php esc_html_e( 'Page vers laquelle rediriger un utilisateur déjà connecté qui tente d\'accéder aux pages SLC. Si aucune page n\'est choisie, il sera redirigé vers la caisse.', 'silently-loggedin-checkout' ); ?>
						</p>
					</td>
				</tr>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

}
