jQuery(function ($) {  // use $ for jQuery
    "use strict";

    /* Calculator Email Field Display */
    $(document).on('click', '.checkflip', function() {
        if ($(this).is(':checked')) {
            $(this).closest('.shmac-form').find('.shmac-email').slideDown('fast');
        } else {
            $(this).closest('.shmac-form').find('.shmac-email').slideUp('fast');
        }
    });

    /* Process calculator results */
    $(document).on('click', '.submit-shmac', function() {
        var mprogresso;
        var currentForm = $(this).closest('.shmac-sc').attr('class').split(' ')[2]; // get the current form
        $('.submit-shmac').prop('disabled', true);

        /*  Progress bar */
        mprogresso = new Mprogress({
            //start: true,  // start it now
            parent: '.' + currentForm + ' .progresso',
            template: 3
        });
        mprogresso.start();

        var mailaddress = '';
        if ( $(this).closest('.shmac-form').find('.send-email').is(':checked')) {
            var sendemail = 'true';
            mailaddress = $(this).closest('.shmac-form').find('.shmac-email-input').val();
        } else {
            var sendemail = 'false';
        }
        var termcycle = $(this).closest('.shmac-form').data('year_label');
		var termType = 'years';
        if ( $(this).closest('.shmac-form').find('.term-months').is(':checked')) {
            termcycle = $(this).closest('.shmac-form').data('month_label');
			termType = 'months';
        }
        var amount   = $(this).closest('.shmac-form').find('.mort-amount').val();
        var interest = $(this).closest('.shmac-form').find('.interest').val();
        var downpay  = $(this).closest('.shmac-form').find('.downpay').val();
        var term     = $(this).closest('.shmac-form').find('.term').val();

        // Overrides
        var override = {};
        override.enableinsurance        = $(this).closest('.shmac-form').data('enableinsurance');
        override.insuranceamountpercent = $(this).closest('.shmac-form').data('insuranceamountpercent');
        override.monthlyinsurance       = $(this).closest('.shmac-form').data('monthlyinsurance');
        override.enablepmi              = $(this).closest('.shmac-form').data('enablepmi');
        override.monthlypmi             = $(this).closest('.shmac-form').data('monthlypmi');
        override.enabletaxes            = $(this).closest('.shmac-form').data('enabletaxes');
        override.taxesperthou           = $(this).closest('.shmac-form').data('taxesperthou');
        override.disclaimer             = $(this).closest('.shmac-form').data('disclaimer');
        override.currencysymbol         = $(this).closest('.shmac-form').data('currencysymbol');
        override.currencyside           = $(this).closest('.shmac-form').data('currencyside');
        override.currencyformat         = $(this).closest('.shmac-form').data('currencyformat');
        override.downpaytype            = $(this).closest('.shmac-form').data('downpaytype');
        override.bccemail               = $(this).closest('.shmac-form').data('bccemail');
        override.fromemail              = $(this).closest('.shmac-form').data('fromemail');
        override.emailsubject           = $(this).closest('.shmac-form').data('emailsubject');
        override.emailcontent           = $(this).closest('.shmac-form').data('emailcontent');
        override.pdfcolor               = $(this).closest('.shmac-form').data('pdfcolor');
        override.pdflogo                = $(this).closest('.shmac-form').data('pdflogo');
        override.pdfheader              = $(this).closest('.shmac-form').data('pdfheader');
        override.downpayshow            = $(this).closest('.shmac-form').data('downpayshow');
		override.location               = $(this).closest('.shmac-form').data('location');
		// schedule overrides
		override.detail_original        = $(this).closest('.shmac-form').data('detail_original');
		override.detail_down_payment    = $(this).closest('.shmac-form').data('detail_down_payment');
		override.detail_interest        = $(this).closest('.shmac-form').data('detail_interest');
		override.detail_term            = $(this).closest('.shmac-form').data('detail_term');
		override.detail_loan_after_down = $(this).closest('.shmac-form').data('detail_loan_after_down');
		override.detail_down_payment_amount = $(this).closest('.shmac-form').data('detail_down_payment_amount');
		override.detail_monthly_payment = $(this).closest('.shmac-form').data('detail_monthly_payment');
		override.detail_total_payments  = $(this).closest('.shmac-form').data('detail_total_payments');
		override.header_payment         = $(this).closest('.shmac-form').data('header_payment');
		override.header_payment_amount  = $(this).closest('.shmac-form').data('header_payment_amount');
		override.header_interest        = $(this).closest('.shmac-form').data('header_interest');
		override.header_total_interest  = $(this).closest('.shmac-form').data('header_total_interest');
		override.header_principal       = $(this).closest('.shmac-form').data('header_principal');
		override.header_balance         = $(this).closest('.shmac-form').data('header_balance');
		override.header_loan_text       = $(this).closest('.shmac-form').data('header_loan_text');
		override.header_schedule_text   = $(this).closest('.shmac-form').data('header_schedule_text');
		override.otherfactors           = $(this).closest('.shmac-form').data('otherfactors');
		override.down_factor_1          = $(this).closest('.shmac-form').data('down_factor_1');
		override.down_factor_2          = $(this).closest('.shmac-form').data('down_factor_2');
		override.tax_factor             = $(this).closest('.shmac-form').data('tax_factor');
		override.insurance_factor       = $(this).closest('.shmac-form').data('insurance_factor');
		override.factor_summary         = $(this).closest('.shmac-form').data('factor_summary');
		override.disclaimer             = $(this).closest('.shmac-form').data('disclaimer');

		

        $.post(SHMAC_Ajax.ajaxurl, {
            action: 'ajax-shmacfrontend',
            // vars
            process    : 'true',
            nextNonce  : SHMAC_Ajax.nextNonce,
            sendemail  : sendemail,
            mailaddress: mailaddress,
            amount     : amount,
            interest   : interest,
            downpay    : downpay,
            termcycle  : termcycle,
			termtype   : termType,
            term       : term,
            override   : override
        }, function(response) {
            mprogresso.end();
            $('.submit-shmac').prop('disabled', false);
            if (response.error == '1') {      // Handle Errors
                if (response.error_field == 'amount') {
                    $('.' + currentForm).find('.mort-amount').addClass('error-field');
                    $('.' + currentForm).find('.mort-amount').closest('.mui-form-group').find('.err-msg').text(response.message);
                } else if (response.error_field == 'interest') {
                    $('.' + currentForm).find('.interest').addClass('error-field');
                    $('.' + currentForm).find('.interest').closest('.mui-form-group').find('.err-msg').text(response.message);
                } else if (response.error_field == 'down') {
                    $('.' + currentForm).find('.downpay').addClass('error-field');
                    $('.' + currentForm).find('.downpay').closest('.mui-form-group').find('.err-msg').text(response.message);
                } else if (response.error_field == 'term') {
                    $('.' + currentForm).find('.term').addClass('error-field');
                    $('.' + currentForm).find('.term').closest('.mui-form-group').find('.err-msg').text(response.message);
                } else if (response.error_field == 'email') {
                    $('.' + currentForm).find('.shmac-email-input').addClass('error-field');
                    $('.' + currentForm).find('.shmac-email-input').closest('.mui-form-group').find('.err-msg')
                        .text(response.message);
                }



            } else { 
                activateModalorDiv(response.payment, response.headers, response.vals, response.details, currentForm, response.location);
            }
        });
        return false;
    });

    /* Remove error messages and styling on focus */
    $(document).on('focus', '.shmac-form input', function() {
        $(this).removeClass('error-field');
        $(this).closest('.mui-form-group').find('.err-msg').text('');
    });

    /* Hide email field if open on reset along with clearing fields */
    $(document).on('click', '.shmac-reset', function() {
		if ($(this).closest('.shmac-form').find('.shmac-check').is(":visible")) {  // only if optional send pdf shown
        	$(this).closest('.shmac-form').find('.shmac-email').slideUp('fast');
		}
        $(this).closest('.shmac-form').find('.term-years').prop('checked', true);
        $(this).closest('.shmac-form').find('.term-months').prop('checked', false);
        $(this).closest('.shmac-form').find('input').each( function() {
            $(this).removeClass('error-field');
            $(this).closest('.mui-form-group').find('.err-msg').text('');
        });
		var currentForm = $(this).closest('.shmac-sc').attr('class').split(' ')[2]; // get the current form
		var inlineDiv = $('#shmac-inline-' + currentForm);
		$('.shmac-div.divfrom-' + currentForm).mCustomScrollbar("destroy");
		inlineDiv.empty().removeClass().addClass('shmac-inline-form'); //clear it out and reset display form
    });

    /* Term selection Years & Months */
    $(document).ready(function() {
        if ($('.term-years').length) {
            $('.term-years').prop('checked', true);
            $('.term-months').prop('checked', false);
        } else {
            $('.term-months').prop('checked', true);
        }
    });

    $(".term-group").each(function() {
        $(this).change(function() {
            $(this).closest('.shmac-form').find(".term-group").prop('checked',false);
            $(this).prop('checked',true);
        });
    });     

    /* Don't respond to clicks on tooltips */
    $(document).on('click', '.shmac-tip', function() {
        return false;
    });

    /* Results Modal function */
    function activateModalorDiv(payment, headers, vals, details, currentForm, location) {
        // initialize modal element
        // Details

        var dpshow1='';
        var dpshow2='';
		var loanAfterdp='';
        if(vals.downpayshow=='yes'){
            dpshow1 = '<td>' + details.down_payment + ': <br /><strong>' + vals.down + ' %</strong></td>';
            dpshow2 = '<td>' + details.down_payment_amount + ': <br /><strong>' + vals.moneydown2
                    + '</strong></td>';
			loanAfterdp = '<td>' + details.loan_after_down + ': <br /><strong>' + vals.mortgage2 + '</strong></td>';
        }
        var detailsTable = '<h3 class="shmac-header">' + headers.loan_text + '</h3>'
                    + '<table class="mui-table detail-table" data-mui-borders="true">'
                    + '<tr><td>' + details.original + ': <br /><strong>' + vals.price2 + '</strong></td>'
                    + dpshow1
                    + '<td>' + details.interest + ': <br /><strong>' + vals.interest + ' %</strong></td>'
                    + '<td>' + details.term + ': <br /><strong>' + vals.term + ' ' + vals.cycle_text + '</strong></td>'
                    + '</tr>'
                    + '<tr>'
					+ loanAfterdp
                    + dpshow2
                    + '<td>' + details.monthly_payment + ': <br /><strong>' + vals.monthly_payment2
                    + '</strong></td>'
                    + '<td>' + details.total_payments + ': <br /><strong>' + vals.total_payments
                    + '</strong></td>' 
                    + '</tr></table>';

            
        // Taxes Insurance & PMI (TIP)
        var tip = '';
        if (vals.enable_insurance == 'yes'
            || vals.enable_pmi == 'yes'
            || vals.enable_taxes == 'yes'
        ) {
            tip += '<p>' + vals.otherfactors + '</p>'
                + '<ul class="shmac-ul">';
            // check for pmi enabled
            if (vals.enable_pmi == 'yes') {
                tip += '<li><img src="' + vals.shmac_root + '/assets/img/info_outline.png" /> ' + vals.pmi_text + '</li>';
            }
            // check for taxes enabled
            if (vals.enable_taxes == 'yes') {
                tip += '<li><img src="' + vals.shmac_root + '/assets/img/info_outline.png" /> ' + vals.tax_text + '</li>';
            }
            // check for insurance enabled
            if (vals.enable_insurance == 'yes') {
                tip += '<li><img src="' + vals.shmac_root + '/assets/img/info_outline.png" /> ' + vals.insurance_text + '</li>';
            }
            tip += '</ul>'
                 + '<p class="border-p">' + vals.total_monthlies + '</p>'
                 + '<p></p>';
        } else {
            tip += '<p></p>';
        }   

        // Schedule
		if (headers.show_schedule != 'no') {
        	var schedule = '<h3 class="shmac-header">' + headers.schedule_text + '</h3>'
                     + '<table class="mui-table schedule-table" data-mui-borders="true">'
                     + '<thead>'
                     + '<tr class="schedule-head"><th>' + headers.payment + '</th><th>' + headers.payment_amount 
                     + '</th><th>' + headers.principal 
                     + '</th><th>' + headers.interest + '</th><th>' + headers.total_interest + '</th><th>'
                     + headers.balance + '</th><th></tr></thead><tbody>';

        	$.each( payment, function( k, v) {
            	schedule += '<tr><td data-th="' + headers.payment + '">' + v.value 
                     + '</td><td data-th="' + headers.payment_amount + '">' + vals.monthly_payment2 
                     + '</td><td data-th="' + headers.principal + '">' + v.principal 
                     + '</td><td data-th="' + headers.interest + '">' + v.interest 
                     + '</td><td data-th="' + headers.total_interest + '">' + v.total_interest 
                     + '</td><td data-th="' + headers.balance + '"><strong>' + v.newMortgage + '</strong></td></tr>';
        	});
        	schedule += '</tbody></table>';
		}

        // Disclaimer
		if (headers.show_disclaimer != 'no') {
        	var disclaimerDiv = '<div class="disclaimer">' + details.disclaimer + '</div>';
		}

		if (location == 'inline') {
			var inlineDiv = $('#shmac-inline-' + currentForm);
			$('.shmac-div.divfrom-' + currentForm).mCustomScrollbar("destroy");
			inlineDiv.empty(); //clear it out first

        	$(detailsTable).appendTo(inlineDiv);
        	$(tip).appendTo(inlineDiv);
        	$(schedule).appendTo(inlineDiv);
        	$(disclaimerDiv).appendTo(inlineDiv);
        	$(inlineDiv).css('padding', '0px').css('max-height', '500px');
        	$(inlineDiv).addClass('shmac-div').addClass('divfrom-' + currentForm);
			divScroll(currentForm);

		} else { // modal popup
			var modalE1 = document.createElement('div');
	        $(detailsTable).appendTo(modalE1);
        	$(tip).appendTo(modalE1);
        	$(schedule).appendTo(modalE1);
        	$(disclaimerDiv).appendTo(modalE1);
        	$(modalE1).css('padding', '20px').css('width', '75%').css('height', '75%').css('margin', '100px auto');
        	$(modalE1).css('overflow', 'hidden').addClass('shmac-div').addClass('divfrom-' + currentForm);

        	// show modal
        	mui.overlay('on', modalE1);
			modalScroll();
		}
    }

	/* load scrollbar inline */
	function divScroll(currentForm) {
		$('.shmac-div.divfrom-' + currentForm).mCustomScrollbar({
            live: "on",
            theme: 'minimal-dark',
			advanced: {
				updateOnContentResize: true,
			},
			callbacks: {
                onInit: function() {
                    $('.shmac-div.divfrom-' + currentForm).css('overflow','auto'); // set overflow auto after init to avoid normal scroll from appearing
					$('.shmac-div.divfrom-' + currentForm + ' .disclaimer').css('margin-bottom', '0px');
					$('.shmac-div.divfrom-' + currentForm + ' .disclaimer').css('margin-bottom', '20px');
					
                    var firstRow = $('.shmac-div-' + currentForm + ' .schedule-table tr').eq(1).clone();
                    var lastRow = $('.shmac-div-' + currentForm + ' .schedule-table tr:last').clone();
                    $('<div class="schedule-head-fixed"><table class="mui-table schedule-table"></table></div>')
                        .appendTo('.shmac-div.divfrom-' + currentForm).hide();
                    $('.divfrom-' + currentForm + ' .schedule-head').clone().appendTo('.divfrom-' + currentForm + ' .schedule-head-fixed .schedule-table');
                    $('.divfrom-' + currentForm + ' .schedule-head-fixed .schedule-table').append(firstRow).append(lastRow);
                },
                whileScrolling: function() {
                    var $window = $(window);
                    var windowsize = $window.width();
                    if (windowsize > 768 && $('.divfrom-' + currentForm + ' .schedule-head').length ) {
                        var schedulePos = $('.divfrom-' + currentForm + ' .schedule-head').offset().top - $('.shmac-div.divfrom-' + currentForm).offset().top;
                        if (schedulePos < 0) {
                            $('.divfrom-' + currentForm + ' .schedule-head-fixed').show();
                        } else {
                            $('.divfrom-' + currentForm + ' .schedule-head-fixed').hide();
                        }
                    } else {
                        $('.divfrom-' + currentForm + ' .schedule-head-fixed').hide();
                    }
                }
            }
        });
	}


    /* load scrollbar on modal div */
	function modalScroll() {
    	$('.shmac-div').mCustomScrollbar({
        	live: "on",
        	theme: 'minimal-dark',
        	callbacks: {
            	onInit: function() {
                	$('.shmac-div').css('overflow','auto'); // set overflow auto after init to avoid normal scroll from appearing
                	var firstRow = $('.schedule-table tr').eq(1).clone();
                	var lastRow = $('.schedule-table tr:last').clone();
                	$('<div class="schedule-head-fixed"><table class="mui-table schedule-table"></table></div>')
                    	.appendTo('.shmac-div').hide();
                	$('.schedule-head').clone().appendTo('.schedule-head-fixed .schedule-table');
                	$('.schedule-head-fixed .schedule-table').append(firstRow).append(lastRow);
            	},
            	whileScrolling: function() {
                	var $window = $(window);
                	var windowsize = $window.width();
                	if (windowsize > 768 && $('.schedule-head').length ) {
                    	var schedulePos = $('.schedule-head').offset().top - $('.shmac-div').offset().top;
                    	if (schedulePos < 0) {
                        	$('.schedule-head-fixed').show();
                    	} else {
                        	$('.schedule-head-fixed').hide();
                    	}
                	} else {
                    	$('.schedule-head-fixed').hide();
                	}
            	}
        	}
    	});
	}
    /* Number & Char limiting for field inputs */
    $('.shmac-form .mort-amount').autoNumeric('init');
    $('.shmac-form .interest').autoNumeric('init');
    $('.shmac-form .downpay').autoNumeric('init');
    $('.shmac-form .term').autoNumeric('init');

    /* Random String Generation */
    function randomString() {
        var result = (Math.random()*1e32).toString(36);
        return result;
    }

    /*Adding Class for Non-empty Inputs */
    jQuery("input.mui-form-control").blur(function(){
        if(jQuery.trim(jQuery(this).val())!=''){
            if(!jQuery(this).next(".mui-form-floating-label").hasClass('non-empty-ipt'))
                jQuery(this).next(".mui-form-floating-label").addClass('non-empty-ipt');
        }else{
            if(jQuery(this).next(".mui-form-floating-label").hasClass('non-empty-ipt'))
                jQuery(this).next(".mui-form-floating-label").removeClass('non-empty-ipt');
        }
    });
});
