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
	 * Set the cache data.
	 *
	 * @param string $output The output.
	 * @param array  $conditions The conditions array.
	 *
	 * @return bool If the operation was successful.
	 */
	protected function set_cache_data( $output, $conditions ) {
		$key = $this->get_key( $conditions );

		$expires = isset( $conditions['expires'] ) && ! empty( $conditions['expires'] ) ? absint( $conditions['expires'] ) : $this->default_expires;

		return wp_cache_set( $key, $output, $this->group, $expires );
	}

	/**
	 * Get the cached data.
	 *
	 * @param array $conditions Array of Conditions.
	 *
	 * @return string The cache.
	 */
	protected function get_cache_data( $conditions ) {
		$key = $this->get_key( $conditions );
		return wp_cache_get( $key, $this->group );
	}

	/**
	 * Clear the cache.
	 *
	 * The return value can be used to determine if
	 * the cache was already empty.
	 *
	 * @return bool If the cache group was found.
	 */
	public function clear_cache() {
		global $wp_object_cache;

		$cache = $wp_object_cache->cache;

		if ( isset( $cache[ $this->group ] ) ) {
			unset( $cache[ $this->group ] );

			$wp_object_cache->cache = $cache;
			return true;
		}

		return false;
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
