jQuery(function ($) {  // use $ for jQuery
    "use strict";

	// Add Color Picker to all inputs that have 'color-field' class
    $(function() {
        $('.color-field').wpColorPicker();
    });

	// Detect current tab and show save button on pages that use it (css hidden by default)
	$(function() {
      	var current_tab = $("form input[name='option_page']").val();
		if (current_tab != 'shmac_info' ) { // not used currently
			$('form #submit').show();
		}
    });

	/* First Tab Settings */
	// Hide pagination results if use pagination is false
	$(document).ready( function() {
		checkInsurance();
		checkPmi();
		checkTaxes();
		checkDown();
		checkSlider();
		removeInfoSubmit();
	});
	$(document).on('change', '.shmac-enable-insurance', function() {
		checkInsurance();
	});
	$(document).on('change', '.shmac-enable-pmi', function() {
		checkPmi();
	});
	$(document).on('change', '.shmac-enable-taxes', function() {
		checkTaxes();
	});
	$(document).on('change', '.shmac-enable-slider', function() {
		checkSlider();
	});
	$(document).on('change', '.shmac-down-show', function() {
        checkDown();
    });

	/* Don't show submit button on info page */
	function removeInfoSubmit() {
		var current_tab = $("form input[name='option_page']").val();
		if (current_tab == 'shmac_info') {
			$('form #submit').hide();
		}
	}

	function checkInsurance() {
		var insurance = $('.shmac-enable-insurance').prop("checked");
		if (insurance) {
			$('.shmac-insurance').closest('tr').show();
			$('.shmac-insurance-amount-percent').closest('tr').show();

		} else {
			$('.shmac-insurance').closest('tr').hide();
			$('.shmac-insurance-amount-percent').closest('tr').hide();
		}
	}

	function checkPmi() {
        var pmi = $('.shmac-enable-pmi').prop("checked");
        if (pmi) {
            $('.shmac-pmi').closest('tr').show();
        } else {
            $('.shmac-pmi').closest('tr').hide();
        }
    }

	function checkTaxes() {
        var taxes = $('.shmac-enable-taxes').prop("checked");
        if (taxes) {
            $('.shmac-tax-rate').closest('tr').show();
        } else {
            $('.shmac-tax-rate').closest('tr').hide();
        }
    }

	function checkSlider() {
		var slider = $('.shmac-enable-slider').prop("checked");
		if (slider) {
			$('.shmac-enable-input-readonly').closest('tr').show();
			$('.shmac-slider-theme').closest('tr').show();
			$('.shmac-purchase-min-price').closest('tr').show();
			$('.shmac-purchase-max-price').closest('tr').show();
			$('.shmac-purchase-slider-step').closest('tr').show();
			$('.shmac-interest-min-rate').closest('tr').show();
			$('.shmac-interest-max-rate').closest('tr').show();
			$('.shmac-interest-slider-step').closest('tr').show();
			$('.shmac-dwnpay-min-rate').closest('tr').show();
			$('.shmac-dwnpay-max-rate').closest('tr').show();
			$('.shmac-dwnpay-slider-step').closest('tr').show();
			$('.shmac-term-min-value').closest('tr').show();
			$('.shmac-term-max-value').closest('tr').show();
			$('.shmac-term-slider-step').closest('tr').show();
		} else {
			$('.shmac-enable-input-readonly').closest('tr').hide();
            $('.shmac-slider-theme').closest('tr').hide();
            $('.shmac-purchase-min-price').closest('tr').hide();
            $('.shmac-purchase-max-price').closest('tr').hide();
            $('.shmac-purchase-slider-step').closest('tr').hide();
            $('.shmac-interest-min-rate').closest('tr').hide();
            $('.shmac-interest-max-rate').closest('tr').hide();
            $('.shmac-interest-slider-step').closest('tr').hide();
            $('.shmac-dwnpay-min-rate').closest('tr').hide();
            $('.shmac-dwnpay-max-rate').closest('tr').hide();
            $('.shmac-dwnpay-slider-step').closest('tr').hide();
            $('.shmac-term-min-value').closest('tr').hide();
            $('.shmac-term-max-value').closest('tr').hide();
            $('.shmac-term-slider-step').closest('tr').hide();
		}
	}

	function checkDown() {
		var downShow = $('.shmac-down-show').prop("checked");
		var slider = $('.shmac-enable-slider').prop("checked");
		if (downShow) {
			$('.shmac-down-label').closest('tr').show();
			$('.shmac-down-info').closest('tr').show();
			$('.shmac-down-type').closest('tr').show();
			$('.shmac-down_payment').closest('tr').show();
			$('.shmac-down-error').closest('tr').show();
			if (slider) {
            	$('.shmac-dwnpay-min-rate').closest('tr').show();
				$('.shmac-dwnpay-max-rate').closest('tr').show();
				$('.shmac-dwnpay-slider-step').closest('tr').show();
			}
		} else {
			$('.shmac-down-label').closest('tr').hide();
            $('.shmac-down-info').closest('tr').hide();
            $('.shmac-down-type').closest('tr').hide();
            $('.shmac-down_payment').closest('tr').hide();
          	$('.shmac-dwnpay-min-rate').closest('tr').hide();
           	$('.shmac-dwnpay-max-rate').closest('tr').hide();
           	$('.shmac-dwnpay-slider-step').closest('tr').hide();
			$('.shmac-down-error').closest('tr').hide();
		}
	}
			

	// Show / Hide the Entry Image Upload fields on Entry Image Change
	$(document).on('change', '.wcp-contact-image', function() {
		var selected = $('.wcp-contact-image option:selected').val();
		if (selected == 'true') {
			$(document).find('#upload_now2').closest('.contact-upload').show();
		} else {
			$(document).find('#upload_now2').closest('.contact-upload').hide();
		}
	});

	// CSS and JS editor setup
    $(function(){
        if( $('#code_editor_page_js').length ) {
            var editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
            editorSettings.codemirror = _.extend(
                {},
                editorSettings.codemirror,
                {
                    indentUnit: 2,
                    tabSize: 2,
                    mode: 'javascript',
                }
            );
            var editor = wp.codeEditor.initialize( $('#code_editor_page_js'), editorSettings );
        }

        if( $('#code_editor_page_css').length ) {
            var editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
            editorSettings.codemirror = _.extend(
                {},
                editorSettings.codemirror,
                {
                    indentUnit: 2,
                    tabSize: 2,
                    mode: 'css',
                }
            );
            var editor = wp.codeEditor.initialize( $('#code_editor_page_css'), editorSettings );
        }
    });

	// background image upload
    $(document).on('click', '.bg_clear', function() {
        $('.custom_bg_url').val('');
        $('.custom_bg_id').val('');
        $('.shmac_bg_image').hide();
        return false;
    });
    $(document).ready( function() {
        if ($('.shmac_bg_image').length) {
            var image_url = $('.shmac_bg_image').attr('src');
            if (image_url.length) {  // show it if it's got it
                $('.shmac_bg_image').show();
            }
        }
    });
    $('.custom_bg_upload').click(function(e) {
        e.preventDefault();

        var custom_uploader = wp.media({
            title: 'Background',
            button: {
                text: 'Set Background'
            },
            multiple: false  // Set this to true to allow multiple files to be selected
        })
        .on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('.shmac_bg_image').attr('src', attachment.url);
            $('.shmac_bg_image').show();
            $('.custom_bg_url').val(attachment.url);
            $('.custom_bg_id').val(attachment.id);
        })
        .open();
    });


	/* End First Tab Settings */

	/* Second Tab (Fields) Settings */

	// show / hide all related fields based on email report allowed or not
	$(document).ready( function() {
		showHideFields('quick');
	});
	$(document).on('change', '.shmac-allow-email', function() {
        showHideFields('fade');
    });
	
	function showHideFields(method) {
		if ($('.shmac_email .shmac-allow-email').prop("checked") == false) {   // make sure we are on the right tab
			if (method == 'quick') {
				$('.shmac_email .shmac-email-bcc').closest('tr').hide();
        		$('.shmac_email .shmac-email-from').closest('tr').hide();
        		$('.shmac_email .email-subject').closest('tr').hide();
        		$('.shmac_email .email-text').closest('tr').hide();
            	$('.shmac_email .email-subject').closest('tr').hide();
            	$('.shmac_email .custom_media_url').closest('tr').hide();
				$('.shmac_email .wp-color-result').closest('tr').hide();
				$('.shmac_email .pdf-header').closest('tr').hide();
				$('.shmac_email .shmac-ltr-rtl').closest('tr').hide();
            	$('.shmac_email .shmac-pdf-font').closest('tr').hide();
				$('.shmac_email .brought-by').closest('tr').hide();
			} else {
				$('.shmac_email .shmac-email-bcc').closest('tr').fadeOut();
                $('.shmac_email .shmac-email-from').closest('tr').fadeOut();
                $('.shmac_email .email-subject').closest('tr').fadeOut();
                $('.shmac_email .email-text').closest('tr').fadeOut();
				$('.shmac_email .email-subject').closest('tr').fadeOut();
				$('.shmac_email .custom_media_url').closest('tr').fadeOut();
				$('.shmac_email .wp-color-result').closest('tr').fadeOut();
                $('.shmac_email .pdf-header').closest('tr').fadeOut();
                $('.shmac_email .shmac-ltr-rtl').closest('tr').fadeOut();
				$('.shmac_email .shmac-pdf-font').closest('tr').fadeOut();
				$('.shmac_email .brought-by').closest('tr').hide();
			}
		} else {
			if(method == 'quick') {
				$('.shmac_email input').closest('tr').show();
            	$('.shmac_email textarea').closest('tr').show();
            	$('.shmac_email .custom_media_url').closest('tr').show();
            	$('.shmac_email .shmac-pdf-font').closest('tr').show();
			} else {
				$('.shmac_email input').closest('tr').fadeIn();
           		$('.shmac_email textarea').closest('tr').fadeIn();
           		$('.shmac_email .custom_media_url').closest('tr').fadeIn();
				$('.shmac_email .shmac-pdf-font').closest('tr').fadeIn();
			}
		}
	}
			
	// logo image upload
    $(document).on('click', '.logo_clear', function() {
        $('.custom_media_url').val('');
        $('.custom_media_id').val('');
        $('.shmac_logo_image').hide();
        return false;
    });
    $(document).ready( function() {
        if ($('.shmac_logo_image').length) {
            var image_url = $('.shmac_logo_image').attr('src');
            if (image_url.length) {  // show it if it's got it
                $('.shmac_logo_image').show();
            }
        }
    });
    $('.custom_media_upload').click(function(e) {
        e.preventDefault();

        var custom_uploader = wp.media({
            title: 'Logo',
            button: {
                text: 'Set Logo'
            },
            multiple: false  // Set this to true to allow multiple files to be selected
        })
        .on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $('.shmac_logo_image').attr('src', attachment.url);
            $('.shmac_logo_image').show();
            $('.custom_media_url').val(attachment.url);
            $('.custom_media_id').val(attachment.id);
        })
        .open();
    });



	/* End Second Tab Settings */
});
