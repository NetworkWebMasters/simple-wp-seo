<?php
/*
Plugin Name: Simple WP SEO 
Plugin URI: http://sarathlal.com/wp-plugins/
Description: Simple SEO plugin that will add option to enter meta title and meta description for all registered post types.
Version: 1.0.0
Author: Sarathlal N
Author URI: http://sarathlal.com
Text Domain: seo
*/

/*
 * Simple SEO meta fields
 * */
function seo_get_meta( $value ) {
	global $post;

	$field = get_post_meta( $post->ID, $value, true );
	if ( ! empty( $field ) ) {
		return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
	} else {
		return false;
	}
}

function seo_add_meta_box() {	
	$args = array('public'   => true );
	$post_types = get_post_types( $args );
	foreach ( $post_types  as $post_type ) {
		add_meta_box( 'seo_meta_box', __( 'Meta Tags for SEO', 'seo' ), 'seo_metabox_add_html', $post_type,'normal', 'default');
	}	
}
add_action( 'add_meta_boxes', 'seo_add_meta_box' );

function seo_metabox_add_html( $post) {
	wp_nonce_field( '_seo_nonce', 'seo_nonce' ); ?>

	<p>
		<label for="seo_title"><?php _e( 'Meta Title', 'seo' ); ?></label><br>
		<input type="text" class="custom-mbox-input" name="seo_title" id="seo_title" value="<?php echo seo_get_meta( 'seo_title' ); ?>"style="width: 100%; height: 2em;">
	</p>	<p>
		<label for="seo_description"><?php _e( 'Meta Description', 'seo' ); ?></label><br>
		<textarea name="seo_description"  class="custom-mbox-textarea" id="seo_description" style="width: 100%; height: 4em;" ><?php echo seo_get_meta( 'seo_description' ); ?></textarea>
	
	</p><?php
}

function seo_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! isset( $_POST['seo_nonce'] ) || ! wp_verify_nonce( $_POST['seo_nonce'], '_seo_nonce' ) ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	if ( isset( $_POST['seo_title'] ) )
		update_post_meta( $post_id, 'seo_title', esc_attr( $_POST['seo_title'] ) );
	if ( isset( $_POST['seo_description'] ) )
		update_post_meta( $post_id, 'seo_description', esc_attr( $_POST['seo_description'] ) );
}
add_action( 'save_post', 'seo_save' );

//Title Tag
add_filter('pre_get_document_title', 'change_the_title');
function change_the_title($title) {
	if ( is_singular() ) {
	$title_meta = seo_get_meta( 'seo_title' );
		if(!empty($title_meta)) {
			$seo_title = $title_meta . " &#8211; " . get_bloginfo( 'name' );
			return $seo_title;
		}
	}
}
//Meta description tag
function add_meta_tags() {
    if ( is_singular() ) {
		$description_meta = seo_get_meta( 'seo_description' );
		if(!empty($description_meta)) {
			 echo '<meta name="description" content="' . $description_meta . '" />' . "\n";
		}
    }
}
add_action( 'wp_head', 'add_meta_tags' , 2 );
