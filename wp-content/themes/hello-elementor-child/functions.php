<?php
/**
 * Recommended way to include parent theme styles.
 * (Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme)
 *
 */  

add_action( 'wp_enqueue_scripts', 'hello_elementor_child_style' );
				function hello_elementor_child_style() {
					wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
					wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style') );
				}

/**
 * Your code goes below.
 */

function extra_profile_fields( $user ) { ?>
   
    <h3><?php _e('Extra User Details'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="gmail">Dealer Name</label></th>
            <td>
            <input type="text" name="dealer_name" id="dealer_name" value="<?php echo esc_attr( get_the_author_meta( 'dealer_name', $user->ID ) ); ?>" class="regular-text" /><br />
            </td>
        </tr>
        <tr>
            <th><label for="yahoo">Servicer Name</label></th>
            <td>
            <input type="text" name="servicer_name" id="servicer_name" value="<?php echo esc_attr( get_the_author_meta( 'servicer_name', $user->ID ) ); ?>" class="regular-text" /><br />
            </td>
        </tr>
        <tr>
            <th><label for="hotmail">Customer Name</label></th>
            <td>
            <input type="text" name="customer_name" id="customer_name" value="<?php echo esc_attr( get_the_author_meta( 'customer_name', $user->ID ) ); ?>" class="regular-text" /><br />
            </td>
        </tr>
		<tr>
            <th><label for="hotmail">Position</label></th>
            <td>
            <input type="text" name="position" id="position" value="<?php echo esc_attr( get_the_author_meta( 'position', $user->ID ) ); ?>" class="regular-text" /><br />
            </td>
        </tr>
    </table>
<?php

}

// Then we hook the function to "show_user_profile" and "edit_user_profile"
add_action( 'show_user_profile', 'extra_profile_fields', 10 );
add_action( 'edit_user_profile', 'extra_profile_fields', 10 );

function save_extra_profile_fields( $user_id ) {

    if ( !current_user_can( 'edit_user', $user_id ) )
        return false;

    /* Edit the following lines according to your set fields */
	update_usermeta( $user_id, 'dealer_name', $_POST['dealer_name'] );
    update_usermeta( $user_id, 'servicer_name', $_POST['servicer_name'] );
    update_usermeta( $user_id, 'customer_name', $_POST['customer_name'] );
    update_usermeta( $user_id, 'position', $_POST['position'] );
}

add_action( 'personal_options_update', 'save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_profile_fields' );