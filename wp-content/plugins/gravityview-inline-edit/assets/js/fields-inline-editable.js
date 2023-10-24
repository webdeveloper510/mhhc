/**
 * Initialize inline edit and handle all operations
 * related to it
 *
 * @license   GPL2+
 * @link      http://gravityview.co
 *
 * @since 1.0.0
 */

/* global gv_inline_x */ // PhpStorm helper
( function ( jQuery ) {
    'use strict';

    jQuery( document ).ready( function ( $ ) {

        var self = {};

        /**
         * Set inline edit defaults and initialize everything
         */
        self.init = function () {
            $.fn.editable.defaults.mode = gv_inline_x.mode;
            $.fn.editable.defaults.emptytext = gv_inline_x.emptytext;
            $.fn.editableform.buttons = gv_inline_x.buttons;
            $.fn.editableContainer.Inline.prototype.containerClass = 'gv-editable-container gform_wrapper editable-container editable-inline';
            $.fn.editableContainer.Popup.prototype.containerClass = 'gv-editable-container gform_wrapper editable-container editable-popup gv-editable-popover';
			( $.fn.popover.Constructor.defaults || $.fn.popover.Constructor.Default || {} ).template = '<div class="gv-editable-popover popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>';
            $.fn.popover.Constructor.prototype.destroy = function () {
                var that = this;
                clearTimeout( this.timeout );
                this.hide( function () {
                    // editable('destroy') method removes the element before Bootstrap's popover destroy method fires, so a check for "null" is needed or else "cannot read property 'off' of null" is thrown
                    if ( that.$element == null ) {
                        return;
                    }
                    that.$element.off( '.' + that.type ).removeData( 'bs.' + that.type );
                    if ( that.$tip ) {
                        that.$tip.detach();
                    }
                    that.$tip = null;
                    that.$arrow = null;
                    that.$viewport = null;
                    that.$element = null;
                } );
            };
            self.setInlineEditableFields();
            self.setInlineEditableInitialState();
            self.toggleInlineEdit();
            self.initColumnToggle();
            self.gfMoveToggleButton();
            self.preventClicks();
        };

        /**
         * From an instance of WP_Error, get the first error message
         * Like the equivalent WP_Error method, get the first item from the first error
         *
         * @return {string} WP_Error message
         */
        self.getWPErrorMessage = function ( $wpError ) {
            for ( var error in $wpError ) {
                if ( $wpError.hasOwnProperty( error ) ) {
                    return $wpError[error][0];
                }
            }
        };

        /**
         * After editing a field via inline edit, update fields based on the response.
         * Used primarily for updating number calculation fields
         *
         * @param {array} updateResponse  AJAX response
         */
		self.updateFieldsOnEdit = function ( updateResponse ) {
            if ( !$.isArray( updateResponse ) ) {
				return;
			}

			$.each( updateResponse, function ( i, response ) {
				if ( response.selector && response.data ) {
					var $selector = $( response.selector );
					var value = response.value;

					try {
						value = JSON.parse( response.value );
					} catch ( e ) {
					}

					$.each( $selector, function ( i, el ) {
						var $el = $( el );
						var displayValue = response.data.display_value || '';
						var entryLink = $el.attr( 'data-entry-link' );
						var showMapLink = $el.attr( 'data-show-map-link' );

						if ( showMapLink && response.data.map_link ) {
							displayValue += '<br>' + response.data.map_link;
						}

						if ( entryLink ) {
							displayValue = $( '<a />', { href: entryLink, html: displayValue } );
						}

						$el.attr( 'data-display', displayValue );

                        // Change 'total' field that has calculation.
                        if ( response.has_calculation ) {
                            $el.text( displayValue );
                        }

						if ( typeof value === 'object' ) {
						    $el.editable( 'setValue', value );
						}
					} );
				}
			} );
		};

        /**
         * Convert the GF date format into a friendly X-editable format
         *
         * @param  {string} $GFdateFormat The GF date format
         * @return {string} X-editable date format
         */
        self.convertDateFormat = function ( $GFdateFormat ) {
            var $formatName;

            switch ( $GFdateFormat ) {
                case 'mdy' :
                    $formatName = 'mm/dd/yyyy';
                    break;
                case 'dmy' :
                    $formatName = 'dd/mm/yyyy';
                    break;
                case 'dmy_dash' :
                    $formatName = 'dd-mm-yyyy';
                    break;
                case 'dmy_dot' :
                    $formatName = 'dd.mm.yyyy';
                    break;
                case 'ymd_slash' :
                    $formatName = 'yyyy/mm/dd';
                    break;
                case 'ymd_dash' :
                    $formatName = 'yyyy-mm-dd';
                    break;
                case 'ymd_dot' :
                    $formatName = 'yyyy.mm.dd';
                    break;
                case 'fdy' :
                    $formatName = 'MM d, yyyy';
                    break;
            }
            return $formatName;
        };

        /**
         * Iterate through all fields with class `gv-inline-editable-view` and
         * class `gv-inline-editable-field-*` and convert them into inline-editable fields
         */
        self.setInlineEditableFields = function () {
            $( '.gv-inline-editable-view [class^=gv-inline-editable-field]' ).each( function ( i, val ) {

                self.initializeEditableField( $( this ) );
            } );
        };

        /**
         * Make field editable
         *
         * @param {Object} $field Field DOM object
         */
        self.initializeEditableField = function ( $field ) {
            var editableOptions, tplName;

            var form_id = $field.data( 'formid' );
            var field_id = $field.data( 'fieldid' );
            var input_id = $field.data( 'inputid' );
            var view_id = $field.data( 'viewid' );
            var entry_id = $field.data( 'entryid' );
            var field_type = $field.data( 'type' );

            editableOptions = {
                pk: entry_id,
                url: gv_inline_x.url,
                container: gv_inline_x.container,
                showbuttons: gv_inline_x.showbuttons,
                onblur: gv_inline_x.onblur,
                success: function( response ) {
                    if ( response && response.errors ) {
                        return self.getWPErrorMessage( response.errors );
                    }
                    self.updateFieldsOnEdit( response );
                },
                display: null,
                savenochange: true,
            };


            // This is a datepicker field
            if ( $field.data( 'dateformat' ) ) {
                editableOptions.format = 'yyyy-mm-dd';
                editableOptions.viewformat = self.convertDateFormat( $field.data( 'dateformat' ) );
                editableOptions.datepicker = {
                    firstDay: 1,
                    showOn: 'focus',
                };
                editableOptions.mode = 'popup';
            }

            // Set templates
            tplName = field_type;
            if ( $field.data( 'tplmode' ) && 'address' !== field_type ) {
                tplName += $field.data( 'tplmode' );
            }

            if ( gv_inline_x.templates.hasOwnProperty( tplName + '_' + form_id + '_' + field_id ) ) {
                var template = gv_inline_x.templates[tplName + '_' + form_id + '_' + field_id];
                var fieldValue = $field.attr( 'data-value' );
                var fieldValueOther = $field.attr( 'data-other-choice' ) || '';

                // Since we're using 1 template for all radio fields, it will contain a hardcoded value for "Other" choice of whichever last entry that's displayed.
                // To correct this, we need to replace the value in the template with the one associated with the entry.
                if ( fieldValue === 'gf_other_choice' && fieldValueOther.length ) {
                    template = template.replace( /(_other.*?value=')(.*?)'/, '$1' + fieldValueOther + '\'' );
                }

                editableOptions.tpl = template;
            } else if ( gv_inline_x.templates.hasOwnProperty( tplName ) ) {
                editableOptions.tpl = gv_inline_x.templates[tplName];
            }

            // Switch template to single-field equivalanes where applicable
            var singleModeDetails = self.getSingleFieldModeTemplate( $field, field_type, editableOptions.tpl );

            if ( singleModeDetails ) {
                if ( singleModeDetails.disableField ) {
                    $field.removeClass();
                    return;
                }
                editableOptions.tpl = singleModeDetails.tpl;
            }

            // Set parameters passed to the server when the field is saved
            editableOptions.params = function ( fieldParms ) {
                var defaultParams = {
                    gv_inline_edit_field: 'true',
                    nonce: gv_inline_x.nonce,
                    type: field_type,
                    form_id: form_id,
                    field_id: field_id,
                    input_id: input_id,
                    view_id: view_id,
                };

                if ( singleModeDetails ) {
                    editableOptions.params.inputNumber = singleModeDetails.inputNumber;
                }

                // Make sure that we're passing the actual "Other" Text input value and not "gf_other_choice" that's detected by Editable
                if ( field_type === 'radiolist' ) {
                    // "Other" Text input element is located multiple locations based on whether we're editing inline (there's also difference between Entries and View screens) or inside a popup
                    var popupId = $( '#' + fieldParms.name ).attr( 'aria-describedby' );
                    var $input;

                    if ( popupId ) {
                        $input = $( '#' + popupId ).find( 'input:checked' );
                    } else {
                        $input = ( window.pagenow === 'forms_page_gf_entries' ) ? $( this ).parents( 'td' ).find( '.gv-editable-container input:checked' ) : $( this ).next( '.gv-editable-container' ).find( 'input:checked' );
                    }

                    // Set or clear "data-other-choice" attribute when "Other" choice is selected, so that we're properly initializing this field when it's recreated after each update/close
                    if ( $input.val() === 'gf_other_choice' ) {
                        fieldParms.value = $input.next().val();

                        $( '#' + fieldParms.name ).attr( 'data-other-choice', $input.next().val() );
                    } else {

                        $( '#' + fieldParms.name ).removeAttr( 'data-other-choice' );
                    }
                }

                return Object.assign( {}, defaultParams, fieldParms );
            };


            if(field_id === 'created_by' && field_type === 'select2'){
                editableOptions.select2 = {
                    allowClear: true,
                    placeholder: gv_inline_x.searchforuserstext,
                    dropdownParent:'.editable-container',
                    minimumInputLength: 1,
                    width:'200px',
                    dropdownAutoWidth: true,
                    ajax: {
                        url: gv_inline_x.url,
                        dataType: 'json',
                        type: 'POST',
                        delay: 250,
                        cache: true,
                        data: function( params ) {
                            var query = {
                                search: params.term,
                                action: 'gv_inline_edit_get_users',
                                nonce: gv_inline_x.nonce
                            }
                            return query;
                        },
                    },
                };
    
                editableOptions.tpl= '<select></select>';
                editableOptions.ajaxOptions= {
                    type: 'POST'
                };
            
            }


            $field.editable( editableOptions )

            // For textarea fields
            if ( $field.data( 'maxLength' ) ) {
                editableOptions.params.maxlength = $field.data( 'maxLength' );
            }

            // For gvlist, multicolumn support
            if ( 'gvlist' === field_type && $field.data( 'tplmode' ).match( /multi_/ ) ) {
                $field.editable( 'option', 'multicolumn', true );
                $field.editable( 'option', 'colcount', $field.data( 'colcount' ) );
                self.setGvListEmpty( $field );
            }

            // File upload fields should have "{#} files" text. If not, the field is empty.
            if( 'file' === field_type && $field.attr('multiple') && '' === $field.text() ) {
                $field.editable( 'setValue', '' );
            }

            $field.on( 'shown', self.inlineEditFieldsOnShown );
        };

        /**
         * Runs after all inline edit fields are shown
         */
        self.inlineEditFieldsOnShown = function ( e, editable ) {
            
            if($(editable.$element).hasClass('gv-inline-edit-user-select2')){
                $(editable.input.$input).on('select2:select', function (e) {
                    var data = e.params.data;
                    editable.options.display = function(value,source){
                        $(this).html(data.text);
                    };
                });
            }

            // We're in Entries. The first column has a link, so we need to move the form up one level.
            if ( 'inline' === editable.options.mode ) {
                if ( editable.container.$element.parents( '.column-primary' ).length ) {
                    editable.container.$tip.insertBefore( editable.container.$element.parent() );
                }
            }

            if ( 'textarea' === editable.input.type ) {
                editable.input.$input.attr( 'maxlength', editable.options.maxlength );
            }
            if ( 'number' === editable.input.type ) {
                editable.input.$input.attr( 'step', 'any' );
            }
  
            // Prevent column editing submitting at once.
            if(editable.input.$input.length > 0){
                editable.input.$input[0].addEventListener("input", function() {
                    $(this).addClass('edited-input');
                });
            }

        };

        /**
         * For a multi-column `gvlist` item, if the value is
         * empty, set the field to empty instead of showing column headers
         *
         * @param {Object} $gvList DOM object of the gvlist entry to be examined
         */
        self.setGvListEmpty = function ( $gvList ) {
            var gvListSourceData = $gvList.data( 'source' );

            if ( null === gvListSourceData[0] || 'object' !== typeof gvListSourceData[0] ) {
                return;
            }

            var valueArray = $.map( gvListSourceData[0], function ( val, index ) {
                if ( val.length > 0 ) {
                    return [ val ];
                }
            } );

            if ( 0 === valueArray.length ) {
                $gvList.editable( 'setValue', '' );
            }
        };

        /**
         * For some advanced fields, it's possible to use one of their input fields as
         * a stand-alone field in a view e.g. For an address, you can use the 'Country' field
         * as an entry in a view. This method returns the appropriate template for each of
         * these fields and the corresponding input number.
         * Note that each field in an advanced field has a corresponding unique input number.
         *
         * @param {Object} $field    The field being evaluated
         * @param {string} fieldType The field type
         * @return {Object|boolean} false if the field isn't eligible for single-field mode or an object containing the template and input number for the single field
         */
        self.getSingleFieldModeTemplate = function ( $field, fieldType, template ) {
            var eligibleFieldsSingleFieldMode = [ 'name', 'address', 'gvtime', 'checklist' ]; // Which fields do we enable this mode on

            var formId = $field.data( 'formid' );
            var fieldId = $field.data( 'fieldid' );
            var inputNumber = $field.data( 'inputid' );

            /*
             We activate this mode only for fields in eligibleFieldsSingleFieldMode; even then, we need to be able to retrieve the
             input number for the field in question. All advanced fields have input numbers for the individual fields they store;
             we get that input number by traversing the DOM to get the wrapping td
             */
            if ( -1 === $.inArray( fieldType, eligibleFieldsSingleFieldMode ) || '' === inputNumber ) {
                return false;
            }

            inputNumber = inputNumber * 1;
            var singleFieldModeTpl = '';
            var disableField = false;

            switch ( fieldType ) {
                case 'name':
                    if ( 2 === inputNumber ) { // prefix input
                        singleFieldModeTpl += '<span class="gv-inline-edit-single-field-mode" data-inputnumber="' + inputNumber + '">' + gv_inline_x.templates[fieldType + 'prefixes'] + '</span>';
                    }
                    break;
                case 'checklist':
                    // template contains all checkboxes in a <div><ul></div>
                    var single_checkbox_html = $( template )
                        // Remove all checkboxes that aren't the one we want to show
                        .find( 'li,div' ).not( ':has(input[name$="\.' + inputNumber + '"])' ).remove().end()
                        // And grab the <div class="ginput_container"> wrapper
                        .parents( 'div' )[0].outerHTML;

                    singleFieldModeTpl += '<div data-inputnumber="' + inputNumber + '">' + single_checkbox_html + '</div>';
                    break;
                case 'address':
                    // Hide disabled inputs
                    var hiddenFields = $field.data( 'hidden' );
                    var tplMode = $field.data( 'tplmode' );
                    var tplId = fieldType + tplMode + '_' + formId + '_' + fieldId;

                    if ( $.isArray( hiddenFields ) ) {
                        $.each( hiddenFields, function ( i, val ) { // $.inArray does strict comparison so it'll fail
                            if ( parseInt( inputNumber, 10 ) === parseInt( val, 10 ) ) {
                                disableField = true;
                            }
                        } );
                    }
                    if ( 4 === inputNumber && 'international' !== tplMode && gv_inline_x.templates.hasOwnProperty( tplId ) ) { // canadian provinces, US states and any custom states added via filters. Differentiated using $field.data( 'tplmode' )
                        singleFieldModeTpl += '<select class="gv-inline-edit-single-field-mode" data-inputnumber="' + inputNumber + '">' + gv_inline_x.templates[tplId] + '</select>';
                    }
                    if ( 6 === inputNumber ) { // country input
                        singleFieldModeTpl += '<select class="gv-inline-edit-single-field-mode" data-inputnumber="' + inputNumber + '">' + gv_inline_x.templates[tplId] + '</select>';
                    }
                    break;
                case 'gvtime':
                    if ( 3 === inputNumber ) { // am/pm selector
                        singleFieldModeTpl += '<select class="gv-inline-edit-single-field-mode" data-inputnumber="' + inputNumber + '">' + '<option value="am">AM</option>' + '<option value="pm">PM</option>' + '</select>';
                    }
                    break;
            }

            if ( !singleFieldModeTpl ) {
                singleFieldModeTpl += '<input type="text" class="gv-inline-edit-single-field-mode" data-inputnumber="' + inputNumber + '"/>';
            }
            return {
                tpl: '<div>' + singleFieldModeTpl + '</div>',
                inputNumber: inputNumber,
                disableField: disableField,
            };
        };

        /**
         * Override the default inline-edit error method to support
         * HTML in the error message
         */
        $.fn.editableform.Constructor.prototype.error = function ( msg ) {
            var $group = this.$form.find( '.control-group' ),
                $block = this.$form.find( '.editable-error-block' );

            if ( msg === false ) {
                $group.removeClass( $.fn.editableform.errorGroupClass );
                $block.removeClass( $.fn.editableform.errorBlockClass ).empty().hide();
            } else {
                $group.addClass( $.fn.editableform.errorGroupClass );
                $block.addClass( $.fn.editableform.errorBlockClass ).html( msg ).show();
            }
        };

        /**
         * Set the editable state for a View on load
         *
         * @return {void}
         */
        self.setInlineEditableInitialState = function () {
            $( '.gv-inline-editable-view' ).each( function () {

                if ( !$.cookie ) {

                    if ( console ) {
                        console.info( 'jQuery Cookie not available; setting initial state to enabled.' );
                    }

                    self.setEditableState( $( this ), 'enabled', false );

                    return;
                }

                var view_id = $( this ).find( '.gravityview-inline-edit-id' ).val();

                if ( 'undefined' === typeof view_id ) {
                    if ( console ) {
                        console.error( 'View ID is undefined when setting initial state.' );
                    }
                    return;
                }

                // On load
                var stored_state = $.cookie( 'gv-inline-edit-' + view_id );

                self.setEditableState( $( this ), stored_state, false );

            } );
        };

        /**
         * Set the state, update button text, set cookie & and add classes associated with editable enabled/disabled states
         *
         * @param {jQuery[]} $views
         * @param {string} state
         * @param {bool} set_cookie
         *
         * @return {void}
         */
        self.setEditableState = function ( $views, state, set_cookie ) {

            $views.find( '.gv-inline-editable-disabled' ).toggleClass( 'editable-disabled', ( state === 'enabled' ) );

            if ( state && state === 'enabled' ) {

                $views.removeClass( 'gv-inline-edit-off' ).addClass( 'gv-inline-edit-on' );

                $views.find( '[class^=gv-inline-editable-field]' ).editable( 'enable' );

                if ( gv_inline_x.showinputs ) {
                    if ( '1' === gv_inline_x.showinputs ) {
                        gv_inline_x.showinputs = '[class^=gv-inline-editable-field]';
                    }
                    $views.find( gv_inline_x.showinputs ).editable( 'show' );
                }

            } else {
                $views.removeClass( 'gv-inline-edit-on' ).addClass( 'gv-inline-edit-off' );

                $views.find( '[class^=gv-inline-editable-field]' ).editable( 'disable' );
            }

            $views.find( '.inline-edit-enable' ).text( function () {
                return $( this ).attr( 'data-label-' + state );
            } );

            if ( set_cookie && $.cookie ) {
                var view_id = $views.find( '.gravityview-inline-edit-id' ).val();
                $.cookie( 'gv-inline-edit-' + view_id, state, {
                    domain: gv_inline_x.cookie_domain,
                    path: gv_inline_x.cookiepath,
                } );
            } else if ( set_cookie && !$.cookie && console ) {
                console.error( 'jQuery Cookie script not loaded' );
            }

            /**
             * todo: jsDoc
             */
            $( document ).trigger( 'gravityview-inline-edit/set-state', {
                'state': state,
                'views': $views,
            } );
        };

        /**
         * In the front-end, turn inline edit functionality on/off
         *
         * // TODO: Toggle body class to allow better Bootstrap popover namespacing
         *
         * @return {void}
         */
        self.toggleInlineEdit = function () {

            $( '.inline-edit-enable' ).on( 'click', function ( e ) {
                e.preventDefault();
                e.stopImmediatePropagation();

                // What View?
                var $view = $( this ).parents( '.gv-inline-editable-view' );
                var $editable = $view.find( '[class^=gv-inline-editable-field]' );

                // No editable fields. Show an alert and return early.
                if ( ! $editable.length ) {
                    alert( gv_inline_x.nofieldstext );
                    return;
                }

                // Toggle
                var enabled_state;
                if ( $editable.data( 'editable' ).options['disabled'] ) {
                    $editable.editable( 'enable' );
                    enabled_state = 'enabled';
                } else {
                    $editable.editable( 'disable' );
                    enabled_state = 'disabled';
                }

                self.setEditableState( $view, enabled_state, true );
            } );
        };

        /**
         * When Inline Edit is active, click on a column header in GF or GravityView, and you can edit the whole column directly
         *
         * @since 1.0
         *
         * @return {void}
         */
        self.initColumnToggle = function () {

            $( '.wp-admin .gf_entries, .gv-table-view' ).find( 'thead th, tfoot th' ).on( 'click', function ( e ) {

                // If Inline Edit isn't enabled, act normal
                if ( !$( this ).parents( '.gv-inline-edit-on' ).length ) {
                    return true;
                }

                if ( $( this ).is( '#column_selector' ) ) {
                    return true;
                }

                e.preventDefault();

                $( this ).toggleClass( 'gv-inline-edit-column-on' );

                // If in the Entries screen, columns have `column-field_id-` class structure.
                // In GravityView frontend, columns have `gv-field-` structure
                var regex_search = $( 'body' ).hasClass( 'wp-admin' ) ? /column-field_id-/ : /gv-field-/;

                // Get an array of all classes in the `class` attribute
                var class_list = $( this ).attr( 'class' ).split( /\s+/ );

                // We only want the one matching CSS name
                var css_class_name = false;
                $.each( class_list, function ( index, column_class_name ) {
                    if ( column_class_name.match( regex_search ) ) {
                        css_class_name = column_class_name;
                        return false;
                    }
                } );

                // Something's gone wrong, the class wasn't found
                if ( !css_class_name ) {
                    return false;
                }

                // If column already enabled, turn off and restore options setting
                var is_column_on = $( this ).hasClass( 'gv-inline-edit-column-on' );

                var $editables = $( this )
                    .parents( 'table' )
                    .find( 'td[class~="' + css_class_name + '"]' )
                    .find( '[class^=gv-inline-editable-field]' );

                $editables
                    .editable( 'option', 'showbuttons', false )
                    .editable( 'option', 'mode', is_column_on ? 'inline' : gv_inline_x.mode )
                    .editable( 'option', 'onblur', is_column_on ? 'submit' : gv_inline_x.onblur );

                $( 'html, body' ).css( { overflow: 'hidden', height: '100%' } );

                if ( is_column_on ) {
                    $editables.editable( 'show' );
                }

                $( ':focus' ).blur();

                $( 'html, body' ).css( { overflow: 'auto', height: 'auto' } );

                return false;
            } );
        };

        /**
         * When in the WP Admin "Entries" screen, allow our script to disable links by adding `disabled="disabled` to them
         * @return {void}
         */
        self.gfMoveToggleButton = function () {
            $( '.wp-admin .inline-edit-enable' ).insertAfter( '.tablenav.top .bulkactions' ).removeClass('hidden');
        };

        /**
         * When Inline Editing is active and a field isn't editable, prevent link clicking
         * @return {void}
         */
        self.preventClicks = function () {
            $( '.gv-inline-editable-disabled a' ).on( 'click', function ( e ) {
                if ( $( this ).parents( '.editable-disabled' ).length ) {
                    e.preventDefault();
                    return false;
                }
                return true;
            } );
        };

        /**
         * Fix select2 issue focus in 'created_by' field.
         */
        $(window).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        /**
         * Trigger to extend global gv_inline_x object's templates data
         */
        $( window ).on( 'gravityview-inline-edit/extend-template-data', function ( e, data ) {
            window.gv_inline_x.templates = $.extend( {}, window.gv_inline_x.templates, data );
        } );

        /**
         * Trigger to initialize plugin
         */
        $( window ).on( 'gravityview-inline-edit/init', function () {
            $( self.init );
        } );

        /**
         * Trigger to initialize plugin on Datatables responsive mode.
         * @since 1.4.4
         */
        $( window ).on( 'gravityview-datatables/event/responsive', self.init );

        /**
         * Initialize plugin on load if disableInitOnLoad parameter is not set
         */
        if ( !!!+gv_inline_x.disableInitOnLoad ) {
            $( self.init );
        }
    } );

}( jQuery ) );
