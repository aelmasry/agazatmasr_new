<?php
/**
 * Admin Navigation View (Left Sidebar)
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Nav' ) ) {

	/**
	 * Admin Navigation Class.
	 *
	 * Renders the left sidebar navigation, including the main menu and drill-down submenus.
	 */
	class HT_CTC_Admin_Nav {

		/**
		 * Display the sidebar navigation.
		 *
		 * @return void
		 */
		public static function display() {
			$os = HT_CTC_Utils::get_option( 'ht_ctc_othersettings' );

			$is_group_enabled = isset( $os['enable_group'] ) ? true : false;
			$is_share_enabled = isset( $os['enable_share'] ) ? true : false;

			$main_nav_items = self::build_main_nav_items();

			self::render_nav( $main_nav_items, $is_group_enabled, $is_share_enabled );
		}

		/**
		 * Build the left-sidebar main navigation items.
		 *
		 * Each item is one entry in the top-level menu. `drill-down` items open a
		 * nested menu (defined in render_nav()) instead of switching tabs.
		 *
		 * @return array
		 */
		private static function build_main_nav_items() {
			$main_nav_items = array(
				array(
					'tab'    => 'general-settings',
					'icon'   => 'dashicons-admin-generic',
					'label'  => 'General',
					'active' => true,
				),
				array(
					'tab'   => 'greetings-settings',
					'icon'  => 'dashicons-format-chat',
					'label' => 'Greetings',
				),
				array(
					'tab'   => 'display-settings',
					'icon'  => 'dashicons-desktop',
					'label' => __( 'Display', 'click-to-chat-for-whatsapp' ),
				),
				array(
					'tab'   => 'analytics-settings',
					'icon'  => 'dashicons-chart-bar',
					'label' => __( 'Analytics', 'click-to-chat-for-whatsapp' ),
				),
			);

			if ( class_exists( 'WooCommerce' ) ) {
				$main_nav_items[] = array(
					'type'   => 'drill-down',
					'target' => 'woo-menu',
					'icon'   => 'dashicons-cart',
					'label'  => 'WooCommerce',
				);
			}

			// WooCommerce is handled via a drill-down menu.
			$main_nav_items[] = array(
				'tab'   => 'advanced-settings',
				'icon'  => 'dashicons-admin-settings',
				'label' => 'Advanced',
			);

			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				$main_nav_items[] = array(
					'tab'         => 'pro-features',
					'icon'        => 'dashicons-star-filled',
					'label'       => 'Upgrade to Pro',
					'icon_style'  => 'color: #eab308;',
					'label_style' => 'color: #eab308; font-weight: 600;',
				);
			}

			return apply_filters( 'ht_ctc_fh_admin_nav_main_menu_items', $main_nav_items );
		}

		/**
		 * Render the left sidebar (main menu + drill-down sub-menus).
		 *
		 * Group/Share drill-down items have init-display gating tied to the
		 * `enable_group` / `enable_share` settings; the JS-side `data-watch`
		 * keeps them in sync after toggle without a page reload.
		 *
		 * @param array $main_nav_items     Top-level menu items from build_main_nav_items().
		 * @param bool  $is_group_enabled   Whether the group feature is enabled.
		 * @param bool  $is_share_enabled   Whether the share feature is enabled.
		 * @return void
		 */
		private static function render_nav( $main_nav_items, $is_group_enabled, $is_share_enabled ) {
			?>
			<!-- Sidebar - menu -->
			<aside id="sidebar" class="sidebar">
				<div class="sidebar-header">
					<div class="sidebar-title"><?php echo esc_html__( 'Settings', 'click-to-chat-for-whatsapp' ); ?></div>
					<button type="button" id="close-sidebar" class="close-sidebar"
						aria-label="Close settings menu">
						<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
					</button>
				</div>
				<?php // Navigation items map via data-tab to matching section IDs. ?>
				<nav class="sidebar-nav">
					<div class="sidebar-menus-container">
						<div id="main-menu" class="sidebar-menu active">
							<ul>
								<?php
								foreach ( $main_nav_items as $item ) {
									$active_class = ! empty( $item['active'] ) ? ' active' : '';
									$icon_style   = ! empty( $item['icon_style'] ) ? $item['icon_style'] : '';
									$label_style  = ! empty( $item['label_style'] ) ? $item['label_style'] : '';

									if ( ! empty( $item['type'] ) && 'drill-down' === $item['type'] ) {
										?>
										<li>
											<button type="button" class="drill-down-btn" data-target="<?php echo esc_attr( $item['target'] ); ?>" title="<?php echo esc_attr( wp_strip_all_tags( $item['label'] ) ); ?>">
												<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>" style="<?php echo esc_attr( $icon_style ); ?>"></span>
												<span style="<?php echo esc_attr( $label_style ); ?>"><?php echo esc_html( $item['label'] ); ?></span>
											</button>
										</li>
										<?php
									} else {
										?>
										<li>
											<button type="button" class="nav-item<?php echo esc_attr( $active_class ); ?>" data-tab="<?php echo esc_attr( $item['tab'] ); ?>" title="<?php echo esc_attr( wp_strip_all_tags( $item['label'] ) ); ?>">
												<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>" style="<?php echo esc_attr( $icon_style ); ?>"></span>
												<span style="<?php echo esc_attr( $label_style ); ?>"><?php echo esc_html( $item['label'] ); ?></span>
											</button>
										</li>
										<?php
									}
								}
								?>
							<?php /* Group/Share nav items are part of the same <ul> above */ ?>
								<li data-watch="#enable_group" data-show-when="1"
								<?php
								if ( ! $is_group_enabled ) {
									echo 'class="ctc_init_display_none"'; }
								?>
								>
									<button type="button" class="drill-down-btn" data-target="group-menu" title="<?php esc_attr_e( 'Group Settings', 'click-to-chat-for-whatsapp' ); ?>">
										<span class="dashicons dashicons-groups"></span>
										<span>Group</span>
									</button>
								</li>
								<li data-watch="#enable_share" data-show-when="1"
								<?php
								if ( ! $is_share_enabled ) {
									echo 'class="ctc_init_display_none"'; }
								?>
								>
									<button type="button" class="drill-down-btn" data-target="share-menu" title="<?php esc_attr_e( 'Share', 'click-to-chat-for-whatsapp' ); ?>">
										<span class="dashicons dashicons-share"></span>
										<span><?php esc_html_e( 'Share', 'click-to-chat-for-whatsapp' ); ?></span>
									</button>
								</li>
							</ul>
						</div>

						<?php if ( class_exists( 'WooCommerce' ) ) { ?>
						<!-- WooCommerce Drill-Down Menu -->
						<div id="woo-menu" class="sidebar-menu">
							<button type="button" class="drill-down-back-btn" data-target="main-menu" title="Back to Main">
								<span class="dashicons dashicons-arrow-left-alt2"></span>
								<span>Back to Main</span>
							</button>
							<ul>
								<li>
									<button type="button" class="nav-item" data-tab="woo-add-whatsapp-settings" title="<?php esc_attr_e( 'Add WhatsApp', 'click-to-chat-for-whatsapp' ); ?>">
										<span class="dashicons dashicons-plus"></span>
										<span><?php esc_html_e( 'Add WhatsApp', 'click-to-chat-for-whatsapp' ); ?></span>
									</button>
								</li>
								<li>
									<button type="button" class="nav-item" data-tab="woo-overwrite-settings" title="Overwrite settings">
										<span class="dashicons dashicons-edit"></span>
										<span>Overwrite settings</span>
									</button>
								</li>
							</ul>
						</div>
						<?php } ?>

						<!-- Group Drill-Down Menu -->
						<div id="group-menu" class="sidebar-menu" data-watch="#enable_group" data-show-when="1">
							<button type="button" class="drill-down-back-btn" data-target="main-menu" title="Back to Main">
								<span class="dashicons dashicons-arrow-left-alt2"></span>
								<span>Back to Main</span>
							</button>
							<ul>
								<li>
									<button type="button" class="nav-item" data-tab="group-settings" title="<?php esc_attr_e( 'Group Settings', 'click-to-chat-for-whatsapp' ); ?>">
										<span class="dashicons dashicons-groups"></span>
										<span><?php esc_html_e( 'Group Settings', 'click-to-chat-for-whatsapp' ); ?></span>
									</button>
								</li>
							</ul>
						</div>

						<!-- Share Drill-Down Menu -->
						<div id="share-menu" class="sidebar-menu" data-watch="#enable_share" data-show-when="1">
							<button type="button" class="drill-down-back-btn" data-target="main-menu" title="Back to Main">
								<span class="dashicons dashicons-arrow-left-alt2"></span>
								<span>Back to Main</span>
							</button>
							<ul>
								<li>
									<button type="button" class="nav-item" data-tab="share-settings" title="<?php echo esc_attr( sprintf( '%1$s %2$s', __( 'Share', 'click-to-chat-for-whatsapp' ), __( 'Settings', 'click-to-chat-for-whatsapp' ) ) ); ?>">
										<span class="dashicons dashicons-share"></span>
										<span><?php echo esc_html( sprintf( '%1$s %2$s', __( 'Share', 'click-to-chat-for-whatsapp' ), __( 'Settings', 'click-to-chat-for-whatsapp' ) ) ); ?></span>
									</button>
								</li>
							</ul>
						</div>

					</div>
				</nav>
			</aside>
			<?php
		}
	}
}
