<?php
/**
 * WP Fragment Cache Framework
 *
 * @package   Mindsize/WP_Fragment_Cache
 * @author    Mindsize
 * @copyright Copyright (c) 2017-2021, Mindsize, LLC.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WP_Fragment_Cache' ) ) {
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
	 * @param string $output     Output string.
	 * @param array  $conditions Array of conditions.
	 */
	abstract protected function set_cache_data( $output, $conditions );

	/**
	 * Abstracted method for classes to override and get their data.
	 *
	 * @param array $conditions Array of conditions.
	 */
	abstract protected function get_cache_data( $conditions );

	/**
	 * Abstracted method for classes to override and clear their cache.
	 */
	abstract public function clear_cache();

	/**
	 * Get the opening comment before cached content is output.
	 * This is stored within the cached content and is not generated on every load.
	 */
	protected function get_cache_start_comment() {
		return null;
	}

	/**
	 * Get the closing comment before cached content is output.
	 * This is stored within the cached content and is not generated on every load.
	 */
	protected function get_cache_close_comment() {
		return null;
	}

	/**
	 * Get the debug comment string for the cached data.
	 *
	 * @param float $start The start time in UNIX format.
	 * @param float $end   The end time in UNIX format.
	 *
	 * @return string
	 */
	protected function get_cache_debug_comment( $start, $end ) {
		// translators: The number of seconds elapsed.
		return sprintf( __( 'Took %f seconds to store cached content.', 'ms-wp-fragment-cache' ), floatval( $end ) - floatval( $start ) );
	}

	/**
	 * Output a HTML comment, if the comment is empty, no output is rendered.
	 *
	 * @todo Use wp_kses_allowed_html or similar.
	 *
	 * @param string $comment The code Comment.
	 */
	protected function output_comment( $comment ) {
		if ( empty( $comment ) || ! is_string( $comment ) ) {
			return;
		}

		// Escape as best we can.
		printf( '<!-- %s -->', esc_html( $comment ) );
	}

	/**
	 * Actually cache the output of the passed callback, optionally outputting the result also. Allows for passing
	 * arguments for conditional caching.
	 *
	 * @todo Rendering the content must have some kind of escaping.
	 *       Dangerous input will be served to multiple users as the cached content is presented.
	 * @link https://portswigger.net/web-security/web-cache-poisoning
	 *
	 * @param string|array $callback   Callable callback function/method.
	 * @param array        $conditions Optional. Array of Conditions.
	 * @param bool         $render     Optional. Render the cached HTML in addition to returning. Defaults to true.
	 * @param bool         $refresh    Optional. Refresh the cache.  Defaults to false.
	 *
	 * @return mixed The cached content, else false.
	 */
	public function do( $callback, $conditions = array(), $render = true, $refresh = false ) {

		// If the callback passed is not callable, just bail here and false and a notice.
		if ( ! is_callable( $callback ) && $this->is_debug() ) {
			// Ignore PHPCS as "is_debug" is verified.
			// phpcs:disable WordPress.PHP.DevelopmentFunctions
			trigger_error( esc_html_e( 'Unable to call required callback function.', 'ms-wp-fragment-cache' ) );
			// phpcs:enable
			return false;
		}

		$contents = $this->get_cache_data( $conditions );

		if ( true === $refresh || false === $contents ) {
			// Start saving all output into a buffer for capture later.
			ob_start();

			// Output the starting HTML comment.
			$this->output_comment( $this->get_cache_start_comment() );

			// Call the callback, pass the conditions to the callback as well.
			$start = microtime( true );
			call_user_func_array( $callback, $conditions );
			$end = microtime( true );

			// Output the closing HTML comment.
			$this->output_comment( $this->get_cache_close_comment() );

			// If we are debugging, then also output some debugging data.
			if ( $this->is_debug() ) {
				$this->output_comment( $this->get_cache_debug_comment( $start, $end ) );
			}

			$contents = ob_get_clean();

			$this->set_cache_data( $contents, $conditions );
		}

		// Allow optional rendering after caching.
		if ( true === $render ) {
			print( $contents );
		}

		return $contents;
	}

	/**
	 * Get the class slug.
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
	 * @param string $name The hook name.
	 *
	 * @return string
	 */
	protected function get_hook_name( $name ) {
		return sanitize_title( sprintf( '%s_%s', $this->get_slug(), $name ) );
	}
}
