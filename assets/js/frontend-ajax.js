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

        var mailaddress = 'noone@nowhere.com';
        if ( $(this).closest('.shmac-form').find('.send-email').is(':checked')) {
            var sendemail = 'true';
            mailaddress = $(this).closest('.shmac-form').find('.shmac-email-input').val();
        } else {
            var sendemail = 'false';
        }
        var termcycle = 'years';
        if ( $(this).closest('.shmac-form').find('.term-months').is(':checked')) {
            termcycle = 'months';
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
        override.downpayshow              = $(this).closest('.shmac-form').data('downpayshow');

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
                activateModal(response.payment, response.headers, response.vals, response.details, currentForm);
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
        $(this).closest('.shmac-form').find('.shmac-email').slideUp('fast');
        $(this).closest('.shmac-form').find('.term-years').prop('checked', true);
        $(this).closest('.shmac-form').find('.term-months').prop('checked', false);
        $(this).closest('.shmac-form').find('input').each( function() {
            $(this).removeClass('error-field');
            $(this).closest('.mui-form-group').find('.err-msg').text('');
        });
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
    function activateModal(payment, headers, vals, details, currentForm) {
        // initialize modal element
        // Details

        var dpshow1='';
        var dpshow2='';
        if(vals.downpayshow=='yes'){
            dpshow1 = '<td>' + details.down_payment + ': <br /><strong>' + vals.down + ' %</strong></td>';
            dpshow2 = '<td>' + details.down_payment_amount + ': <br /><strong>' + vals.moneydown2
                    + '</strong></td>';
        }
        var detailsTable = '<h3 class="shmac-header">' + headers.loan_text + '</h3>'
                    + '<table class="mui-table detail-table" data-mui-borders="true">'
                    + '<tr><td>' + details.original + ': <br /><strong>' + vals.price2 + '</strong></td>'
                    + dpshow1
                    + '<td>' + details.interest + ': <br /><strong>' + vals.interest + ' %</strong></td>'
                    + '<td>' + details.term + ': <br /><strong>' + vals.term + ' ' + vals.cycle_text + '</strong></td>'
                    + '</tr>'
                    + '<tr><td>' + details.loan_after_down + ': <br /><strong>' + vals.mortgage2
                    + '</strong></td>'
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
                tip += '<li><img src="' + vals.shmac_root + '/assets/img/info.png" /> ' + vals.pmi_text + '</li>';
            }
            // check for taxes enabled
            if (vals.enable_taxes == 'yes') {
                tip += '<li><img src="' + vals.shmac_root + '/assets/img/info.png" /> ' + vals.tax_text + '</li>';
            }
            // check for insurance enabled
            if (vals.enable_insurance == 'yes') {
                tip += '<li><img src="' + vals.shmac_root + '/assets/img/info.png" /> ' + vals.insurance_text + '</li>';
            }
            tip += '</ul>'
                 + '<p class="border-p">' + vals.total_monthlies + '</p>'
                 + '<p></p>';
        } else {
            tip += '<p></p>';
        }   

        // Schedule
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

        // Disclaimer
        var disclaimerDiv = '<div class="disclaimer">' + details.disclaimer + '</div>';

        var modalE1 = document.createElement('div');
        $(detailsTable).appendTo(modalE1);
        $(tip).appendTo(modalE1);
        $(schedule).appendTo(modalE1);
        $(disclaimerDiv).appendTo(modalE1);
        $(modalE1).css('padding', '20px').css('width', '75%').css('height', '75%').css('margin', '100px auto');
        $(modalE1).css('overflow', 'hidden').addClass('shmac-div').addClass('divfrom-' + currentForm);

        // show modal
        mui.overlay('on', modalE1);
    }

    /* load scrollbar on modal div */
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
