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
            if ( !shortcode_exists( 'shmac_calc' ) ) {
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
            add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 700 );
            add_action( 'shmac_enqueue_scripts', array( $this, 'enqueue_scripts' ), 700 );
            add_action( 'shmac_enqueue_minified_scripts', array( $this, 'enqueue_minified_scripts' ), 700 );
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

			// mui
            //wp_register_style( 'mui', SHMAC_ROOT_URL . '/assets/css/mui.css', array(), '0.1.22-rc1' );
            //wp_enqueue_style('mui');

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
            
            //Minified Css
			wp_register_style( 'mCustomScroll-mprogress-nouislider', SHMAC_ROOT_URL .  '/assets/css/mCustomScroll.mprogress.nouislider.min.css', array('shmac-frontend'), '920');
           
			// ie9
			/*
			wp_register_style('shmac-ie9', SHMAC_ROOT_URL . '/assets/css/ie9.css');
			wp_enqueue_style('shmac-ie9');
			$wp_styles->add_data('shmac-ie9', 'conditional', 'IE 9');
			*/

			// dynamic css
			wp_register_style('shmac-dynamic-css', 
				admin_url('admin-ajax.php').'?action=shmac_dynamic_css', 'shmac-frontend', SHMAC_PLUGIN_VERSION );
			wp_enqueue_style('shmac-dynamic-css');
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
         
        public function register_scripts () {
			// autoNumeric
			wp_register_script('autoNumeric', SHMAC_ROOT_URL . '/assets/js/autoNumeric.js', array('jquery'), '1.9.26', true);
			// Mui
			wp_register_script( 'mui', SHMAC_ROOT_URL . '/assets/js/mui.js', array(), '0.1.22-rc1', true );
			// Scrollbar
			wp_register_script( 'shmac-custom-scrollbar', SHMAC_ROOT_URL 
				. '/assets/js/jquery.mCustomScrollbar.concat.min.js', array('jquery'), '3.0.9', true);
			// mprogress
			wp_register_script( 'mprogress', SHMAC_ROOT_URL . '/assets/js/mprogress.min.js', array('jquery'), '1.0', true);
			//nouislider
			wp_register_script( 'nouislider', SHMAC_ROOT_URL . '/assets/js/nouislider.min.js', array('jquery'), '920', true);
			//wNumb
			wp_register_script( 'wNumb', SHMAC_ROOT_URL . '/assets/js/wNumb.js', array('jquery'), '110', true);

			//Minified JS
			wp_register_script( 'autoNumeric-mCustomScroll-mprogress-nouislider-wNumb-mui', SHMAC_ROOT_URL . '/assets/js/autoNumeric.mCustomScroll.mprogress.nouislider.wNumb.mui.min.js', array('jquery'), '1.0', true);

			// Plugin js    
            wp_register_script( 'shmac-frontend-ajax', SHMAC_ROOT_URL . '/assets/js/frontend-ajax.js', array('jquery'), SHMAC_PLUGIN_VERSION, true );

			wp_localize_script(  'shmac-frontend-ajax', 'SHMAC_Ajax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nextNonce' => wp_create_nonce( 'myajax-next-nonce' ),
				'shmacColor' => isset($this->first_tab['page_color'])?$this->first_tab['page_color']:'#03a9f4'
            ));
		}
        public function enqueue_scripts () {
			$list = 'enqueued';
			$enqueueCssList = array("shmac-frontend","mprogress","shmac-custom-scrollbar","nouislider");
			foreach($enqueueCssList as $css){
				if (wp_script_is( $css, $list )) {
					return;
				} else {
					wp_enqueue_style( $css );
				}
			}
			$enqueueJsList = array("autoNumeric","mui","shmac-custom-scrollbar","mprogress","shmac-frontend-ajax","nouislider","wNumb");
			foreach($enqueueJsList as $js){
				if (wp_script_is( $js, $list )) {
					return;
				} else {
					wp_enqueue_script( $js );
				}
			}
			if (wp_script_is( "autoNumeric-mCustomScroll-mprogress-nouislider-wNumb-mui", "registered" )) {
				wp_deregister_script( "autoNumeric-mCustomScroll-mprogress-nouislider-wNumb-mui" ); 
			}
			if (wp_script_is( "mCustomScroll-mprogress-nouislider", "registered" )) {
				wp_deregister_style( "mCustomScroll-mprogress-nouislider" ); 
			}
			
		} // End enqueue_scripts 

		
        public function enqueue_minified_scripts () {
			$list = 'enqueued';
			$enqueueCssList = array("shmac-frontend","mCustomScroll-mprogress-nouislider");
			foreach($enqueueCssList as $css){
				if (wp_script_is( $css, $list )) {
					return;
				} else {
					wp_enqueue_style( $css );
				}
			}
			$enqueueJsList = array("autoNumeric-mCustomScroll-mprogress-nouislider-wNumb-mui","shmac-frontend-ajax");
			foreach($enqueueJsList as $js){
				if (wp_script_is( $js, $list )) {
					return;
				} else {
					wp_enqueue_script( $js );
				}
			}
			$deenqueueJsList = array("autoNumeric","mui","shmac-custom-scrollbar","mprogress","nouislider","wNumb");
			foreach($deenqueueJsList as $js){
				if (wp_script_is( $js, "registered" )) {
					wp_deregister_script( $js ); 
				}       
			}
			$deenqueueCssList = array("mprogress","shmac-custom-scrollbar","nouislider");
			foreach($deenqueueCssList as $css){
				if (wp_script_is( $css, "registered" )) {
					wp_deregister_style( $css ); 
				}
			}			
			
        } // End enqueue_minified_scripts 

		
		/**
         * Shortcode generation
         * @since 1.0.0
         * @access public
         * @return $output
         */
        public function shmac_calc($atts, $content=NULL) {
			global $calc_inc; // tracking multiple calculators
			$calc_inc++;
			if($this->shmac_settings['minify_css_js']=="yes")
				do_action('shmac_enqueue_minified_scripts');
			else
				do_action('shmac_enqueue_scripts');
            extract(shortcode_atts(array( 
				'extraclass'       => '',
				// Base Settings Overrides
				'primarycolor'     => '',
				'color'            => '',
				'calctitle'        => '',
				'emailtext'        => '',
				'emaillabel'       => '',
				'amountlabel'      => '',
				'amountinfo'       => '',
				'defaultpurchase'  => '',
				'interestlabel'    => '',
				'interestinfo'     => '',
				'defaultinterest'  => '',
				'downpaylabel'     => '',
				'downpayinfo'      => '',
				'downpaytype'      => '',
				'defaultdown'      => '',
				'termlabel'        => '',
				'terminfo'         => '',
				'defaultterm'      => '',
				'enableinsurance'  => '',
				'insuranceamountpercent' => '',
				'monthlyinsurance' => '',
				'enablepmi'        => '',
				'monthlypmi'       => '',
				'enabletaxes'      => '',
				'taxesperthou'     => '',
				'disclaimer'       => '',
				'currencysymbol'   => '',
				'currencyformat'   => '',
				'currencyside'     => '',
				// Email Settings Overrides
				'allowemail'       => '',
				'bccemail'         => '',
				'fromemail'        => '',
				'emailsubject'     => '',
				'emailcontent'     => '',
				'pdfcolor'         => '',
				'pdflogo'          => '',
				'pdfheader'        => '',
				// Extras
				'calcsubmit'       => '',
				'calcreset'        => '',
				//Slider Settings 
				'enable_slideroverride'        => '',
				'inputreadonly'  => '',
				'slider_theme'  => '',
				'sliderminamount'       => '',
				'slidermaxamount'       => '',
				'sliderstepsamount'     => '',
				'slidermininterest'     => '',
				'slidermaxinterest'     => '',
				'sliderstepsinterest'     => '',
				'slidermindown'     => '',
				'slidermaxdown'     => '',
				'sliderstepsdown'     => '',
				'sliderminterm'     => '',
				'slidermaxterm'     => '',
				'sliderstepsterm'     => '',
            ), $atts));

			// Messages
			$years =     __('Years', 'shmac');
			$months =    __('Months', 'shmac');

			if ($calcsubmit != '') {
				$btn_calc = $calcsubmit;
			} else {
				$btn_calc =  __('Calculate', 'shmac');
			}
			if ($calcreset != '') {
				$btn_reset = $calcreset;
			} else {
				$btn_reset = __('Reset', 'shmac');
			}

			// Money Formats
			if ($currencyformat != '') {  // Overrides
				if ($currencyformat == '2') {
					$money_format = ' data-a-dec="," data-a-sep="." ';
				} elseif ($currencyformat == '3') {
					$money_format = ' data-a-dec="," data-a-sep=" " ';
				} else { // Standard Format
					$money_format = ' data-a-dec="." data-a-sep="," ';
				}
			} else {
				if ($this->shmac_settings['currency_format'] == '2') {
					$money_format = ' data-a-dec="," data-a-sep="." ';
				} elseif ($this->shmac_settings['currency_format'] == '3') {
					$money_format = ' data-a-dec="," data-a-sep=" " ';
				} else { // Standard Format
					$money_format = ' data-a-dec="." data-a-sep="," ';
				}
			}

            $form_style = '';
            
            if ($primarycolor != '') {
                // color overrides
                require_once( SHMAC_ROOT_PATH . '/includes/shmac-utils.php' );
                $shmac_utils = new shmac_utils();
                $primarycolor_light = $shmac_utils->hex2rgba($primarycolor, $opacity = 0.4);
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
.form-$calc_inc .shmac-form input[type=password]:focus:not([readonly]) {
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
.form-$calc_inc .ui-mprogress .bar-bg,
.form-$calc_inc .ui-mprogress .buffer-bg,
.form-$calc_inc .ui-mprogress .mp-ui-dashed {
  background: $primarycolor;
}
.form-$calc_inc .ui-mprogress .bar-bg,
.form-$calc_inc .ui-mprogress .buffer-bg {
  background: $primarycolor_light;
}
.noUi-handle:focus {
	box-shadow: 0 0 5px $primarycolor;
}

</style>
EOT;

            } // End color style

			// Form display items
			if ($allowemail == '') {
				$allowemail = $this->shmac_email['allow_email'];				
			}
			if ($currencyside == '') {
				$currencyside = $this->shmac_settings['currency_side'];
			}
			if ($currencysymbol == '') {
				$currencysymbol = $this->shmac_settings['currency'];
			}
            if ($calctitle == '') {
                $calctitle = $this->shmac_settings['calc_title'];
            }
            if ($emailtext == '') {
                $emailtext = $this->shmac_settings['send_email_text'];
            }
            if ($emaillabel == '') {
                $emaillabel = $this->shmac_settings['email_placeholder'];
            }
            if ($amountlabel == '') {
                $amountlabel = $this->shmac_settings['purchase_price_label'];
            }
            if ($amountinfo == '') {
                $amountinfo = $this->shmac_settings['purchase_price_info'];
            }
            if ($interestlabel == '') {
                $interestlabel = $this->shmac_settings['interest_rate_label'];
            }
            if ($interestinfo == '') {
                $interestinfo = $this->shmac_settings['interest_rate_info'];
            }
            if ($downpaylabel == '') {
                $downpaylabel = $this->shmac_settings['down_payment_label'];
            }
            if ($downpayinfo == '') {
                $downpayinfo = $this->shmac_settings['down_payment_info'];
            } 
			if ($downpaytype == '') {
				$downpaytype = isset($this->shmac_settings['down_payment_type']) 
					? $this->shmac_settings['down_payment_type'] : 'percent';
			}
            if ($termlabel == '') {
                $termlabel = $this->shmac_settings['loan_term_label'];
            }
            if ($terminfo == '') {
                $terminfo = $this->shmac_settings['loan_term_info'];
            }
           
            //Slider Settings Values
			$page_color =$this->shmac_settings['page_color'];
			if($enable_slideroverride=='') $enable_slideroverride              = $this->shmac_settings['enable_slider'];
			if($inputreadonly=='') $inputreadonly            = $this->shmac_settings['enable_input_readonly'];
			if($sliderminamount=='')$sliderminamount         = $this->shmac_settings['amount_min_value'];
			if($slidermaxamount=='')$slidermaxamount         = $this->shmac_settings['amount_max_value'];
			if($defaultpurchase=='')$defaultpurchase         = $this->shmac_settings['purchase_price'];
			if($sliderstepsamount=='')$sliderstepsamount     = $this->shmac_settings['amount_slider_step'];
			if($slidermininterest=='')$slidermininterest     = $this->shmac_settings['interest_min_value'];
			$slidermininterest = number_format($slidermininterest, 3); // for autoNumeric to allow decimals
			if($slidermaxinterest=='')$slidermaxinterest     = $this->shmac_settings['interest_max_value'];
			if($defaultinterest=='')$defaultinterest         = $this->shmac_settings['interest_rate'];
			if($sliderstepsinterest=='')$sliderstepsinterest = $this->shmac_settings['interest_slider_step'];
			if($slidermindown=='')$slidermindown             = $this->shmac_settings['dwnpay_min_value'];
			if($slidermaxdown=='')$slidermaxdown             = $this->shmac_settings['dwnpay_max_value'];
			if($defaultdown=='')$defaultdown                 = $this->shmac_settings['down_payment'];
			if($sliderstepsdown=='')$sliderstepsdown         = $this->shmac_settings['dwnpay_slider_step'];
			if($sliderminterm=='')$sliderminterm             = $this->shmac_settings['term_min_value'];
			if($slidermaxterm=='')$slidermaxterm             = $this->shmac_settings['term_max_value'];
			if($defaultterm=='')$defaultterm                 = $this->shmac_settings['loan_term'];
			if($sliderstepsterm=='')$sliderstepsterm         = $this->shmac_settings['term_slider_step'];          
			//SLider Theme
			if ($slider_theme == '') {
                $slider_theme = $this->shmac_settings['slider_theme'];
            }
            if($slider_theme=='narrow'){
				 $form_style .= <<<EOT
<style>
.form-$calc_inc .sliders {margin-top: 15px;}
.form-$calc_inc .noUi-target{box-shadow:none;}
.form-$calc_inc .noUi-handle::after, .form-$calc_inc .noUi-handle::before{background:none;}
.form-$calc_inc .noUi-horizontal .noUi-handle {
    height: 24px;
    top: -9px;
    width: 24px;
}
.form-$calc_inc .noUi-horizontal {height: 6px;}
</style>
EOT;
			}
			// Individual Form Overrides set initial empty
			$o_enableinsurance = $o_insuranceamountpercent = $o_monthlyinsurance = $o_enablepmi = $o_monthlypmi = $o_enabletaxes 
			= $o_taxesperthou = $o_disclaimer = $o_currencysymbol = $o_currencyformat = $o_currencyside  = $o_downpaytype
			= $o_bccemail = $o_fromemail = $o_emailsubject = $o_emailcontent = $o_pdfcolor = $o_pdflogo = $o_pdfheader = '';

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
			if ($disclaimer != '') {
				$o_disclaimer = 'data-disclaimer="' . $disclaimer . '" ';
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
			if ($pdfheader != '') {
				$o_pdfheader = 'data-pdfheader="' . $pdfheader . '" ';
			}

			// Form data attributes
			$data_atts = $o_enableinsurance . $o_insuranceamountpercent . $o_monthlyinsurance . $o_enablepmi . $o_monthlypmi 
			    . $o_enabletaxes . $o_taxesperthou . $o_disclaimer . $o_currencysymbol . $o_currencyformat . $o_currencyside 
				. $o_downpaytype . $o_bccemail . $o_fromemail . $o_emailsubject . $o_emailcontent . $o_pdfcolor . $o_pdflogo 
				. $o_pdfheader;

			$info_src = SHMAC_ROOT_URL . '/assets/img/info.png'; 
			$symbol_side = '';
			$input_no_pad = '';
			if ($currencyside == 'right') {
				$symbol_side = "right";
				$input_no_pad = 'input-no-pad';
			}

			$output = <<<EOT
$form_style
<div class="shmac-holder $extraclass">
  <div class="mui-panel shmac-sc form-$calc_inc">
	<form class="shmac-form" $data_atts >
		<legend>$calctitle</legend>
EOT;
			
     		if ($allowemail == 'yes') { 
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

			//Down Payment format (percent or amount)
			if ($enable_slideroverride == 'yes') {
				$downpay_format = 'data-v-min="'. $slidermindown . '" data-v-max="' . $slidermaxdown . '" data-a-pad="false"';
			} elseif ($downpaytype == 'percent') {
				$downpay_format = 'data-v-min="0.000" data-v-max="100.000" data-a-pad="false"';
			} else {
				$downpay_format = 'data-a-pad="false"';
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
			$minmax = '';
			$minmax_int = 'data-v-min="0.000" data-v-max="100.000"';
			$minmax_term = '';
			if($enable_slideroverride=="yes"|| $enable_slideroverride=="enable"){
				$amtSlider = '<div class="sliders" id="amount_slider_'.$calc_inc.'"></div>';
				$intSlider = '<div class="sliders" id="interest_slider_'.$calc_inc.'"></div>';
				$dwnpaySlider = '<div class="sliders" id="downpay_slider_'.$calc_inc.'"></div>';
				$termSlider = '<div class="sliders" id="term_slider_'.$calc_inc.'"></div>';
				$minmax = 'data-v-min="' . $sliderminamount . '" data-v-max="' . $slidermaxamount . '"';
				$minmax_int = 'data-v-min="' . $slidermininterest . '" data-v-max="' . $slidermaxinterest . '"';
				$minmax_term = 'data-v-min="' . $sliderminterm . '" data-v-max="' . $slidermaxterm . '"';

				if($inputreadonly=="yes"|| $inputreadonly=="enable") $readonlyHTML = "readonly='readonly'";			
			}
			
			$output .= <<<EOT
			<div class="mui-form-group shmac-amount">
				<a href="#" class="shmac-tip" title=" " data-title="$amountinfo">
                	<span>
                    	<img src="$info_src" class="shmac-info" />
                	</span>
            	</a>
				<div class="shmac-symbol $symbol_side">$currencysymbol</div>
          		<input type="text" class="mort-amount mui-form-control $input_no_pad amountinput_$calc_inc" value="$defaultpurchase" $money_format  $minmax $readonlyHTML /> 
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
				<input type="text" class="interest mui-form-control interestinput_$calc_inc" value="$defaultinterest" 
					$minmax_int data-a-pad="false" $readonlyHTML  />
				<label class="mui-form-floating-label">$interestlabel</label>
				$intSlider
				<div class="err-msg"></div>
			</div>

			<div class="mui-form-group shmac-down">
				 <a href="#" class="shmac-tip" title=" " data-title="$downpayinfo">
                    <span>
                        <img src="$info_src" class="shmac-info" />
                    </span>
                </a>
				$downpay_symbol
				<input type="text" class="downpay $symbol_class mui-form-control $input_no_pad downpayinput_$calc_inc" value="$defaultdown"
					$downpay_format $readonlyHTML />
				<label class="mui-form-floating-label">$downpaylabel</label>
				$dwnpaySlider
				<div class="err-msg"></div>
			</div>

			<div class="mui-form-group shmac-term">
				<div class="shmac-term-years">
                <input type="checkbox" id="term-years-$calc_inc" class="term-years term-group" checked="checked" />
                <label for="term-years-$calc_inc">$years</label>
            	</div>
				<div class="shmac-term-months">
                <input type="checkbox" id="term-months-$calc_inc" class="term-months term-group" />
                <label for="term-months-$calc_inc">$months</label>
                </div>

                 <a href="#" class="shmac-tip" title=" " data-title="$terminfo">
                    <span>
                        <img src="$info_src" class="shmac-info" />
                    </span>
                </a>
                <input type="text" class="term mui-form-control terminput_$calc_inc" value="$defaultterm"
                    $minmax_term data-a-pad="false" $readonlyHTML />
                <label class="mui-form-floating-label">$termlabel</label>
                $termSlider
				<div class="err-msg"></div>
            </div>
			<div class="progresso"> &nbsp;</div>
        	<div class="buttonRow">
         		<button class="mui-btn submit-shmac" data-mui-color="{$this->shmac_settings['page_color']}">$btn_calc</button>
        	 	<button class="mui-btn shmac-reset_{$calc_inc}" type="reset">$btn_reset</button>
			</div>
				
    </form>
  </div>
</div><!-- End shmac-holder -->
EOT;
			if($enable_slideroverride=="yes" || $enable_slideroverride=="enable"){
				ob_start();				
				if($primarycolor!='') $pColor = $primarycolor;
				else if ($page_color != '') $pColor = $page_color;
				?>
				<style>
					.shmac-sc.form-<?php echo $calc_inc ?> .noUi-handle:hover { box-shadow: 0 0 5px <?php echo $pColor ?>; }
					.shmac-sc.form-<?php echo $calc_inc ?> .sliders { background-color: <?php echo $pColor ?>; }
				</style>
				<script type='text/javascript'>
				jQuery(document).ready(function(){
					//Amount Slider- Input Script
					var amtStart= '<?php echo $defaultpurchase; ?>';
					amtStart = Number(amtStart.replace(/,/g , ''));
					var amtMin= '<?php echo $sliderminamount; ?>';
					amtMin = Number(amtMin.replace(/,/g , ''));
					var amtStep = Number('<?php echo $sliderstepsamount; ?>');
					var amtMax = '<?php echo $slidermaxamount; ?>';
					amtMax = Number(amtMax.replace(/,/g , ''));
					var amount_slider_<?php echo $calc_inc ?> = document.getElementById("amount_slider_<?php echo $calc_inc ?>");
					var amount_slider_value = jQuery(".amountinput_<?php echo $calc_inc ?>");
					noUiSlider.create(amount_slider_<?php echo $calc_inc ?>, {
						start: amtStart,
						step: amtStep,
						range: {
							'min': amtMin,
							'max': amtMax
						},
						format:wNumb({
							decimals: 2,							
							<?php   if($currencyformat==2){ ?>
								mark: ',',
								thousand: '.',
							<?php } else if($currencyformat==3){ ?>
								mark: ',',
								thousand: ' ',
							<?php }else if($currencyformat==1){ ?>
								mark: '.',
								thousand: ',',
							<?php } ?>
						})
					});
					amount_slider_<?php echo $calc_inc ?>.noUiSlider.on('update', function( values, handle ){
						if ( handle == 0 ) {
							amount_slider_value.val(values[handle]);
						}
					});
					amount_slider_value.on('change', function(){
						var amtVal = Number(this.value.replace(/,/g , ''));						
						amount_slider_<?php echo $calc_inc ?>.noUiSlider.set(amtVal);
					});
					amount_slider_value.on('keyup', function(e){
						var amtVal = Number(this.value.replace(/,/g , ''));						
						switch ( e.which ) {
							case 13:
								amount_slider_<?php echo $calc_inc ?>.noUiSlider.set(amtVal);
								return false;							
							break;
							case 38:
								amount_slider_<?php echo $calc_inc ?>.noUiSlider.set(amtVal);
							break;
							case 40:
								amount_slider_<?php echo $calc_inc ?>.noUiSlider.set(amtVal);
							break;
						}
						
					});
					//~ amount_slider_<?php echo $calc_inc ?>.noUiSlider.on('change', function() {
						//~ jQuery('.amountinput_<?php echo $calc_inc; ?>').autoNumeric('update');
					//~ });
					//~ amount_slider_<?php echo $calc_inc ?>.noUiSlider.on('end', function() {
                        //~ jQuery('.amountinput_<?php echo $calc_inc; ?>').autoNumeric('update');
                    //~ });
					//Interest Slider- Input Script
					var intMin= parseFloat('<?php echo $slidermininterest; ?>');
					var intStep = parseFloat('<?php echo $sliderstepsinterest; ?>');
					var intMax = parseFloat('<?php echo $slidermaxinterest; ?>');
					var intStart = parseFloat('<?php echo $defaultinterest; ?>');
					var interest_slider_<?php echo $calc_inc ?> = document.getElementById("interest_slider_<?php echo $calc_inc ?>");
					var interest_slider_value = jQuery(".interestinput_<?php echo $calc_inc ?>");
					noUiSlider.create(interest_slider_<?php echo $calc_inc ?>, {
						start: intStart,
						step: intStep,
						range: {
							'min': intMin,
							'max': intMax
						}
					});				
					interest_slider_<?php echo $calc_inc ?>.noUiSlider.on('update', function( values, handle ){
						if ( handle == 0 ) {
							interest_slider_value.val(values[handle]);
						}
					});					
					//~ interest_slider.on('change', function(){
						//~ var intVal = Number(this.value.replace(/,/g , ''));						
						//~ interest_slider.noUiSlider.set(intVal);
					//~ });
					interest_slider_value.on('keyup', function(e){
						var intVal = Number(this.value.replace(/,/g , ''));						
						switch ( e.which ) {
							case 13:
								interest_slider_<?php echo $calc_inc ?>.noUiSlider.set(intVal);
								return false;							
							break;
							case 38:
								interest_slider_<?php echo $calc_inc ?>.noUiSlider.set(intVal);
							break;
							case 40:
								interest_slider_<?php echo $calc_inc ?>.noUiSlider.set(intVal);
							break;
						}						
					});
					//DownPayment Slider- Input Script
					var dwnpayMin= parseFloat('<?php echo $slidermindown; ?>');
					var dwnpayStep = parseFloat('<?php echo $sliderstepsdown; ?>');
					var dwnpayMax = parseFloat('<?php echo $slidermaxdown; ?>');
					var dwnpayStart = parseFloat('<?php echo $defaultdown; ?>');
					var downpay_slider_<?php echo $calc_inc ?> = document.getElementById("downpay_slider_<?php echo $calc_inc ?>");
					var downpay_slider_value = jQuery(".downpayinput_<?php echo $calc_inc ?>");
					noUiSlider.create(downpay_slider_<?php echo $calc_inc ?>, {
						start: dwnpayStart,
						step: dwnpayStep,
						range: {
							'min': dwnpayMin,
							'max': dwnpayMax
						},
						format: wNumb({
							decimals: 2,							
							<?php   if($downpaytype=="amount"){ ?>
								<?php   if($currencyformat==2){ ?>
									mark: ',',
									thousand: '.',
								<?php } else if($currencyformat==3){ ?>
									mark: ',',
									thousand: ' ',
								<?php }else{ ?>
									mark: '.',
									thousand: ',',
								<?php } ?>
							<?php } ?>
						})
						
					});				
					downpay_slider_<?php echo $calc_inc ?>.noUiSlider.on('update', function( values, handle ){
						if(handle==0){
							downpay_slider_value.val(values[handle]);
						}
					});
					
					downpay_slider_value.on('change', function(){
						var downpayVal = Number(this.value.replace(/,/g , ''));						
						downpay_slider_<?php echo $calc_inc ?>.noUiSlider.set(downpayVal);
					});
					downpay_slider_value.on('keyup', function(e){
						var downpayVal = Number(this.value.replace(/,/g , ''));						
						switch ( e.which ) {
							case 13:
								downpay_slider_<?php echo $calc_inc ?>.noUiSlider.set(downpayVal);
								return false;							
							break;
							case 38:
								downpay_slider_<?php echo $calc_inc ?>.noUiSlider.set(downpayVal);
							break;
							case 40:
								downpay_slider_<?php echo $calc_inc ?>.noUiSlider.set(downpayVal);
							break;
						}
						
					});
					//Term Slider- Input Script
					var termMin= parseFloat('<?php echo $sliderminterm; ?>');
					var termStep = parseFloat('<?php echo $sliderstepsterm; ?>');
					var termMax = parseFloat('<?php echo $slidermaxterm; ?>');
					var termStart = parseFloat('<?php echo $defaultterm; ?>');
					var term_slider_<?php echo $calc_inc ?> = document.getElementById("term_slider_<?php echo $calc_inc ?>");
					var term_slider_value = jQuery(".terminput_<?php echo $calc_inc ?>");					
					noUiSlider.create(term_slider_<?php echo $calc_inc ?>, {
						start: termStart,
						step: termStep,
						range: {
							'min': termMin,
							'max': termMax
						},
						format: wNumb({decimals: 0})
					});				
					term_slider_<?php echo $calc_inc ?>.noUiSlider.on('update', function( values, handle ){
						var value = values[handle];
						if(handle==0){
							term_slider_value.val(value);
						}
					});				
					term_slider_value.on('change', function(){
						var termVal = Number(this.value.replace(/,/g , ''));						
						term_slider_<?php echo $calc_inc ?>.noUiSlider.set(termVal);
					});
					term_slider_value.on('keyup', function(e){
						var termVal = Number(this.value.replace(/,/g , ''));						
						switch ( e.which ) {
							case 13:
								term_slider_<?php echo $calc_inc ?>.noUiSlider.set(termVal);
								return false;							
							break;
							case 38:
								term_slider_<?php echo $calc_inc ?>.noUiSlider.set(termVal);
							break;
							case 40:
								term_slider_<?php echo $calc_inc ?>.noUiSlider.set(termVal);
							break;
						}						
					});
					jQuery(".shmac-reset_<?php echo $calc_inc ?>").on("click",function(){
						amount_slider_<?php echo $calc_inc ?>.noUiSlider.reset();
						term_slider_<?php echo $calc_inc ?>.noUiSlider.reset();
						downpay_slider_<?php echo $calc_inc ?>.noUiSlider.reset();
						interest_slider_<?php echo $calc_inc ?>.noUiSlider.reset();						
					});
					
					
				});
				</script>
				<?php
				
				$sliderScript = ob_get_clean();
				$output = $output. $sliderScript;
			}
            return $output;
        }

	} // end class
