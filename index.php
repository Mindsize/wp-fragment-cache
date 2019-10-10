<?php
/**
 * Plugin Name: Mindsize - WP Fragment Cache
 * Description: Not intended to be used as a normal plugin, requires developer integration. Abstraction of fragment caching methods for developers to use and integration into WordPress plugins and themes.
 * Version: 1.1.0
 * Author: Mindsize
 * Author URI: http://mindsize.me/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Load the autoload file distributed with the plugin version of this library.
 * For sites not driven by composer.
 */
if( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload_52.php' ) ) {
	require( plugin_dir_path( __FILE__ ) . 'vendor/autoload_52.php' );
}
require_once( 'src/wp-fragment-cache.php' );
require_once( 'src/wp-fragment-html-cache.php' );
require_once( 'src/wp-fragment-object-cache.php' );
