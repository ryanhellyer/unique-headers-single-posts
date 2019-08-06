<?php
/*
Plugin Name: Unique Headers Single Posts
Plugin URI: https://geek.hellyer.kiwi/plugins/unique-headers-single-posts/
Description: Forces single posts pages to grab the first category from a post and use it's header image for that post
Version: 1.5
Author: Ryan Hellyer
Author URI: https://geek.hellyer.kiwi/

------------------------------------------------------------------------
Copyright Ryan Hellyer 2016

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

*/



/**
 * Do not continue processing since file was called directly
 * 
 * @since 1.0
 * @author Ryan Hellyer <ryanhellyer@gmail.com>
 */
if ( !defined( 'ABSPATH' ) ) {
	die( 'Eh! What you doin in here?' );
}


/**
 * Set single post header images to use same header as category
 *
 * @copyright Copyright (c), Ryan Hellyer
 * @license http://www.gnu.org/licenses/gpl.html GPL
 * @author Ryan Hellyer <ryanhellyer@gmail.com>
 * @since 1.0
 */
class Single_Post_Header_Images {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Print styles to admin page
	 */
	public function init() {

		if ( ! class_exists( 'Unique_Headers_Taxonomy_Header_Images' ) ) {
			return;
		}

		// Add filters
		add_filter( 'theme_mod_header_image', array( $this, 'header_image_filter' ) );

	}

	/*
	 * Filter for modifying the output of get_header()
	 */
	public function header_image_filter( $url ) {
		global $post;

		// Bail out now if not in category
		if ( ! is_single() ) {
			return $url;
		}

		// Loop through all taxonomies
		$taxonomies = get_taxonomies( array( 'public' => true ) );
		if ( is_array( $taxonomies ) ) {
			foreach( $taxonomies as $x => $taxonomy ) {
				if ( ! isset( $url_set ) ) {

					// Loop through terms in the taxonomy
					$terms = wp_get_post_terms( get_the_ID(), $taxonomy );

					$count = 0;
					foreach ( $terms as $term ) {

						// Only bother doing the first 10 terms (avoids overloading the database)
						if ( $count < 10 && ! isset( $url_set ) ) {
							$term_id = $term->term_id;

							// Grab stored taxonomy header
							$meta = get_term_meta( $term_id, 'taxonomy-header-image', true );
							if ( is_numeric( $meta ) ) {
								$attachment = wp_get_attachment_image_src( $meta, 'full' );
								$url = $attachment[0];
								$url_set = true;
							} elseif ( '' != $meta ) {
								$url = $meta; // Handling legacy URL's from earlier versions of the plugin
								$url_set = true;
							} else {
								// Leave image URL as is
							}

						}

						$count++;
					}

				}
			}
		}

		return $url;
	}

}
new Single_Post_Header_Images;
