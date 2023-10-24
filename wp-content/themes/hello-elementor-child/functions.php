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


//custom code start Here


function restrict_and_redirect_page_ids() {
    // Define the page IDs to restrict
    $restricted_page_ids = array(9, 31, 34, 107); // Add the page IDs to restrict

    // Check if the user is logged in
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $current_page_id = get_queried_object_id();

        // Define the dashboard page IDs for each role
        $role_to_dashboard = array(
            'administrator' => 74, // Admin dashboard
            'dealer' => 1525,
            'servicer' => 1553,
            'customer' => 1575
        );

        // Check if the user has a role and if the current page is restricted
        if (in_array($current_page_id, $restricted_page_ids) && array_key_exists($current_user->roles[0], $role_to_dashboard)) {
            $dashboard_page_id = $role_to_dashboard[$current_user->roles[0]];

            // Redirect to the user's dashboard page
            wp_redirect(get_permalink($dashboard_page_id));
            exit();
        }
    }
}
add_action('template_redirect', 'restrict_and_redirect_page_ids');



// restrict page access if user is not logged in based on page id 

function restrict_and_redirect_unlogged_users() {
    // Define an array of page IDs that should be restricted to logged-in users
    $restricted_page_ids = array(74, 303, 305, 307, 309, 315, 320, 842, 849, 1320, 322, 317, 311, 1341, 1343, 1525, 1553, 1575);

    // Check if the user is not logged in
    if (!is_user_logged_in()) {
        global $post;
        if (in_array($post->ID, $restricted_page_ids)) {
            // Redirect to the login page
           $current_domain = 'http://' . $_SERVER['HTTP_HOST'];

            // Redirect to the current domain
            wp_redirect($current_domain);
            exit();
        }
    }
}
add_action('template_redirect', 'restrict_and_redirect_unlogged_users');


// restrict page access od dealer, servicer and cutomer 


function restrict_dashboard_access() {
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $current_page_id = get_queried_object_id();

        // Check if the user is a Dealer
        if (in_array('dealer', $current_user->roles)) {
            // Redirect the Dealer away from Servicer and Customer pages
            if ($current_page_id == 1553 || $current_page_id == 1575 || $current_page_id == 74) {
                wp_redirect(home_url()); // Redirect to the homepage or a different page
                exit;
            }
        }

        // Check if the user is a Servicer
        if (in_array('servicer', $current_user->roles)) {
            // Redirect the Servicer away from Dealer and Customer pages
            if ($current_page_id == 1525 || $current_page_id == 1575 || $current_page_id == 74) {
                wp_redirect(home_url()); // Redirect to the homepage or a different page
                exit;
            }
        }

        // Check if the user is a Customer
        if (in_array('customer', $current_user->roles)) {
            // Redirect the Customer away from Dealer and Servicer pages
            if ($current_page_id == 1525 || $current_page_id == 1553 || $current_page_id == 74) {
                wp_redirect(home_url()); // Redirect to the homepage or a different page
                exit;
            }
        }
    }
}
add_action('template_redirect', 'restrict_dashboard_access');