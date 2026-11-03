<?php
/**
 * Deactivation feedback modal for the Plugins screen.
 *
 * Prompts for optional feedback when deactivating the plugin
 * and submits responses to the feedback endpoint.
 *
 * @package Click_To_Chat
 * @subpackage Plugins_Page
 * @since 4.46
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Deactivation_Feedback' ) ) {

	/**
	 * Deactivation feedback on the Plugins screen.
	 */
	class HT_CTC_Deactivation_Feedback {

		/**
		 * Feedback submission endpoint URL.
		 */
		const ENDPOINT = 'https://holithemes.com/wp-json/ht-code/v1/ctc-deactivation-feedback';

		/**
		 * Privacy policy covering what the endpoint receives.
		 */
		const PRIVACY_URL = 'https://holithemes.com/privacy/';

		/**
		 * Free WordPress.org support forum, offered for reasons help could fix.
		 * The anchor is the "Create Topic" form, shown only to logged-in users;
		 * logged out, the page just opens at the top.
		 */
		const SUPPORT_URL = 'https://wordpress.org/support/plugin/click-to-chat-for-whatsapp/#new-topic-0';

		/**
		 * AJAX action name, also used as the nonce action.
		 */
		const ACTION = 'ht_ctc_deactivation_feedback';

		/**
		 * Maximum character limit for feedback comment.
		 */
		const COMMENT_MAX = 1000;

		/**
		 * Reason sent for a comment with no reason picked. Not offered in the
		 * dialog: writing is enough, so the comment itself is the answer.
		 */
		const REASON_COMMENT_ONLY = 'comment_only';

		/**
		 * Maximum length of the contact email the user may type.
		 */
		const EMAIL_MAX = 100;

		/**
		 * Days after an answer or a skip during which the modal is snoozed,
		 * unless the user picks another of SNOOZE_CHOICES.
		 */
		const SNOOZE_DAYS = 2;

		/**
		 * Default snooze when the reason is "temporary" (testing or debugging).
		 */
		const SNOOZE_DAYS_TEMPORARY = 7;

		/**
		 * Snooze lengths, in days, the user may pick. Anything else falls back to SNOOZE_DAYS.
		 */
		const SNOOZE_CHOICES = array( 2, 7, 30 );

		/**
		 * Remote request timeout in seconds.
		 */
		const HTTP_TIMEOUT = 3;

		/**
		 * Extra time the browser allows on top of HTTP_TIMEOUT, in milliseconds,
		 * before it deactivates without waiting for the answer.
		 */
		const FAILSAFE_MARGIN_MS = 1500;

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Register AJAX handler for feedback submission.
			add_action( 'wp_ajax_' . self::ACTION, array( $this, 'ajax_submit' ) );

			// Initialize hooks on the plugins screen.
			add_action( 'load-plugins.php', array( $this, 'on_plugins_screen' ) );
		}

		/**
		 * Register asset and markup hooks on the plugins screen.
		 *
		 * @return void
		 */
		public function on_plugins_screen() {

			if ( ! $this->is_enabled() ) {
				return;
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
			add_action( 'admin_footer', array( $this, 'render_modal' ) );
		}

		/**
		 * Check whether feedback modal is enabled for this request.
		 *
		 * @return bool
		 */
		private function is_enabled() {

			// Same check core uses to print and to run this plugin's Deactivate link.
			if ( ! current_user_can( 'deactivate_plugin', HT_CTC_PLUGIN_BASENAME ) ) {
				return false;
			}

			// Skip on network admin screen.
			if ( is_network_admin() ) {
				return false;
			}

			// Network-active: a subsite's Plugins screen has no Deactivate link for it.
			if ( is_multisite() && is_plugin_active_for_network( HT_CTC_PLUGIN_BASENAME ) ) {
				return false;
			}

			// Answered or skipped recently. Not a permission check like the three
			// above: it keeps the dialog, its assets and a repeat answer away for a while.
			if ( get_transient( $this->snooze_key() ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Transient key holding this user's snooze.
		 *
		 * @return string
		 */
		private function snooze_key() {

			return 'ht_ctc_df_snooze_' . get_current_user_id();
		}

		/**
		 * Start this user's snooze for the length they picked.
		 *
		 * Only SNOOZE_CHOICES are accepted, so a hand-made request cannot snooze longer.
		 *
		 * @return void
		 */
		private function start_snooze() {

			$days = isset( $_POST['snooze_days'] ) ? absint( wp_unslash( $_POST['snooze_days'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in ajax_submit().

			if ( ! in_array( $days, self::SNOOZE_CHOICES, true ) ) {
				$days = self::SNOOZE_DAYS;
			}

			set_transient( $this->snooze_key(), 1, $days * DAY_IN_SECONDS );
		}

		/**
		 * Label for a snooze length, e.g. "7 days".
		 *
		 * @param int $days Days.
		 * @return string
		 */
		private static function days_label( $days ) {

			return ( 1 === (int) $days ) ? "{$days} day" : "{$days} days";
		}

		/**
		 * Feedback reason options and placeholders.
		 *
		 * @return array<string, array<string, string>>
		 */
		public static function reasons() {

			return array(
				'temporary'        => array(
					'label'       => 'Just deactivating temporarily (testing or debugging)',
					'placeholder' => '',
				),
				'not_working'      => array(
					'label'       => 'It didn’t work, or caused a problem',
					'placeholder' => 'What happened? Any error message, or a plugin or theme it clashed with?',
					'help'        => true,
				),
				'too_complicated'  => array(
					'label'       => 'Harder to set up than I expected',
					'placeholder' => 'Which part was confusing, or took longer than it should?',
					'help'        => true,
				),
				'missing_feature'  => array(
					'label'       => 'A feature I need is missing',
					'placeholder' => 'Which feature were you looking for?',
				),
				'found_better'     => array(
					'label'       => 'I found something that suits me better',
					'placeholder' => 'What are you using instead? It helps to know what it does better.',
				),
				'no_longer_needed' => array(
					'label'       => 'I no longer need WhatsApp chat on this site',
					'placeholder' => '',
				),
				'other'            => array(
					'label'       => 'Something else',
					'placeholder' => 'Tell us what happened.',
				),
			);
		}

		/**
		 * Resolve asset path to minified or dev source.
		 *
		 * @param string $relative Relative asset file name.
		 * @return string Plugin-relative path.
		 */
		private function asset_path( $relative ) {

			$min = 'new/inc/plugins-page/assets/min/' . $relative;

			if ( ! defined( 'HT_CTC_DEBUG_MODE' ) && file_exists( HT_CTC_PLUGIN_DIR . $min ) ) {
				return $min;
			}

			return 'new/inc/plugins-page/assets/dev/' . $relative;
		}

		/**
		 * Enqueue stylesheet and script for deactivation feedback.
		 *
		 * @return void
		 */
		public function enqueue() {

			wp_enqueue_style(
				'ht-ctc-deactivation-feedback',
				plugins_url( $this->asset_path( 'deactivation-feedback.css' ), HT_CTC_PLUGIN_FILE ),
				array( 'dashicons' ),
				HT_CTC_VERSION
			);

			wp_enqueue_script(
				'ht-ctc-deactivation-feedback',
				plugins_url( $this->asset_path( 'deactivation-feedback.js' ), HT_CTC_PLUGIN_FILE ),
				array(),
				HT_CTC_VERSION,
				true
			);

			$ht_ctc_df_var = array(
				'basename'       => HT_CTC_PLUGIN_BASENAME,
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
				'action'         => self::ACTION,
				'nonce'          => wp_create_nonce( self::ACTION ),
				'failsafe_ms'    => self::HTTP_TIMEOUT * 1000 + self::FAILSAFE_MARGIN_MS,
				'sending_label'  => 'Sending…',
				'email_required' => 'Enter the email we should reply to, or untick the box.',
				'snooze'         => array(
					'default'   => self::SNOOZE_DAYS,
					'temporary' => self::SNOOZE_DAYS_TEMPORARY,
				),
			);

			wp_add_inline_script(
				'ht-ctc-deactivation-feedback',
				'window.ht_ctc_df_var = ' . wp_json_encode( $ht_ctc_df_var ) . ';',
				'before'
			);
		}

		/**
		 * Render modal markup in the admin footer.
		 *
		 * @return void
		 */
		public function render_modal() {

			$reasons = self::reasons();

			$user  = wp_get_current_user();
			$email = is_email( $user->user_email ) ? sanitize_email( $user->user_email ) : '';

			?>
			<div class="ht-ctc-df-overlay" id="ht-ctc-df-modal" hidden>
				<div class="ht-ctc-df-dialog" role="dialog" aria-modal="true" aria-labelledby="ht-ctc-df-title" aria-describedby="ht-ctc-df-sub">

					<div class="ht-ctc-df-head">
						<h2 class="ht-ctc-df-title" id="ht-ctc-df-title">Click to Chat</h2>
						<p class="ht-ctc-df-sub" id="ht-ctc-df-sub">If you have a moment, tell us why you’re deactivating — it helps us find out what to fix.</p>
						<button type="button" class="ht-ctc-df-close" id="ht-ctc-df-close" aria-label="Cancel and keep the plugin active" title="Cancel and keep the plugin active">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true" focusable="false"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
						</button>
					</div>

					<div class="ht-ctc-df-body">
						<fieldset class="ht-ctc-df-fieldset">
							<legend class="screen-reader-text">Reason for deactivating</legend>
							<ul class="ht-ctc-df-reasons">
								<?php foreach ( $reasons as $ctc_key => $ctc_reason ) { ?>
									<li class="ht-ctc-df-reason">
										<label class="ht-ctc-df-reason-label" for="ht-ctc-df-reason-<?php echo esc_attr( $ctc_key ); ?>">
											<input
												type="radio"
												name="ht_ctc_df_reason"
												id="ht-ctc-df-reason-<?php echo esc_attr( $ctc_key ); ?>"
												value="<?php echo esc_attr( $ctc_key ); ?>"
												data-placeholder="<?php echo esc_attr( $ctc_reason['placeholder'] ); ?>"
											>
											<span><?php echo esc_html( $ctc_reason['label'] ); ?></span>
										</label>
										<?php
										// For reasons help could fix: the free forum, never a sales page.
										// Shown at the end of the row once it is chosen (CSS).
										if ( ! empty( $ctc_reason['help'] ) ) {
											?>
											<a class="ht-ctc-df-reason-help" href="<?php echo esc_url( self::SUPPORT_URL ); ?>" target="_blank" rel="noopener noreferrer" title="Talk to us in the WordPress.org support forum">Talk to us<span class="screen-reader-text"> (opens in a new tab)</span></a>
										<?php } ?>
									</li>
								<?php } ?>
							</ul>
						</fieldset>

						<div class="ht-ctc-df-detail" id="ht-ctc-df-detail">
							<label class="screen-reader-text" for="ht-ctc-df-comment">More detail</label>
							<textarea id="ht-ctc-df-comment" rows="3" maxlength="<?php echo esc_attr( self::COMMENT_MAX ); ?>" placeholder="Anything you’d like to add?"></textarea>
						</div>

						<?php
						// Prefilled with the account email, which is often a shared or
						// placeholder address. The address (or its pencil) is one button
						// that swaps in a field; the address in that field is the one sent.
						$ctc_email_field = sprintf(
							'<input type="email" class="ht-ctc-df-contact-email" id="ht-ctc-df-contact-email" value="%1$s" data-default="%1$s" maxlength="%2$d" autocomplete="email" placeholder="%3$s" aria-label="%4$s"%5$s>',
							esc_attr( $email ),
							(int) self::EMAIL_MAX,
							'you@example.com',
							'Email to contact you at',
							( '' !== $email ) ? ' hidden' : ''
						);

						if ( '' !== $email ) {
							// The hint is a description, not part of the button's text, so the
							// checkbox's name reads as one sentence: "…about this at <email>".
							$ctc_email_field = '<button type="button" class="ht-ctc-df-email-edit" id="ht-ctc-df-email-edit" aria-describedby="ht-ctc-df-email-hint" title="Use a different email">'
								. '<strong id="ht-ctc-df-email-text">' . esc_html( $email ) . '</strong>'
								. '<span class="dashicons dashicons-edit" aria-hidden="true"></span>'
								. '</button>'
								. $ctc_email_field;
						}

						// The address comes last and outside the <label>: a label may not hold
						// other controls, and a click on the address must never toggle the box.
						// aria-labelledby gives the checkbox the whole sentence, address included.
						$ctc_email_name_id = ( '' !== $email ) ? 'ht-ctc-df-email-text' : 'ht-ctc-df-contact-email';
						?>
						<div class="ht-ctc-df-contact" id="ht-ctc-df-contact-wrap">
							<input type="checkbox" id="ht-ctc-df-contact-opt-in" value="1" aria-labelledby="ht-ctc-df-contact-label <?php echo esc_attr( $ctc_email_name_id ); ?>">
							<span>
								<label for="ht-ctc-df-contact-opt-in" id="ht-ctc-df-contact-label">You may contact me about this at</label>
								<?php echo $ctc_email_field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built above from escaped parts. ?>
							</span>
						</div>
						<?php if ( '' !== $email ) { ?>
							<span class="screen-reader-text" id="ht-ctc-df-email-hint">Use a different email</span>
						<?php } ?>

						<details class="ht-ctc-df-more" id="ht-ctc-df-more">
							<?php // The one line of the disclosure that is always visible: keep it, the panel below starts closed. ?>
							<summary>Send shares your answer and a few technical details with holithemes.com.</summary>
							<div class="ht-ctc-df-more-body">
								<?php // Keep in step with post_feedback() and the readme's External Services section. ?>
								<h3>Sent when you click Send</h3>
								<ul>
									<li>The reason you chose, and your comment if you write one</li>
									<li>Plugin version, PRO version (if any), admin interface</li>
									<li>WordPress and PHP version, site language, multisite yes/no</li>
									<li>The version you first installed, and how many days ago</li>
								</ul>

								<h3>Not sent — unless you tick “You may contact me”</h3>
								<ul>
									<li>Your website address, and the email you choose</li>
								</ul>

								<h3>Never sent</h3>
								<ul>
									<li>Your settings, WhatsApp numbers, passwords, or anything about your visitors</li>
								</ul>

								<h3>Asking again</h3>
								<p class="ht-ctc-df-snooze-row">
									<label for="ht-ctc-df-snooze">Don’t ask again on this site for</label>
									<select id="ht-ctc-df-snooze">
										<?php foreach ( self::SNOOZE_CHOICES as $ctc_days ) { ?>
											<option value="<?php echo esc_attr( $ctc_days ); ?>" <?php selected( $ctc_days, self::SNOOZE_DAYS ); ?>><?php echo esc_html( self::days_label( $ctc_days ) ); ?></option>
										<?php } ?>
									</select>
								</p>

								<p class="ht-ctc-df-policy">
									<a href="<?php echo esc_url( self::PRIVACY_URL ); ?>" target="_blank" rel="noopener noreferrer">Privacy policy<span class="screen-reader-text"> (opens in a new tab)</span></a>
								</p>
							</div>
						</details>
					</div>

					<div class="ht-ctc-df-foot">
						<button type="button" class="button button-link ht-ctc-df-skip" id="ht-ctc-df-skip">Skip & deactivate</button>
						<button type="button" class="button button-primary ht-ctc-df-submit" id="ht-ctc-df-submit">Send & deactivate</button>
					</div>

				</div>
			</div>
			<?php
		}

		/**
		 * Handle AJAX feedback submission.
		 *
		 * @return void
		 */
		public function ajax_submit() {

			// Verify nonce (CSRF).
			check_ajax_referer( self::ACTION, 'nonce' );

			if ( ! current_user_can( 'deactivate_plugin', HT_CTC_PLUGIN_BASENAME ) ) {
				wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
			}

			if ( ! $this->is_enabled() ) {
				wp_send_json_error( array( 'message' => 'Disabled' ), 403 );
			}

			// Skip: start the snooze the dialog promised, and send nothing out.
			if ( isset( $_POST['is_skip'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['is_skip'] ) ) ) {
				$this->start_snooze();
				wp_send_json_success( array( 'sent' => false ) );
			}

			$comment = isset( $_POST['comment'] ) ? sanitize_textarea_field( wp_unslash( $_POST['comment'] ) ) : '';
			$comment = $this->trim_comment( $comment );

			$reasons = self::reasons();
			$reason  = isset( $_POST['reason'] ) ? sanitize_key( wp_unslash( $_POST['reason'] ) ) : '';

			// No reason picked but something written: the comment is the answer.
			if ( '' === $reason && '' !== $comment ) {
				$reason = self::REASON_COMMENT_ONLY;
			}

			// comment_only is only ever a comment's reason: without one there is no answer.
			$is_comment_only = self::REASON_COMMENT_ONLY === $reason && '' !== $comment;

			if ( ! isset( $reasons[ $reason ] ) && ! $is_comment_only ) {
				wp_send_json_error( array( 'message' => 'Invalid reason' ), 400 );
			}

			// A reason alone is worth a reply, so the opt-in no longer needs a comment.
			$is_contact_opt_in = isset( $_POST['is_contact_opt_in'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['is_contact_opt_in'] ) );

			// The address the user chose in the dialog, not the account email.
			// Not a valid email: no contact, so neither it nor the site is sent.
			$contact_email = '';
			if ( $is_contact_opt_in && isset( $_POST['contact_email'] ) ) {
				$contact_email = sanitize_email( substr( trim( sanitize_text_field( wp_unslash( $_POST['contact_email'] ) ) ), 0, self::EMAIL_MAX ) );
				$contact_email = is_email( $contact_email ) ? $contact_email : '';
			}

			// Starts the snooze, which is_enabled() reads: it is also what stops
			// a duplicate, since a second request is rejected by that check above.
			$this->start_snooze();

			$sent = $this->post_feedback( $reason, $comment, $contact_email );

			if ( ! $sent ) {
				// The snooze stays: the user did answer, most failures (blocked
				// outbound HTTP, endpoint down) would repeat on every ask, and a
				// timed-out send may still have been delivered.
				wp_send_json_error( array( 'message' => 'Not delivered' ) );
			}

			wp_send_json_success( array( 'sent' => true ) );
		}

		/**
		 * Trim comment to maximum allowed length.
		 *
		 * @param string $comment Sanitized comment.
		 * @return string
		 */
		private function trim_comment( $comment ) {

			if ( function_exists( 'mb_substr' ) ) {
				$comment = mb_substr( $comment, 0, self::COMMENT_MAX );
			} else {
				// substr() can cut a multibyte character in half; drop the broken bytes.
				$comment = wp_check_invalid_utf8( substr( $comment, 0, self::COMMENT_MAX ), true );
			}

			return trim( $comment );
		}

		/**
		 * Send feedback payload to remote endpoint.
		 *
		 * @param string $reason            Selected reason key.
		 * @param string $comment           User comment.
		 * @param string $contact_email     Email the user chose to be contacted at; '' when they did not opt in.
		 * @return bool
		 */
		private function post_feedback( $reason, $comment, $contact_email ) {

			$plugin_details = HT_CTC_Utils::get_option( 'ht_ctc_plugin_details' );

			$payload = array(
				'Plugin'         => defined( 'HT_CTC_PLUGIN_SLUG' ) ? HT_CTC_PLUGIN_SLUG : 'click-to-chat-for-whatsapp',
				'Version'        => HT_CTC_VERSION,
				'Reason'         => $reason,
				'Comment'        => $comment,
				'WP_Version'     => get_bloginfo( 'version' ),
				'PHP_Version'    => PHP_VERSION,
				'Locale'         => get_locale(),
				'Multisite'      => is_multisite() ? 'yes' : 'no',
				'Admin_UI'       => defined( 'HT_CTC_ADMIN_UI' ) ? HT_CTC_ADMIN_UI : '',
				'PRO_Version'    => defined( 'HT_CTC_PRO_VERSION' ) ? HT_CTC_PRO_VERSION : '',
				'First_Version'  => is_array( $plugin_details ) && isset( $plugin_details['first_version'] ) ? sanitize_text_field( $plugin_details['first_version'] ) : '',
				'Days_Installed' => $this->days_installed( $plugin_details ),
				'Site'           => '',
				'Email'          => '',
			);

			if ( '' !== $contact_email ) {
				$payload['Site']  = esc_url_raw( home_url() );
				$payload['Email'] = $contact_email;
			}

			$response = wp_remote_post(
				self::ENDPOINT,
				array(
					'timeout'     => self::HTTP_TIMEOUT,
					'redirection' => 0,
					'blocking'    => true,
					'headers'     => array(
						'Content-Type' => 'application/json',
						'Accept'       => 'application/json',
					),
					'body'        => wp_json_encode( array( 'feedback_data' => $payload ) ),
					// Our own User-Agent on purpose. WordPress's default one is
					// "WordPress/<version>; <home_url()>", so leaving it would send
					// the site address with every answer — even when Site above is
					// empty because the user did not tick "You may contact me".
					// The site address leaves this server only with that opt-in.
					'user-agent'  => 'HT-CTC-Plugin/' . HT_CTC_VERSION,
				)
			);

			if ( is_wp_error( $response ) ) {
				HT_CTC_Utils::debug_log( 'deactivation feedback: request failed', array( 'error' => $response->get_error_message() ) );
				return false;
			}

			$code = (int) wp_remote_retrieve_response_code( $response );

			if ( $code < 200 || $code >= 300 ) {
				HT_CTC_Utils::debug_log( 'deactivation feedback: endpoint rejected', array( 'code' => $code ) );
				return false;
			}

			return true;
		}

		/**
		 * Number of days since first install.
		 *
		 * @param mixed $plugin_details The ht_ctc_plugin_details option value.
		 * @return int|string Days installed, or empty string if not available.
		 */
		private function days_installed( $plugin_details ) {

			if ( ! is_array( $plugin_details ) || ! isset( $plugin_details['first_install_time'] ) ) {
				return '';
			}

			$first_install = (int) $plugin_details['first_install_time'];

			if ( $first_install <= 0 || $first_install > time() ) {
				return '';
			}

			return (int) floor( ( time() - $first_install ) / DAY_IN_SECONDS );
		}
	}
} // END class_exists check
