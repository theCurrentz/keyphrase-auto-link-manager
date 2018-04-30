<?php
/////////////////////////////////////
// Add Custom Meta Box
/////////////////////////////////////

//Fire our meta box setup function on the post editor screen.
add_action( 'load-post.php', 'keyphrase_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'keyphrase_post_meta_boxes_setup' );

//Meta box setup function.
function keyphrase_post_meta_boxes_setup() {

	//Add meta boxes on the 'add_meta_boxes' hook.
	add_action( 'add_meta_boxes', 'keyphrase_add_post_meta_boxes' );

}

//searches for a match in post meta data to display checked
function keyphrase_is_checked($needle, $haystack)
{
  echo ( $needle == $haystack ) ? 'checked' : '';
}

//Create one or more meta boxes to be displayed on the post editor screen.
if ( !function_exists( 'keyphrase_add_post_meta_boxes' ) ) {
  function keyphrase_add_post_meta_boxes() {

    add_meta_box(
      'format_options',			// Unique ID
      'Auto Keyphrases',		// Title
      'keyphrase_options_meta_box',		// Callback function
      'post',					// Admin page (or post type)
      'side',					// Context
      'core'					// Priority
    );
  }
}
// Display the post meta box for ads toggling
function keyphrase_options_meta_box( $post ) {
  wp_nonce_field( basename( __FILE__ ), 'keyphrase_options_nonce' );
  $selected = get_post_meta( $post->ID, 'keyphrase_options', true );
  ?>
  <p>
    <input type="checkbox" name="keyphrase_options_1" id="keyphrase_options_1" value="on" <?php keyphrase_is_checked("on", $selected); ?>>Enable
    <br />
  </p>
<?php  }

// Save the ad toggle meta box's post metadata.
function keyphrase_options_save_meta( $post_id, $post ) {
  global $post;
  // verify meta box nonce
  if ( !isset( $_POST['keyphrase_options_nonce'] ) || !wp_verify_nonce( $_POST['keyphrase_options_nonce'], basename( __FILE__ ) ) ) {
  	return;
  }

  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
  		return;
  }

  if ( !current_user_can( 'edit_post', $post->ID ) ) {
   	return;
  }

  $format_options_checkbox_values = $_POST['keyphrase_options_1'];
  update_post_meta( $post->ID, 'keyphrase_options', $format_options_checkbox_values );

}
add_action( 'save_post', 'keyphrase_options_save_meta', 10, 2 );
