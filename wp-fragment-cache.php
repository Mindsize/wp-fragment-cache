<?php
/**
 * Plugin Name: Mindsize - WP Fragment Cache
 * Description: Not intended to be used as a normal plugin, requires developer integration. Abstraction of fragment caching methods for developers to use and integration into WordPress plugins and themes.
 * Version:     1.1.0
 * Author:      Mindsize
 * Author URI:  http://mindsize.me/
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ms-wp-fragment-cache
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
 *
 * @package   Mindsize/WP_Fragment_Cache
 * @author    Mindsize
 * @copyright Copyright (c) 2017-2021, Mindsize, LLC.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 */

$ms_autoloader = dirname( __FILE__ ) . '/vendor/autoload.php';

if ( file_exists( $ms_autoloader ) ) {
	require_once $ms_autoloader;
}
