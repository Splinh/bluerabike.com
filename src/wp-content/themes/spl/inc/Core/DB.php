<?php
/**
 * Database helper wrapper.
 *
 * @package HD\Core
 */

namespace HD\Core;

defined( 'ABSPATH' ) || exit;

final class DB {

	/**
	 * Returns the global $wpdb object.
	 *
	 * @return \wpdb
	 */
	public static function db(): \wpdb {
		global $wpdb;
		return $wpdb;
	}
}
