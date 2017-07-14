function itsg_gf_ajax_charlimit_function(){
	var character_limit_fields = character_limit.character_limit_fields;
	var form_id = character_limit.form_id;
	//var myJson = JSON.parse(character_limit_fields);
	
	for ( var key in character_limit_fields ) {
		// skip loop if the property is from prototype
		if ( !character_limit_fields.hasOwnProperty( key ) ) continue;

		var obj = character_limit_fields[ key ];
		for ( var prop in obj ) {
			// skip loop if the property is from prototype
			if( !obj.hasOwnProperty( prop ) ) continue;
			
			var field_id = key;
			var field_column = prop;
			var field_char_length = obj[ prop ];
			
			console.log( 'list-field-character-limit-for-gravity-forms :: field_id: ' + field_id + ' field_column: ' + field_column + ' field_char_length: ' + field_char_length );
			
			jQuery( '#field_'+form_id+'_'+field_id+' .gfield_list .gfield_list_'+field_id+'_cell'+field_column+' .charcount' ).each(
				function() {
					jQuery( this ).textareaCount({'maxCharacterSize': field_char_length, 'originalStyle': 'ginput_counter', 'displayFormat' : '#input of #max max characters' } );
					jQuery( this ).parents( 'table.gfield_list:not([role="grid"])' ).addClass( 'itsg_charcount_95w' );
				}
			);
			
		}
	};
}

if ( '1' == character_limit.is_entry_detail ) {
	// runs the main function when the page loads -- entry editor -- configures any existing upload fields
	jQuery( document ).ready( function($) {
		itsg_gf_ajax_charlimit_function();
		// bind the datepicker function to the 'add list item' button click event
		jQuery( '.gfield_list' ).on( 'click', '.add_list_item', function() {
			// remove contents of new row
			jQuery( this ).parents( 'tr' ).next().find( 'textarea' ).val('');
			// remove all existing counters
			jQuery( this ).parents( 'form#entry_form' ).each(function() {
				jQuery( this ).find( '.gfield_list tr .ginput_counter' ).remove();
			});
			itsg_gf_ajax_charlimit_function();
		});
	});
} else {
	// runs the main function when the page loads
	jQuery( document ).bind( 'gform_post_render', function($) {
		itsg_gf_ajax_charlimit_function();
		// bind the datepicker function to the 'add list item' button click event
		jQuery( '.gfield_list' ).on( 'click', '.add_list_item', function() {
			// remove contents of new row
			jQuery( this ).parents( 'tr' ).next().find( 'textarea' ).val('');
			// remove all existing counters
			jQuery( '.gform_wrapper' ).find( '.gfield_list tr' ).each(function() {
				jQuery( this ).find( '.ginput_counter' ).remove();
			});
			itsg_gf_ajax_charlimit_function();
		});
	});
}