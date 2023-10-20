<?php
/**
 * The template for displaying the footer.
 *
 * Contains the body & html closing tags.
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
	if ( did_action( 'elementor/loaded' ) && hello_header_footer_experiment_active() ) {
		get_template_part( 'template-parts/dynamic-footer' );
	} else {
		get_template_part( 'template-parts/footer' );
	}
}
?>

<?php wp_footer(); ?>
<?php $page_id =  get_the_ID();
if($page_id == '34'){ ?>
<script>
  jQuery(document).ready(function() {
    if (window.location.href.indexOf("login") > -1) {
     jQuery('#title_text .elementor-heading-title').text('Reset Password');
    }
  });
</script>
<?php } ?>
</body>
</html>