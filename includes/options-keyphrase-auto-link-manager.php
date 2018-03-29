<?php
global $post;
//options panel for keyphrase management tool

// Register Custom Post Type
function kpal() {

	$labels = array(
		'name'                  => 'Keyphrases',
		'singular_name'         => 'Keyphrase',
		'menu_name'             => 'Keyphrases',
		'name_admin_bar'        => 'Keyphrases',
		'all_items'             => 'All Items',
		'add_new'               => 'Add New',
    'add_new_item'          => 'Add New Keyphrase Auto Link',
		'new_item'              => 'New Item',
		'edit_item'             => 'Edit Item',
		'update_item'           => 'Update Item',
		'view_item'             => 'View Item',
		'view_items'            => 'View Items',
		'search_items'          => 'Search Item',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image'
	);
	$args = array(
		'label'                 => 'Keyphrase',
		'description'           => 'Manages Keyphrases for auto linking',
		'labels'                => $labels,
		'supports'              => array('title'),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 80,
		'menu_icon'             => 'dashicons-admin-links',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => false,
		'can_export'            => false,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'capability_type'       => 'page',
	);
	register_post_type( 'keyphrase', $args );

}
add_action( 'init', 'kpal', 0 );

//remove yoast from our custom post type
function my_remove_wp_seo_meta_box() {
	remove_meta_box('wpseo_meta', 'keyphrase', 'normal');
}
add_action('add_meta_boxes', 'my_remove_wp_seo_meta_box', 100);


//Fire our meta box setup function on the post editor screen.
add_action( 'load-post.php', 'kpal_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'kpal_post_meta_boxes_setup' );

//Meta box setup function.
function kpal_post_meta_boxes_setup() {

	//Add meta boxes on the 'add_meta_boxes' hook.
	add_action( 'add_meta_boxes', 'kpal_add_post_meta_boxes' );
}

//Create one or more meta boxes to be displayed on the post editor screen.
if ( !function_exists( 'kpal_add_post_meta_boxes' ) ) {
  function kpal_add_post_meta_boxes() {

    add_meta_box(
      'keyphrases',			// Unique ID
      'Keyphrases',		// Title
      'kpal_keyphrases_meta_box',		// Callback function
      'keyphrase',					// Admin page (or post type)
      'normal',					// Context
      'core'					// Priority
    );

  }
}

//execute the callback
function kpal_keyphrases_meta_box($post) {
  global $post;
  wp_nonce_field( basename( __FILE__ ), 'kpal_keyphrases_nonce' );
  ?>
  <p>
    <label>Keyphrases - comma delimited</label>
    <input type="text" name="kpal_keyphrases_words" id="kpal_keyphrases_words" class="widefat" value="<?php echo get_post_meta($post->ID, 'kpal_keyphrases_words')[0];  ?>">
    <br /><br />
    <label>URL to inject</label>
    <input type="text" name="kpal_keyphrases_url" id="kpal_keyphrases_url" class="widefat" value="<?php echo get_post_meta($post->ID, 'kpal_keyphrases_url')[0];  ?>">
    <br />
  </p>
<?php
}

// Save the keyphrase meta box's post metadata.
function kpal_keyphrases_save_meta( $post_id, $post ) {

  // verify meta box nonce
  if ( !isset( $_POST['kpal_keyphrases_nonce'] ) || !wp_verify_nonce( $_POST['kpal_keyphrases_nonce'], basename( __FILE__ ) ) ) {
  	return;
  }

  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
  		return;
  }

  if ( !current_user_can( 'edit_post', $post->ID ) ) {
   	return;
  }

  $kpal_phrases = $_POST['kpal_keyphrases_words'];
  $kpal_url = $_POST['kpal_keyphrases_url'];

  update_post_meta( $post->ID, 'kpal_keyphrases_words', $kpal_phrases );
  update_post_meta( $post->ID, 'kpal_keyphrases_url', $kpal_url );

}
add_action( 'save_post', 'kpal_keyphrases_save_meta', 10, 2 );



//add custom columns to post type admin interface and fill with meta content
function add_keyphrase_columns($columns) {

    return array(
      'title'=>'Title',
      'kpal_keyphrases_words'=>'Keyphrases',
      'kpal_keyphrases_url'=>'URL'
    );

}
add_filter('manage_keyphrase_posts_columns' , 'add_keyphrase_columns');



add_action('manage_posts_custom_column', 'kpal_columns', 10, 2);

// ADD TWO NEW COLUMNS
function kpal_columns($column) {
  global $post;
  switch ( $column ) {

    case 'kpal_keyphrases_words':
      echo get_post_meta($post->ID, 'kpal_keyphrases_words')[0];
      break;

    case 'kpal_keyphrases_url':
      echo get_post_meta($post->ID, 'kpal_keyphrases_words')[0];
      break;

  }
}


//keyphrase auto linker function
function kpal_render($content)
{
  global $page,
         $numpages,
         $multipage;

  //used to throttle occurences on string replace
  $kpalthrottle = (($multipage) ? 3 : 11);
  if($page == 2) echo $kpalthrottle;
  define("THROTTLE", $kpalthrottle);

  $content = $content;

  //defines an enhanced str replace method with a limit parameter
  function str_replace_limit($find, $replacement, $subject, $limit = 0) {

    if ($limit == 0)
      return str_replace($find, $replacement, $subject);

    for ($i = 0; $i < count($find); $i++) {
      $find[$i] = '/' . preg_quote($find[$i],'/') . '/';
    }

    return preg_replace($find, $replacement, $subject, $limit);
  }


  //query all keyphrase sets
  $kpal_query = new WP_Query(
    array(
      'post_type' => array('keyphrase')
    )
  );

  //if and while we have keyphrase entries
  if ( $kpal_query->have_posts() ) {
    while ( $kpal_query->have_posts() ) {
      $kpal_query->the_post();
          //store keyphrase and url info in variables
          $keyphrases = get_post_meta(get_the_ID(), 'kpal_keyphrases_words')[0];
          //explode keyphrases into an array
          $keyphrases = explode(",", $keyphrases);
          $url = get_post_meta(get_the_ID(), "kpal_keyphrases_url")[0];
          $url = preg_replace('/\s+/', '', $url);
          $url = '<a href="'.$url.'">';

          //foreach value in keyphrases array, perform a str replace for the targeted phrase
          foreach($keyphrases as $phrase)
          {
            $kpalthrottle = constant('THROTTLE');
            $phraseToLower = strtolower($phrase);
            $phraseCases = array($phrase, $phraseToLower);
            $phraseCasesReplace = array($url . $phrase . '</a>', $url . $phraseToLower . '</a>');

            $content = str_replace_limit($phraseCases, $phraseCasesReplace, $content, $kpalthrottle);
          }

    }
    wp_reset_postdata();
  }
  $content = $content;
  return $content;
}

//filter the_content with kpal render
add_filter( 'the_content', 'kpal_render');
