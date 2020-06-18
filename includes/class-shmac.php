<?php
/**
 * SHMAC Class for frontend display, extends main class
 */

    class shmac {
        // properties

        protected $shmac_settings; // Settings for the calculator
        protected $shmac_email; // Second tab settings for calculator

        // methods

        /**
         * Class Constructor. Defines the args and actions for the existing class
         *
         * @since       1.0.0
         * @access      public
         * @param       array SHMAC class instance
         * @return      void
         */
        public function __construct() {
            require_once( SHMAC_ROOT_PATH . '/includes/class-shmac-options.php' );
            $options = new shmac_options();
            $this->shmac_settings = $options->shmac_settings;
            $this->shmac_email = $options->shmac_email;
            if ( !shortcode_exists( 'shmac_calc_sc' ) ) {
                add_shortcode('shmac_calc_sc', array($this, 'shmac_calc'));
            }
            // Widget instance
            require_once SHMAC_ROOT_PATH . '/includes/shmac-widget.php';

            // ajax
            require_once(SHMAC_ROOT_PATH . '/includes/class-shmac-ajax.php');
            $shmac_ajax = new shmac_ajax();
            add_action( 'wp_ajax_ajax-shmacfrontend', array($shmac_ajax, 'myajax_shmacfrontend_callback'));
            add_action( 'wp_ajax_nopriv_ajax-shmacfrontend', array($shmac_ajax, 'myajax_shmacfrontend_callback'));
            // Load frontend JS & CSS
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 700 );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 700 );
            // Dynamic CSS
            add_action('wp_ajax_shmac_dynamic_css', array($this, 'shmac_dynamic_css'));
            add_action('wp_ajax_nopriv_shmac_dynamic_css', array($this, 'shmac_dynamic_css'));
        }

        /**
         * Load frontend CSS.
         * @access public
         * @since 1.0.0
         * @return void
         */
        public function enqueue_styles () {
            global $wp_styles;

			/* Dynamic CSS file loads all other files, minifies and outputs them in one request
			 * We'll keep this method unless users have some unforseen issues
			 */

			/*
            // main css
            wp_register_style( 'shmac-frontend', SHMAC_ROOT_URL . '/assets/css/frontend.css',
            array(), SHMAC_PLUGIN_VERSION );

            // Scrollbar
            wp_register_style( 'shmac-custom-scrollbar', SHMAC_ROOT_URL .  '/assets/css/jquery.mCustomScrollbar.min.css',
            array('shmac-frontend'), '3.0.9');          

            // mprogress
            wp_register_style( 'mprogress', SHMAC_ROOT_URL .  '/assets/css/mprogress.min.css',
            array('shmac-frontend'), '1.0');
           
            //nouislider
            wp_register_style( 'nouislider', SHMAC_ROOT_URL .  '/assets/css/nouislider.min.css',
            array('shmac-frontend'), '920');
			*/
			// rtl css
			if (is_rtl() ) {
				wp_register_style('shmac-rtl', SHMAC_ROOT_URL . '/assets/css/shmac-rtl.css', array('shmac-frontend'), SHMAC_PLUGIN_VERSION);
				wp_enqueue_style('shmac-rtl');
			}

			// dynamic css
            wp_register_style('shmac-frontend',
                admin_url('admin-ajax.php').'?action=shmac_dynamic_css', array(), SHMAC_PLUGIN_VERSION );
            wp_enqueue_style('shmac-frontend');

        } // End enqueue_styles ()

        // Load our dynamic php stylesheet
        public function shmac_dynamic_css() {
            require_once(SHMAC_ROOT_PATH . '/assets/css/shmacdyn.css.php');
            exit();
        }

        /**
         * Load frontend Javascript.
         * @access public
         * @since 1.0.0
         * @return void
         */
         
        public function enqueue_scripts () {
            // autoNumeric
            wp_register_script('autoNumeric', SHMAC_ROOT_URL . '/assets/js/autoNumeric.min.js', array('jquery'), '2.0.13', true);
			wp_enqueue_script('autoNumeric');
            // Mui
            wp_register_script( 'mui', SHMAC_ROOT_URL . '/assets/js/mui.min.js', array(), '0.1.22-rc1', true );
			wp_enqueue_script('mui');
            // Scrollbar
            wp_register_script( 'shmac-custom-scrollbar', SHMAC_ROOT_URL 
                . '/assets/js/jquery.mCustomScrollbar.concat.min.js', array('jquery'), '3.0.9', true);
			wp_enqueue_script('shmac-custom-scrollbar');
            // mprogress
            wp_register_script( 'mprogress', SHMAC_ROOT_URL . '/assets/js/mprogress.min.js', array('jquery'), '1.0', true);
			wp_enqueue_script('mprogress');
            //nouislider
            wp_register_script( 'nouislider', SHMAC_ROOT_URL . '/assets/js/nouislider.min.js', array('jquery'), '9.20', true);
			wp_enqueue_script('nouislider');

            // Plugin js    
            wp_register_script( 'shmac-frontend-ajax', SHMAC_ROOT_URL . '/assets/js/frontend-ajax.js', array('nouislider'), SHMAC_PLUGIN_VERSION, true );

            wp_localize_script(  'shmac-frontend-ajax', 'SHMAC_Ajax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nextNonce' => wp_create_nonce( 'myajax-next-nonce' ),
                'shmacColor' => isset($this->first_tab['page_color'])?$this->first_tab['page_color']:'#03a9f4'
            ));
			wp_enqueue_script('shmac-frontend-ajax');

			// have to get options in this function for options since it's called by add_action
            $shmac_options = get_option('shmac_settings');
            $custom_js = isset($shmac_options['custom_js']) ? $shmac_options['custom_js'] : '';
            wp_add_inline_script('shmac-frontend-ajax', $custom_js, 'after');	
        }

        /**
         * Shortcode generation
         * @since 1.0.0
         * @access public
         * @return $output
         */
        public function shmac_calc($atts, $content=NULL) {
            global $calc_inc; // tracking multiple calculators
			global $slider_vars; // multiple sliders array for localized slider js

            $calc_inc++;

            $settings = (shortcode_atts(array( 
                'extraclass'            => '',
                // Base Settings Overrides
                'primarycolor'           => '',
                'color'                  => '',
                'calctitle'              => '',
                'emailtext'              => '',
                'emaillabel'             => '',
                'amountlabel'            => '',
                'amountinfo'             => '',
                'defaultpurchase'        => '',
                'interestlabel'          => '',
                'interestinfo'           => '',
                'defaultinterest'        => '',
                'downpayshow'            => '',
                'downpaylabel'           => '',
                'downpayinfo'            => '',
                'downpaytype'            => '',
                'defaultdown'            => '',
                'termlabel'              => '',
				'year_label'             => '',
                'month_label'            => '',
                'terminfo'               => '',
                'defaultterm'            => '',
                'enableinsurance'        => '',
                'insuranceamountpercent' => '',
                'monthlyinsurance'       => '',
                'enablepmi'              => '',
                'monthlypmi'             => '',
                'enabletaxes'            => '',
                'taxesperthou'           => '',
                'currencysymbol'         => '',
                'currencyformat'         => '',
                'currencyside'           => '',
				'location'               => '',
				'bg_attachment_url'      => '',
				'bg_color'               => '',
				// Report Overrides
				'detail_original'        => '',
				'detail_down_payment'    => '',
				'detail_interest'        => '',
				'detail_term'            => '',
				'detail_loan_after_down' => '',
				'detail_down_payment_amount' => '',
				'detail_monthly_payment' => '',
				'detail_total_payments'  => '',
				'header_payment'         => '',
				'header_payment_amount'  => '',
				'header_interest'        => '',
				'header_total_interest'  => '',
				'header_principal'       => '',
				'header_balance'         => '',
				'header_loan_text'       => '',
				'header_schedule_text'   => '',
				'otherfactors'           => '',
				'down_factor_1'          => '',
				'down_factor_2'          => '',
				'tax_factor'             => '',
				'insurance_factor'       => '',
				'factor_summary'         => '',
				'disclaimer'             => '',
                // Email Settings Overrides
                'allowemail'             => '',
                'bccemail'               => '',
                'fromemail'              => '',
                'emailsubject'           => '',
                'emailcontent'           => '',
                'pdfcolor'               => '',
                'pdflogo'                => '',
                'pdfheader'              => '',
                // Extras
                'calcsubmit'             => '',
                'calcreset'              => '',
                //Slider Settings 
                'enable_slideroverride'  => '',
                'enable_emailoverride'   => '',
                'inputreadonly'          => '',
                'slider_theme'           => '',
                'sliderminamount'        => '',
                'slidermaxamount'        => '',
                'sliderstepsamount'      => '',
                'slidermininterest'      => '',
                'slidermaxinterest'      => '',
                'sliderstepsinterest'    => '',
                'slidermindown'          => '',
                'slidermaxdown'          => '',
                'sliderstepsdown'        => '',
                'sliderminterm'          => '',
                'slidermaxterm'          => '',
                'sliderstepsterm'        => '',
                'termtype'               => '',
            ), $atts));

			// Convert back to variables and check for overrides with fallbacks to main settings
			$extraclass             = $settings['extraclass'];
			$primarycolor           = ($settings['primarycolor'] != '' ? $settings['primarycolor'] : $this->shmac_settings['page_color']);
			$calctitle              = ($settings['calctitle'] != '' ? $settings['calctitle'] : $this->shmac_settings['calc_title']);
			$emailtext              = ($settings['emailtext'] != '' ? $settings['emailtext'] : $this->shmac_settings['send_email_text']);
			$emaillabel             = ($settings['emaillabel'] != '' ? $settings['emaillabel'] : $this->shmac_settings['email_placeholder']);
			$amountlabel            = ($settings['amountlabel'] != '' ? $settings['amountlabel'] : $this->shmac_settings['purchase_price_label']);
			$amountinfo             = ($settings['amountinfo'] != '' ? $settings['amountinfo'] : $this->shmac_settings['purchase_price_info']);
			$defaultpurchase        = ($settings['defaultpurchase'] != '' ? $settings['defaultpurchase'] : $this->shmac_settings['purchase_price']);
			$interestlabel          = ($settings['interestlabel'] != '' ? $settings['interestlabel'] : $this->shmac_settings['interest_rate_label']);
			$interestinfo           = ($settings['interestinfo'] != '' ? $settings['interestinfo'] : $this->shmac_settings['interest_rate_info']);
			$defaultinterest        = ($settings['defaultinterest'] != '' ? $settings['defaultinterest'] : $this->shmac_settings['interest_rate']);
			$downpayshow            = ($settings['downpayshow'] != '' ? $settings['downpayshow'] : $this->shmac_settings['down_payment_show']);
			$downpaylabel           = ($settings['downpaylabel'] != '' ? $settings['downpaylabel'] : $this->shmac_settings['down_payment_label']);
			$downpayinfo            = ($settings['downpayinfo'] != '' ? $settings['downpayinfo'] : $this->shmac_settings['down_payment_info']);
			$downpaytype            = ($settings['downpaytype'] != '' ? $settings['downpaytype'] : $this->shmac_settings['down_payment_type']);
			$defaultdown            = ($settings['defaultdown'] != '' ? $settings['defaultdown'] : $this->shmac_settings['down_payment']);
			$termlabel              = ($settings['termlabel'] != '' ? $settings['termlabel'] : $this->shmac_settings['loan_term_label']);
			$terminfo               = ($settings['terminfo'] != '' ? $settings['terminfo'] : $this->shmac_settings['loan_term_info']);
			$defaultterm            = ($settings['defaultterm'] != '' ? $settings['defaultterm'] : $this->shmac_settings['loan_term']);
			$enableinsurance        = $settings['enableinsurance'];
			$insuranceamountpercent = $settings['insuranceamountpercent'];
			$monthlyinsurance       = $settings['monthlyinsurance'];
			$enablepmi              = $settings['enablepmi'];
			$monthlypmi             = $settings['monthlypmi'];
			$enabletaxes            = $settings['enabletaxes'];
			$taxesperthou           = $settings['taxesperthou'];

// Report texts
			$detail_original        = ($settings['detail_original'] != '' ? $settings['detail_original'] : $this->shmac_email['detail_original']);
			$detail_down_payment    = ($settings['detail_down_payment'] != '' ? $settings['detail_down_payment'] : $this->shmac_email['detail_down_payment']);
			$detail_interest        = ($settings['detail_interest'] != '' ? $settings['detail_interest'] : $this->shmac_email['detail_interest']);
			$detail_term			= ($settings['detail_term'] != '' ? $settings['detail_term'] : $this->shmac_email['detail_term']);
			$detail_loan_after_down = ($settings['detail_loan_after_down'] != '' ? $settings['detail_loan_after_down'] : $this->shmac_email['detail_loan_after_down']);
			$detail_down_payment_amount = ($settings['detail_down_payment_amount'] != '' ? $settings['detail_down_payment_amount'] : $this->shmac_email['detail_down_payment_amount']);
			$detail_monthly_payment = ($settings['detail_monthly_payment'] != '' ? $settings['detail_monthly_payment'] : $this->shmac_email['detail_monthly_payment']);
			$detail_total_payments  = ($settings['detail_total_payments'] != '' ? $settings['detail_total_payments'] : $this->shmac_email['detail_total_payments']);
			$header_payment         = ($settings['header_payment'] != '' ? $settings['header_payment'] : $this->shmac_email['header_payment']);
			$header_payment_amount  = ($settings['header_payment_amount'] != '' ? $settings['header_payment_amount'] : $this->shmac_email['header_payment_amount']);
			$header_interest		= ($settings['header_interest'] != '' ? $settings['header_interest'] : $this->shmac_email['header_interest']);
			$header_total_interest  = ($settings['header_total_interest'] != '' ? $settings['header_total_interest'] : $this->shmac_email['header_total_interest']);
			$header_principal		= ($settings['header_principal'] != '' ? $settings['header_principal'] : $this->shmac_email['header_principal']);
			$header_balance			= ($settings['header_balance'] != '' ? $settings['header_balance'] : $this->shmac_email['header_balance']);
			$header_loan_text		= ($settings['header_loan_text'] != '' ? $settings['header_loan_text'] : $this->shmac_email['header_loan_text']);
			$header_schedule_text	= ($settings['header_schedule_text'] != '' ? $settings['header_schedule_text'] : $this->shmac_email['header_schedule_text']);
			$otherfactors			= ($settings['otherfactors'] != '' ? $settings['otherfactors'] : $this->shmac_email['otherfactors']);
			$down_factor_1			= ($settings['down_factor_1'] != '' ? $settings['down_factor_1'] : $this->shmac_email['down_factor_1']);
			$down_factor_2			= ($settings['down_factor_2'] != '' ? $settings['down_factor_2'] : $this->shmac_email['down_factor_2']);
			$tax_factor				= ($settings['tax_factor'] != '' ? $settings['tax_factor'] : $this->shmac_email['tax_factor']);
			$insurance_factor		= ($settings['insurance_factor'] != '' ? $settings['insurance_factor'] : $this->shmac_email['insurance_factor']);
			$factor_summary			= ($settings['factor_summary'] != '' ? $settings['factor_summary'] : $this->shmac_email['factor_summary']);
			$disclaimer             = ($settings['disclaimer'] != '' ? $settings['disclaimer'] : $this->shmac_email['disclaimer']);
			

			$currencysymbol         = ($settings['currencysymbol'] != '' ? $settings['currencysymbol'] : $this->shmac_settings['currency']);
			$currencyformat         = ($settings['currencyformat'] != '' ? $settings['currencyformat'] : $this->shmac_settings['currency_format']);
			$currencyside           = ($settings['currencyside'] != '' ? $settings['currencyside'] : $this->shmac_settings['currency_side']);
			$allowemail             = ($settings['allowemail'] != '' ? $settings['allowemail'] : $this->shmac_email['allow_email']);
			$bccemail               = $settings['bccemail'];
			$fromemail              = $settings['fromemail'];
			$emailsubject           = $settings['emailsubject'];
			$emailcontent           = $settings['emailcontent'];
			$pdfcolor               = $settings['pdfcolor'];
			$pdflogo                = $settings['pdflogo'];
			$pdfheader              = $settings['pdfheader'];
			$calcsubmit             = ($settings['calcsubmit'] != '' ? $settings['calcsubmit'] : $this->shmac_settings['calcsubmit']);
			$calcreset              = ($settings['calcreset'] != '' ? $settings['calcreset'] : $this->shmac_settings['calcreset']);
			$enable_slideroverride  = ($settings['enable_slideroverride'] != '' ? $settings['enable_slideroverride'] : $this->shmac_settings['enable_slider']);
			$enable_emailoverride   = ($settings['enable_emailoverride'] != '' ? $settings['enable_emailloverride'] : $this->shmac_settings['enable_email']);
			$inputreadonly          = ($settings['inputreadonly'] != '' ? $settings['inputreadonly'] : $this->shmac_settings['enable_input_readonly']);
			$slider_theme           = ($settings['slider_theme'] != '' ? $settings['slider_theme'] : $this->shmac_settings['slider_theme']);
			$sliderminamount        = ($settings['sliderminamount'] != '' ? $settings['sliderminamount'] : $this->shmac_settings['amount_min_value']);
			$slidermaxamount        = ($settings['slidermaxamount'] != '' ? $settings['slidermaxamount'] : $this->shmac_settings['amount_max_value']);
			$sliderstepsamount      = ($settings['sliderstepsamount'] != '' ? $settings['sliderstepsamount'] : $this->shmac_settings['amount_slider_step']);
			$slidermininterest      = ($settings['slidermininterest'] != '' ? $settings['slidermininterest'] : $this->shmac_settings['interest_min_value']);
			$slidermaxinterest      = ($settings['slidermaxinterest'] != '' ? $settings['slidermaxinterest'] : $this->shmac_settings['interest_max_value']);
			$sliderstepsinterest    = ($settings['sliderstepsinterest'] != '' ? $settings['sliderstepsinterest'] : $this->shmac_settings['interest_slider_step']);
			$slidermindown          = ($settings['slidermindown'] != '' ? $settings['slidermindown'] : $this->shmac_settings['dwnpay_min_value']);
			$slidermaxdown          = ($settings['slidermaxdown'] != '' ? $settings['slidermaxdown'] : $this->shmac_settings['dwnpay_max_value']);
			$sliderstepsdown        = ($settings['sliderstepsdown'] != '' ? $settings['sliderstepsdown'] : $this->shmac_settings['dwnpay_slider_step']);
			$sliderminterm          = ($settings['sliderminterm'] != '' ? $settings['sliderminterm'] : $this->shmac_settings['term_min_value']);
			$slidermaxterm          = ($settings['slidermaxterm'] != '' ? $settings['slidermaxterm'] : $this->shmac_settings['term_max_value']);
			$sliderstepsterm        = ($settings['sliderstepsterm'] != '' ? $settings['sliderstepsterm'] : $this->shmac_settings['term_slider_step']);
			$termtype               = ($settings['termtype'] != '' ? $settings['termtype'] : $this->shmac_settings['term_type']);
			$year_label             = ($settings['year_label'] != '' ? $settings['year_label'] : $this->shmac_settings['year_label']);
			$month_label            = ($settings['month_label'] !='' ? $settings['month_label'] : $this->shmac_settings['month_label']);
			$location               = ($settings['location'] != '' ? $settings['location'] : $this->shmac_settings['location']);
			$bg_attachment_url      = ($settings['bg_attachment_url'] != '' ? $settings['bg_attachment_url'] : $this->shmac_settings['bg_attachment_url']);
			$bg_color               = ($settings['bg_color'] != '' ? $settings['bg_color'] : $this->shmac_settings['bg_color']);


			// format strings for autoNumeric min max values
			//$sliderminamount = str_replace(',', '', $sliderminamount);
			//$slidermaxamount = str_replace(',', '', $slidermaxamount);

			// change postid integer to url for VC background image
			if (is_numeric($bg_attachment_url)) {
				$image_array = wp_get_attachment_image_src($bg_attachment_url, 'full');
				$bg_attachment_url = $image_array[0];
			}

            // Money Formats
            if ($currencyformat == '2') {  // French, Spanish 
                $money_format = ' data-decimal-character="," data-digit-group-separator="." ';
            } elseif ($currencyformat == '3') {  // Euro
                $money_format = ' data-decimal-character="," data-digit-group-separator=" " ';
			} elseif ($currencyformat == '4') { // Indian
				$money_format = 'data-digital-group-spacing="2" data-decimal-character="." data-digit-group-separator="," ';
			} elseif ($currencyformat == '5') { // Swiss
				$money_format = 'data-decimal-character="." data-digit-group-separator="\'" ';

            } else { // Standard Format
                $money_format = ' data-decimal-character="." data-digit-group-separator="," ';
            }
        

            $form_style = '';
            if ($primarycolor != '') {
                $form_style = <<<EOT

<style>
.form-$calc_inc .shmac-form .mui-form-group > .mui-form-control:focus ~ label {
    color: $primarycolor;
}
.form-$calc_inc .shmac-form .mui-select:focus > select {
    border-color: $primarycolor;
}
.form-$calc_inc .shmac-form .mui-btn.submit-shmac,
.form-$calc_inc .shmac-form .mui-btn.submit-shmac:hover {
    background-color: $primarycolor;
}
.form-$calc_inc .shmac-form input[type=text]:focus:not([readonly]),
.form-$calc_inc .shmac-form input[type=email]:focus:not([readonly]),
.form-$calc_inc .shmac-form input[type=password]:focus:not([readonly]) {
	border: none;
    border-bottom: 1px solid $primarycolor;
    box-shadow: 0 1px 0 0 $primarycolor;
}
.form-$calc_inc .shmac-form [type="checkbox"]:checked + label:before {
  border-right: 2px solid $primarycolor;
  border-bottom: 2px solid $primarycolor;
}
.shmac-div.divfrom-form-$calc_inc {
    border: 5px solid $primarycolor;
}
.shmac-div.divfrom-form-$calc_inc .schedule-table th {
    color: $primarycolor;
}
.form-$calc_inc .ui-mprogress .deter-bar,
.form-$calc_inc .ui-mprogress .indeter-bar,
.form-$calc_inc .ui-mprogress .query-bar,
.form-$calc_inc .ui-mprogress .mp-ui-dashed {
  background: $primarycolor;
}
.noUi-handle:focus {
    box-shadow: 0 0 7px #888;
    border: 8px solid $primarycolor;
}
</style>
EOT;

            } // End color style

            //Slider Settings Values
            $page_color =$this->shmac_settings['page_color'];
            $slidermininterest = number_format($slidermininterest, 3); // for autoNumeric to allow decimals
            $slidermaxinterest = number_format($slidermaxinterest, 3);
			if ($primarycolor!='') {
                $pColor = $primarycolor;
            } else if ($page_color != '') {
                $pColor = $page_color;
            }

			$narrow = '';
			$broad = '';
            if($slider_theme=='narrow'){
				$narrow = ".form-$calc_inc .sliders {margin-top: 15px;}\n"
						. ".form-$calc_inc .noUi-target{box-shadow:none;}\n"
						. ".form-$calc_inc .noUi-handle::after, .form-$calc_inc .noUi-handle::before{background:none;}\n"
						. ".form-$calc_inc .noUi-horizontal .noUi-handle {\n"
						. "	height: 20px;\n"
						. "	top: -7px;\n"
						. "	width: 20px;\n"
						. " border: 5px solid $pColor;\n"
						. "}\n"
						. ".form-$calc_inc .noUi-horizontal {height: 6px;}\n"
						. ".form-$calc_inc .noUi-handle {left: -8 !important; top: -8px !important;}\n";
			} else {
				$broad = ".form-$calc_inc .sliders .noUi-handle {top: -8px; width: 35px; height: 35px; border: 8px solid $pColor;}\n";
			}
            /* Background Image and color */
            $bg = ".shmac-holder-$calc_inc {\n"
				. "  background: url($bg_attachment_url);\n"
   				. "  background-size: cover;\n"
				. "}"
				. ".mui-panel.form-$calc_inc {background-color: $bg_color;}\n";

            $form_style .= <<<EOT

<style>
$bg
$narrow
$broad
.shmac-sc.form-$calc_inc .sliders { background-color: $pColor; }
</style>
EOT;
            // Individual Form Overrides set initial empty
            $o_enableinsurance = $o_insuranceamountpercent = $o_monthlyinsurance = $o_enablepmi = $o_monthlypmi = $o_enabletaxes 
            = $o_taxesperthou = $o_currencysymbol = $o_currencyformat = $o_currencyside  = $o_downpaytype
            = $o_bccemail = $o_fromemail = $o_emailsubject = $o_emailcontent = $o_pdfcolor = $o_pdflogo = $o_pdfheader = $o_location = '';

			// report overrides set to empty too
			$o_detail_original = $o_detail_down_payment = $o_detail_interest = $o_detail_term = $o_detail_loan_after_down = $o_detail_down_payment_amount = $o_detail_monthly_payment = $o_detail_total_payments = $o_header_payment = $o_header_payment_amount = $o_header_interest = $o_header_total_interest = $o_header_principal = $o_header_balance = $o_header_loan_text = $o_header_schedule_text = $o_otherfactors = $o_down_factor_1 = $o_down_factor_2 = $o_tax_factor = $o_insurance_factor = $o_factor_summary = $o_year_label = $o_month_label = $o_disclaimer = '';

            // Individual Form Overrides
            if ($enableinsurance != '') {
                $o_enableinsurance = 'data-enableinsurance="' . $enableinsurance . '" ';
            }
            if ($insuranceamountpercent != '') {
                $o_insuranceamountpercent = 'data-insuranceamountpercent="' . $insuranceamountpercent . '" ';
            }
            if ($monthlyinsurance != '') {
                $o_monthlyinsurance = 'data-monthlyinsurance="' . $monthlyinsurance . '" ';
            }
            if ($enablepmi != '') {
                $o_enablepmi = 'data-enablepmi="' . $enablepmi . '" ';
            }
            if ($monthlypmi != '') {
                $o_monthlypmi = 'data-monthlypmi="' . $monthlypmi . '" ';
            }
            if ($enabletaxes != '') {
                $o_enabletaxes = 'data-enabletaxes="' . $enabletaxes . '" ';
            }
            if ($taxesperthou != '') {
                $o_taxesperthou = 'data-taxesperthou=' . $taxesperthou . '" ';
            }
            if ($currencysymbol != '') {
                $o_currencysymbol = 'data-currencysymbol="' . $currencysymbol . '" ';
            }
            if ($currencyside != '') {
                $o_currencyside = 'data-currencyside="' . $currencyside . '" ';
            }
            if ($currencyformat != '') {
                $o_currencyformat = 'data-currencyformat="' . $currencyformat . '" ';
            }
            if ($downpaytype != '') {
                $o_downpaytype = 'data-downpaytype="' . $downpaytype . '" ';
            }

            if ($allowemail != '') {
                $o_allowemail = 'data-allowemail="' . $allowemail . '" ';
            }
            if ($bccemail != '') {
                $o_bccemail = 'data-bccemail="' . $bccemail . '" ';
            } 
            if ($fromemail != '') {
                $o_fromemail = 'data-fromemail="' . $fromemail . '" ';
            }
            if ($emailsubject != '') {
                $o_emailsubject = 'data-emailsubject="' . $emailsubject . '" ';
            }
            if ($emailcontent != '') {
                $o_emailcontent = 'data-emailcontent="' . $emailcontent . '" ';
            }
            if ($pdfcolor != '') {
                $o_pdfcolor = 'data-pdfcolor="' . $pdfcolor . '" ';
            }
            if ($pdflogo != '') {
                $o_pdflogo = 'data-pdflogo="' . $pdflogo . '" ';
            }
            if ($downpayshow == ''||$downpayshow == 'no') {
                $o_downpay = 'data-downpayshow="no" ';
            }else{
                $o_downpay = 'data-downpayshow="yes" ';
            }
            if ($pdfheader != '') {
                $o_pdfheader = 'data-pdfheader="' . $pdfheader . '" ';
            }
			if ($location != '') {
				$o_location = 'data-location="' . $location . '" ';
			}
			// Report overrides
		    if ($detail_original != '') {
                $o_detail_original = 'data-detail_original="' . $detail_original . '" ';
            }
            if ($detail_down_payment != '') {
                $o_detail_down_payment = 'data-detail_down_payment="' . $detail_down_payment . '" ';
            }
            if ($detail_interest != '') {
                $o_detail_interest = 'data-detail_interest="' . $detail_interest . '" ';
            }
            if ($detail_term != '') {
                $o_detail_term = 'data-detail_term="' . $detail_term . '" ';
            }
            if ($detail_loan_after_down != '') {
                $o_detail_loan_after_down = 'data-detail_loan_after_down="' . $detail_loan_after_down . '" ';
            }
            if ($detail_down_payment_amount != '') {
                $o_detail_down_payment_amount = 'data-detail_down_payment_amount="' . $detail_down_payment_amount . '" ';
            }
            if ($detail_monthly_payment != '') {
                $o_detail_monthly_payment = 'data-detail_monthly_payment="' . $detail_monthly_payment . '" ';
            }
            if ($detail_total_payments != '') {
                $o_detail_total_payments = 'data-detail_total_payments="' . $detail_total_payments . '" ';
            }
		    if ($header_payment != '') {
                $o_header_payment = 'data-header_payment="' . $header_payment . '" ';
            }
            if ($header_payment_amount != '') {
                $o_header_payment_amount = 'data-header_payment_amount="' . $header_payment_amount . '" ';
            }
            if ($header_interest != '') {
                $o_header_interest = 'data-header_interest="' . $header_interest . '" ';
            }
            if ($header_total_interest != '') {
                $o_header_total_interest = 'data-header_total_interest="' . $header_total_interest . '" ';
            }
            if ($header_principal != '') {
                $o_header_principal = 'data-header_principal="' . $header_principal . '" ';
            }
            if ($header_balance != '') {
                $o_header_balance = 'data-header_balance="' . $header_balance . '" ';
            }
			if ($header_loan_text != '') {
				$o_header_loan_text = 'data-header_loan_text="' . $header_loan_text . '" ';
			}
			if ($header_schedule_text != '') {
                $o_header_schedule_text = 'data-header_schedule_text="' . $header_schedule_text . '" ';
            }			
			if ($otherfactors != '') {
                $o_otherfactors = 'data-otherfactors="' . $otherfactors . '" ';
            }
			if ($down_factor_1 != '') {
                $o_down_factor_1 = 'data-down_factor_1="' . $down_factor_1 . '" ';
            }
			if ($down_factor_2 != '') {
                $o_down_factor_2 = 'data-down_factor_2="' . $down_factor_2 . '" ';
            }
			if ($tax_factor != '') {
                $o_tax_factor = 'data-tax_factor="' . $tax_factor . '" ';
            }
			if ($insurance_factor != '') {
                $o_insurance_factor = 'data-insurance_factor="' . $insurance_factor . '" ';
            }
			if ($factor_summary != '') {
                $o_factor_summary = 'data-factor_summary="' . $factor_summary . '" ';
            }
			if ($year_label != '') {
				$o_year_label = 'data-year_label="' . $year_label . '" ';
			}
			if ($month_label != '') {
                $o_month_label = 'data-month_label="' . $month_label . '" ';
            }	
            if ($disclaimer != '') {
                $o_disclaimer = 'data-disclaimer="' . $disclaimer . '" ';
            }

            // Form data attributes
            $data_atts = $o_enableinsurance . $o_insuranceamountpercent . $o_monthlyinsurance . $o_enablepmi . $o_monthlypmi 
                . $o_enabletaxes . $o_taxesperthou . $o_currencysymbol . $o_currencyformat . $o_currencyside 
                . $o_downpaytype . $o_bccemail . $o_fromemail . $o_emailsubject . $o_emailcontent . $o_pdfcolor . $o_pdflogo 
                . $o_pdfheader . $o_downpay . $o_location 
				. $o_detail_original . $o_detail_interest . $o_detail_term . $o_detail_loan_after_down . $o_detail_down_payment . $o_detail_down_payment_amount 
				. $o_detail_monthly_payment . $o_detail_total_payments . $o_header_payment . $o_header_payment_amount . $o_header_interest 
				. $o_header_total_interest . $o_header_principal . $o_header_balance . $o_header_loan_text . $o_header_schedule_text 
				. $o_otherfactors . $o_down_factor_1 . $o_down_factor_2 . $o_tax_factor . $o_insurance_factor . $o_factor_summary 
				. $o_year_label . $o_month_label . $o_disclaimer;

            $info_src = SHMAC_ROOT_URL . '/assets/img/info_outline.png'; 
            $symbol_side = '';
            $input_no_pad = '';
            if ($currencyside == 'right') {
                $symbol_side = "right";
                $input_no_pad = 'input-no-pad';
            }

            $output = <<<EOT
$form_style
<div class="shmac-holder shmac-holder-$calc_inc $extraclass">
  <div class="mui-panel shmac-sc form-$calc_inc">
    <form class="shmac-form" $data_atts >
        <div class="shmac-title">$calctitle</div>
EOT;
            
            if ($allowemail == 'yes') {
                if($enable_emailoverride=="yes"|| $enable_emailoverride=="enable"){
                    $output .= <<<EOT
                    <div class="shmac-check" style="display:none;">
                        <input type="checkbox" id="checkflip-$calc_inc" class="checkflip send-email" checked="checked" />
                    </div>
                    <div class="mui-form-group shmac-email" >
                        <input type="email" class="shmac-email-input mui-form-control" />
                        <label class="mui-form-floating-label">$emaillabel</label>
                        <div class="err-msg"></div>
                    </div>
EOT;
                }
                else{
                    $output .= <<<EOT
                    <div class="shmac-check">
                        <input type="checkbox" id="checkflip-$calc_inc" class="checkflip send-email" />
                        <label for="checkflip-$calc_inc">$emailtext</label>
                    </div>
                    <div class="mui-form-group shmac-email" style="display:none;">
                        <input type="text" class="shmac-email-input mui-form-control" />
                        <label class="mui-form-floating-label">$emaillabel</label>
                        <div class="err-msg"></div>
                    </div>
EOT;
                }
            } 

            //Down Payment format (percent or amount)
            if ($enable_slideroverride == 'yes') {
                $downpay_format = 'data-minimum-value="'. $slidermindown . '" data-maximum-value="' . $slidermaxdown . '"';
            } elseif ($downpaytype == 'percent') {
                $downpay_format = 'data-minimum-value="0.00" data-maximum-value="100.00"';
            } else {
                $downpay_format = '';
            }
            $downpay_symbol = '';
            $symbol_class = '';
            if ($downpaytype == 'amount') {
                $downpay_format = $money_format;
                $downpay_symbol = '<div class="shmac-symbol ' . $symbol_side . '">' . $currencysymbol . '</div>';
                $symbol_class = 'input-symbol';
            }
            //Slider
            $amtSlider = '';
            $intSlider = '';
            $dwnpaySlider = '';
            $termSlider = '';
            $readonlyHTML='';
            if ($enable_slideroverride == "yes" || $enable_slideroverride == "enable"){
                $amtSlider    = '<div class="sliders" id="amount_slider_'.$calc_inc.'"></div>';
                $intSlider    = '<div class="sliders" id="interest_slider_'.$calc_inc.'"></div>';
                $dwnpaySlider = '<div class="sliders" id="downpay_slider_'.$calc_inc.'"></div>';
                $termSlider   = '<div class="sliders" id="term_slider_'.$calc_inc.'"></div>';

                if ($inputreadonly == "yes" || $inputreadonly == "enable") $readonlyHTML = "readonly='readonly'";         
            }
            if ($termtype == "both") {
                $term_output =<<<EOT
                <div class="shmac-term-years">
                <input type="checkbox" id="term-years-$calc_inc" class="term-years term-group" checked="checked" />
                <label for="term-years-$calc_inc">$year_label</label>
                </div>
                <div class="shmac-term-months">
                <input type="checkbox" id="term-months-$calc_inc" class="term-months term-group" />
                <label for="term-months-$calc_inc">$month_label</label>
                </div>
EOT;
            }
            else if ($termtype=="year") {
                $term_output =<<<EOT
                <div class="shmac-term-years">
                <input type="checkbox" id="term-years-$calc_inc" class="term-years term-group" checked="checked" />
                <label for="term-years-$calc_inc">$year_label</label>
                </div>
EOT;
            }
            else if ($termtype=="month") {
                $term_output =<<<EOT
                <div class="shmac-term-months">
                <input type="checkbox" id="term-months-$calc_inc" class="term-months term-group" checked="checked" />
                <label for="term-months-$calc_inc">$month_label</label>
                </div>
EOT;
            }
            else {
                $term_output =<<<EOT
                <div class="shmac-term-years">
                <input type="checkbox" id="term-years-$calc_inc" class="term-years term-group" checked="checked" />
                <label for="term-years-$calc_inc">$year_label</label>
                </div>
                <div class="shmac-term-months">
                <input type="checkbox" id="term-months-$calc_inc" class="term-months term-group" />
                <label for="term-months-$calc_inc">$month_label</label>
                </div>
EOT;
            }
            $downpay_output = '';
            if ($downpayshow == "yes") {
                $downpay_output =<<<EOT
                    <div class="mui-form-group shmac-down">
                         <a href="#" class="shmac-tip" title=" " data-title="$downpayinfo">
                            <span>
                                <img src="$info_src" class="shmac-info" />
                            </span>
                        </a>
                        $downpay_symbol
                        <input type="text" class="downpay $symbol_class mui-form-control $input_no_pad downpayinput_$calc_inc" value="$defaultdown" $downpay_format $readonlyHTML />
                        <label class="mui-form-floating-label">$downpaylabel</label>
                        $dwnpaySlider
                        <div class="err-msg"></div>
                    </div>
EOT;
            }
            $output .= <<<EOT
            <div class="mui-form-group shmac-amount">
                <a href="#" class="shmac-tip" title=" " data-title="$amountinfo">
                    <span>
                        <img src="$info_src" class="shmac-info" />
                    </span>
                </a>
                <div class="shmac-symbol $symbol_side">$currencysymbol</div>
                <input type="text" class="mort-amount mui-form-control $input_no_pad amountinput_$calc_inc" value="$defaultpurchase" $money_format $readonlyHTML /> 
                <label class="mui-form-floating-label">$amountlabel</label>
                $amtSlider
                <div class="err-msg"></div>
            </div>

            <div class="mui-form-group shmac-interest">
                 <a href="#" class="shmac-tip" title=" " data-title="$interestinfo">
                    <span>
                        <img src="$info_src" class="shmac-info" />
                    </span>
                </a>
                <input type="text" class="interest mui-form-control interestinput_$calc_inc" value="$defaultinterest" $readonlyHTML  />
                <label class="mui-form-floating-label">$interestlabel</label>
                $intSlider
                <div class="err-msg"></div>
            </div>

            $downpay_output

            <div class="mui-form-group shmac-term">
                $term_output

                 <a href="#" class="shmac-tip" title=" " data-title="$terminfo">
                    <span>
                        <img src="$info_src" class="shmac-info" />
                    </span>
                </a>
                <input type="text" class="term mui-form-control terminput_$calc_inc" value="$defaultterm" data-decimal-places-override="0" $readonlyHTML />
                <label class="mui-form-label">$termlabel</label>
                $termSlider
                <div class="err-msg"></div>
            </div>
            <div class="progresso"> &nbsp;</div>
            <div class="buttonRow">
                <button class="mui-btn submit-shmac" data-mui-color="$page_color">$calcsubmit</button>
                <button class="mui-btn shmac-reset shmac-reset_{$calc_inc}" type="reset">$calcreset</button>
            </div>
                
    </form>
  </div>
</div><!-- End shmac-holder -->
<div class="shmac-inline-form" id="shmac-inline-form-$calc_inc"></div>
EOT;
			// Set up variables that go to frontend-slider.js
			if (is_rtl()) {
				$sliderdir = 'rtl';
			} else {
				$sliderdir = 'ltr';
			}
            if($enable_slideroverride=="yes" || $enable_slideroverride=="enable"){
				$slider_vars[$calc_inc]['calc_inc'] = $calc_inc;
				$slider_vars[$calc_inc]['defaultpurchase'] = $defaultpurchase;
				$slider_vars[$calc_inc]['sliderminamount'] = $sliderminamount;
				$slider_vars[$calc_inc]['sliderstepsamount'] = $sliderstepsamount;
				$slider_vars[$calc_inc]['slidermaxamount'] = $slidermaxamount;
				$slider_vars[$calc_inc]['currencyformat'] = $currencyformat;
				$slider_vars[$calc_inc]['slidermininterest'] = $slidermininterest;
				$slider_vars[$calc_inc]['sliderstepsinterest'] = $sliderstepsinterest;
				$slider_vars[$calc_inc]['slidermaxinterest'] = $slidermaxinterest;
				$slider_vars[$calc_inc]['defaultinterest'] = $defaultinterest;
				$slider_vars[$calc_inc]['downpayshow'] = $downpayshow;
				$slider_vars[$calc_inc]['slidermindown'] = $slidermindown;
				$slider_vars[$calc_inc]['sliderstepsdown'] = $sliderstepsdown;
				$slider_vars[$calc_inc]['slidermaxdown'] = $slidermaxdown;
				$slider_vars[$calc_inc]['defaultdown'] = $defaultdown;
				$slider_vars[$calc_inc]['downpaytype'] = $downpaytype;
				$slider_vars[$calc_inc]['sliderminterm'] = $sliderminterm;
				$slider_vars[$calc_inc]['sliderstepsterm'] = $sliderstepsterm;
				$slider_vars[$calc_inc]['slidermaxterm'] = $slidermaxterm;
				$slider_vars[$calc_inc]['defaultterm'] = $defaultterm;
				$slider_vars[$calc_inc]['sliderdir'] = $sliderdir;

				// enqueue the slider script and styles
				wp_register_script( 'shmac-frontend-slider', SHMAC_ROOT_URL . '/assets/js/frontend-slider.js', array('jquery'), SHMAC_PLUGIN_VERSION, true );

            	wp_localize_script(  'shmac-frontend-slider', 'SHMAC_Slider', array(
                	'ajaxurl'             => admin_url('admin-ajax.php'),
                	'nextNonce'           => wp_create_nonce( 'myajax-next-nonce' ),
					'defaultpurchase'     => $defaultpurchase,
					'sliderminamount'     => $sliderminamount,
					'sliderstepsamount'   => $sliderstepsamount,
					'slidermaxamount'     => $slidermaxamount,
					'calc_inc'            => $calc_inc,
					//'calc_slider'         => $calc_slider,
					'slider_vars'         => $slider_vars,
					'currencyformat'      => $currencyformat,
					'slidermininterest'   => $slidermininterest,
					'sliderstepsinterest' => $sliderstepsinterest,
					'slidermaxinterest'   => $slidermaxinterest,
					'defaultinterest'     => $defaultinterest,
					'downpayshow'         => $downpayshow,
					'slidermindown'       => $slidermindown,
					'sliderstepsdown'     => $sliderstepsdown,
					'slidermaxdown'       => $slidermaxdown,
					'defaultdown'         => $defaultdown,
					'downpaytype'         => $downpaytype,
					'sliderminterm'       => $sliderminterm,
					'sliderstepsterm'     => $sliderstepsterm,
					'slidermaxterm'       => $slidermaxterm,
					'defaultterm'         => $defaultterm,
            	));
				wp_enqueue_script('shmac-frontend-slider');
            }
            return $output;
        }

    } // end class
