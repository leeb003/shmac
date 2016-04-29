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

	function checkInsurance() {
		var insurance = $('.shmac-enable-insurance :selected').val();
		if (insurance == 'yes') {
			$('.shmac-insurance').closest('tr').show();
			$('.shmac-insurance-amount-percent').closest('tr').show();

		} else {
			$('.shmac-insurance').closest('tr').hide();
			$('.shmac-insurance-amount-percent').closest('tr').hide();
		}
	}

	function checkPmi() {
        var pmi = $('.shmac-enable-pmi :selected').val();
        if (pmi == 'yes') {
            $('.shmac-pmi').closest('tr').show();
        } else {
            $('.shmac-pmi').closest('tr').hide();
        }
    }

	function checkTaxes() {
        var taxes = $('.shmac-enable-taxes :selected').val();
        if (taxes == 'yes') {
            $('.shmac-tax-rate').closest('tr').show();
        } else {
            $('.shmac-tax-rate').closest('tr').hide();
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

	/* End First Tab Settings */

	/* Second Tab (Fields) Settings */

	// show / hide all fields based on email report allowed or not
	$(document).ready( function() {
		showHideFields();
	});
	$(document).on('change', '.shmac-allow-email', function() {
        showHideFields();
    });
	
	function showHideFields() {
		if ($('.shmac-allow-email').length) {   // make sure we are on the right tab
			var allowEmail = $('.shmac-allow-email :selected').val();
			if (allowEmail == 'no') {
				$('input').closest('tr').hide();
				$('textarea').closest('tr').hide();
				$('.custom_media_url').closest('tr').hide();
			} else {
				$('input').closest('tr').show();
            	$('textarea').closest('tr').show();
            	$('.custom_media_url').closest('tr').show();
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
