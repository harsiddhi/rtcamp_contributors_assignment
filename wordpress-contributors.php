<?php
/**
 *Plugin Name: Wordpress Contributors
 *Description: This plugin allow to set more than one post author in wordpress post.
 *Author: Harsiddhi Thakkar
 *Author Url:https://github.com/harsiddhi
 *Version: 1.0
 * License:GPL-2.0+
 */


 // Add wp-contributors the class .
require_once 'includes/class-wordpress-contributors.php';
/**
 * Calls the class on the post edit screen.
 */
function call_wp_post_contributors() {
	new WordPress_Contributors();
}

/**
 * Calls the function (only when in admin area)
 */
if ( is_admin() ) {
	add_action( 'load-post.php', 'call_wp_post_contributors' );
	add_action( 'load-post-new.php', 'call_wp_post_contributors' );
}
/**
 * Calls the function  (only when not in admin area)
 */
if ( ! is_admin() ) {
	add_filter( 'the_content', 'wp_contributors_post_content_meta_box' );
}
/**
 * Display post_contributors below post content.
 *
 * @param  object $content
 *
 * @return object $content
 */
function wp_contributors_post_content_meta_box( $content ) {

	global $post;

	// Use get_post_meta to retrieve an existing value from the database.
	$post_contributors = maybe_unserialize( get_post_meta( $post->ID, '_post_contributors_list_meta_value_key', true ) );

	if ( ! empty( $post_contributors ) ) {

		$contributors_box = '<h3 class="post-contributors-box">';
		$contributors_box .= __( 'Contributors', 'post_contributors' );
		$contributors_box .= '</h3>';
		$contributors_box .= '<ul class="post-contributors-box">';

		foreach ( $post_contributors as $post_contributor ) {

			$contributors_name = get_the_author_meta( 'user_login', $post_contributor );

			// if user is there
			if ( $contributors_name ) {

				$contributors_url    = get_author_posts_url( $post_contributor, $contributors_name );
				$contributors_avatar = get_avatar( $post_contributor, 24, '', 'avatar' );
				$contributors_count  = count_user_posts( $post_contributor ); // get the post count

				$contributors_box .= '<li>';
				$contributors_box .= $contributors_avatar;

				// if post count > 0 , add link.
				if ( $contributors_count > 0 ) {
					$contributors_box .= '<a href="' . $contributors_url . '" rel="author" target="_blank">';
				}

				$contributors_box .= '<span>';
				$contributors_box .= $contributors_name;
				$contributors_box .= '</span>';

				// if post count > 0 , close link.
				if ( $contributors_count > 0 ) {
					$contributors_box .= '</a>';
				}

				$contributors_box .= '</li>';
			}
		}

		$contributors_box .= '</ul>';
		$content          = $content . $contributors_box;
	}

	return $content;
}