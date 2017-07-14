<?php
/*
Plugin Name: List Field Character Limit for Gravity Forms
Description: Gives the option of adding a textarea to a list field column
Version: 1.2.0
Author: Adrian Gordon
Author URI: http://www.itsupportguides.com 
License: GPL2
Text Domain: list-field-character-limit-for-gravity-forms

------------------------------------------------------------------------
Copyright 2016 Adrian Gordon

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

load_plugin_textdomain( 'itsg-list-field-character-limit-for-gravity-forms', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

add_action( 'admin_notices', array( 'ITSG_GF_List_Field_Character_Limit', 'admin_warnings' ), 20 );

if ( ! class_exists( 'ITSG_GF_List_Field_Character_Limit' ) ) {
    class ITSG_GF_List_Field_Character_Limit
    {
		private static $name = 'List Fields Character Limit for Gravity Forms';
		private static $slug = 'list-field-character-limit-for-gravity-forms';
		
		/**
         * Construct the plugin object
         */
		 public function __construct() {
			// register plugin functions through 'gform_loaded' - 
			// this delays the registration until Gravity Forms has loaded, ensuring it does not run before Gravity Forms is available.
            add_action( 'gform_loaded', array( &$this, 'register_actions' ) );
		}
		
		/*
         * Register plugin functions
         */
		function register_actions() {
            if ( ( self::is_gravityforms_installed() ) ) {
				
				// addon framework
				require_once( plugin_dir_path( __FILE__ ).'list-field-character-limit-for-gravity-forms-addon.php' );
				
				// start the plugin
				add_filter( 'gform_column_input_content', array( &$this, 'change_column_content' ), 10, 6 );
				add_action( 'gform_editor_js', array( &$this, 'editor_js' ) );
				
				// patch to allow JS and CSS to load when loading forms through wp-ajax requests
				add_action( 'gform_enqueue_scripts', array( &$this, 'datepicker_js' ), 90, 2 );
				
				add_action( 'gform_field_appearance_settings', array( &$this, 'field_datepicker_settings' ) , 10, 2 );
				add_filter( 'gform_tooltips', array( &$this, 'field_datepicker_tooltip' ) );
			}
		}
		
		
	/**
	 * BEGIN: patch to allow JS and CSS to load when loading forms through wp-ajax requests
	 *
	 */

		/*
         * Enqueue JavaScript to footer
         */
		public function datepicker_js( $form, $is_ajax ) {
			if ( $this->requires_scripts( $form, $is_ajax ) ) {
				wp_enqueue_script( 'gform_textarea_counter' );
				wp_enqueue_style( 'character-limit-css',  plugins_url( '/css/character-limit-css.css', __FILE__ ) );
				wp_register_script( 'character-limit-script', plugins_url( '/js/character-limit-script.js', __FILE__ ),  array( 'jquery' ) );
				// Localize the script with new data
				$this->localize_scripts( $form, $is_ajax );
				
			}
		} // END datepicker_js
		
		public function requires_scripts( $form, $is_ajax ) {
			if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX && ! GFCommon::is_form_editor() && is_array( $form ) ) {
				foreach ( $form['fields'] as $field ) {
					if ( 'list' == $field->type ) {
						$has_columns = is_array( $field->choices );
						if ( $has_columns ) {
							foreach( $field['choices'] as $choice ){
								if ( true  == rgar( $choice, 'isCharacterLimit' ) ) {
									return true;
								}
							}
						} elseif ( true == $field->itsg_list_field_character_limit ) {
							return true;
						}
					}
				}
			}
			
			return false;
		} // END requires_scripts
		
		function localize_scripts( $form, $is_ajax ) {
			// Localize the script with new data
			$character_limit_fields = array();
			if ( is_array( $form['fields'] ) ) {
				foreach ( $form['fields'] as $field ) {
					if ( 'list' == $field->type ) {
						$form_id = $form['id'];
						$field_id = $field['id'];
						$has_columns = is_array( $field->choices );
						if ( $has_columns ) {
							foreach( $field['choices'] as $key=>$choice ){
								if ( true  == rgar( $choice, 'isCharacterLimit' ) )  {
									$column_number = $key + 1;
									$character_limit_number = isset( $choice['isCharacterLimitNumber'] ) ? $choice['isCharacterLimitNumber'] : "";
									if ( ctype_digit( $character_limit_number ) ) {
										$character_limit_fields[ $field_id ][ $column_number ] = $character_limit_number;
									}
								}
							}
						} elseif ( true == $field->itsg_list_field_character_limit ) {
							$character_limit_number = isset( $field['itsg_list_field_character_limit_number'] ) ? $field['itsg_list_field_character_limit_number'] : "";
							if ( ctype_digit( $character_limit_number ) ) {
								$character_limit_fields[ $field_id ][1] = $character_limit_number;
							}
						}
					}
				}
			}
			
			$settings_array = array(
					'max_characters' => esc_attr__( 'max characters', 'list-field-character-limit-for-gravity-forms' ),
					'character_limit_fields' => $character_limit_fields,
					'form_id' => $form['id']
				);
			
			wp_localize_script( 'character-limit-script', 'character_limit', $settings_array );
			
			// Enqueued script with localized data.
			wp_enqueue_script( 'character-limit-script' );

		} // END localize_scripts
		
	/**
	 * END: patch to allow JS and CSS to load when loading forms through wp-ajax requests
	 *
	 */
			
		/*
         * Changes column field if 'character limit' option is ticked. 
         */
		public static function change_column_content( $input, $input_info, $field, $text, $value, $form_id ) {
			if ( GFCommon::is_form_editor() ) {
				return $input;
			} else {
				$has_columns = is_array( $field['choices'] );
				if ( $has_columns ) {
					foreach( $field['choices'] as $choice ){
						if ( $text == rgar( $choice, 'text' ) && true == rgar( $choice, 'isCharacterLimit' ) ) {
							if ( 'textarea' == rgar( $choice, 'isCharacterLimitFormat' ) ) {
								$textarea_value = !empty($value) ? 'value="' . $value . '"' : '';
								$new_input = str_replace( "<input ", "<textarea class='charcount' ", $input );
								return $new_input . $value . '</textarea>';
							} else {
								$new_input = str_replace( "<input ", "<input class='charcount' ", $input );
								return $new_input;
							}
						} elseif ( $text == rgar( $choice, 'text' ) ) {
							return $input;
						}
					}
				} else {
					if ( true == $field->itsg_list_field_character_limit ) {
						if ( 'textarea' == $field->itsg_list_field_character_limit_format ) {
							$textarea_value = !empty($value) ? 'value="' . $value . '"' : '';
							$new_input = str_replace( "<input ", "<textarea class='charcount' ", $input );
							return $new_input . $value . '</textarea>';
						} else {
							$new_input = str_replace( "<input ", "<input class='charcount' ", $input );
							return $new_input;
						}
					}
					return $input;
				}
			}
		} // itsg_gp_list_field_datepicker_change_column_content
				
		/*
         * JavaScript used by form editor - Functions taken from Gravity Forms source and extended to handle the 'Date field' option
         */
		public static function editor_js() {
		?>
		<script>
		// ADD drop down options to list field in form editor - hooks into existing GetFieldChoices function.
		(function (w){
			var GetFieldChoicesOld = w.GetFieldChoices;
			
			w.GetFieldChoices = function (){

				str = GetFieldChoicesOld.apply(this, [field]);
				
				if(field.choices == undefined)
				return "";
				
				for(var i=0; i<field.choices.length; i++){
				var inputType = GetInputType(field);
				var isCharacterLimit = field.choices[i].isCharacterLimit ? "checked" : "";
				var value = field.enableChoiceValue ? String(field.choices[i].value) : field.choices[i].text;
				//var isCharacterLimitFormat = typeof field.choices[i].isCharacterLimitFormat !== 'undefined' && field.choices[i].isCharacterLimitFormat ? field.choices[i].isCharacterLimitFormat : "input";
				var isCharacterLimitNumber = typeof field.choices[i].isCharacterLimitNumber !== 'undefined' ? field.choices[i].isCharacterLimitNumber : "";
				if (inputType == 'list' ){
				if (i == 0 ){
				str += "<p><strong><?php esc_attr_e( 'Character limits', 'list-field-character-limit-for-gravity-forms' ) ?></strong><br><?php esc_attr_e( 'Place a tick next to the column name to enable character limits. Enter a limit and select a field type.', 'list-field-character-limit-for-gravity-forms' ) ?></p>";
				}
				str += "<div>";
				 str += "<input type='checkbox' name='choice_charlimit' id='" + inputType + '_choice_charlimit_' + i + "' " + isCharacterLimit + " onclick=\"SetFieldChoiceCL( '" + inputType + "', " + i + " );itsg_gf_list_charlimit_function();\" /> ";
				 str += "	<label class='inline' for='"+ inputType + '_choice_charlimit_' + i + "'>"+value+" - Apply character limit</label>";
				 str += "<div style='display:none; background: rgb(244, 244, 244) none repeat scroll 0px 0px; padding: 10px; border-bottom: 1px solid grey; margin: 10px 0;' class='itsg_charlimit'>";
				 str += "<label for='" + inputType + '_choice_charlimit_' + i + "'>";
				 str += "<?php _e( 'Input type', 'list-field-character-limit-for-gravity-forms' ); ?></label>";
				 str += "<select class='choice_charlimitformat' id='" + inputType + '_choice_charlimitformat_' + i + "' onchange=\"SetFieldChoiceCL( '" + inputType + "', " + i + " );\" style='margin-bottom: 10px;'>";
				 str += "<option value='input'>input</option>";
				 str += "<option value='textarea'>textarea</option>";
				 str += "</select>";
				str += "<br>";
				 str += "<label for='" + inputType + '_choice_charlimtnumber_' + i + "'>";
				 str += "<?php _e( 'Character limit', 'list-field-character-limit-for-gravity-forms' ); ?></label>";
				 str += "<input type='number' value=\"" + isCharacterLimitNumber.replace(/"/g, "&quot;" ) + "\" class='choice_dropdown' id='" + inputType + '_choice_charlimtnumber_' + i + "' onblur=\"SetFieldChoiceCL( '" + inputType + "', " + i + " );\">";
				 str += "</div>";
				 str += "</div>";
				 }
				 }
				return str;
			}
		})(window || {});
		function SetFieldChoiceCL( inputType, index ){
			
			var element = jQuery( '#' + inputType + '_choice_selected_' + index );
			
			if ( 'list' == inputType ) {
				var element = jQuery( '#' + inputType + '_choice_charlimit_' + index );
				isCharacterLimit = element.is( ':checked' );
				isCharacterLimitFormat = jQuery( '#' + inputType + '_choice_charlimitformat_' + index ).val();
				isCharacterLimitNumber = jQuery( '#' + inputType + '_choice_charlimtnumber_' + index ).val();
			}
			field = GetSelectedField();

			if ( 'list' == inputType ) {
				isCharacterLimitFormat_value = typeof isCharacterLimitFormat !== 'undefined' && isCharacterLimitFormat ? isCharacterLimitFormat : "input";
				field.choices[index].isCharacterLimitFormat = isCharacterLimitFormat_value;
				field.choices[index].isCharacterLimitNumber = isCharacterLimitNumber;
			}

			//set field selections
			jQuery( '#field_columns input[name="choice_charlimit"]' ).each( function( index ){
				field.choices[index].isCharacterLimit = this.checked;
			});

			LoadBulkChoices( field );

			UpdateFieldChoices( GetInputType( field ) );
		}

		function itsg_gf_list_charlimit_function(){
			// handles displaying the date format option for multi column lists
			jQuery( '#field_columns input[name=choice_charlimit]' ).each( function() {
				if (jQuery( this ).is( ':checked' ) ) {
						jQuery( this ).parent( 'div' ).find( '.itsg_charlimit' ).show();
					}
					else {
						jQuery( this ).parent( 'div' ).find( '.itsg_charlimit' ).hide();
					}
			});
			
			// handles displaying the date format option for single column lists
			jQuery( '.ui-tabs-panel input#itsg_list_field_character_limit' ).each( function() {
				if (jQuery( this ).is( ':checked' ) ) {
						jQuery( this ).parent( 'li' ).find( '#itsg_list_field_character_limit_format_div' ).show();
					}
					else {
						jQuery( this ).parent( 'li' ).find( '#itsg_list_field_character_limit_format_div' ).hide();
					}
			});

			// only display this option if a single column list field
			jQuery( '#field_settings input[id=field_columns_enabled]:visible' ).each(function() {
				if (jQuery( this ).is( ':checked' ) ) {
						jQuery( this ).closest( '#field_settings' ).find( '.itsg_list_field_character_limit' ).hide();
						jQuery( '#field_columns:visible select.choice_charlimitformat' ).each( function( index ){
							jQuery( this ).val( field.choices[index].isCharacterLimitFormat );
						});
					}
					else {
						jQuery( this ).closest( '#field_settings' ).find( '.itsg_list_field_character_limit' ).show();
					}
			});
		}
		
		// trigger for when field is opened
		jQuery( document ).on( 'click', 'ul.gform_fields', function(){
			itsg_gf_list_charlimit_function();  
		});
		
		// trigger when 'Enable multiple columns' is ticked
		jQuery( document ).on( 'change', '#field_settings input[id=field_columns_enabled], .ui-tabs-panel input#itsg_list_field_character_limit', function(){
			itsg_gf_list_charlimit_function();
		});
		
		// trigger for when column titles are updated
		jQuery( document ).on( 'change', '#gfield_settings_columns_container #field_columns li', function() {
			InsertFieldChoice(0);
			DeleteFieldChoice(0);
			itsg_gf_list_charlimit_function();
		});
		
		// handle 'Enable datepicker' option in the Gravity forms editor
		jQuery( document ).ready( function($) {
				//adding setting to fields of type "list"
				fieldSettings['list'] += ', .itsg_list_field_character_limit';
				//set field values when field loads		
				jQuery( document ).bind( 'gform_load_field_settings', function( event, field, form ){
					jQuery( '#itsg_list_field_character_limit' ).prop( 'checked', field['itsg_list_field_character_limit'] );
				});
			});
			
		// handle 'Enable datepicker format' option in the Gravity forms editor
		jQuery( document ).ready( function($) {
				//adding setting to fields of type "list"
				fieldSettings['list'] += ', .itsg_list_field_character_limit_format';
				//set field values when field loads		
				jQuery( document ).bind( 'gform_load_field_settings', function( event, field, form ){
					var format_value = field['itsg_list_field_character_limit_format'] ? field['itsg_list_field_character_limit_format'] : 'input';
					jQuery( '#itsg_list_field_character_limit_format' ).val( format_value );
					jQuery( '#itsg_list_field_character_limit_number' ).val( field['itsg_list_field_character_limit_number'] );
				});
			});
		</script>	
		<?php
		} // END itsg_gp_list_field_datepicker_editor_js
		
		/*
          * Adds custom sortable setting for field
          */
        public static function field_datepicker_settings( $position, $form_id ) {      
            // Create settings on position 50 (top position)
            if ( 50 == $position ) {
				?>
				<li class="itsg_list_field_character_limit field_setting">
					<input type="checkbox" id="itsg_list_field_character_limit" onclick="SetFieldProperty( 'itsg_list_field_character_limit', this.checked);">
					<label class="inline" for="itsg_list_field_character_limit">
					<?php esc_attr_e( 'Apply character limit', 'list-field-character-limit-for-gravity-forms' ); ?>
					<?php gform_tooltip( 'itsg_list_field_character_limit' );?>
					</label>
					<div id="itsg_list_field_character_limit_format_div" style="background: rgb(244, 244, 244) none repeat scroll 0px 0px; padding: 10px; border-bottom: 1px solid grey; margin-top: 10px;" >
						<p><strong><?php esc_attr_e( 'Configure Character Limit Field', 'list-field-character-limit-for-gravity-forms' ); ?></strong>
						<br>
						<?php esc_attr_e( "Enter a limit and select a field type.", 'list-field-character-limit-for-gravity-forms' ); ?>
						</p>
						<label for="itsg_list_field_character_limit_format" ><?php esc_attr_e( 'Input type', 'list-field-character-limit-for-gravity-forms' ); ?></label>
						<select onchange="SetFieldProperty( 'itsg_list_field_character_limit_format', this.value);" id="itsg_list_field_character_limit_format" class="itsg_list_field_character_limit_format" style='margin-bottom: 10px;' >
							<option value="input">input</option>
							<option value="textarea">textarea</option>
						</select>
						<br>
						<label for="itsg_list_field_character_limit_number" ><?php esc_attr_e( 'Character limit:', 'list-field-character-limit-for-gravity-forms' ); ?></label>
						<input onchange="SetFieldProperty( 'itsg_list_field_character_limit_number', this.value);" type="number" id="itsg_list_field_character_limit_number" class="itsg_list_field_character_limit_number">
					</div>
				</li>
			<?php
            }
        } // END field_datepicker_settings
		
		/*
         * Tooltip for for datepicker option
         */
		public static function field_datepicker_tooltip( $tooltips ){
			$tooltips['itsg_list_field_character_limit'] = '<h6>' . __( 'Apply limit', 'list-field-character-limit-for-gravity-forms' ) . '</h6>' . __( 'Applies character limit to column. Only applies to single column list fields.', 'list-field-character-limit-for-gravity-forms' );
			return $tooltips;
		} // END field_datepicker_tooltip
		
		/*
         * Warning message if Gravity Forms is installed and enabled
         */
		public static function admin_warnings() {
			if ( !self::is_gravityforms_installed() ) {
				printf(
					'<div class="error"><h3>%s</h3><p>%s</p><p>%s</p></div>',
						__( 'Warning', 'list-field-character-limit-for-gravity-forms' ),
						sprintf ( __( 'The plugin %s requires Gravity Forms to be installed.', 'list-field-character-limit-for-gravity-forms' ), '<strong>'.self::$name.'</strong>' ),
						sprintf ( esc_html__( 'Please %sdownload the latest version of Gravity Forms%s and try again.', 'list-field-character-limit-for-gravity-forms' ), '<a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=299380" target="_blank">', '</a>' )
					);
			}
		} // END admin_warnings
		
		/*
         * Check if GF is installed
         */
        private static function is_gravityforms_installed() {
			if ( !function_exists( 'is_plugin_active' ) || !function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			if ( is_multisite() ) {
				return (is_plugin_active_for_network( 'gravityforms/gravityforms.php' ) || is_plugin_active( 'gravityforms/gravityforms.php' ) );
			} else {
				return is_plugin_active( 'gravityforms/gravityforms.php' );
			}
        } // END is_gravityforms_installed
				
		/*
         * Check if list field has a date picker in the current form
         */
		private static function list_has_datepicker_field($form ) {
			if ( is_array( $form['fields'] ) ) {
				foreach ( $form['fields'] as $field ) {
					if ( 'list' == $field->type ) {
						$has_columns = is_array( $field->choices );
						if ( $has_columns ) {
							foreach( $field['choices'] as $choice ){
								if ( true  == rgar( $choice, 'isCharacterLimit' ) ) {
									return true;
								}
							}
						} elseif ( true == $field->itsg_list_field_character_limit ) {
							return true;
						}
					}
				}
			}
		return false;
		} // END list_has_datepicker_field
		
	}
    $ITSG_GF_List_Field_Character_Limit = new ITSG_GF_List_Field_Character_Limit();
}