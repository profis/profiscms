function PC_add_validation(form_selector, validation, config) {
	var validation_config = {
		ok_background_color: '#fff',
		error_background_color: '#FEE',
		error_parent_class: false,
		scroll_to_invalid_field: true
	};
	if (config) {
		$.extend(validation_config, config);
	}
	
	PC_remove_validation(form_selector);

	var $form = $(form_selector);

	// set new validation handler
	$form.data('pc_validation_data', [validation_config, $.extend({}, validation)]);
	$form.on('submit.pc_validator', function(){
		var valid = true;
		var first_input_field = false;
		var validation_data = $(this).data('pc_validation_data');
		console.log(validation_data);
		if( !validation_data ) {
			$(this).off('submit.pc_validation');
			return;
		}
		$.each(validation_data[1], function(input_name, validation_rules) {
			var valid_input = true;
			var input_name_parts = input_name.split(':');
			var input_field = false;
			if (input_name_parts.length == 2 && input_name_parts[0] == 'id') {
				input_field = $('#' + input_name_parts[1]);
			}
			else {
				input_field =$(form_selector + " input[name="+input_name+"]" + ', ' + form_selector + " select[name="+input_name+"]" + ', ' + form_selector + " textarea[name="+input_name+"]");
			}
			if (!input_field || !input_field.length) {
				return 'continue';	
			}
			var input_value = $.trim(input_field.val());
			$.each(validation_rules, function(index, validation_rule) {
				if (validation_rule.if_checked) {
					var check_box_field = $(form_selector + " input:checked[name="+validation_rule.if_checked+"]");
					if (!check_box_field || !check_box_field.length) {
						return 'continue';	
					}
				}
				if (validation_rule.if_checked_id) {
					var check_field = $("#" + validation_rule.if_checked_id + ":checked");
					if (!check_field || !check_field.length) {
						return 'continue';	
					}
				}
				switch(validation_rule.rule) {
					case 'required':
						if (!input_value) {
							valid_input = false;
						}
						break;
					case 'min_length':
						if (input_value.length < validation_rule.param) {
							valid_input = false;
						}
						break;
					case 'email':
						var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
						if (!pattern.test(input_value)) {
							valid_input = false;
						}
						break;	
					default:
				}
			});
			if (!valid_input) {
				if (!first_input_field) {
					first_input_field = input_field;
				}
				valid = false;
				if (validation_data[0].error_background_color) {
					input_field.css("background-color", validation_data[0].error_background_color);
				}
				if (validation_data[0].error_parent_class) {
					input_field.parent().addClass(validation_data[0].error_parent_class);
				}
			}
			else {
				if (validation_data[0].ok_background_color) {
					input_field.css("background-color", validation_data[0].ok_background_color);
				}
				if (validation_data[0].error_parent_class) {
					input_field.parent().removeClass(validation_data[0].error_parent_class);
				}
			}
		});
		if (!valid && first_input_field && validation_data[0].scroll_to_invalid_field) {
			$(window).scrollTop(first_input_field.offset().top);
		}
		return valid;
	});
}

function PC_remove_validation(form_selector) {
	var $form = $(form_selector);

	// remove previous validation handler and error marks from the form
	$form.off('submit.pc_validator');
	var validation_data = $form.data('pc_validation_data');
	if( validation_data ) {
		$.each(validation_data[1], function(input_name, validation_rules) {
			var input_name_parts = input_name.split(':');
			var input_field = false;
			if (input_name_parts.length == 2 && input_name_parts[0] == 'id') {
				input_field = $('#' + input_name_parts[1]);
			}
			else {
				input_field =$(form_selector + " input[name="+input_name+"]" + ', ' + form_selector + " select[name="+input_name+"]" + ', ' + form_selector + " textarea[name="+input_name+"]");
			}
			if( input_field ) {
				if (validation_data[0].ok_background_color) {
					input_field.css("background-color", validation_data[0].ok_background_color);
				}
				if (validation_data[0].error_parent_class) {
					input_field.parent().removeClass(validation_data[0].error_parent_class);
				}
			}
		});
	}
}