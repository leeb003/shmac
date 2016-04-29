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

			// mui
            //wp_register_style( 'mui', SHMAC_ROOT_URL . '/assets/css/mui.css', array(), '0.1.22-rc1' );
            //wp_enqueue_style('mui');

			// main css
            wp_register_style( 'shmac-frontend', SHMAC_ROOT_URL . '/assets/css/frontend.css',
            array(), SHMAC_PLUGIN_VERSION );
            wp_enqueue_style( 'shmac-frontend' );

			// Scrollbar
			wp_register_style( 'shmac-custom-scrollbar', SHMAC_ROOT_URL .  '/assets/css/jquery.mCustomScrollbar.min.css',
			array('shmac-frontend'), '3.0.9');
			wp_enqueue_style( 'shmac-custom-scrollbar' );

			// mprogress
			wp_register_style( 'mprogress', SHMAC_ROOT_URL .  '/assets/css/mprogress.min.css',
            array('shmac-frontend'), '1.0');
            wp_enqueue_style( 'mprogress' );

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
        public function enqueue_scripts () {
			// autoNumeric
			wp_register_script('autoNumeric', SHMAC_ROOT_URL . '/assets/js/autoNumeric.js', array('jquery'), '1.9.26', true);
			wp_enqueue_script('autoNumeric');
			// Mui
			wp_register_script( 'mui', SHMAC_ROOT_URL . '/assets/js/mui.js', array(), '0.1.22-rc1', true );
            wp_enqueue_script('mui');
			// Scrollbar
			wp_register_script( 'shmac-custom-scrollbar', SHMAC_ROOT_URL 
				. '/assets/js/jquery.mCustomScrollbar.concat.min.js', array('jquery'), '3.0.9', true);
			wp_enqueue_script( 'shmac-custom-scrollbar' );
			// mprogress
			wp_register_script( 'mprogress', SHMAC_ROOT_URL . '/assets/js/mprogress.min.js', array('jquery'), '1.0', true);
			wp_enqueue_script( 'mprogress' );
			// Plugin js    
            wp_register_script( 'shmac-frontend-ajax',
                SHMAC_ROOT_URL . '/assets/js/frontend-ajax.js',
                array('jquery'), SHMAC_PLUGIN_VERSION, true );
			wp_enqueue_script( 'shmac-frontend-ajax' );
            wp_localize_script(  'shmac-frontend-ajax', 'SHMAC_Ajax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nextNonce' => wp_create_nonce( 'myajax-next-nonce' ),
				'shmacColor' => isset($this->first_tab['page_color'])?$this->first_tab['page_color']:'#03a9f4'
            ));

        } // End enqueue_scripts 

		
		/**
         * Shortcode generation
         * @since 1.0.0
         * @access public
         * @return $output
         */
        public function shmac_calc($atts, $content=NULL) {
			global $calc_inc; // tracking multiple calculators
			$calc_inc++;
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
            if ($defaultpurchase == '') {
                $defaultpurchase = $this->shmac_settings['purchase_price'];
            }
            if ($interestlabel == '') {
                $interestlabel = $this->shmac_settings['interest_rate_label'];
            }
            if ($interestinfo == '') {
                $interestinfo = $this->shmac_settings['interest_rate_info'];
            }
            if ($defaultinterest == '') {
                $defaultinterest = $this->shmac_settings['interest_rate'];
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
            if ($defaultdown == '') {
                $defaultdown = $this->shmac_settings['down_payment'];
            }
            if ($termlabel == '') {
                $termlabel = $this->shmac_settings['loan_term_label'];
            }
            if ($terminfo == '') {
                $terminfo = $this->shmac_settings['loan_term_info'];
            }
            if ($defaultterm == '') {
                $defaultterm =$this->shmac_settings['loan_term'];
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
			$downpay_format = 'data-v-min="0.00" data-v-max="100.000" data-a-pad="false"';
			$downpay_symbol = '';
			$symbol_class = '';
			if ($downpaytype == 'amount') {
				$downpay_format = $money_format;
				$downpay_symbol = '<div class="shmac-symbol ' . $symbol_side . '">' . $currencysymbol . '</div>';
				$symbol_class = 'input-symbol';
			}

			$output .= <<<EOT
			<div class="mui-form-group shmac-amount">
				<a href="#" class="shmac-tip" title=" " data-title="$amountinfo">
                	<span>
                    	<img src="$info_src" class="shmac-info" />
                	</span>
            	</a>
				<div class="shmac-symbol $symbol_side">$currencysymbol</div>
          		<input type="text" class="mort-amount mui-form-control $input_no_pad" value="$defaultpurchase" 
					$money_format /> 
				<label class="mui-form-floating-label">$amountlabel</label>
				<div class="err-msg"></div>
			</div>

			<div class="mui-form-group shmac-interest">
				 <a href="#" class="shmac-tip" title=" " data-title="$interestinfo">
                    <span>
                        <img src="$info_src" class="shmac-info" />
                    </span>
                </a>
				<input type="text" class="interest mui-form-control" value="$defaultinterest" 
					data-v-min="0.00" data-v-max="100.000" data-a-pad="false" />
				<label class="mui-form-floating-label">$interestlabel</label>
				<div class="err-msg"></div>
			</div>

			<div class="mui-form-group shmac-down">
				 <a href="#" class="shmac-tip" title=" " data-title="$downpayinfo">
                    <span>
                        <img src="$info_src" class="shmac-info" />
                    </span>
                </a>
				$downpay_symbol
				<input type="text" class="downpay $symbol_class mui-form-control $input_no_pad" value="$defaultdown"
					$downpay_format />
				<label class="mui-form-floating-label">$downpaylabel</label>
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
                <input type="text" class="term mui-form-control" value="$defaultterm"
                    data-v-min="0" data-v-max="600" data-a-pad="false" />
                <label class="mui-form-floating-label">$termlabel</label>
				<div class="err-msg"></div>
            </div>
			<div class="progresso"> &nbsp;</div>
        	<div class="buttonRow">
         		<button class="mui-btn submit-shmac" data-mui-color="{$this->shmac_settings['page_color']}">$btn_calc</button>
        	 	<button class="mui-btn shmac-reset" type="reset">$btn_reset</button>
			</div>
				
    </form>
  </div>
</div><!-- End shmac-holder -->
EOT;

            return $output;
        }

	} // end class
