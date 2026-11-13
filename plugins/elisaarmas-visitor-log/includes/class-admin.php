<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EA_VL_Admin {
	const CAP  = 'manage_options';
	const SLUG = 'ea-visitor-log';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_ea_vl_block', array( __CLASS__, 'handle_block' ) );
		add_action( 'admin_post_ea_vl_unblock', array( __CLASS__, 'handle_unblock' ) );
		add_action( 'admin_post_ea_vl_delete', array( __CLASS__, 'handle_delete' ) );
		add_action( 'admin_post_ea_vl_delete_activity', array( __CLASS__, 'handle_delete_activity' ) );
		add_action( 'admin_post_ea_vl_block_manual', array( __CLASS__, 'handle_block_manual' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function menu() {
		add_menu_page(
			'Visitor Log',
			'Visitor Log',
			self::CAP,
			self::SLUG,
			array( __CLASS__, 'render_page' ),
			'dashicons-shield',
			58
		);
	}

	public static function assets( $hook ) {
		if ( 'toplevel_page_' . self::SLUG !== $hook ) {
			return;
		}
		wp_add_inline_style(
			'wp-admin',
			'
			.ea-vl-wrap { max-width: 1200px; }
			.ea-vl-stats { display:flex; gap:12px; margin:16px 0 20px; flex-wrap:wrap; }
			.ea-vl-stat { background:#fff; border:1px solid #c3c4c7; border-radius:8px; padding:12px 16px; min-width:140px; }
			.ea-vl-stat strong { display:block; font-size:20px; line-height:1.2; }
			.ea-vl-table td, .ea-vl-table th { vertical-align: top; }
			.ea-vl-msg { max-width:280px; white-space:pre-wrap; word-break:break-word; color:#50575e; font-size:12px; }
			.ea-vl-loc { font-size:12px; color:#50575e; }
			.ea-vl-badge { display:inline-block; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:600; }
			.ea-vl-badge.ok { background:#d1fadf; color:#05603a; }
			.ea-vl-badge.blocked { background:#fce8e6; color:#b42318; }
			.ea-vl-badge.event { background:#e8f0fe; color:#174ea6; }
			.ea-vl-blocked-box { background:#fff; border:1px solid #c3c4c7; border-radius:8px; padding:16px; margin-bottom:20px; }
			.ea-vl-blocked-list { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
			.ea-vl-chip { background:#f0f0f1; border-radius:999px; padding:4px 10px; font-family:monospace; font-size:12px; display:inline-flex; align-items:center; gap:8px; }
			.ea-vl-nav { margin: 12px 0 18px; }
			.ea-vl-mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size:12px; }
			'
		);
	}

	public static function render_page() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'elisaarmas-visitor-log' ) );
		}

		EA_VL_DB::activate();

		$tab     = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'messages';
		if ( ! in_array( $tab, array( 'messages', 'activity', 'visits' ), true ) ) {
			$tab = 'messages';
		}

		$page    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
		$per     = 25;
		$offset  = ( $page - 1 ) * $per;
		$filter_ip = isset( $_GET['ip'] ) ? sanitize_text_field( wp_unslash( $_GET['ip'] ) ) : '';
		if ( $filter_ip && ! filter_var( $filter_ip, FILTER_VALIDATE_IP ) ) {
			$filter_ip = '';
		}

		$total_msg  = EA_VL_DB::count_logs();
		$total_act  = EA_VL_DB::count_activities( $filter_ip );
		$blocked    = EA_VL_DB::get_blocked_ips();
		$view_id    = isset( $_GET['view'] ) ? absint( $_GET['view'] ) : 0;
		$view       = ( 'messages' === $tab && $view_id ) ? EA_VL_DB::get_log( $view_id ) : null;
		$notice     = isset( $_GET['ea_vl_notice'] ) ? sanitize_key( (string) wp_unslash( $_GET['ea_vl_notice'] ) ) : '';

		$base = admin_url( 'admin.php?page=' . self::SLUG );
		?>
		<div class="wrap ea-vl-wrap">
			<h1>Visitor Log <span style="font-size:13px;font-weight:400;color:#646970;">elisaarmas.com · admin only</span></h1>
			<p>Contact messages and on-site activity (clicks, modal, form). Blocked visitors get a 403 and cannot email you.</p>

			<?php if ( $notice ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html( self::notice_text( $notice ) ); ?></p></div>
			<?php endif; ?>

			<?php
			$today = current_time( 'Y-m-d' );
			$spans = array(
				'Today'        => $today . ' 00:00:00',
				'Last 7 days'  => gmdate( 'Y-m-d 00:00:00', strtotime( $today . ' -6 days' ) ),
				'Last 30 days' => gmdate( 'Y-m-d 00:00:00', strtotime( $today . ' -29 days' ) ),
				'All time'     => '',
			);
			?>
			<h2 style="margin-bottom:0;">Website visits</h2>
			<p style="margin-top:4px;color:#646970;">Page views by people (search engines and bots not counted; your own visits while logged in are skipped). Unique visitors in brackets.</p>
			<div class="ea-vl-stats">
				<?php foreach ( $spans as $label => $since ) : $t = EA_VL_DB::visit_totals( $since ); ?>
					<div class="ea-vl-stat"><strong><?php echo esc_html( number_format_i18n( $t['views'] ) ); ?></strong><?php echo esc_html( $label ); ?> <span style="color:#646970;">(<?php echo esc_html( number_format_i18n( $t['visitors'] ) ); ?>)</span></div>
				<?php endforeach; ?>
			</div>

			<div class="ea-vl-stats">
				<div class="ea-vl-stat"><strong><?php echo esc_html( (string) $total_msg ); ?></strong>Form messages</div>
				<div class="ea-vl-stat"><strong><?php echo esc_html( (string) EA_VL_DB::count_activities() ); ?></strong>Activity events</div>
				<div class="ea-vl-stat"><strong><?php echo esc_html( (string) count( $blocked ) ); ?></strong>Blocked IPs</div>
			</div>

			<nav class="nav-tab-wrapper ea-vl-nav">
				<a class="nav-tab <?php echo 'messages' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $base . '&tab=messages' ); ?>">Messages</a>
				<a class="nav-tab <?php echo 'activity' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $base . '&tab=activity' ); ?>">Activity</a>
				<a class="nav-tab <?php echo 'visits' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $base . '&tab=visits' ); ?>">Visits by day</a>
			</nav>

			<div class="ea-vl-blocked-box">
				<h2 style="margin-top:0;">Blocked IPs</h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
					<?php wp_nonce_field( 'ea_vl_block_manual' ); ?>
					<input type="hidden" name="action" value="ea_vl_block_manual" />
					<input type="text" name="ip" class="regular-text" placeholder="e.g. 203.177.x.x" required />
					<button type="submit" class="button button-secondary">Block IP</button>
				</form>
				<?php if ( empty( $blocked ) ) : ?>
					<p style="margin:12px 0 0;color:#646970;">No IPs blocked yet.</p>
				<?php else : ?>
					<div class="ea-vl-blocked-list">
						<?php foreach ( $blocked as $ip ) : ?>
							<span class="ea-vl-chip">
								<?php echo esc_html( $ip ); ?>
								<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'activity', 'ip' => $ip ), $base ) ); ?>">Activity</a>
								<a href="<?php echo esc_url( self::action_url( 'ea_vl_unblock', array( 'ip' => $ip ) ) ); ?>">Unblock</a>
							</span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( 'visits' === $tab ) : ?>
				<?php self::render_visits_tab(); ?>
			<?php elseif ( 'activity' === $tab ) : ?>
				<?php self::render_activity_tab( $per, $offset, $page, $total_act, $filter_ip, $base ); ?>
			<?php else : ?>
				<?php self::render_messages_tab( $per, $offset, $page, $total_msg, $view, $base ); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	private static function render_visits_tab() {
		$days = EA_VL_DB::visits_by_day( 30 );
		$top  = EA_VL_DB::top_pages( 30, 10 );
		?>
		<div style="display:flex;gap:24px;flex-wrap:wrap;align-items:flex-start;">
			<div style="flex:1 1 420px;">
				<h2>Last 30 days</h2>
				<table class="widefat striped ea-vl-table">
					<thead><tr><th>Day</th><th>Page views</th><th>Visitors</th><th>Bot views</th></tr></thead>
					<tbody>
					<?php if ( ! $days ) : ?>
						<tr><td colspan="4">No visits recorded yet.</td></tr>
					<?php endif; ?>
					<?php foreach ( $days as $d ) : ?>
						<tr>
							<td><?php echo esc_html( date_i18n( 'D j M Y', strtotime( $d->day ) ) ); ?></td>
							<td><strong><?php echo esc_html( number_format_i18n( (int) $d->views ) ); ?></strong></td>
							<td><?php echo esc_html( number_format_i18n( (int) $d->visitors ) ); ?></td>
							<td style="color:#646970;"><?php echo esc_html( number_format_i18n( (int) $d->bots ) ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div style="flex:1 1 360px;">
				<h2>Top pages (30 days)</h2>
				<table class="widefat striped ea-vl-table">
					<thead><tr><th>Page</th><th>Views</th></tr></thead>
					<tbody>
					<?php if ( ! $top ) : ?>
						<tr><td colspan="2">No visits recorded yet.</td></tr>
					<?php endif; ?>
					<?php foreach ( $top as $row ) : ?>
						<tr>
							<td class="ea-vl-mono"><?php echo esc_html( (string) wp_parse_url( $row->url, PHP_URL_PATH ) ?: '/' ); ?></td>
							<td><?php echo esc_html( number_format_i18n( (int) $row->views ) ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}

	private static function render_activity_tab( $per, $offset, $page, $total, $filter_ip, $base ) {
		$logs  = EA_VL_DB::get_activities( $per, $offset, $filter_ip );
		$pages = max( 1, (int) ceil( max( 1, $total ) / $per ) );
		if ( 0 === $total ) {
			$pages = 1;
		}
		?>
		<div class="ea-vl-blocked-box" style="margin-bottom:16px;">
			<form method="get" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::SLUG ); ?>" />
				<input type="hidden" name="tab" value="activity" />
				<label for="ea-vl-ip-filter"><strong>Filter by IP</strong></label>
				<input type="text" id="ea-vl-ip-filter" name="ip" value="<?php echo esc_attr( $filter_ip ); ?>" class="regular-text" placeholder="All IPs" />
				<button type="submit" class="button">Filter</button>
				<?php if ( $filter_ip ) : ?>
					<a class="button" href="<?php echo esc_url( $base . '&tab=activity' ); ?>">Clear</a>
				<?php endif; ?>
			</form>
			<p style="margin:10px 0 0;color:#646970;font-size:12px;">Tracks page views, button/link clicks, contact modal open/close, and form submit attempts. Your own admin browsing is not logged.</p>
		</div>

		<table class="widefat striped ea-vl-table">
			<thead>
				<tr>
					<th>When</th>
					<th>Event</th>
					<th>Label / element</th>
					<th>Location</th>
					<th>IP</th>
					<th>Page</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $logs ) ) : ?>
				<tr><td colspan="7">No activity yet. Open the site in a private window (not logged in as admin) and click around — events will appear here.</td></tr>
			<?php else : ?>
				<?php foreach ( $logs as $row ) : ?>
					<?php $is_blocked = $row->ip && EA_VL_DB::is_blocked( $row->ip ); ?>
					<tr>
						<td><?php echo esc_html( $row->created_at ); ?></td>
						<td><span class="ea-vl-badge event"><?php echo esc_html( $row->event_type ); ?></span></td>
						<td>
							<strong><?php echo esc_html( $row->event_label ?: '—' ); ?></strong>
							<?php if ( $row->element_text && $row->element_text !== $row->event_label ) : ?>
								<div class="ea-vl-loc"><?php echo esc_html( $row->element_text ); ?></div>
							<?php endif; ?>
							<?php if ( $row->element_href ) : ?>
								<div class="ea-vl-mono"><?php echo esc_html( $row->element_href ); ?></div>
							<?php endif; ?>
						</td>
						<td class="ea-vl-loc"><?php echo esc_html( self::format_location( $row ) ); ?><?php if ( $row->isp ) : ?><br><?php echo esc_html( $row->isp ); ?><?php endif; ?></td>
						<td>
							<code><?php echo esc_html( $row->ip ?: '—' ); ?></code>
							<?php if ( $row->ip ) : ?>
								<div><a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'activity', 'ip' => $row->ip ), $base ) ); ?>">Filter</a></div>
							<?php endif; ?>
						</td>
						<td class="ea-vl-mono"><?php echo esc_html( wp_html_excerpt( (string) $row->page_url, 60, '…' ) ); ?></td>
						<td>
							<?php if ( $row->ip && ! $is_blocked ) : ?>
								<a class="button button-small" href="<?php echo esc_url( self::action_url( 'ea_vl_block', array( 'ip' => $row->ip ) ) ); ?>" onclick="return confirm('Block <?php echo esc_js( $row->ip ); ?>?');">Block IP</a>
							<?php elseif ( $row->ip && $is_blocked ) : ?>
								<span class="ea-vl-badge blocked">Blocked</span>
								<a class="button button-small" href="<?php echo esc_url( self::action_url( 'ea_vl_unblock', array( 'ip' => $row->ip ) ) ); ?>">Unblock</a>
							<?php endif; ?>
							<a class="button button-small" href="<?php echo esc_url( self::action_url( 'ea_vl_delete_activity', array( 'id' => $row->id ) ) ); ?>" onclick="return confirm('Delete this event?');">Delete</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
		<?php self::render_pagination( $page, $pages, array( 'tab' => 'activity', 'ip' => $filter_ip ) ); ?>
		<?php
	}

	private static function render_messages_tab( $per, $offset, $page, $total, $view, $base ) {
		$logs  = EA_VL_DB::get_logs( $per, $offset );
		$pages = max( 1, (int) ceil( max( 1, $total ) / $per ) );
		if ( 0 === $total ) {
			$pages = 1;
		}

		if ( $view ) :
			?>
			<div class="ea-vl-blocked-box">
				<h2 style="margin-top:0;">Message #<?php echo esc_html( (string) $view->id ); ?></h2>
				<p><strong>When:</strong> <?php echo esc_html( $view->created_at ); ?></p>
				<p><strong>Name:</strong> <?php echo esc_html( $view->name ); ?></p>
				<p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr( $view->email ); ?>"><?php echo esc_html( $view->email ); ?></a></p>
				<p><strong>IP:</strong> <code><?php echo esc_html( $view->ip ); ?></code>
					<?php if ( $view->ip && ! EA_VL_DB::is_blocked( $view->ip ) ) : ?>
						· <a href="<?php echo esc_url( self::action_url( 'ea_vl_block', array( 'ip' => $view->ip, 'id' => $view->id ) ) ); ?>">Block this IP</a>
					<?php elseif ( $view->ip ) : ?>
						· <span class="ea-vl-badge blocked">Blocked</span>
					<?php endif; ?>
					<?php if ( $view->ip ) : ?>
						· <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'activity', 'ip' => $view->ip ), $base ) ); ?>">See their activity</a>
					<?php endif; ?>
				</p>
				<p><strong>Location:</strong> <?php echo esc_html( self::format_location( $view ) ); ?></p>
				<p><strong>ISP:</strong> <?php echo esc_html( $view->isp ?: '—' ); ?></p>
				<p><strong>Status:</strong> <?php echo esc_html( $view->status ); ?></p>
				<p><strong>User agent:</strong> <span class="ea-vl-loc"><?php echo esc_html( $view->user_agent ?: '—' ); ?></span></p>
				<p><strong>Message:</strong></p>
				<div class="ea-vl-msg" style="max-width:100%;background:#f6f7f7;padding:12px;border-radius:6px;"><?php echo esc_html( $view->message ); ?></div>
				<p style="margin-top:16px;"><a class="button" href="<?php echo esc_url( $base . '&tab=messages' ); ?>">← Back to list</a></p>
			</div>
			<?php
		endif;
		?>

		<table class="widefat striped ea-vl-table">
			<thead>
				<tr>
					<th>Date</th>
					<th>Name / Email</th>
					<th>Location</th>
					<th>IP</th>
					<th>Status</th>
					<th>Message</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $logs ) ) : ?>
				<tr><td colspan="7">No contact form submissions logged yet.</td></tr>
			<?php else : ?>
				<?php foreach ( $logs as $row ) : ?>
					<?php $is_blocked = $row->ip && EA_VL_DB::is_blocked( $row->ip ); ?>
					<tr>
						<td><?php echo esc_html( $row->created_at ); ?></td>
						<td>
							<strong><?php echo esc_html( $row->name ?: '—' ); ?></strong><br>
							<a href="mailto:<?php echo esc_attr( $row->email ); ?>"><?php echo esc_html( $row->email ?: '—' ); ?></a>
						</td>
						<td class="ea-vl-loc"><?php echo esc_html( self::format_location( $row ) ); ?><?php if ( $row->isp ) : ?><br><?php echo esc_html( $row->isp ); ?><?php endif; ?></td>
						<td><code><?php echo esc_html( $row->ip ?: '—' ); ?></code></td>
						<td>
							<?php if ( 'blocked_attempt' === $row->status || $is_blocked ) : ?>
								<span class="ea-vl-badge blocked"><?php echo esc_html( $is_blocked ? 'IP blocked' : 'Blocked attempt' ); ?></span>
							<?php else : ?>
								<span class="ea-vl-badge ok">Submitted</span>
							<?php endif; ?>
						</td>
						<td><div class="ea-vl-msg"><?php echo esc_html( wp_html_excerpt( (string) $row->message, 120, '…' ) ); ?></div></td>
						<td>
							<a class="button button-small" href="<?php echo esc_url( $base . '&tab=messages&view=' . (int) $row->id ); ?>">View</a>
							<?php if ( $row->ip ) : ?>
								<a class="button button-small" href="<?php echo esc_url( add_query_arg( array( 'tab' => 'activity', 'ip' => $row->ip ), $base ) ); ?>">Activity</a>
							<?php endif; ?>
							<?php if ( $row->ip && ! $is_blocked ) : ?>
								<a class="button button-small" href="<?php echo esc_url( self::action_url( 'ea_vl_block', array( 'ip' => $row->ip, 'id' => $row->id ) ) ); ?>" onclick="return confirm('Block <?php echo esc_js( $row->ip ); ?>?');">Block IP</a>
							<?php elseif ( $row->ip && $is_blocked ) : ?>
								<a class="button button-small" href="<?php echo esc_url( self::action_url( 'ea_vl_unblock', array( 'ip' => $row->ip ) ) ); ?>">Unblock</a>
							<?php endif; ?>
							<a class="button button-small" href="<?php echo esc_url( self::action_url( 'ea_vl_delete', array( 'id' => $row->id ) ) ); ?>" onclick="return confirm('Delete this log entry?');">Delete</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
		<?php self::render_pagination( $page, $pages, array( 'tab' => 'messages' ) ); ?>
		<?php
	}

	private static function render_pagination( $page, $pages, $args = array() ) {
		if ( $pages <= 1 ) {
			return;
		}
		$base_args = array_merge( array( 'page' => self::SLUG ), $args );
		echo '<div class="tablenav"><div class="tablenav-pages">';
		echo wp_kses_post(
			paginate_links(
				array(
					'base'      => add_query_arg( array_merge( $base_args, array( 'paged' => '%#%' ) ), admin_url( 'admin.php' ) ),
					'format'    => '',
					'current'   => $page,
					'total'     => $pages,
					'prev_text' => '&laquo;',
					'next_text' => '&raquo;',
				)
			)
		);
		echo '</div></div>';
	}

	private static function format_location( $row ) {
		$parts = array_filter( array( $row->city ?? '', $row->region ?? '', $row->country ?? '' ) );
		return $parts ? implode( ', ', $parts ) : '—';
	}

	private static function notice_text( $key ) {
		$map = array(
			'blocked'   => 'IP blocked. That visitor can no longer access the site or send the contact form.',
			'unblocked' => 'IP unblocked.',
			'deleted'   => 'Entry deleted.',
			'invalid'   => 'Invalid IP address.',
			'own_ip'    => 'That is your current IP — not blocked (to avoid locking yourself out).',
			'denied'    => 'Action not allowed.',
		);
		return $map[ $key ] ?? 'Done.';
	}

	private static function action_url( $action, array $args = array() ) {
		$args['action'] = $action;
		$url            = add_query_arg( $args, admin_url( 'admin-post.php' ) );
		return wp_nonce_url( $url, $action );
	}

	private static function redirect( $notice, $extra = array() ) {
		$url = add_query_arg(
			array_merge(
				array(
					'page'         => self::SLUG,
					'ea_vl_notice' => $notice,
				),
				$extra
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $url );
		exit;
	}

	public static function handle_block() {
		if ( ! current_user_can( self::CAP ) || ! check_admin_referer( 'ea_vl_block' ) ) {
			self::redirect( 'denied' );
		}
		$ip   = isset( $_GET['ip'] ) ? sanitize_text_field( wp_unslash( $_GET['ip'] ) ) : '';
		$mine = EA_VL_Blocker::client_ip();
		if ( $mine && $ip === $mine ) {
			self::redirect( 'own_ip' );
		}
		if ( ! EA_VL_DB::block_ip( $ip ) ) {
			self::redirect( 'invalid' );
		}
		self::redirect( 'blocked' );
	}

	public static function handle_unblock() {
		if ( ! current_user_can( self::CAP ) || ! check_admin_referer( 'ea_vl_unblock' ) ) {
			self::redirect( 'denied' );
		}
		$ip = isset( $_GET['ip'] ) ? sanitize_text_field( wp_unslash( $_GET['ip'] ) ) : '';
		EA_VL_DB::unblock_ip( $ip );
		self::redirect( 'unblocked' );
	}

	public static function handle_delete() {
		if ( ! current_user_can( self::CAP ) || ! check_admin_referer( 'ea_vl_delete' ) ) {
			self::redirect( 'denied' );
		}
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		if ( $id ) {
			EA_VL_DB::delete_log( $id );
		}
		self::redirect( 'deleted', array( 'tab' => 'messages' ) );
	}

	public static function handle_delete_activity() {
		if ( ! current_user_can( self::CAP ) || ! check_admin_referer( 'ea_vl_delete_activity' ) ) {
			self::redirect( 'denied' );
		}
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		if ( $id ) {
			EA_VL_DB::delete_activity( $id );
		}
		self::redirect( 'deleted', array( 'tab' => 'activity' ) );
	}

	public static function handle_block_manual() {
		if ( ! current_user_can( self::CAP ) || ! check_admin_referer( 'ea_vl_block_manual' ) ) {
			self::redirect( 'denied' );
		}
		$ip   = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
		$mine = EA_VL_Blocker::client_ip();
		if ( $mine && $ip === $mine ) {
			self::redirect( 'own_ip' );
		}
		if ( ! EA_VL_DB::block_ip( $ip ) ) {
			self::redirect( 'invalid' );
		}
		self::redirect( 'blocked' );
	}
}
