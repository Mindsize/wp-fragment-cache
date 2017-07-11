<?php
/**
 * WP Fragment Cache Framework
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-2.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@mindsize.me so we can send you a copy immediately.
 *
 * @package   Mindsize/WP_Fragment_Cache
 * @author    Mindsize
 * @copyright Copyright (c) 2017, Mindsize, LLC.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 */

if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * If the class already exists, no need to redeclare it.
 */
if( class_exists( 'WP_HTML_Fragment_Cache' ) ) {
	return;
}

/**
 * Class WP_HTML_Fragment_Cache
 *
 * @since 1.0.0
 */
abstract class WP_HTML_Fragment_Cache extends WP_Fragment_Cache {

	/**
	 * The slug of the fragment cache prefixes all hooks and is sanitized to create the default cache directory.
	 *
	 * @var string
	 */
	protected $slug = 'wp-html-fragment-cache';

	/**
	 * Get cached data from HTML file and return as a string.
	 *
	 * @param $conditions
	 *
	 * @return string|bool
	 */
	public function get_cache_data( $conditions ) {
		$file = $this->get_cache_file_path( $conditions );

		return file_exists( $file ) ? file_get_contents( $file ) : false;
	}

	/**
	 * Store cached data into HTML file.
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function set_cache_data( $output, $conditions ) {
		$bytes  = 0;
		$closed = false;

		$file = $this->get_cache_file_path( $conditions );

		$this->ensure_directory_exists( dirname( $file ) );

		if( $cache_file = @fopen( $file, 'w' ) ) {
			$bytes  = fwrite( $cache_file, $output );
			$closed = fclose( $cache_file );
		}

		return 0 < $bytes && true == $closed;
	}

	public function get_cache_file_path( $conditions ) {
		/**
		 * HTML file name will be generated based on passed conditions. Allow for customizing these conditions further.
		 *
		 * This will create a new args array which will create a separate version of the cached fragment.
		 */
		$file_conditions = apply_filters( $this->get_hook_name( 'file_conditions' ), (array) $conditions, $this );

		/**
		 * Sort the args in the array so that if two arrays have identical values but just were just out of order
		 * it doesn't mean we need to store separate caches. This reduces the total size of the cache dir.
		 */
		array_multisort( $file_conditions );

		$file_base = apply_filters( $this->get_hook_name( 'file_base' ), trailingslashit( $this->get_cache_path() ), $file_conditions, $this );
		$file_name = apply_filters( $this->get_hook_name( 'file_name' ), md5( json_encode( $file_conditions ) ), $file_conditions, $this );
		$file_path = apply_filters( $this->get_hook_name( 'file_path' ), trailingslashit( $file_base ) . $file_name
		                                                                 . '.html', $file_base, $file_name, $file_conditions, $this );

		return $file_path;
	}

	/**
	 * Get the name of the directory for the cache. sanitize_title is run outside of the filter, all contents will be
	 * sanitiezd.
	 *
	 * @return string
	 */
	public function get_cache_dir() {
		return sanitize_title( apply_filters( $this->get_hook_name( 'dir' ), $this->get_slug() ) );
	}

	/**
	 * Get the path to the cache directory, plus any potentially added URI.
	 *
	 * @return string
	 */
	public function get_cache_path( $extra = null ) {
		$path = trailingslashit( apply_filters( $this->get_hook_name( 'path' ), WP_CONTENT_DIR . '/cache/'
		                                                                        . $this->get_cache_dir() ) );

		/**
		 * Add any extra path that was passed
		 */
		$path .= $extra;

		return trailingslashit( $path );
	}

	/**
	 * Ensures the cache directory exists, and that the cache is clean.
	 */
	public function clear_cache( $product_id = null ) {
		$path = $this->get_cache_path( $product_id );

		$this->ensure_directory_exists( $path );
		$this->delete_directory_contents( $path );
	}

	/**
	 * Ensure that the HTML cache directory exists, and create it if it does not.
	 */
	protected function ensure_directory_exists( $path = null ) {
		$path = ! empty( $path ) ? $path : $this->get_cache_path();

		if( ! is_dir( $path ) ) {
			wp_mkdir_p( $path );
		}
	}

	/**
	 * Delete an entire directory, does call itself recursively.
	 */
	protected function delete_directory_contents( $dir ) {
		foreach(
			array_diff( scandir( $dir ), array(
				'.',
				'..'
			) ) as $file
		) {
			$object = wp_normalize_path( trailingslashit( $dir ) . $file );

			if( is_dir( $object ) ) {
				$this->delete_directory_contents( $object );
			} else {
				unlink( $object );
			}
		}
	}
}