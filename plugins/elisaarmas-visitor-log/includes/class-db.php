<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EA_VL_DB {
	const OPTION_BLOCKED = 'ea_vl_blocked_ips';

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ea_visitor_log';
	}

	public static function activity_table() {
		global $wpdb;
		return $wpdb->prefix . 'ea_visitor_activity';
	}

	public static function activate() {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();
		$table   = self::table();
		$act     = self::activity_table();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			created_at datetime NOT NULL,
			name varchar(191) NOT NULL DEFAULT '',
			email varchar(191) NOT NULL DEFAULT '',
			message longtext NULL,
			ip varchar(45) NOT NULL DEFAULT '',
			user_agent text NULL,
			country varchar(100) NOT NULL DEFAULT '',
			region varchar(100) NOT NULL DEFAULT '',
			city varchar(100) NOT NULL DEFAULT '',
			isp varchar(191) NOT NULL DEFAULT '',
			page_url text NULL,
			status varchar(32) NOT NULL DEFAULT 'submitted',
			PRIMARY KEY  (id),
			KEY created_at (created_at),
			KEY ip (ip),
			KEY email (email)
		) {$charset};";

		$sql_act = "CREATE TABLE {$act} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			created_at datetime NOT NULL,
			session_id varchar(64) NOT NULL DEFAULT '',
			ip varchar(45) NOT NULL DEFAULT '',
			event_type varchar(64) NOT NULL DEFAULT '',
			event_label varchar(255) NOT NULL DEFAULT '',
			page_url text NULL,
			element_text varchar(255) NOT NULL DEFAULT '',
			element_href text NULL,
			country varchar(100) NOT NULL DEFAULT '',
			region varchar(100) NOT NULL DEFAULT '',
			city varchar(100) NOT NULL DEFAULT '',
			isp varchar(191) NOT NULL DEFAULT '',
			user_agent text NULL,
			meta longtext NULL,
			PRIMARY KEY  (id),
			KEY created_at (created_at),
			KEY ip (ip),
			KEY session_id (session_id),
			KEY event_type (event_type)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		dbDelta( $sql_act );

		if ( false === get_option( self::OPTION_BLOCKED, false ) ) {
			add_option( self::OPTION_BLOCKED, array(), '', false );
		}
	}

	public static function insert( array $row ) {
		global $wpdb;
		$defaults = array(
			'created_at' => current_time( 'mysql' ),
			'name'       => '',
			'email'      => '',
			'message'    => '',
			'ip'         => '',
			'user_agent' => '',
			'country'    => '',
			'region'     => '',
			'city'       => '',
			'isp'        => '',
			'page_url'   => '',
			'status'     => 'submitted',
		);
		$data = array_merge( $defaults, $row );

		$wpdb->insert(
			self::table(),
			array(
				'created_at' => $data['created_at'],
				'name'       => $data['name'],
				'email'      => $data['email'],
				'message'    => $data['message'],
				'ip'         => $data['ip'],
				'user_agent' => $data['user_agent'],
				'country'    => $data['country'],
				'region'     => $data['region'],
				'city'       => $data['city'],
				'isp'        => $data['isp'],
				'page_url'   => $data['page_url'],
				'status'     => $data['status'],
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	public static function insert_activity( array $row ) {
		global $wpdb;
		$defaults = array(
			'created_at'   => current_time( 'mysql' ),
			'session_id'   => '',
			'ip'           => '',
			'event_type'   => '',
			'event_label'  => '',
			'page_url'     => '',
			'element_text' => '',
			'element_href' => '',
			'country'      => '',
			'region'       => '',
			'city'         => '',
			'isp'          => '',
			'user_agent'   => '',
			'meta'         => '',
		);
		$data = array_merge( $defaults, $row );

		if ( is_array( $data['meta'] ) ) {
			$data['meta'] = wp_json_encode( $data['meta'] );
		}

		$wpdb->insert(
			self::activity_table(),
			array(
				'created_at'   => $data['created_at'],
				'session_id'   => substr( (string) $data['session_id'], 0, 64 ),
				'ip'           => $data['ip'],
				'event_type'   => substr( (string) $data['event_type'], 0, 64 ),
				'event_label'  => substr( (string) $data['event_label'], 0, 255 ),
				'page_url'     => $data['page_url'],
				'element_text' => substr( (string) $data['element_text'], 0, 255 ),
				'element_href' => $data['element_href'],
				'country'      => $data['country'],
				'region'       => $data['region'],
				'city'         => $data['city'],
				'isp'          => $data['isp'],
				'user_agent'   => $data['user_agent'],
				'meta'         => $data['meta'],
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	public static function get_logs( $limit = 50, $offset = 0 ) {
		global $wpdb;
		$table = self::table();
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$limit,
				$offset
			)
		);
	}

	public static function get_activities( $limit = 50, $offset = 0, $ip = '' ) {
		global $wpdb;
		$table = self::activity_table();
		if ( $ip ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE ip = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
					$ip,
					$limit,
					$offset
				)
			);
		}
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$limit,
				$offset
			)
		);
	}

	public static function count_logs() {
		global $wpdb;
		$table = self::table();
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	public static function count_activities( $ip = '' ) {
		global $wpdb;
		$table = self::activity_table();
		if ( $ip ) {
			return (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE ip = %s", $ip )
			);
		}
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	public static function get_log( $id ) {
		global $wpdb;
		$table = self::table();
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id )
		);
	}

	public static function delete_log( $id ) {
		global $wpdb;
		return (bool) $wpdb->delete( self::table(), array( 'id' => (int) $id ), array( '%d' ) );
	}

	public static function delete_activity( $id ) {
		global $wpdb;
		return (bool) $wpdb->delete( self::activity_table(), array( 'id' => (int) $id ), array( '%d' ) );
	}

	public static function get_blocked_ips() {
		$ips = get_option( self::OPTION_BLOCKED, array() );
		return is_array( $ips ) ? array_values( array_unique( array_filter( array_map( 'strval', $ips ) ) ) ) : array();
	}

	public static function save_blocked_ips( array $ips ) {
		$clean = array();
		foreach ( $ips as $ip ) {
			$ip = trim( (string) $ip );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				$clean[] = $ip;
			}
		}
		$clean = array_values( array_unique( $clean ) );
		update_option( self::OPTION_BLOCKED, $clean, false );
		return $clean;
	}

	public static function block_ip( $ip ) {
		$ip = trim( (string) $ip );
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return false;
		}
		$ips   = self::get_blocked_ips();
		$ips[] = $ip;
		self::save_blocked_ips( $ips );
		return true;
	}

	public static function unblock_ip( $ip ) {
		$ip  = trim( (string) $ip );
		$ips = array_values(
			array_filter(
				self::get_blocked_ips(),
				static function ( $existing ) use ( $ip ) {
					return $existing !== $ip;
				}
			)
		);
		self::save_blocked_ips( $ips );
		return true;
	}

	public static function is_blocked( $ip ) {
		$ip = trim( (string) $ip );
		return in_array( $ip, self::get_blocked_ips(), true );
	}
}
