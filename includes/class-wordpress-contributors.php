<?php

/**
 * WordPress_Contributors class.
 */
class WordPress_Contributors {
	/**
	 * @access public
	 *
	 */
	public function __construct() {

		// Actions
		add_action( 'add_meta_boxes', array( $this, 'wp_contributors_add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	/**
	 * Adds the meta box container.
	 *
	 * @access public
	 *
	 * @param  string $post_type
	 *
	 * @return void
	 */
	public function wp_contributors_add_meta_box( $post_type ) {

		$post_types = array( 'post' );     //limit meta box to certain post types i.e  'page' or product etc.

		if ( in_array( $post_type, $post_types ) ) {
			add_meta_box(
				'post_contributors'
				, __( 'Contributors', 'post_contributors' )
				, array( $this, 'render_meta_box_content' )
				, $post_type
				, 'advanced'
				, 'high'


			);
		}
	}

	/**
	 * Render Meta Box content.
	 *
	 * @access public
	 *
	 * @param  object $post
	 *
	 * @return void
	 */
	public function render_meta_box_content( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'post_contributors_box', 'post_contributors_box_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$values = maybe_unserialize( get_post_meta( $post->ID, '_post_contributors_list_meta_value_key', true ) );

		// Get Blog User list as a array
		/** @noinspection PhpVariableNamingConventionInspection */
		$users = get_users( 'role=author' );

		// Display the form, using the current value.
		echo '<label for="post_contributors_list">';
		_e( 'Choose the author', 'post_contributors' );
		echo '</label> ';
		echo '<br /> ';
		echo "<ul>";
		/** @noinspection PhpUnusedLocalVariableInspection */
		$selected = '';
		foreach ( $users as $user_object ) {
			$user_id   = $user_object->ID;
			$user_name = $user_object->display_name;


			if ( is_array( $values ) && in_array( $user_id, $values ) ) {
				$selected = 'checked="checked"';
			} else {
				$selected = '';
			}
			echo "<li>";
			echo "<label>";
			echo "<input type='checkbox' name='post_contributors_list[]' value='" . esc_attr( $user_id ) . "' " . $selected . "/>";
			echo esc_attr( __( $user_name, 'post_contributors' ) );
			echo "</label>";
			echo "</li>";
			/** @noinspection PhpUnusedLocalVariableInspection */
			$selected = '';
		}
		echo "</ul>";


	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @access public
	 *
	 * @param  int $post_id
	 *
	 * @return int $post_id
	 */
	public function save( $post_id ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['post_contributors_box_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['post_contributors_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'post_contributors_box' ) ) {
			return $post_id;
		}

		// If this is an save, our form has not been submitted,
		//     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}


		// Sanitize the user input.
		$contributors_list = sanitize_text_field( serialize( $_POST['post_contributors_list'] ) );

		// Update the meta field.
		update_post_meta( $post_id, '_post_contributors_list_meta_value_key', $contributors_list );

		return $post_id;
	}
}
