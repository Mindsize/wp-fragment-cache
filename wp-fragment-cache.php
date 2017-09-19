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
if( class_exists( 'WP_Fragment_Cache' ) ) {
	return;
}

/**
 * Class WP_Fragment_Cache
 *
 * @since 1.0.0
 */
abstract class WP_Fragment_Cache {

	/**
	 * The slug of the fragment cache prefixes all hooks and is sanitized to create the default cache directory.
	 *
	 * @var string
	 */
	protected $slug = 'wp-fragment-cache';

	/**
	 * Abstracted method for classes to override and store their data.
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	abstract protected function set_cache_data( $output, $conditions );

	/**
	 * Abstracted method for classes to override and get their data.
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	abstract protected function get_cache_data( $conditions );

	/**
	 * Abstracted method for classes to override and clear their cache
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	abstract public function clear_cache();

	/**
	 * Opening comment before cached content is output.
	 * Is stored within the cached content and is not generated on every load.
	 */
	protected function get_cache_start_comment() { return null; }

	/**
	 * Closing comment before cached content is output.
	 * Is stored within the cached content and is not generated on every load.
	 */
	protected function get_cache_close_comment() { return null; }

	/**
	 * Debug comment string for the cached data
	 */
	protected function get_cache_debug_comment( $start, $end ) {
		return sprintf( __( 'Took %f seconds to store cached content.', 'ms-wp-fragment-cache' ), $end - $start );
	}

	/**
	 * Output a HTML comment, if the comment is empty, no HTML is output.
	 */
	protected function output_comment( $comment ) {
		if( ! empty( $comment ) ) {
			printf( '<!-- %s -->', $comment );
		}
	}

	/**
	 * Actually cache the output of the passed callback, optionally outputting the result also. Allows for passing
	 * arguments for conditional caching.
	 *
	 * @param       $callback
	 * @param bool  $also_output
	 * @param array $args
	 *
	 * @return bool
	 */
	public function do( $callback, $conditions = array(), $also_output = true, $refresh = false ) {
		/**
		 * If the callback passed is not callable, just bail here and false and a notice.
		 */
		if( ! is_callable( $callback ) ) {
			trigger_error( __( 'Unable to call required callback function.', 'ms-wp-fragment-cache' ) );

			return false;
		}

		$contents = $this->get_cache_data( $conditions );
		if( true === $refresh || false === $contents ) {
			/**
			 * Start saving all output into a buffer for capture later.
			 */
			ob_start();

			/**
			 * Output the starting HTML comment.
			 */
			$this->output_comment( $this->get_cache_start_comment() );

			/**
			 * Call the callback, pass the conditions to the callback as well.
			 */
			$start = microtime( true );
			call_user_func_array( $callback, $conditions );
			$end = microtime( true );

			/**
			 * Output the closing HTML comment.
			 */
			$this->output_comment( $this->get_cache_close_comment() );

			/**
			 * If we are debugging, then also output some debugging data.
			 */
			if( $this->is_debug() ) {
				$this->output_comment( $this->get_cache_debug_comment( $start, $end ) );
			}

			$contents = ob_get_clean();

			$this->set_cache_data( $contents, $conditions );
		}

		if( true === $also_output ) {
			print( $contents );
		}

		return $contents;
	}

	/**
	 * Get the slug of the class
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Determine if the site is in debug mode, and if we should output additional debug HTML.
	 *
	 * @return bool
	 */
	public function is_debug() {
		return (bool) apply_filters( $this->get_hook_name( 'debug' ), WP_DEBUG );
	}

	/**
	 * Use the class slug to define hooks dynamically.
	 *
	 * @param $name
	 *
	 * @return string
	 */
	protected function get_hook_name( $name ) {
		return sanitize_title( sprintf( '%s_%s', $this->get_slug(), $name ) );
	}
}