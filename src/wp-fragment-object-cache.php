<?php
/**
 * WP Fragment Cache Framework - Object Cache
 *
 * @package Mindsize/WP_Fragment_Cache
 * @author  Mindsize
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WP_Fragment_Object_Cache' ) ) {
	return;
}

/**
 * Class WP_Fragment_Object_Cache
 *
 * Based on work by Gabor Javorszky (@javorszky)
 * https://github.com/javorszky/wp-fragment-cache
 *
 * @since 1.1.0
 */
abstract class WP_Fragment_Object_Cache extends WP_Fragment_Cache {

	/**
	 * Cache group name.
	 *
	 * @var string
	 */
	protected $group = 'wp-fragment-object-cache';

	/**
	 * Default expiration time, in seconds.
	 *
	 * @var int
	 */
	protected $default_expires = MONTH_IN_SECONDS;

	/**
	 * @inheritdoc
	 */
	protected function set_cache_data( $output, $conditions ) {
		$key = $this->get_key( $conditions );

		$expires = isset( $conditions['expires'] ) && ! empty( $conditions['expires'] ) ? absint( $conditions['expires'] ) : $this->default_expires;

		return wp_cache_set( $key, $output, $this->group, $expires );
	}

	/**
	 * @inheritdoc
	 */
	protected function get_cache_data( $conditions ) {
		$key = $this->get_key( $conditions );
		return wp_cache_get( $key, $this->group );
	}

	/**
	 * @inheritdoc
	 */
	public function clear_cache() {
		if ( function_exists( 'wp_cache_delete_group' ) ) {
			wp_cache_delete_group( $this->group );
		}
	}

	/**
	 * Get the cache key.
	 *
	 * @param array $conditions Array of conditions.
	 *
	 * @return string Encoded cache key string.
	 */
	protected function get_key( $conditions ) {
		// Sort array to ensure misordered but otherwise identical conditions aren't saved separately.
		array_multisort( $conditions );
		return md5( wp_json_encode( $conditions ) );
	}
}
