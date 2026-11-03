<?php
/**
 * Admin Settings Panels View (Main Content)
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 4.46
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Panels' ) ) {

	/**
	 * Admin Settings Panels Class.
	 *
	 * Renders the main content area: one `<section class="settings-panel">` per
	 * tab, with the General panel's fields inlined.
	 */
	class HT_CTC_Admin_Panels {

		/**
		 * Render the main content area (settings panel sections + skeleton placeholders).
		 *
		 * The `general-settings` panel is hydrated inline via window.ht_ctc_fields_general_settings;
		 * other panels fetch their field definitions on first tab activation.
		 *
		 * @return void
		 */
		public static function display() {

			HT_CTC_Utils::load_file( 'new/admin2/views/class-ht-ctc-admin-settings-fields.php' );

			// Initially load general settings fields. Remaining tabs load dynamically via REST API.
			$general_settings_fields = HT_CTC_Admin_Settings_Fields::general_settings();
			$settings_panels         = self::build_settings_panels();
			?>
			<!-- Main Content -->
			<main class="main-content">

				<?php
				settings_errors();
				?>

				<script>
					window.ht_ctc_fields_general_settings = <?php echo wp_json_encode( $general_settings_fields ); ?>;
				</script>

				<?php
				// Remaining panels are hydrated dynamically by App.loadTabSettings().
				foreach ( $settings_panels as $panel ) {
					$active_class = ! empty( $panel['active'] ) ? ' active' : '';

					// Comma-separated auxiliary / contextual field groups to preload alongside the tab.
					$extra_groups = ! empty( $panel['groups'] ) ? (string) $panel['groups'] : '';
					?>
					<section id="<?php echo esc_attr( $panel['id'] ); ?>" class="settings-panel<?php echo esc_attr( $active_class ); ?>" data-group="<?php echo esc_attr( $panel['group'] ); ?>" data-groups="<?php echo esc_attr( $extra_groups ); ?>" data-loaded="false">
						<div class="panel-header">
							<div class="panel-title"><?php echo esc_html( $panel['title'] ); ?></div>
							<p><?php echo esc_html( $panel['desc'] ); ?></p>
						</div>
						<div class="fields-container">
							<div class="ctc-loading-skeleton">
								<div class="ctc-skeleton-row"></div>
								<div class="ctc-skeleton-row"></div>
								<div class="ctc-skeleton-row"></div>
							</div>
						</div>
					</section>
					<?php
				}
				?>

				<?php if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) { ?>
				<section id="pro-features" class="settings-panel" data-group="pro_features" data-loaded="false">
					<div class="fields-container ctc-pro-features-wrapper" style="padding:0;">
						<!-- Loading placeholder -->
						<div class="ctc-loading-skeleton" style="padding: 30px;">
							<div class="ctc-skeleton-header"></div>
							<div class="ctc-skeleton-row" style="height: 120px; margin-top: 20px;"></div>
							<div class="ctc-skeleton-row" style="height: 120px;"></div>
							<div class="ctc-skeleton-row" style="height: 120px;"></div>
						</div>
					</div>
				</section>
				<?php } ?>
			</main>
			<?php
		}

		/**
		 * Build the settings-panel definitions rendered in the main content area.
		 *
		 * Each panel is rendered as a `<section class="settings-panel">` container.
		 * Field definitions are dynamically rendered by App.js either from the inline
		 * general settings payload or fetched on demand/preloaded via REST API.
		 *
		 * Array keys:
		 *   'id'     - Section element ID (e.g. 'general-settings').
		 *   'group'  - Primary settings group (renders as data-group).
		 *   'groups' - (Optional) Comma-separated auxiliary/contextual groups to preload (data-groups).
		 *   'title'  - Heading text.
		 *   'desc'   - Description text.
		 *   'active' - (Optional) Set true if panel is active on initial load.
		 *
		 * @return array
		 */
		private static function build_settings_panels() {
			$settings_panels = array(
				array(
					'id'     => 'general-settings',
					'group'  => 'general_settings',
					'groups' => 'contextual_styles',
					'title'  => 'General ' . __( 'Settings', 'click-to-chat-for-whatsapp' ),
					'desc'   => 'Your ' . __( 'WhatsApp Number', 'click-to-chat-for-whatsapp' ) . ' and ' . __( 'Pre-filled Message', 'click-to-chat-for-whatsapp' ),
					'active' => true,
				),
				array(
					'id'     => 'greetings-settings',
					'group'  => 'greetings_settings',
					'groups' => 'contextual_greetings,contextual_styles',
					'title'  => 'Greetings ' . __( 'Settings', 'click-to-chat-for-whatsapp' ),
					'desc'   => 'Add a popup dialog that engages visitors before they start chatting',
				),
				array(
					'id'    => 'display-settings',
					'group' => 'display_settings',
					'title' => __( 'Display Settings', 'click-to-chat-for-whatsapp' ),
					'desc'  => 'Control where and when the chat widget appears',
				),
				array(
					'id'    => 'analytics-settings',
					'group' => 'analytics_settings',
					'title' => sprintf( '%1$s %2$s', __( 'Analytics', 'click-to-chat-for-whatsapp' ), __( 'Settings', 'click-to-chat-for-whatsapp' ) ),
					'desc'  => 'Track chat interactions in Google Analytics and Meta Pixel',
				),
			);

			if ( class_exists( 'WooCommerce' ) ) {
				$settings_panels[] = array(
					'id'     => 'woo-add-whatsapp-settings',
					'group'  => 'woo_add_whatsapp_settings',
					'groups' => 'contextual_styles',
					'title'  => __( 'Add WhatsApp', 'click-to-chat-for-whatsapp' ),
					'desc'   => 'Add WhatsApp in WooCommerce Pages (single product, Shop)',
				);
				$settings_panels[] = array(
					'id'     => 'woo-overwrite-settings',
					'group'  => 'woo_overwrite_settings',
					'groups' => 'contextual_styles',
					'title'  => 'WooCommerce Overwrite Settings',
					'desc'   => 'Overwrite Settings for WooCommerce Pages',
				);
			}

			$settings_panels[] = array(
				'id'    => 'advanced-settings',
				'group' => 'advanced_settings',
				'title' => sprintf( '%1$s %2$s', __( 'Advanced', 'click-to-chat-for-whatsapp' ), __( 'Settings', 'click-to-chat-for-whatsapp' ) ),
				'desc'  => vsprintf( '%1$s, %2$s, and %3$s options', array( __( 'Animations', 'click-to-chat-for-whatsapp' ), __( 'Entry Effects', 'click-to-chat-for-whatsapp' ), __( 'Notification Badge', 'click-to-chat-for-whatsapp' ) ) ),
			);
			$settings_panels[] = array(
				'id'    => 'support-settings',
				'group' => 'support_settings',
				'title' => sprintf( '%1$s & %2$s', str_replace( 'Contact ', '', __( 'Contact Support', 'click-to-chat-for-whatsapp' ) ), __( 'FAQ', 'click-to-chat-for-whatsapp' ) ),
				'desc'  => 'Find answers to common questions, and troubleshoot issues',
			);

			$settings_panels[] = array(
				'id'    => 'group-settings',
				'group' => 'group_settings',
				'title' => __( 'Group Settings', 'click-to-chat-for-whatsapp' ),
				'desc'  => 'Configure the WhatsApp group widget for your site',
			);

			$settings_panels[] = array(
				'id'    => 'share-settings',
				'group' => 'share_settings',
				'title' => sprintf( '%1$s %2$s', __( 'Share', 'click-to-chat-for-whatsapp' ), __( 'Settings', 'click-to-chat-for-whatsapp' ) ),
				'desc'  => 'Let visitors share your page directly via WhatsApp',
			);

			return apply_filters( 'ht_ctc_fh_admin_settings_panels', $settings_panels );
		}
	}
}
