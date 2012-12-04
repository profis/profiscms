/*!
 * ProfisCMS form validation.
 * Checks if required fields are filled in.
 * Based on HTML5 Form Fallback script
 * from http://forrst.com/posts/HTML5_Form_jQuery_fallback-hTO
 * 
 * HTML5 Form Fallback
 * http://www.sitebase.be
 *
 * Copyright (c) 2010 Sitebase
 *
 * Date: 16th September, 2010
 * Version : 1.00
 */
$(document).ready(function(){
	// Create input element to do tests
	var input = document.createElement('input');
	var supports_required = 'required' in input;
	var supports_filereader = Boolean(window.FileReader);

	function markValid(element) {
		var $$ = $(element);
		if(element.backupBackgroundColor) {
			$(element).css('backgroundColor', element.backupBackgroundColor);
		}
	}

	function markInvalid(element) {
		var $$ = $(element);
		if(!element.backupBackgroundColor) {
			element.backupBackgroundColor = $$.css('backgroundColor');
		}
		$$.css('backgroundColor', '#ffcccc');
	}

	// Validate an element
	function validate(element){
		var $$ = $(element);
		var valid = true;

		// If not supported natively, check whether this field is
		// required and missing
		if(!supports_required) {
			var value = $$.val();
			var required = element.getAttribute('required') == null ? false : true;
			if(valid && required && ((value == null) || (value == ''))) {
				valid = false;
			}
		}

		// If window.FileReader supported and we have files selected for
		// submission, ensure they are not too big
		if(valid && supports_filereader && element.files && element.files[0]) {
			if($$.data().maxuploadsize && ($$.data().maxuploadsize < element.files[0].size)) {
				valid = false;
			}
		}

		// Set input to valid or invalid
		if(valid){
			markValid(element);
			return true;
		}else{
			markInvalid(element);
			return false;
		}
	}

	// Handle live validation
	$('input,textarea,select').keyup(function() {
		validate(this);
	}).change(function() {
		validate(this);
	});

	// Block submit if there are invalid fields found
	$('form').submit(function() {
		var formValid = true;
		$('input,textarea,select').each(function() {
			inputValid = validate(this);
			formValid = formValid && inputValid;
		});
		return formValid;
	});
	
});
