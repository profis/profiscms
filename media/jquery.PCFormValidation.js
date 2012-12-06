/*!
 * ProfisCMS form validation.
 * Checks if required fields are filled in.
 * Initially based on HTML5 Form Fallback script from
 * http://forrst.com/posts/HTML5_Form_jQuery_fallback-hTO
 *
 * Copyright (c) 2010 Sitebase, http://www.sitebase.be
 * Copyright (c) 2012 Profis, http://www.profis.eu
 */
$(document).ready(function(){
	// Create input element to do tests
	var input = document.createElement('input');
	var supports_required = 'required' in input;
	var supports_filereader = Boolean(window.FileReader);
	
	function markValid(element) {
		try {
			element.setCustomValidity('');
		} catch(e) {
			var $$ = $(element);
			var backupBackgroundColor = $$.data('backupBackgroundColor');
			var backupTitle = $$.data('backupTitle');
			if(typeof backupBackgroundColor !== 'undefined') {
				$$.css('backgroundColor', backupBackgroundColor);
			}
			if(typeof backupTitle !== 'undefined') {
				$$.attr('title', backupTitle);
			}
		}
	}
	
	function markInvalid(element, message) {
		try {
			element.setCustomValidity(message);
		} catch(e) {
			var $$ = $(element);
			if(typeof $$.data('backupBackgroundColor') === 'undefined') {
				$$.data('backupBackgroundColor', $$.css('backgroundColor'));
			}
			if(typeof $$.data('backupTitle') === 'undefined') {
				var backupTitle = $$.attr('title');
				if(!backupTitle) {
					backupTitle = '';
				}
				$$.data('backupTitle', backupTitle);
			}
			$$.css('backgroundColor', '#ffcccc');
			$$.attr('title', message);
		}
	}
	
	// Validate an element
	function validate(element, live){
		var $$ = $(element);
		var valid = true;
		var message = null;
		
		// Mark fields that have been marked as bad in the backend
		if($$.data('error')) {
			valid = false;
			message = $$.data('error');
			$$.data('error', false);
		}
		if (live && valid) {
			// If not supported natively, check whether this field is
			// required and missing
			//if(!supports_required) {
				var value = $$.val();
				var required = element.getAttribute('required') == null ? false : true;
				if(valid && required && ((value == null) || (value == ''))) {
					valid = false;
					message = $$.data('msgRequired');
				}
			//}

			// If window.FileReader supported and we have files selected for
			// submission, ensure they are not too big
			if(valid && supports_filereader && element.files && element.files[0]) {
				var maxSize = $$.data('maxuploadsize');
				if(maxSize && (maxSize < element.files[0].size)) {
					valid = false;
					message = $$.data('msgFiletoobig');
				}
			}
		}
		
		// Set input to valid or invalid
		if(valid){
			markValid(element);
			return true;
		}else{
			markInvalid(element, message);
			return false;
		}
	}

	// Perform initial validation and highlight invalid fields
	// then bind the same function to handle live validation
	$('input,textarea,select').each(function() {
		validate(this, false);
	}).bind('keyup change blur', function() {
		validate(this, true);
	});

	
	// Block submit if there are invalid fields found
	$('form').bind('submit', function() {
		var formValid = true;
		$('input,textarea,select').each(function() {
			inputValid = validate(this, true);
			formValid = formValid && inputValid;
		});
		return formValid;
	});
});
