<?php
/**
 * Admin Right Sidebar View
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Right_Sidebar' ) ) {

	/**
	 * Admin Right Sidebar Class.
	 *
	 * Renders the right sidebar panels: Support, Feedback, Live Preview, and PRO promotional widgets.
	 */
	class HT_CTC_Admin_Right_Sidebar {

		/**
		 * Display the right sidebar.
		 *
		 * @return void
		 */
		public static function display() {
			?>
			<!-- Right Sidebar -->
			<aside id="right-sidebar" class="right-sidebar">
				<div class="right-sidebar-header">
					<div class="sidebar-tabs" role="tablist" aria-label="Sidebar panels">
						<button type="button" id="sidebar-tabbtn-help" class="sidebar-tab-btn active" data-sidebar-tab="help" title="Support" aria-label="Support" role="tab" aria-controls="sidebar-tab-help" aria-selected="true" tabindex="0">
							<span class="dashicons dashicons-sos" aria-hidden="true"></span>
							<span class="sidebar-tab-label">Support</span>
						</button>
						<button type="button" id="sidebar-tabbtn-feedback" class="sidebar-tab-btn" data-sidebar-tab="feedback" title="Feedback" aria-label="Feedback" role="tab" aria-controls="sidebar-tab-feedback" aria-selected="false" tabindex="-1">
							<span class="dashicons dashicons-megaphone" aria-hidden="true"></span>
							<span class="sidebar-tab-label">Feedback</span>
						</button>
						<button type="button" id="sidebar-tabbtn-preview" class="sidebar-tab-btn" data-sidebar-tab="preview" title="Preview" aria-label="Preview" role="tab" aria-controls="sidebar-tab-preview" aria-selected="false" tabindex="-1">
							<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
							<span class="sidebar-tab-label">Preview</span>
						</button>
					</div>
				</div>

				<div class="right-sidebar-content">

					<!-- Help & Contact Tab -->
					<div id="sidebar-tab-help" class="sidebar-tab-content active" role="tabpanel" aria-labelledby="sidebar-tabbtn-help" tabindex="0">
						<div class="sidebar-widget">
							<div class="widget-header">
								<span class="dashicons dashicons-email"></span>
								<h3><?php esc_html_e( 'Contact Us', 'click-to-chat-for-whatsapp' ); ?></h3>
							</div>
							<div class="widget-body">
								<p>Got a question? 😊 We’d love to hear from you!</p>
								<?php
								if ( defined( 'HT_CTC_PRO_VERSION' ) ) {
									$support_url = HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/support/' );
								} else {
									$support_url = 'https://wordpress.org/support/plugin/click-to-chat-for-whatsapp/#new-topic-0';
								}
								?>
								<a href="<?php echo esc_url( $support_url ); ?>" target="_blank" class="widget-btn widget-btn-outline"><?php esc_html_e( 'Contact Support', 'click-to-chat-for-whatsapp' ); ?></a>
							</div>
						</div>

						<?php if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) { ?>
						<div class="sidebar-widget feature-widget ctc-pro-promo">
							<div class="widget-header">
								<span class="dashicons dashicons-star-filled ctc-pro-icon"></span>
								<h3 class="ctc-pro-title">More in PRO</h3>
							</div>
							<div class="widget-body">
								<?php
								$pro_features = self::get_pro_features();
								?>
								<ul class="ctc-pro-feature-list">
									<?php
									// Initial render shows General tab features prior to client-side tab activation.
									foreach ( $pro_features as $pro_feature ) {
										$ctc_pro_show = in_array( 'general-settings', explode( ' ', $pro_feature['tabs'] ), true );
										$ctc_pro_link = HT_CTC_Utils::pro_url( 'sidebar', $pro_feature['key'], $pro_feature['url'] );
										?>
									<li data-tabs="<?php echo esc_attr( $pro_feature['tabs'] ); ?>" <?php echo ( $ctc_pro_show ) ? '' : 'hidden'; ?>>
										<div class="ctc-pro-feature-header">
											<span class="dashicons <?php echo esc_attr( $pro_feature['icon'] ); ?> ctc-pro-icon-small" aria-hidden="true"></span>
											<a href="<?php echo esc_url( $ctc_pro_link ); ?>" target="_blank" rel="noopener" class="ctc-pro-feature-link">
												<strong><?php echo esc_html( $pro_feature['title'] ); ?></strong>
												<span class="dashicons dashicons-external" aria-hidden="true"></span>
												<span class="screen-reader-text">(opens in a new tab)</span>
											</a>
										</div>
										<p class="ctc-pro-feature-desc"><?php echo esc_html( $pro_feature['desc'] ); ?></p>
									</li>
										<?php } ?>
								</ul>
								<?php
								$upgrade_url = HT_CTC_Utils::pro_url( 'sidebar' );
								?>
								<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener" class="widget-btn widget-btn-primary ctc-btn-gold">Upgrade to PRO <span class="dashicons dashicons-external" aria-hidden="true"></span><span class="screen-reader-text">(opens in a new tab)</span></a>
								<a href="#pro-features" class="ctc-pro-see-all">See all PRO features <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></a>
							</div>
						</div>
						<?php } ?>
					</div>

					<!-- Feedback Tab -->
					<div id="sidebar-tab-feedback" class="sidebar-tab-content" role="tabpanel" aria-labelledby="sidebar-tabbtn-feedback" tabindex="0">
						<div class="sidebar-widget feature-widget ctc-feedback-widget">
							<div class="widget-header">
								<span class="dashicons dashicons-megaphone"></span>
								<h3>Feedback & Ideas</h3>
							</div>
							<div class="widget-body">
								<p>You help shape what this plugin becomes. Tell us what to build next — or anything else on your mind:</p>
								<ul class="ctc-feedback-prompts">
									<li><span class="ctc-feedback-emoji" aria-hidden="true">🧭</span> What we should build next</li>
									<li><span class="ctc-feedback-emoji" aria-hidden="true">💡</span> An idea or feature you wish existed</li>
									<li><span class="ctc-feedback-emoji" aria-hidden="true">🤔</span> Something confusing or unclear</li>
									<li><span class="ctc-feedback-emoji" aria-hidden="true">🐛</span> Something not working (a bug)</li>
									<li><span class="ctc-feedback-emoji" aria-hidden="true">💬</span> Just casual feedback — we love it</li>
								</ul>
								<p class="ctc-feedback-note">No idea is too small. Every message is read by our team. 😊</p>
								<a href="<?php echo esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/support/' ) ); ?>" target="_blank" class="widget-btn widget-btn-primary">Share Your Idea</a>
							</div>
						</div>
					</div>

					<!-- Preview Tab -->
					<div id="sidebar-tab-preview" class="sidebar-tab-content" role="tabpanel" aria-labelledby="sidebar-tabbtn-preview" tabindex="0">
						<div class="sidebar-widget feature-widget preview-widget">
							<div class="widget-header">
								<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
								<h3>Live Preview</h3>
								<label class="switch" title="Show Preview">
									<input type="checkbox" checked id="ctc-preview-toggle" data-ctc-no-track="true" aria-label="Show Preview">
									<span class="slider"></span>
								</label>
							</div>
							<div class="widget-body">
								<p id="ctc-preview-note" class="help-text" aria-live="polite"></p>
								<p class="help-text preview-hint">Shows the widget at its configured position — updates as you edit, before saving.</p>
								<button type="button" id="ctc-preview-site-view" class="widget-btn widget-btn-outline" data-ctc-no-track="true">
									<span class="dashicons dashicons-admin-site-alt3" aria-hidden="true"></span>
									View on my site
								</button>
							</div>
						</div>
					</div>
					<?php do_action( 'ht_ctc_ah_admin_right_sidebar_panels' ); ?>

				</div>
			</aside>
			<?php
		}

		/**
		 * Features promoted in the sidebar PRO widget, filtered by active tab.
		 *
		 * @return array
		 */
		private static function get_pro_features() {
			return array(
				array(
					'tabs'  => 'default general-settings',
					'icon'  => 'dashicons-groups',
					'title' => 'Multi-Agent Support',
					'desc'  => 'Agents with a photo, a role, their own number and their own weekly hours.',
					'key'   => 'multi_agent',
					'url'   => 'https://holithemes.com/plugins/click-to-chat/multi-agent/',
				),
				array(
					'tabs'  => 'default display-settings woo-add-whatsapp-settings woo-overwrite-settings',
					'icon'  => 'dashicons-clock',
					'title' => __( 'Business Hours', 'click-to-chat-for-whatsapp' ),
					'desc'  => 'Several time slots a day. When you close, switch to an offline number.',
					'key'   => 'business_hours',
					'url'   => 'https://holithemes.com/plugins/click-to-chat/docs/business-hours-online-offline/',
				),
				array(
					'tabs'  => 'greetings-settings',
					'icon'  => 'dashicons-forms',
					'title' => 'Form Filling',
					'desc'  => 'Name, email or phone before the chat opens — 8 field types.',
					'key'   => 'greetings_form',
					'url'   => 'https://holithemes.com/plugins/click-to-chat/greetings-form/',
				),
				array(
					'tabs'  => 'greetings-settings',
					'icon'  => 'dashicons-calendar-alt',
					'title' => 'Date & Time Picker',
					'desc'  => 'A field in the greetings form — visitors pick a date and time that suits them.',
					'key'   => 'greetings_scheduler',
					'url'   => 'https://holithemes.com/plugins/click-to-chat/docs/greetings-form/',
				),
				array(
					'tabs'  => 'greetings-settings',
					'icon'  => 'dashicons-controls-play',
					'title' => 'Auto-Open Triggers',
					'desc'  => 'Open the greeting after a delay, or at a scroll percentage.',
					'key'   => 'greetings_actions',
					'url'   => 'https://holithemes.com/plugins/click-to-chat/greetings-actions/',
				),
				array(
					'tabs'  => 'display-settings',
					'icon'  => 'dashicons-admin-site-alt3',
					'title' => 'Country-Based Display',
					'desc'  => 'Show or hide the chat widget by each visitor\'s country.',
					'key'   => 'country_display',
					'url'   => 'https://holithemes.com/plugins/click-to-chat/display-based-on-country/',
				),
				array(
					'tabs'  => 'display-settings',
					'icon'  => 'dashicons-admin-users',
					'title' => 'Visitor Targeting & Delays',
					'desc'  => 'Choose who sees it by login status, and delay it by seconds or by scroll depth.',
					'key'   => 'schedule_triggers',
					'url'   => 'https://holithemes.com/plugins/click-to-chat/display/',
				),
				array(
					'tabs'  => 'analytics-settings',
					'icon'  => 'dashicons-chart-line',
					'title' => 'Google Ads Conversion',
					'desc'  => 'Send a conversion with your conversion ID and label whenever someone clicks to chat.',
					'key'   => 'google_ads',
					'url'   => 'https://holithemes.com/plugins/click-to-chat/google-ads-conversion/',
				),
				array(
					'tabs'  => 'analytics-settings',
					'icon'  => 'dashicons-facebook',
					'title' => 'Meta Conversions API',
					'desc'  => 'Send click events from your server, so a blocked browser pixel does not lose the conversion.',
					'key'   => 'meta_capi',
					'url'   => 'https://holithemes.com/plugins/click-to-chat/pricing/',
				),
				array(
					'tabs'  => 'analytics-settings advanced-settings',
					'icon'  => 'dashicons-cloud',
					'title' => 'Dynamic Values',
					'desc'  => 'Any URL parameter or cookie — [gclid], [utm_source] — in your webhook and events.',
					'key'   => 'webhooks',
					'url'   => 'https://holithemes.com/plugins/click-to-chat/webhooks/',
				),
				array(
					'tabs'  => 'advanced-settings woo-add-whatsapp-settings woo-overwrite-settings',
					'icon'  => 'dashicons-admin-page',
					'title' => 'Page-Level Overrides',
					'desc'  => 'One page with its own style, greeting, custom link or delay.',
					'key'   => 'page_level',
					'url'   => 'https://holithemes.com/plugins/click-to-chat/change-values-at-page-level/',
				),
				array(
					'tabs'  => 'general-settings',
					'icon'  => 'dashicons-randomize',
					'title' => 'Random & Sequential Numbers',
					'desc'  => 'Spread chats over several numbers, at random or in rotation.',
					'key'   => 'random_number',
					'url'   => 'https://holithemes.com/plugins/click-to-chat/random-number/',
				),
			);
		}
	}
}
