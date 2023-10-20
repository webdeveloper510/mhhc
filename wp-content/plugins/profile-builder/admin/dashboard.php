<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Create the "Dashboard" submenu page
 *
 */
function wppb_register_dashboard_submenu_page() {
    if( isset( $_GET['subpage'] ) && $_GET['subpage'] == 'wppb-setup' )
        $page_title = __( 'Setup Wizard', 'profile-builder' );
    else
        $page_title = __( 'Dashboard', 'profile-builder' );

	add_submenu_page( 'profile-builder', $page_title, $page_title, 'manage_options', 'profile-builder-dashboard', 'wppb_dashboard_content_output' );
}
add_action( 'admin_menu', 'wppb_register_dashboard_submenu_page', 2 );

function wppb_dashboard_content_output() {
    if( isset( $_GET['subpage'] ) && $_GET['subpage'] == 'wppb-setup' )
        do_action( 'wppb_output_dashboard_setup_wizard' );
    else
        wppb_dashboard_page_content();
}

/**
 * Add content to the "Dashboard" submenu page
 *
 */
function wppb_dashboard_page_content() {

?>

    <div class="wrap cozmoslabs-wrap cozmoslabs-wrap--big">

        <div class="cozmoslabs-page-header">
            <div class="cozmoslabs-section-title">
                <h3 class="cozmoslabs-page-title"><?php esc_html_e( 'Dashboard', 'profile-builder' ); ?></h3>
            </div>
        </div>

        <div class="cozmoslabs-page-grid wppb-dashboard-overview">

            <div class="postbox cozmoslabs-form-subsection-wrapper">

                <div class="wppb-dashboard-stats__title">
                    <h4 class="cozmoslabs-subsection-title"><?php esc_html_e( 'Totals', 'profile-builder' ); ?></h4>

                    <select name="wppb_dashboard_stats_select" id="wppb-dashboard-stats-select">
                        <option value="30days" selected>30 days</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_year">This Year</option>
                    </select>

                    <input type="hidden" id="wppb-dashboard-stats-select__nonce" value="<?php echo esc_html( wp_create_nonce( 'wppb_dashboard_get_stats' ) ); ?>" />
                </div>

                <div class="wppb-dashboard-stats">
                    <?php
                    $stats = get_users_stats();
                    $stats_labels = get_users_stats_labels();

                    if( !empty( $stats ) ){
                        foreach( $stats as $key => $value ) : ?>

                            <div class="wppb-dashboard-box <?php echo esc_html( $key ); ?>">
                                <div class="label">
                                    <?php echo esc_html( $stats_labels[ $key ] ); ?>
                                </div>

                                <div class="value">
                                    <?php
                                    echo esc_html( $value );
                                    ?>
                                </div>
                            </div>

                        <?php endforeach;
                    }
                    ?>
                </div>

                <h4 class="cozmoslabs-subsection-title"><?php esc_html_e( 'Recent Registrations', 'profile-builder' ); ?></h4>

                <div class="wppb-dashboard-registrations">
                    <?php $recent_registrations = wppb_recent_registrations(); ?>

                    <?php if( !empty( $recent_registrations ) ): ?>
                    <?php foreach( $recent_registrations as $user_data ): ?>
                    <div class="wppb-dashboard-registrations__row">
                        <a href="<?php echo esc_url( add_query_arg( array( 'user_id' => $user_data['id'] ), admin_url( 'user-edit.php' ) ) ); ?>">
                            <?php printf( esc_html__( '%1s registered as a %2s role', 'profile-builder' ), esc_html( $user_data['display_name'] ), esc_html( $user_data['role'] ) ); ?>
                        </a>
                        <div class="wppb-dashboard-registrations__date">
                            <?php printf( '%1s', esc_html( $user_data['reg_date'] ) ) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <a class="button button-secondary" href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>"><?php esc_html_e( 'View All Users', 'profile-builder' ); ?></a>
            </div>

            <div class="postbox cozmoslabs-form-subsection-wrapper wppb-dashboard-progress">
                <h4 class="cozmoslabs-subsection-title"><?php esc_html_e( 'Setup Progress Review', 'profile-builder' ); ?></h4>

                <?php WPPB_Setup_Wizard::output_progress_steps(); ?>

                <a class="button button-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=profile-builder-dashboard&subpage=wppb-setup' ) ); ?>"><?php esc_html_e( 'Open the Setup Wizard', 'profile-builder' ); ?></a>
            </div>

            <div class="postbox cozmoslabs-form-subsection-wrapper">
                <h4 class="cozmoslabs-subsection-title"><?php esc_html_e( 'Useful shortcodes for setup', 'profile-builder' ); ?></h4>

                <p class="wppb-dashboard-shortcodes__description"><?php esc_html_e( 'Use these shortcodes to quickly setup and customize your membership website.', 'profile-builder' ); ?></p>

                <div class="wppb-dashboard-shortcodes">
                    <div class="wppb-dashboard-shortcodes__row">
                        <div class="wppb-dashboard-shortcodes__row__wrap">
                            <div class="label">Register</div>
                            <p>Add registration forms where users can sign-up and enter their initial details.</p>
                        </div>

                        <div class="wppb-dashboard-shortcodes__row__input">
                            [wppb-register]
                        </div>
                    </div>
                    <div class="wppb-dashboard-shortcodes__row">
                        <div class="wppb-dashboard-shortcodes__row__wrap">
                            <div class="label">Login</div>
                            <p>Allow members to login.</p>
                        </div>

                        <div class="wppb-dashboard-shortcodes__row__input">
                            [wppb-login]
                        </div>
                    </div>
                    <div class="wppb-dashboard-shortcodes__row">
                        <div class="wppb-dashboard-shortcodes__row__wrap">
                            <div class="label">Edit Profile</div>
                            <p>Allow members to edit their account information.</p>
                        </div>

                        <div class="wppb-dashboard-shortcodes__row__input">
                            [wppb-edit-profile]
                        </div>
                    </div>
                    <div class="wppb-dashboard-shortcodes__row">
                        <div class="wppb-dashboard-shortcodes__row__wrap">
                            <div class="label">Restrict Content</div>
                            <p>Restrict pieces of content on individual posts and pages based on user role.</p>
                        </div>

                        <div class="wppb-dashboard-shortcodes__row__input">
                            [wppb-restrict user_roles="subscriber"]
                        </div>
                    </div>
                </div>

                <a class="button button-secondary" href="https://www.cozmoslabs.com/docs/profile-builder/shortcodes/" target="_blank"><?php esc_html_e( 'Learn more about shortcodes', 'profile-builder' ); ?></a>
            </div>
        </div>

    </div>
<?php
}


/**
 * Get Dashboard stats
 *
 */
function wppb_get_dashboard_stats(){
    check_admin_referer( 'wppb_dashboard_get_stats' );

    if( empty( $_POST['interval'] ) )
        return;

    $interval = sanitize_text_field( $_POST['interval'] );
    $return = array(
        'success' => true,
        'data'    => array(),
    );

    // generate filter data
    $args = array();

    if( $interval == 'this_month' ){

        $args['interval'][] = date( 'Y-m-01', time() );
        $args['interval'][] = date( 'Y-m-d', time() );

    } else if( $interval == 'last_month' ){

        $args['interval'][] = date( 'Y-m-01', strtotime( '-1 month' ) );
        $args['interval'][] = date( 'Y-m-t', strtotime( '-1 month' ) );

    } else if( $interval == 'this_year' ){

        $args['interval'][] = date( 'Y-01-01', time() );
        $args['interval'][] = date( 'Y-m-d', time() );

    }

    $return['data'] = get_users_stats( $args );

    echo json_encode( $return );
    die();
}
add_action( 'wp_ajax_wppb_get_dashboard_stats', 'wppb_get_dashboard_stats'  );


/**
 * Get User stats
 *
 */
function get_users_stats( $args = array() ) {

    $total_users = count_users();
    $users_stats = array(
        'all_users'         => $total_users['total_users'],
        'newly_registered'  => '',
    );

    if ( empty( $args ) ) {
        $reg_start_date = date('Y-m-d', strtotime('-30 days'));
        $reg_end_date = date('Y-m-d' );
    }
    else {
        $reg_start_date = $args['interval'][0];
        $reg_end_date = $args['interval'][1];
    }

    $query_args = array(
        'date_query'   => array(
            array(
                'after' => $reg_start_date,
                'before' => $reg_end_date,
                'inclusive' => true,
            ),
        ),
    );

    $user_query = new WP_User_Query( $query_args );
    $users_stats['newly_registered'] = $user_query->get_total();

    return $users_stats;
}


/**
 * Get the Labels for User stats
 *
 */
function get_users_stats_labels() {
    $test = array(
        'all_users'      => __( 'All Users', 'profile-builder' ),
        'newly_registered'      => __( 'New Registered Users', 'profile-builder' ),
    );
    return $test;
}


/**
 * Get recently registered Users
 *
 */
function wppb_recent_registrations( $args = array() ) {

    $query_args = array(
        'orderby'      => 'user_registered',
        'order'        => 'DESC',
        'number'       => 5,
    );

    $user_query = new WP_User_Query( $query_args );

    $users_stats = array();

    if( !empty( $user_query->results ) ){
        foreach ( $user_query->results as $user_data ) {
            $user_roles = is_array( $user_data->roles ) ? implode(', ', $user_data->roles) : $user_data->roles;
    
            $user_info = array(
                'id'           => $user_data->ID,
                'display_name' => $user_data->display_name,
                'role'         => $user_roles,
                'reg_date'     => $user_data->user_registered,
            );
    
            $users_stats[] = $user_info;
        }
    }


    return $users_stats;
}