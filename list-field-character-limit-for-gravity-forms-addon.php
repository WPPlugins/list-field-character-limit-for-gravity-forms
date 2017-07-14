<?php
/* 
 *   Setup the settings page for configuring the options
 */
if ( class_exists( 'GFForms' ) ) {
	GFForms::include_addon_framework();
	class GFListFieldCharLimit extends GFAddOn {
		protected $_version = '1.2.0';
		protected $_min_gravityforms_version = '1.7.9999';
		protected $_slug = 'GFListFieldCharLimit';
		protected $_full_path = __FILE__;
		protected $_title = 'List Field Character Limit for Gravity Forms';
		protected $_short_title = 'List Field Character Limit';
		
		public function scripts() {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
			
			$scripts = array(
				array(
					'handle'    => 'character-limit-script',
					'src'       => $this->get_base_url() . "/js/character-limit-script{$min}.js",
					'version'   => $this->_version,
					'deps'      => array( 'jquery', 'gform_textarea_counter' ),
					'enqueue'   => array( array( $this, 'requires_scripts' ) ),
					'in_footer' => true,
					'callback'  => array( $this, 'localize_scripts' ),
				)
			);
			
			return array_merge( parent::scripts(), $scripts );
		} // END scripts
		
		public function localize_scripts( $form, $is_ajax ) {
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
			
			$is_entry_detail = GFCommon::is_entry_detail();
			
			$settings_array = array(
				'max_characters' => esc_attr__( 'max characters', 'list-field-character-limit-for-gravity-forms' ),
				'character_limit_fields' => $character_limit_fields,
				'form_id' => $form['id'],
				'is_entry_detail' => $is_entry_detail ? $is_entry_detail : 0, 
			);
			
			wp_localize_script( 'character-limit-script', 'character_limit', $settings_array );

		} // END localize_scripts

		public function styles() {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
			
			$styles = array(
				array(
					'handle'  => 'character-limit-css',
					'src'     => $this->get_base_url() . "/css/character-limit-css{$min}.css",
					'version' => $this->_version,
					'media'   => 'screen',
					'enqueue' => array( array( $this, 'requires_scripts' ) ),
				),
			);

			return array_merge( parent::styles(), $styles );
		} // END styles
		
		public function requires_scripts( $form, $is_ajax ) {
			if ( ! $this->is_form_editor() && is_array( $form ) ) {
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
		
    }
    new GFListFieldCharLimit();
}