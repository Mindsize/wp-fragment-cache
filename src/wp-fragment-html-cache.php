<?php
/**
 * WP Fragment Cache Framework - HTML Cache
 *
 * @package   Mindsize/WP_Fragment_Cache
 * @author    Mindsize
 * @since     1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WP_Fragment_HTML_Cache' ) ) {
	return;
}

/**
 * Class WP_Fragment_HTML_Cache
 *
 * @since 1.0.0
 */
abstract class WP_Fragment_HTML_Cache extends WP_Fragment_Cache {

	/**
	 * The slug of the fragment cache prefixes all hooks and is sanitized to create the default cache directory.
	 *
	 * @var string
	 */
	protected $slug = 'wp-fragment-html-cache';

	/**
	 * Get cached data from HTML file and return as a string.
	 *
	 * @todo consider using wp_remote_get().
	 *
	 * @param array $conditions Array of conditions.
	 *
	 * @return string|bool
	 */
	public function get_cache_data( $conditions ) {
		$file = $this->get_cache_file_path( $conditions );

		return file_exists( $file ) ? file_get_contents( $file ) : false;
	}

	/**
	 * Write cached data into an HTML file.
	 *
	 * @param string $output     Input to be stored (assumed HTML).
	 * @param array  $conditions Array of conditions.
	 *
	 * @return bool If the safe was successful.
	 */
	public function set_cache_data( $output, $conditions ) {
		$bytes  = 0;
		$closed = false;

		$file = $this->get_cache_file_path( $conditions );

		$this->ensure_directory_exists( dirname( $file ) );

		// @todo need to do more to validate $cache_file.
		// @todo use WP_Filesystem methods.
		$cache_file = @fopen( $file, 'w' );
		if ( $cache_file ) {
			$bytes  = fwrite( $cache_file, $output );
			$closed = fclose( $cache_file );
		}

		return ( 0 < $bytes ) && ( true === $closed );
	}

	/**
	 * Fetch the path of the cache file.
	 *
	 * @param array $conditions Array of conditions.
	 *
	 * @return string The file path.
	 */
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
		$file_path = apply_filters(
			$this->get_hook_name( 'file_path' ),
			trailingslashit( $file_base ) . $file_name . '.html',
			$file_base,
			$file_name,
			$file_conditions,
			$this
		);

		// @todo Need further validation, such as file_exists().
		if ( empty( $file_path ) || ! is_string( $file_path ) ) {
			return '';
		}

		return $file_path;
	}

	/**
	 * Get the name of the directory for the cache. sanitize_title is run outside of the filter, all contents will be
	 * sanitized.
	 *
	 * @return string
	 */
	public function get_cache_dir() {
		return sanitize_title( apply_filters( $this->get_hook_name( 'dir' ), $this->get_slug() ) );
	}

	/**
	 * Get the path to the cache directory, plus any potentially added URI.
	 *
	 * @param string $append Optional. String to append to the cache path.
	 *
	 * @return string
	 */
	public function get_cache_path( $append = null ) {
		$path = trailingslashit(
			apply_filters(
				$this->get_hook_name( 'path' ),
				WP_CONTENT_DIR . '/cache/' . $this->get_cache_dir()
			)
		);

		// Add any extra path that was passed.
		$path .= $append;

		return trailingslashit( $path );
	}

	/**
	 * Ensures the cache directory exists, and that the cache is clean.
	 *
	 * @param string $append Optional. String to append to the cache path.
	 */
	public function clear_cache( $append = null ) {
		$path = $this->get_cache_path( $append );
		$this->ensure_directory_exists( $path );
		$this->delete_directory_contents( $path );
	}

	/**
	 * Ensure that the HTML cache directory exists, and create it if it does not.
	 *
	 * @param string $path Optional. The cache path.
	 */
	protected function ensure_directory_exists( $path = null ) {
		$path = ! empty( $path ) ? $path : $this->get_cache_path();

		if ( ! is_dir( $path ) ) {
			wp_mkdir_p( $path );
		}
	}

	/**
	 * Delete an entire directory. Calls itself recursively.
	 *
	 * @param string $dir The directory path to be emptied.
	 */
	protected function delete_directory_contents( $dir ) {

		if ( ! is_string( $dir ) ) {
			return;
		}

		// Func scandir can return false.
		$file_array = scandir( $dir );
		if ( empty( $file_array ) || ! is_array( $file_array ) ) {
			return;
		}

		$files = array_diff( $file_array, array( '.', '..' ) );

		foreach ( $file_array as $file ) {
			$object = wp_normalize_path( trailingslashit( $dir ) . $file );

			if ( is_dir( $object ) ) {
				$this->delete_directory_contents( $object );
			} else {
				unlink( $object );
			}
		}
	}
}
