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
		register_setting(
			'slc_settings_group',
			'slc_otp_expiry',
			array( 'sanitize_callback' => array( $this, 'sanitize_otp_expiry' ) )
		);
		register_setting(
			'slc_settings_group',
			'slc_otp_max_attempts',
			array( 'sanitize_callback' => array( $this, 'sanitize_otp_max_attempts' ) )
		);
		register_setting(
			'slc_settings_group',
			'slc_otp_length',
			array( 'sanitize_callback' => array( $this, 'sanitize_otp_length' ) )
		);
		register_setting(
			'slc_settings_group',
			'slc_texts',
			array( 'sanitize_callback' => array( $this, 'sanitize_ui_texts' ) )
		);
		register_setting(
			'slc_settings_group',
			'slc_error_texts',
			array( 'sanitize_callback' => array( $this, 'sanitize_error_texts' ) )
		);
		register_setting(
			'slc_settings_group',
			'slc_email_templates',
			array( 'sanitize_callback' => array( $this, 'sanitize_email_templates' ) )
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
		$otp_expiry             = (int) get_option( 'slc_otp_expiry', 600 );
		$otp_max_attempts       = (int) get_option( 'slc_otp_max_attempts', 3 );
		$otp_length             = (int) get_option( 'slc_otp_length', 6 );

		$ui_texts        = wp_parse_args( (array) get_option( 'slc_texts', array() ), $this->get_ui_text_defaults() );
		$error_texts     = wp_parse_args( (array) get_option( 'slc_error_texts', array() ), $this->get_error_text_defaults() );
		$email_templates = wp_parse_args( (array) get_option( 'slc_email_templates', array() ), $this->get_email_template_defaults() );

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
								<?php esc_html_e( 'Page contenant le code court [slc_otp_verify]. Tous les utilisateurs y seront redirigés pour saisir leur code.', 'silently-loggedin-checkout' ); ?>
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

					<tr>
						<th scope="row">
							<label for="slc_otp_expiry">
								<?php esc_html_e( 'Durée de validité OTP (secondes)', 'silently-loggedin-checkout' ); ?>
							</label>
						</th>
						<td>
							<input
								type="number"
								id="slc_otp_expiry"
								name="slc_otp_expiry"
								value="<?php echo esc_attr( (string) $otp_expiry ); ?>"
								min="60"
								step="1"
								class="small-text"
							>
							<p class="description"><?php esc_html_e( 'Exemple: 600 = 10 minutes.', 'silently-loggedin-checkout' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="slc_otp_max_attempts">
								<?php esc_html_e( 'Nombre max de tentatives OTP', 'silently-loggedin-checkout' ); ?>
							</label>
						</th>
						<td>
							<input
								type="number"
								id="slc_otp_max_attempts"
								name="slc_otp_max_attempts"
								value="<?php echo esc_attr( (string) $otp_max_attempts ); ?>"
								min="1"
								step="1"
								class="small-text"
							>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="slc_otp_length">
								<?php esc_html_e( 'Longueur du code OTP', 'silently-loggedin-checkout' ); ?>
							</label>
						</th>
						<td>
							<input
								type="number"
								id="slc_otp_length"
								name="slc_otp_length"
								value="<?php echo esc_attr( (string) $otp_length ); ?>"
								min="4"
								max="9"
								step="1"
								class="small-text"
							>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Textes - Formulaire e-mail', 'silently-loggedin-checkout' ); ?></th>
						<td>
							<?php $this->render_text_input( 'slc_texts[email_prompt_title]', $ui_texts['email_prompt_title'] ); ?>
							<p class="description"><?php esc_html_e( 'Titre du formulaire de saisie e-mail.', 'silently-loggedin-checkout' ); ?></p>
							<?php $this->render_textarea_input( 'slc_texts[email_prompt_intro]', $ui_texts['email_prompt_intro'] ); ?>
							<p class="description"><?php esc_html_e( 'Texte d\'introduction sous le titre.', 'silently-loggedin-checkout' ); ?></p>
							<?php $this->render_text_input( 'slc_texts[email_label]', $ui_texts['email_label'] ); ?>
							<?php $this->render_text_input( 'slc_texts[email_submit_button]', $ui_texts['email_submit_button'] ); ?>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Textes - Formulaire OTP', 'silently-loggedin-checkout' ); ?></th>
						<td>
							<?php $this->render_text_input( 'slc_texts[otp_title]', $ui_texts['otp_title'] ); ?>
							<?php $this->render_textarea_input( 'slc_texts[otp_intro_template]', $ui_texts['otp_intro_template'] ); ?>
							<p class="description"><?php esc_html_e( 'Placeholders disponibles: {email}, {minutes}, {otp_length}.', 'silently-loggedin-checkout' ); ?></p>
							<?php $this->render_text_input( 'slc_texts[otp_label_template]', $ui_texts['otp_label_template'] ); ?>
							<p class="description"><?php esc_html_e( 'Placeholder disponible: {otp_length}.', 'silently-loggedin-checkout' ); ?></p>
							<?php $this->render_text_input( 'slc_texts[otp_submit_button]', $ui_texts['otp_submit_button'] ); ?>
							<?php $this->render_text_input( 'slc_texts[otp_back_to_email]', $ui_texts['otp_back_to_email'] ); ?>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Templates e-mail OTP', 'silently-loggedin-checkout' ); ?></th>
						<td>
							<?php $this->render_text_input( 'slc_email_templates[subject_template]', $email_templates['subject_template'] ); ?>
							<p class="description"><?php esc_html_e( 'Placeholders disponibles: {shop_name}, {otp}, {minutes}, {otp_length}.', 'silently-loggedin-checkout' ); ?></p>
							<?php $this->render_textarea_input( 'slc_email_templates[body_template]', $email_templates['body_template'] ); ?>
							<p class="description"><?php esc_html_e( 'HTML autorisé (ex: <br>, <strong>). Placeholders disponibles: {shop_name}, {otp}, {minutes}, {otp_length}.', 'silently-loggedin-checkout' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Messages d\'erreur', 'silently-loggedin-checkout' ); ?></th>
						<td>
							<?php foreach ( $error_texts as $error_key => $error_value ) : ?>
								<label for="<?php echo esc_attr( 'slc_error_texts_' . $error_key ); ?>">
									<?php echo esc_html( $error_key ); ?>
								</label>
								<?php $this->render_text_input( 'slc_error_texts[' . $error_key . ']', $error_value, 'slc_error_texts_' . $error_key ); ?>
							<?php endforeach; ?>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Sanitize OTP expiry setting.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public function sanitize_otp_expiry( $value ) {
		$expiry = absint( $value );

		return max( 60, $expiry );
	}

	/**
	 * Sanitize OTP max attempts setting.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public function sanitize_otp_max_attempts( $value ) {
		$attempts = absint( $value );

		return max( 1, $attempts );
	}

	/**
	 * Sanitize OTP length setting.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public function sanitize_otp_length( $value ) {
		$length = absint( $value );

		if ( $length < 4 ) {
			return 4;
		}

		if ( $length > 9 ) {
			return 9;
		}

		return $length;
	}

	/**
	 * Sanitize UI text settings.
	 *
	 * @param array<string, mixed> $values Raw values.
	 * @return array<string, string>
	 */
	public function sanitize_ui_texts( $values ) {
		$defaults = $this->get_ui_text_defaults();
		$values   = is_array( $values ) ? $values : array();
		$output   = array();

		foreach ( $defaults as $key => $default ) {
			if ( ! isset( $values[ $key ] ) ) {
				$output[ $key ] = $default;
				continue;
			}

			if ( false !== strpos( $key, 'intro' ) ) {
				$output[ $key ] = sanitize_textarea_field( (string) $values[ $key ] );
			} else {
				$output[ $key ] = sanitize_text_field( (string) $values[ $key ] );
			}
		}

		return $output;
	}

	/**
	 * Sanitize error text settings.
	 *
	 * @param array<string, mixed> $values Raw values.
	 * @return array<string, string>
	 */
	public function sanitize_error_texts( $values ) {
		$defaults = $this->get_error_text_defaults();
		$values   = is_array( $values ) ? $values : array();
		$output   = array();

		foreach ( $defaults as $key => $default ) {
			$output[ $key ] = isset( $values[ $key ] )
				? sanitize_text_field( (string) $values[ $key ] )
				: $default;
		}

		return $output;
	}

	/**
	 * Sanitize e-mail template settings.
	 *
	 * @param array<string, mixed> $values Raw values.
	 * @return array<string, string>
	 */
	public function sanitize_email_templates( $values ) {
		$defaults = $this->get_email_template_defaults();
		$values   = is_array( $values ) ? $values : array();
		$output   = array();

		$output['subject_template'] = isset( $values['subject_template'] )
			? sanitize_text_field( (string) $values['subject_template'] )
			: $defaults['subject_template'];

		$output['body_template'] = isset( $values['body_template'] )
			? wp_kses_post( (string) $values['body_template'] )
			: $defaults['body_template'];

		return $output;
	}

	/**
	 * Return default UI texts.
	 *
	 * @return array<string, string>
	 */
	private function get_ui_text_defaults() {
		return array(
			'email_prompt_title'  => __( 'Entrez votre adresse e-mail', 'silently-loggedin-checkout' ),
			'email_prompt_intro'  => __( 'Afin de finaliser votre commande, veuillez entrer votre adresse e-mail. Un compte sera créé automatiquement si vous n\'en avez pas encore.', 'silently-loggedin-checkout' ),
			'email_label'         => __( 'Adresse e-mail :', 'silently-loggedin-checkout' ),
			'email_submit_button' => __( 'Continuer →', 'silently-loggedin-checkout' ),
			'otp_title'           => __( 'Entrez votre code de vérification', 'silently-loggedin-checkout' ),
			'otp_intro_template'  => __( 'Un code à {otp_length} chiffres a été envoyé à {email}. Saisissez-le ci-dessous pour vous connecter (valable {minutes} minutes).', 'silently-loggedin-checkout' ),
			'otp_label_template'  => __( 'Code OTP ({otp_length} chiffres) :', 'silently-loggedin-checkout' ),
			'otp_submit_button'   => __( 'Vérifier →', 'silently-loggedin-checkout' ),
			'otp_back_to_email'   => __( 'Changer d\'adresse e-mail', 'silently-loggedin-checkout' ),
		);
	}

	/**
	 * Return default error texts.
	 *
	 * @return array<string, string>
	 */
	private function get_error_text_defaults() {
		return array(
			'invalid_nonce'       => __( 'Requête invalide. Veuillez réessayer.', 'silently-loggedin-checkout' ),
			'invalid_email'       => __( 'Adresse e-mail invalide.', 'silently-loggedin-checkout' ),
			'registration_failed' => __( 'Impossible de créer votre compte. Veuillez réessayer.', 'silently-loggedin-checkout' ),
			'otp_expired'         => __( 'Le code OTP a expiré. Veuillez recommencer.', 'silently-loggedin-checkout' ),
			'otp_invalid'         => __( 'Code OTP incorrect. Veuillez réessayer.', 'silently-loggedin-checkout' ),
			'otp_max_attempts'    => __( 'Trop de tentatives incorrectes. Veuillez recommencer depuis le début.', 'silently-loggedin-checkout' ),
			'user_not_found'      => __( 'Utilisateur introuvable. Veuillez réessayer.', 'silently-loggedin-checkout' ),
			'generic_error'       => __( 'Une erreur est survenue. Veuillez réessayer.', 'silently-loggedin-checkout' ),
		);
	}

	/**
	 * Return default e-mail templates.
	 *
	 * @return array<string, string>
	 */
	private function get_email_template_defaults() {
		return array(
			'subject_template' => __( 'Votre code de connexion {shop_name}', 'silently-loggedin-checkout' ),
			'body_template'    => __( "Bonjour,<br><br>Votre code de connexion {shop_name} est : <strong>{otp}</strong><br><br>Ce code est valable pendant {minutes} minutes.<br><br>Si vous n'avez pas demandé ce code, vous pouvez ignorer cet e-mail.", 'silently-loggedin-checkout' ),
		);
	}

	/**
	 * Render a text input.
	 *
	 * @param string $name  Input name.
	 * @param string $value Input value.
	 * @param string $id    Input id.
	 */
	private function render_text_input( $name, $value, $id = '' ) {
		$input_id = $id ? $id : sanitize_key( str_replace( array( '[', ']' ), '_', $name ) );
		?>
		<input
			type="text"
			id="<?php echo esc_attr( $input_id ); ?>"
			name="<?php echo esc_attr( $name ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
		>
		<?php
	}

	/**
	 * Render a textarea input.
	 *
	 * @param string $name  Input name.
	 * @param string $value Input value.
	 */
	private function render_textarea_input( $name, $value ) {
		$input_id = sanitize_key( str_replace( array( '[', ']' ), '_', $name ) );
		?>
		<textarea
			id="<?php echo esc_attr( $input_id ); ?>"
			name="<?php echo esc_attr( $name ); ?>"
			rows="4"
			class="large-text"
		><?php echo esc_textarea( $value ); ?></textarea>
		<?php
	}

}
