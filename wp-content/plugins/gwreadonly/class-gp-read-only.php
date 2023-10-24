<?php

class GP_Read_Only extends GWPerk {

	public $version                      = GP_READ_ONLY_VERSION;
	protected $min_gravity_perks_version = '1.0-beta-3';
	protected $min_gravity_forms_version = '2.4';
	protected $min_wp_version            = '3.0';

	private $unsupported_field_types  = array( 'hidden', 'html', 'captcha', 'page', 'section', 'form' );
	private $disable_attr_field_types = array( 'radio', 'select', 'checkbox', 'multiselect', 'time', 'date', 'name', 'address', 'workflow_user', 'workflow_role', 'workflow_assignee_select' );

	public function init() {

		load_plugin_textdomain( 'gwreadonly', false, basename( dirname( __file__ ) ) . '/languages/' );

		$this->add_tooltip( $this->key( 'readonly' ), __( '<h6>Read-only</h6> Set field as "readonly". Read-only fields will be visible on the form but cannot be modified by the user.', 'gravityperks' ) );
		$this->enqueue_field_settings();

		// Actions
		add_action( 'gform_enqueue_scripts', array( $this, 'enqueue_form_styles' ) );

		// Filters
		add_filter( 'gform_field_input', array( $this, 'read_only_input' ), 11, 5 );

		add_filter( 'gform_pre_process', array( $this, 'process_hidden_captures' ), 11, 1 );

		add_filter( 'gform_rich_text_editor_options', array( $this, 'filter_rich_text_editor_options' ), 10, 2 );

		// Add support for Gravity View since `gform_pre_process` never fires in GV's edit path.
		add_action( 'gravityview_edit_entry', array( $this, 'process_hidden_captures_gravityview' ), 5, 4 );

		/**
		 * Add support for Gravity Flow's User Input step
		 *
		 * The user input step does not seem to fire the standard form submission's `gform_pre_process` hook.
		 * Here we attempt to intercept validation but only in the `in_progress`/`complete` states which indicate
		 * that an entry is being updated.
		 */
		if ( class_exists( 'Gravity_Flow' ) && in_array( rgpost( 'gravityflow_status' ), array( 'in_progress', 'complete' ) ) ) {
			add_filter( 'gform_pre_validation', function ( $form ) {
				return $this->process_hidden_captures( $form );
			}, 5, 1 );
		}

	}

	function enqueue_form_styles( $form ) {
		if ( ! $this->should_enqueue_frontend( $form ) ) {
			return;
		}

		wp_enqueue_style( 'gwreadonly', $this->get_base_url() . '/css/gwreadonly.css', array(), $this->version );
	}

	/**
	 * Determine if frontend scripts/styles should be enqueued. Loop through fields and check if read only is enabled
	 * on any field.
	 *
	 * @param $form
	 *
	 * @return bool
	 */
	public function should_enqueue_frontend( $form ) {
		if ( GFCommon::is_form_editor() ) {
			return false;
		}

		return $this->is_applicable_form( $form );
	}

	/**
	 * @param $form
	 *
	 * @return boolean Whether this form has read only forms.
	 */
	public function is_applicable_form( $form ) {
		return ! empty( $this->get_readonly_fields( $form ) );
	}

	/**
	 * @param $form
	 *
	 * @return GF_Field[] List of fields that are read-only.
	 */
	public function get_readonly_fields( $form ) {
		if ( empty( $form['fields'] ) ) {
			return array();
		}

		$fields = array();

		foreach ( $form['fields'] as $field ) {
			if ( $this->is_readonly_field( $field ) ) {
				$fields[] = $field;
			}
		}

		return $fields;
	}

	/**
	 * @param GF_Field $field
	 *
	 * @return boolean
	 */
	public function is_readonly_field( $field ) {
		$input_type = RGFormsModel::get_input_type( $field );

		if ( in_array( $input_type, $this->unsupported_field_types ) ) {
			return false;
		}

		return ! ! rgar( $field, $this->key( 'enable' ) );
	}

	public function field_settings_ui() {
		?>

		<li class="<?php echo $this->key( 'field_setting' ); ?> field_setting" style="display:none;">
			<input type="checkbox" id="<?php echo $this->key( 'field_checkbox' ); ?>" value="1" onclick="SetFieldProperty('<?php echo $this->key( 'enable' ); ?>', this.checked)">

			<label class="inline" for="<?php echo $this->key( 'field_checkbox' ); ?>">
				<?php _e( 'Read-only', 'gravityperks' ); ?>
				<?php gform_tooltip( $this->key( 'readonly' ) ); ?>
			</label>
		</li>

		<?php
	}

	public function field_settings_js() {
		?>

		<script type="text/javascript">

			(function($) {

				$(document).ready(function(){

					for(i in fieldSettings) {
						if(isReadOnlyFieldType(i))
							fieldSettings[i] += ', .gwreadonly_field_setting';
					}

				});

				$(document).bind('gform_load_field_settings', function(event, field, form) {
					$("#<?php echo $this->key( 'field_checkbox' ); ?>").prop( 'checked', field["<?php echo $this->key( 'enable' ); ?>"] === true );

					// If calculation is enabled, we typically don't need this Perk since the input will be read-only
					// However, in the case of the product field with a quantity field, the quantity field won't
					// be read-only.
					if( ! isReadOnlyFieldType( GetInputType( field ) ) || (isCalcEnabled( field ) && field.type !== 'product') ) {
						field["<?php echo $this->key( 'enable' ); ?>"] = false;
						$('.gwreadonly_field_setting').hide();
					}
				});

				function isReadOnlyFieldType(type) {
					var unsupportedFieldTypes = <?php echo json_encode( $this->unsupported_field_types ); ?>;
					return $.inArray(type, unsupportedFieldTypes) != -1 ? false : true;
				}

				function isCalcEnabled( field ) {
					return field.enableCalculation == true || GetInputType( field ) == 'calculation';
				}

			})(jQuery);

		</script>

		<?php
	}

	public function read_only_input( $input_html, $field, $value, $entry_id, $form_id ) {

		if ( $field->is_entry_detail() ) {
			return $input_html;
		}

		$input_type = RGFormsModel::get_input_type( $field );
		if ( in_array( $input_type, $this->unsupported_field_types ) || ! rgar( $field, $this->key( 'enable' ) ) ) {
			return $input_html;
		}

		remove_filter( 'gform_field_input', array( $this, 'read_only_input' ), 11, 5 );

		$input_html = GFCommon::get_field_input( $field, $value, $entry_id, $form_id, GFAPI::get_form( $form_id ) );

		switch ( $input_type ) {
			case 'textarea':
			case 'post_content':
			case 'post_excerpt':
			case 'workflow_discussion': // @gravityflow
				$search  = '<textarea';
				$replace = $search . " readonly='readonly'";
				break;
			case 'multiselect':
			case 'select':
			case 'workflow_user': // @gravityflow
			case 'workflow_role': // @gravityflow
			case 'workflow_assignee_select': // @gravityflow
				$search  = '<select';
				$replace = $search . " disabled='disabled'";
				break;
			case 'radio':
			case 'checkbox':
				$search  = '<input';
				$replace = $search . " disabled='disabled'";
				break;
			case 'time':
			case 'address':
			case 'name':
			case 'date':
				$search = array(
					'<input'  => "<input readonly='readonly'",
					'<select' => "<select disabled='disabled'",
				);
				break;
			case 'list':
				// Remove add/remove buttons.
				$input_html = preg_replace( '/<(?:td|div) class=\'gfield_list_icons\'>[\s\S]+?<\/(?:td|div)>/', '', $input_html );
				// Remove add/remove column header.
				$input_html = str_replace( '<div class="gfield_header_item gfield_header_item--icons">&nbsp;</div>', '', $input_html );
				$search     = array(
					'<input'  => "<input readonly='readonly'",
					'<select' => "<select disabled='disabled'",
				);
				break;
			case 'signature':
				$input_html = preg_replace( '/<a href=[\'"]#[\'"].*?signature_image.*?>.*?<\/a>/', '', $input_html ); // Remove sign again button

				if ( rgblank( $value ) ) {
					$input_html = preg_replace( '/<div ((style)|(class)=\'.*\')?\s*?><div id=\'input_' . $form_id . '_' . $field->id . '_Container\' .*?>.*?<\/div><\/div>/', '<div style="display: none;"></div><!-- GPRO placeholder -->', $input_html ); // Remove HTML that contains the canvas
				}

				$search  = '<input';
				$replace = $search . " readonly='readonly'";
				break;
			default:
				$search  = '<input';
				$replace = $search . " readonly='readonly'";
				break;
		}

		if ( ! is_array( $search ) ) {
			$search = array( $search => $replace );
		}

		if ( $input_type == 'date' && $field->dateType == 'datepicker' ) {
			/**
			 * Disable the datepicker for read-only Datepicker fields.
			 *
			 * @since 1.2.13
			 *
			 * @param bool          $is_disabled Whether or not to disable the datepicker for this read-only input.
			 * @param GF_Field_Date $field       GF_Field_Date The current Date field object.
			 * @param int           $entry_id    The current entry ID; 0 when no entry ID is provided.
			 */
			$disable_datepicker = gf_apply_filters( array( 'gpro_disable_datepicker', $form_id, $field->id ), true, $field, $entry_id );
			if ( $disable_datepicker ) {
				// Find 'datepicker' and 'gform-datepicker' CSS class and replace it with our custom class indicating that we've disabled it.
				// This class is used by Conditional Logic Dates to identify read-only Datepicker fields.
				$search['class=\'datepicker gform-datepicker '] = 'class=\'gpro-disabled-datepicker ';

				// Replace only 'datepicker' class for older GF versions.
				$search['class=\'datepicker ']  = 'class=\'gpro-disabled-datepicker ';
			}
		}

		foreach ( $search as $_search => $replace ) {
			$input_html = str_replace( $_search, $replace, $input_html );
		}

		// add hidden capture input markup for disabled field types
		if ( in_array( $input_type, $this->disable_attr_field_types ) ) {

			// Use $value if we have it as it'll likely be from dynamic population (e.g. query param or shortcode).
			$value           = ! rgblank( $value ) ? $value : $this->get_field_value( $field );
			$hc_input_markup = '';

			if ( is_array( $field['inputs'] ) ) {

				switch ( $input_type ) {
					case 'time':
						$hc_input_markup .= $this->get_hidden_capture_markup( $form_id, $field->id . '.3', is_array( $value ) ? array_pop( $value ) : $value );
						break;
					case 'date':
						switch ( rgar( $field, 'dateFormat' ) ) {
							case 'mdy':
								$hc_input_markup .= $this->get_hidden_capture_markup( $form_id, $field->id . '.1', rgar( $value, 'm' ) );
								$hc_input_markup .= $this->get_hidden_capture_markup( $form_id, $field->id . '.2', rgar( $value, 'd' ) );
								$hc_input_markup .= $this->get_hidden_capture_markup( $form_id, $field->id . '.3', rgar( $value, 'y' ) );
								break;
							case 'dmy':
								$hc_input_markup .= $this->get_hidden_capture_markup( $form_id, $field->id . '.1', rgar( $value, 'd' ) );
								$hc_input_markup .= $this->get_hidden_capture_markup( $form_id, $field->id . '.2', rgar( $value, 'm' ) );
								$hc_input_markup .= $this->get_hidden_capture_markup( $form_id, $field->id . '.3', rgar( $value, 'y' ) );
								break;
							case 'ymd':
								$hc_input_markup .= $this->get_hidden_capture_markup( $form_id, $field->id . '.1', rgar( $value, 'y' ) );
								$hc_input_markup .= $this->get_hidden_capture_markup( $form_id, $field->id . '.2', rgar( $value, 'm' ) );
								$hc_input_markup .= $this->get_hidden_capture_markup( $form_id, $field->id . '.3', rgar( $value, 'd' ) );
								break;
						}

						break;
					case 'address':
						$input_id         = sprintf( '%d.%d', $field->id, $this->get_address_select_input_id( $field ) );
						$hc_input_markup .= $this->get_hidden_capture_markup( $form_id, $input_id, rgar( $value, $input_id ) );
						break;
					default:
						foreach ( $field['inputs'] as $input ) {
							$hc_input_markup .= $this->get_hidden_capture_markup( $form_id, $input['id'], $value );
						}
				}
			} else {

				$hc_input_markup = $this->get_hidden_capture_markup( $form_id, $field->id, $value );

			}

			// Check if there's a closing div tag
			if ( strpos( $input_html, '</div>' ) !== false ) {
				// Append GPRO hidden input before last closing div tag.
				// This ensures that GPPA will replace the hidden GPRO input during XHR requests.
				$input_html = preg_replace( '/<\/div>(?!\s*<\/?div>?\s*)(.*)/', str_replace( '$', '\$', $hc_input_markup ) . '</div>$1', $input_html );
			} else {
				// No closing div tag, append GPRO hidden input to the end
				$input_html .= $hc_input_markup;
			}
		}

		add_filter( 'gform_field_input', array( $this, 'read_only_input' ), 11, 5 );

		return $input_html;
	}

	public function get_hidden_capture_input_id( $form_id, $input_id ) {

		if ( intval( $input_id ) != $input_id ) {
			$input_id_bits               = explode( '.', $input_id );
			list( $field_id, $input_id ) = $input_id_bits;
			$hc_input_id                 = sprintf( 'gwro_hidden_capture_%d_%d_%d', $form_id, $field_id, $input_id );
		} else {
			$hc_input_id = sprintf( 'gwro_hidden_capture_%d_%d', $form_id, $input_id );
		}

		return $hc_input_id;
	}

	public function get_hidden_capture_markup( $form_id, $input_id, $value ) {

		$hc_input_id = $this->get_hidden_capture_input_id( $form_id, $input_id );

		$field = GFAPI::get_field( $form_id, $input_id );

		if ( is_array( $value ) && ! empty( $field->inputs ) ) {
			$value = rgar( $value, (string) $input_id );
		} elseif ( is_array( $value ) ) {
			$value = json_encode( $value );
		}

		return sprintf( '<input type="hidden" id="%s" name="%s" value="%s" class="gf-default-disabled" />', $hc_input_id, $hc_input_id, esc_attr( $value ) );
	}

	public function process_hidden_captures( $form ) {

		/**
		 * In some instances (i.e. parent submission of Nested Forms), the gform_pre_process filter may be applied to a
		 * form that is not currently being submitted. Let's make sure we're only working with the submitted form.
		 * Update: We also need a second check here for Gravity Flow as they use `gravityflow_submit` instead. HS#27204
		 */
		if ( rgpost( 'gform_submit' ) != $form['id'] && rgpost( 'gravityflow_submit' ) != $form['id'] ) {
			return $form;
		}

		foreach ( $_POST as $key => $value ) {

			if ( strpos( $key, 'gwro_hidden_capture_' ) !== 0 ) {
				continue;
			}

			// gets 481, 5, & 1 from a string like "gwro_hidden_capture_481_5_1"
			list( $form_id, $field_id, $input_id ) = array_pad( explode( '_', str_replace( 'gwro_hidden_capture_', '', $key ) ), 3, false );

			$field = GFFormsModel::get_field( $form, $field_id );
			switch ( $field->get_input_type() ) {
				// time fields are in array format in the POST
				case 'time':
					$full_input_id = $field_id;
					$full_value    = rgpost( "input_{$full_input_id}" );

					if ( ! is_array( $full_value ) ) {
						break;
					}

					$full_value[] = $value;
					$value        = $full_value;
					break;
				// date drop downs are in array format in the POST
				case 'date':
					$full_input_id = $field_id;
					$full_value    = array(
						rgpost( 'gwro_hidden_capture_' . $form_id . '_' . $field_id . '_1' ),
						rgpost( 'gwro_hidden_capture_' . $form_id . '_' . $field_id . '_2' ),
						rgpost( 'gwro_hidden_capture_' . $form_id . '_' . $field_id . '_3' ),
					);

					if ( count( array_filter( $full_value ) ) !== 3 ) {
						break;
					}

					$value = $full_value;
					break;
				default:
					// gets "5_1" from an array like array( 5, 1 ) or "5" from an array like array( 5, false )
					$full_input_id = implode( '_', array_filter( array( $field_id, $input_id ) ) );
			}

			// Only use hidden capture if $_POST does not already contain a value for this inputs;
			// this allows support for checking/unchecking via JS (i.e. checkbox fields).
			if ( empty( $_POST[ "input_{$full_input_id}" ] ) && $value ) {
				if ( method_exists( 'GFCommon', 'is_json' ) ) {
					$stripped_slashes_value = stripslashes( $value );

					if ( GFCommon::is_json( $stripped_slashes_value ) ) {
						$value = GFCommon::maybe_decode_json( $stripped_slashes_value );
					}
				}

				$_POST[ "input_{$full_input_id}" ] = $value;
			}
		}

		return $form;
	}

	public function process_hidden_captures_gravityview( $_, $entry, $view, $request ) {
		if ( ! wp_verify_nonce( rgpost( 'is_gv_edit_entry' ), 'is_gv_edit_entry' ) ) {
			return;
		}

		$form = GFAPI::get_form( $entry['form_id'] );
		$this->process_hidden_captures( $form );
	}

	public function get_field_value( $field ) {

		$field_values = $submitted_values = false;

		if ( isset( $_GET['gf_token'] ) ) {
			$incomplete_submission_info = GFFormsModel::get_draft_submission_values( $_GET['gf_token'] );
			if ( $incomplete_submission_info['form_id'] == $field['formId'] ) {
				$submission_details_json = $incomplete_submission_info['submission'];
				$submission_details      = json_decode( $submission_details_json, true );
				$submitted_values        = $submission_details['submitted_values'];
				$field_values            = $submission_details['field_values'];
			}
		}

		if ( function_exists( 'gravityview' ) && gravityview()->request->is_edit_entry() ) {
			$gv_entry = gravityview()->request->is_edit_entry();
			$value    = rgar( $gv_entry->as_entry(), $field->id );
		} elseif (
			method_exists( 'GP_Entry_Blocks\GF_Queryer', 'attach_to_current_block' )
			&& GP_Entry_Blocks\GF_Queryer::attach_to_current_block()
			&& GP_Entry_Blocks\GF_Queryer::attach_to_current_block()->is_edit_entry()
		) {
			$value = rgar( GP_Entry_Blocks\GF_Queryer::attach_to_current_block()->entry, $field->id );
		} elseif ( is_array( $submitted_values ) ) {
			$value = $submitted_values[ $field->id ];
		} else {
			$value = $field->get_value_default_if_empty( GFFormsModel::get_field_value( $field, $field_values ) );
		}

		$choices = (array) rgar( $field, 'choices' );
		$choices = array_filter( $choices );

		// Use GPPA hydrated value if current value is empty and gppa-values is enabled
		if ( rgar( $field, 'gppa-values-enabled', false ) && GFCommon::is_empty_array( $value ) ) {
			$value = $field->gppa_hydrated_value;
		}
		if ( ! $value && $field->get_input_type() == 'time' ) {

		}
		// if value is not available from post or prepop, check the choices (if field has choices)
		elseif ( ! $value && ! empty( $choices ) ) {

			$values = array();
			$index  = 1;

			foreach ( $choices as $choice ) {

				if ( $index % 10 == 0 ) {
					$index++;
				}

				if ( rgar( $choice, 'isSelected' ) ) {
					$full_input_id            = sprintf( '%d.%d', $field['id'], $index );
					$price                    = rgempty( 'price', $choice ) ? 0 : GFCommon::to_number( rgar( $choice, 'price' ) );
					$choice_value             = in_array( $field['type'], array( 'product', 'option' ) ) ? sprintf( '%s|%s', $choice['value'], $price ) : $choice['value'];
					$values[ $full_input_id ] = $choice_value;
				}

				$index++;

			}

			$input_type = GFFormsModel::get_input_type( $field );

			// if no choice is preselected and this is a select, get the first choice's value since it will be selected by default in the browser
			if ( empty( $values ) && in_array( $input_type, array( 'select', 'workflow_user', 'workflow_role', 'workflow_assignee_select' ) ) ) {
				$values[] = rgars( $choices, '0/value' );
			}

			switch ( $input_type ) {
				case 'multiselect':
					$value = implode( ',', $values );
					break;
				case 'checkbox':
					$value = $values;
					break;
				default:
					$value = reset( $values );
					break;
			}
		}

		return $value;
	}

	public function filter_rich_text_editor_options( $settings, $field ) {

		if ( $field->gwreadonly_enable ) {
			$settings['tinymce']['init_instance_callback'] = str_replace( 'function (editor) {', 'function (editor) { editor.setMode( "readonly" );', $settings['tinymce']['init_instance_callback'] );
		}

		return $settings;
	}

	public function get_address_select_input_id( $field ) {
		$input_id = false;
		switch ( $field->addressType ) {
			// US, Canadian, and any added using https://docs.gravityforms.com/gform_address_types/
			default:
				$input_id = 4;
				break;
			case 'international':
				$input_id = 6;
				break;
		}
		return $input_id;
	}

}

class GWReadOnly extends GP_Read_Only { };
