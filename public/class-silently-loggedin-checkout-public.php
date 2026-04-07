<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Silently_Loggedin_Checkout
 * @subpackage Silently_Loggedin_Checkout/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Intercepts the WooCommerce checkout for guests and provides a silent
 * registration / OTP login flow before redirecting back to the checkout.
 *
 * @package    Silently_Loggedin_Checkout
 * @subpackage Silently_Loggedin_Checkout/public
 * @author     Téo F <teof.wp@tfia.fr>
 */
class Silently_Loggedin_Checkout_Public {

	/** OTP validity in seconds (10 minutes). */
	const OTP_EXPIRY = 600;

	/** Maximum number of wrong OTP attempts before the code is invalidated. */
	const OTP_MAX_ATTEMPTS = 3;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name  The name of the plugin.
	 * @param    string $version      The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	// =========================================================================
	// Shortcodes
	// =========================================================================

	/**
	 * Register the [slc_email_prompt] and [slc_otp_verify] shortcodes.
	 */
	public function register_shortcodes() {
		add_shortcode( 'slc_email_prompt', array( $this, 'shortcode_email_prompt' ) );
		add_shortcode( 'slc_otp_verify',   array( $this, 'shortcode_otp_verify' ) );
	}

	// =========================================================================
	// Template redirect – interception & page rendering
	// =========================================================================

	/**
	 * Main entry point hooked to template_redirect.
	 *
	 * Three responsibilities:
	 *   1. Redirect logged-in users away from SLC pages (already done, send to checkout).
	 *   2. Intercept the WooCommerce checkout for guests and send them to the email prompt.
	 *   3. Serve the email-prompt and OTP-verify virtual pages.
	 */
	public function handle_template_redirect() {
		$email_prompt_page_id = (int) get_option( 'slc_email_prompt_page_id' );
		$otp_verify_page_id   = (int) get_option( 'slc_otp_verify_page_id' );

		$is_email_prompt = $email_prompt_page_id && is_page( $email_prompt_page_id );
		$is_otp_verify   = $otp_verify_page_id   && is_page( $otp_verify_page_id );

		// --- POST handling on SLC pages ---
		if ( $is_email_prompt || $is_otp_verify ) {
			if ( is_user_logged_in() ) {
				if ( $is_otp_verify && function_exists( 'wc_get_cart_url' ) ) {
					$redirect_url = wc_get_cart_url();
				} else {
					$redirect_page_id = (int) get_option( 'slc_logged_in_redirect_page_id' );
					$redirect_url     = $redirect_page_id
						? get_permalink( $redirect_page_id )
						: wc_get_checkout_url();
				}
				wp_safe_redirect( $redirect_url );
				exit;
			}

			if ( 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
				if ( $is_email_prompt ) {
					$this->handle_email_submission();
				} else {
					$this->handle_otp_submission();
				}
			}

			// GET: shortcode renders the form; nothing more to do here.
			return;
		}

		// --- Checkout interception ---
		if (
			function_exists( 'is_checkout' ) &&
			is_checkout() &&
			! is_wc_endpoint_url() &&
			! is_user_logged_in() &&
			$email_prompt_page_id
		) {
			wp_safe_redirect( get_permalink( $email_prompt_page_id ) );
			exit;
		}
	}

	// =========================================================================
	// Shortcode renderers
	// =========================================================================

	/**
	 * Shortcode [slc_email_prompt] – renders the email-prompt form.
	 *
	 * @return string HTML output.
	 */
	public function shortcode_email_prompt() {
		if ( is_user_logged_in() ) {
			return '';
		}

		$error            = isset( $_GET['slc_error'] )
			? sanitize_text_field( wp_unslash( $_GET['slc_error'] ) )
			: '';
		$email_prompt_url = get_permalink( get_option( 'slc_email_prompt_page_id' ) );

		ob_start();
		?>
		<div class="slc-form-wrapper">
			<h2><?php esc_html_e( 'Entrez votre adresse e-mail', 'silently-loggedin-checkout' ); ?></h2>
			<p><?php esc_html_e( 'Afin de finaliser votre commande, veuillez entrer votre adresse e-mail. Un compte sera créé automatiquement si vous n\'en avez pas encore.', 'silently-loggedin-checkout' ); ?></p>

			<?php if ( $error ) : ?>
				<p class="slc-error">
					<?php echo esc_html( $this->get_error_message( $error ) ); ?>
				</p>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( $email_prompt_url ); ?>">
				<?php wp_nonce_field( 'slc_submit_email', 'slc_email_nonce' ); ?>

				<p>
					<label for="slc_email">
						<?php esc_html_e( 'Adresse e-mail :', 'silently-loggedin-checkout' ); ?>
					</label>
					<input
						type="email"
						id="slc_email"
						name="slc_email"
						required
						autocomplete="email"
					>
				</p>
				<p>
					<button type="submit">
						<?php esc_html_e( 'Continuer →', 'silently-loggedin-checkout' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Shortcode [slc_otp_verify] – renders the OTP-verification form.
	 *
	 * @return string HTML output.
	 */
	public function shortcode_otp_verify() {
		if ( is_user_logged_in() ) {
			return '';
		}

		$email = isset( $_GET['slc_email'] )
			? $this->sanitize_email_value( wp_unslash( $_GET['slc_email'] ) )
			: '';
		$error = isset( $_GET['slc_error'] )
			? sanitize_text_field( wp_unslash( $_GET['slc_error'] ) )
			: '';

		$email_prompt_url = get_permalink( get_option( 'slc_email_prompt_page_id' ) );

		if ( ! is_email( $email ) ) {
			wp_safe_redirect( $email_prompt_url );
			exit;
		}

		$otp_verify_url = add_query_arg(
			'slc_email',
			$email,
			get_permalink( get_option( 'slc_otp_verify_page_id' ) )
		);

		ob_start();
		?>
		<div class="slc-form-wrapper">
			<h2><?php esc_html_e( 'Entrez votre code de vérification', 'silently-loggedin-checkout' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %s: email address */
					esc_html__( 'Un code à 6 chiffres a été envoyé à %s. Saisissez-le ci-dessous pour vous connecter (valable 10 minutes).', 'silently-loggedin-checkout' ),
					'<strong>' . esc_html( $email ) . '</strong>'
				);
				?>
			</p>

			<?php if ( $error ) : ?>
				<p class="slc-error">
					<?php echo esc_html( $this->get_error_message( $error ) ); ?>
				</p>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( $otp_verify_url ); ?>">
				<?php wp_nonce_field( 'slc_submit_otp', 'slc_otp_nonce' ); ?>
				<input type="hidden" name="slc_email" value="<?php echo esc_attr( $email ); ?>">

				<p>
					<label for="slc_otp">
						<?php esc_html_e( 'Code OTP (6 chiffres) :', 'silently-loggedin-checkout' ); ?>
					</label>
					<input
						type="text"
						id="slc_otp"
						name="slc_otp"
						required
						maxlength="6"
						pattern="[0-9]{6}"
						inputmode="numeric"
						autocomplete="one-time-code"
					>
				</p>
				<p>
					<button type="submit">
						<?php esc_html_e( 'Vérifier →', 'silently-loggedin-checkout' ); ?>
					</button>
				</p>
			</form>

			<p class="slc-back-link">
				<a href="<?php echo esc_url( $email_prompt_url ); ?>">
					← <?php esc_html_e( 'Changer d\'adresse e-mail', 'silently-loggedin-checkout' ); ?>
				</a>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}

	// =========================================================================
	// Form submission handlers (admin_post_nopriv_*)
	// =========================================================================

	/**
	 * Handle submission of the email form.
	 *
	 * Always generates and sends an OTP code, then redirects to the OTP page.
	 * Account creation (for unknown emails) is deferred until OTP validation.
	 */
	public function handle_email_submission() {
		// CSRF check.
		$email_prompt_url = get_permalink( get_option( 'slc_email_prompt_page_id' ) );
		$otp_verify_url   = get_permalink( get_option( 'slc_otp_verify_page_id' ) );

		if (
			! isset( $_POST['slc_email_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['slc_email_nonce'] ) ),
				'slc_submit_email'
			)
		) {
			wp_safe_redirect( add_query_arg( 'slc_error', 'invalid_nonce', $email_prompt_url ) );
			exit;
		}

		$email = isset( $_POST['slc_email'] )
			? $this->sanitize_email_value( wp_unslash( $_POST['slc_email'] ) )
			: '';

		if ( ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( 'slc_error', 'invalid_email', $email_prompt_url ) );
			exit;
		}

		$otp = $this->generate_otp();
		set_transient(
			$this->get_otp_transient_key( $email ),
			array( 'otp' => $otp, 'attempts' => 0 ),
			self::OTP_EXPIRY
		);
		$this->send_otp_email( $email, $otp );

		wp_safe_redirect( add_query_arg( 'slc_email', $email, $otp_verify_url ) );
		exit;
	}

	/**
	 * Handle submission of the OTP form.
	 *
	 * - Valid OTP   → delete transient, create customer if needed, auto-login, redirect to checkout.
	 * - Invalid OTP → decrement remaining attempts; on exhaustion send back to email prompt.
	 */
	public function handle_otp_submission() {
		// CSRF check.
		$email_prompt_url = get_permalink( get_option( 'slc_email_prompt_page_id' ) );
		$otp_verify_url   = get_permalink( get_option( 'slc_otp_verify_page_id' ) );

		if (
			! isset( $_POST['slc_otp_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['slc_otp_nonce'] ) ),
				'slc_submit_otp'
			)
		) {
			wp_safe_redirect( add_query_arg( 'slc_error', 'invalid_nonce', $email_prompt_url ) );
			exit;
		}

		$email = isset( $_POST['slc_email'] )
			? $this->sanitize_email_value( wp_unslash( $_POST['slc_email'] ) )
			: '';
		// Allow only digits.
		$otp   = isset( $_POST['slc_otp'] )
			? preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['slc_otp'] ) )
			: '';

		if ( ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( 'slc_error', 'invalid_email', $email_prompt_url ) );
			exit;
		}

		$otp_page_url  = add_query_arg( 'slc_email', $email, $otp_verify_url );
		$transient_key = $this->get_otp_transient_key( $email );
		$transient     = get_transient( $transient_key );

		// OTP has expired.
		if ( false === $transient ) {
			wp_safe_redirect( add_query_arg( 'slc_error', 'otp_expired', $otp_page_url ) );
			exit;
		}

		// Increment the attempt counter before checking, so every call is counted.
		$transient['attempts']++;

		// Constant-time comparison to prevent timing attacks.
		if ( hash_equals( (string) $transient['otp'], $otp ) ) {
			delete_transient( $transient_key );

			$user = get_user_by( 'email', $email );

			if ( $user ) {
				$user_id = $user->ID;
			} else {
				$user_id = $this->create_customer( $email );
				if ( is_wp_error( $user_id ) ) {
					// Handle race condition where account could be created in parallel.
					$existing_user = get_user_by( 'email', $email );
					if ( $existing_user ) {
						$user_id = $existing_user->ID;
					} else {
						wp_safe_redirect( add_query_arg( 'slc_error', 'registration_failed', $email_prompt_url ) );
						exit;
					}
				}
			}

			$this->auto_login( $user_id );
			wp_safe_redirect( wc_get_checkout_url() );
			exit;
		}

		// Wrong OTP.
		if ( $transient['attempts'] >= self::OTP_MAX_ATTEMPTS ) {
			delete_transient( $transient_key );
			wp_safe_redirect( add_query_arg( 'slc_error', 'otp_max_attempts', $email_prompt_url ) );
			exit;
		}

		// Still has attempts left – persist updated counter.
		set_transient( $transient_key, $transient, self::OTP_EXPIRY );
		wp_safe_redirect( add_query_arg( 'slc_error', 'otp_invalid', $otp_page_url ) );
		exit;
	}

	// =========================================================================
	// Private helpers
	// =========================================================================

	/**
	 * Generate a cryptographically random 6-digit OTP string.
	 *
	 * @return string
	 */
	private function generate_otp() {
		return str_pad( (string) random_int( 0, 999999 ), 6, '0', STR_PAD_LEFT );
	}

	/**
	 * Sanitize an email while preserving plus aliases that may arrive as spaces.
	 *
	 * Some URL decoders can convert + to a space. Re-map spaces to + before
	 * sanitize_email() so transient keys stay consistent for addresses like
	 * user+tag@example.com.
	 *
	 * @param string $email_raw Raw email value from request.
	 * @return string
	 */
	private function sanitize_email_value( $email_raw ) {
		$email_raw = trim( (string) $email_raw );
		$email_raw = str_replace( ' ', '+', $email_raw );

		return sanitize_email( $email_raw );
	}

	/**
	 * Return the transient key for a given email address.
	 *
	 * @param  string $email
	 * @return string
	 */
	private function get_otp_transient_key( $email ) {
		return 'slc_otp_' . md5( strtolower( trim( $email ) ) );
	}

	/**
	 * Send the OTP code by e-mail.
	 *
	 * @param string $email Recipient.
	 * @param string $otp   6-digit code.
	 */
	private function send_otp_email( $email, $otp ) {
		$shop_name = get_bloginfo( 'name' );
		$subject   = sprintf(
			/* translators: %s: shop name */
			__( 'Votre code de connexion %s', 'silently-loggedin-checkout' ),
			$shop_name
		);

		$message = sprintf(
			/* translators: %1$s: shop name, %2$s: 6-digit OTP code */
			__(
				"Bonjour,<br><br>Votre code de connexion %1\$s est : <strong>%2\$s</strong><br><br>Ce code est valable pendant 10 minutes.<br><br>Si vous n'avez pas demandé ce code, vous pouvez ignorer cet e-mail.",
				'silently-loggedin-checkout'
			),
			$shop_name,
			$otp
		);

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		wp_mail( $email, $subject, $message, $headers );
	}

	/**
	 * Create a new WooCommerce customer account for the given e-mail.
	 *
	 * Uses wc_create_new_customer() when available (preferred), otherwise
	 * falls back to wp_insert_user() with the 'customer' role.
	 *
	 * @param  string $email
	 * @return int|WP_Error  New user ID, or WP_Error on failure.
	 */
	private function create_customer( $email ) {
		$username = $this->generate_unique_username( $email );
		$password = wp_generate_password( 16 );

		if ( function_exists( 'wc_create_new_customer' ) ) {
			return wc_create_new_customer( $email, $username, $password, array(
				'first_name' => '',
				'last_name'  => '',
			) );
		}

		return wp_insert_user( array(
			'user_email' => $email,
			'user_login' => $username,
			'user_pass'  => $password,
			'role'       => 'customer',
		) );
	}

	/**
	 * Derive a unique username from an e-mail address.
	 *
	 * @param  string $email
	 * @return string
	 */
	private function generate_unique_username( $email ) {
		$base     = sanitize_user( current( explode( '@', $email ) ), true );
		$base     = ! empty( $base ) ? $base : 'customer';
		$username = $base;
		$suffix   = 1;

		while ( username_exists( $username ) ) {
			$username = $base . $suffix;
			$suffix++;
		}

		return $username;
	}

	/**
	 * Log a user in programmatically.
	 *
	 * Sets the auth cookie, switches the current user, and fires the wp_login
	 * action so that other plugins (e.g. WooCommerce session) can react.
	 *
	 * @param int $user_id
	 */
	private function auto_login( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );
		do_action( 'wp_login', $user->user_login, $user );
	}

	/**
	 * Return a human-readable error message for a given error code.
	 *
	 * @param  string $code
	 * @return string
	 */
	private function get_error_message( $code ) {
		$messages = array(
			'invalid_nonce'       => __( 'Requête invalide. Veuillez réessayer.', 'silently-loggedin-checkout' ),
			'invalid_email'       => __( 'Adresse e-mail invalide.', 'silently-loggedin-checkout' ),
			'registration_failed' => __( 'Impossible de créer votre compte. Veuillez réessayer.', 'silently-loggedin-checkout' ),
			'otp_expired'         => __( 'Le code OTP a expiré. Veuillez recommencer.', 'silently-loggedin-checkout' ),
			'otp_invalid'         => __( 'Code OTP incorrect. Veuillez réessayer.', 'silently-loggedin-checkout' ),
			'otp_max_attempts'    => __( 'Trop de tentatives incorrectes. Veuillez recommencer depuis le début.', 'silently-loggedin-checkout' ),
			'user_not_found'      => __( 'Utilisateur introuvable. Veuillez réessayer.', 'silently-loggedin-checkout' ),
		);

		return isset( $messages[ $code ] )
			? $messages[ $code ]
			: __( 'Une erreur est survenue. Veuillez réessayer.', 'silently-loggedin-checkout' );
	}

	// =========================================================================
	// Enqueue
	// =========================================================================

	/**
	 * Enqueue the SLC stylesheet only on the two SLC pages.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$email_page_id = (int) get_option( 'slc_email_prompt_page_id' );
		$otp_page_id   = (int) get_option( 'slc_otp_verify_page_id' );

		if (
			! ( $email_page_id && is_page( $email_page_id ) ) &&
			! ( $otp_page_id   && is_page( $otp_page_id ) )
		) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/silently-loggedin-checkout-public.css',
			array(),
			$this->version
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// No custom JavaScript needed.
	}

}
