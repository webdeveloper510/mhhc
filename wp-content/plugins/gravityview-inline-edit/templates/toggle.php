<?php
/**
 * @see GravityView_Inline_Edit_Settings::add_inline_edit_toggle_button() Called by this method
 * @global array $labels Array with `enable`, `disable` and `toggle` keys, with strings for each
 * @global string $link_class CSS class for the link
 */
?>
<a href="#" class="<?php echo esc_attr( $link_class ); ?> inline-edit-enable"
   data-label-disabled="<?php echo esc_attr( $labels['disabled'] ); ?>"
   data-label-enabled="<?php echo esc_attr( $labels['enabled'] ); ?>"
   data-label-toggle="<?php echo esc_attr( $labels['toggle'] ); ?>"><?php echo esc_attr( $labels['toggle'] ); ?></a>