<?php
/*
 * The main plugin class, holds everything our plugin does,
 * initialized right after declaration
 */
class SHMAC_API_Tabs {
    
    /*
     * For easier overriding we declared the keys
     * here as well as our tabs array which is populated
     * when registering settings
     */
    private $first_tab_key = 'shmac_settings';
    private $second_tab_key = 'shmac_email';
	private $info_tab_key = 'shmac_info';
    private $plugin_options_key = 'shmac_options';
    private $plugin_settings_tabs = array();

    private $show_contact_upload = false;
    
    /*
     * Fired during plugins_loaded (very very early),
     * so don't miss-use this, only actions and filters,
     * current ones speak for themselves.
     */
    function __construct() {
        add_action( 'init', array( &$this, 'load_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_first_tab' ) );
        add_action( 'admin_init', array( &$this, 'register_second_tab' ) );
		add_action( 'admin_init', array( &$this, 'register_info_tab' ) );
        add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );

        // Callback Ajax Backend ajax
        require_once(SHMAC_ROOT_PATH . '/includes/class-shmac-ajax.php');
        $shmac_ajax = new shmac_ajax();
        add_action( 'wp_ajax_ajax-shmacbackend', array($shmac_ajax, 'myajax_shmacbackend_callback'));
    }
    
    /*
     * Loads both the general and advanced settings from
     * the database into their respective arrays. Uses
     * array_merge to merge with default values if they're
     * missing.
     */
    function load_settings() {
        $this->first_tab = (array) get_option( $this->first_tab_key );
        $this->second_tab = (array) get_option( $this->second_tab_key );
		$this->info_tab = (array) get_option( $this->info_tab_key );
        
        // Merge with defaults
        $this->first_tab = array_merge( array(
            'custom_css'               => '',
			'custom_js'                => '',
            'page_color'               => '#00bfa5',
			'location'                 => 'modal',
			'calcsubmit'               => 'Submit',
			'calcreset'                => 'Reset',
            'calc_title'               => 'Amortization Calculator',
            'send_email_text'          => 'Send A PDF report to your email?',
            'email_placeholder'        => 'Your Email',
			'email_error'              => 'Please enter a valid email address.',
            'enable_email'             =>'no',
            'enable_slider'            =>'no',
            'enable_input_readonly'    =>'no',
            'slider_theme'             =>'broad',
            'purchase_price_label'     => 'Mortgage Amount',
			'purchase_price_error'     => 'Please Enter a valid amount.',
            'purchase_price_info'      => 'The total purchase price of the home you wish to buy.',
            'purchase_price'           => '224,000.00',
            'amount_min_value'         =>'0',
            'amount_max_value'         =>'224000',
            'amount_slider_step'       =>'500',
            'interest_rate_label'      => 'Interest Rate (%)',
			'interest_error'           => 'Please Enter a valid interest rate.',
            'interest_rate_info'       => 'The expected percent interest rate you will get on your mortgage.',
            'interest_rate'            => '5.50',
            'interest_min_value'       =>'0',
            'interest_max_value'       =>'30',
            'interest_slider_step'     =>'0.1',
            'down_payment_label'       => 'Down Payment (%)',
			'down_error'			   => 'Please Enter a down payment (can be 0).',
            'down_payment_info'        => 'The percent down payment you wish to put towards the home.',
            'down_payment_type'        => 'percent',
            'down_payment'             => '10.00',
            'down_payment_show'        => 'yes',
            'dwnpay_min_value'         =>'0',
            'dwnpay_max_value'         =>'99',
            'dwnpay_slider_step'       =>'1',
            'loan_term_label'          => 'Term',
			'loan_term_error'          => 'Term cannot be empty.',
			'year_label'               => 'Years',
			'month_label'              => 'Months',
            'loan_term_info'           => 'The length of time it will take to repay the loan amount (30 years is common).',
            'loan_term'                => '30',
            'term_min_value'           =>'1',
            'term_max_value'           =>'30',
            'term_slider_step'         =>'1',
            'term_type'                =>'1',
            'enable_insurance'         => 'yes',
            'insurance_amount_percent' => 'amount',
            'insurance'                => '56.00',
            'enable_pmi'               => 'yes',
            'pmi'                      => '55.00',
            'enable_taxes'             => 'yes',
            'tax_rate'                 => '10.00',
            'currency'                 => '$',
            'currency_format'          => '1',
            'currency_side'            => 'left',          
			'bg_attachment_url'        => SHMAC_ROOT_URL . '/assets/img/wpcontacts.png',
            'bg_attachment_id'         => '',
			'bg_color'                 => '#fff',
            
        ), $this->first_tab );

        $this->second_tab = array_merge( array(
			'detail_original'            => 'Original Loan Amount',
			'detail_down_payment'        => 'Down Payment',
			'detail_interest'            => 'Interest',
			'detail_term'                => 'Term',
			'detail_loan_after_down'     => 'Loan After Down Payment',
			'detail_down_payment_amount' => 'Down Payment Amount',
			'detail_monthly_payment'     => 'Monthly Payment (P & I)',
			'detail_total_payments'      => 'Total Payments',

			'header_payment'        => 'Payment',
			'header_payment_amount' => 'Payment Amt',
			'header_interest'       => 'Interest',
			'header_total_interest' => 'Total Interest',
			'header_principal'      => 'Principal',
			'header_balance'        => 'Balance',
			'header_loan_text'      => 'Loan Details',
			'header_schedule_text'  => 'Amortization Schedule (P & I)',
			
			'otherfactors' => 'Since Principal and Interest are not the only factors of a loan we should include an estimate for other costs involved with a loan.',
			'down_factor_1' => 'Your down payment was less than 20% of the loan, which means you will be paying PMI. This averages around [pmi_display] for every [hundredthou] borrowed. An estimate for PMI will be around [pmi] per month.',
			'down_factor_2' => 'Since you are putting down 20% or more of the loan, you will not have to pay PMI.',
			'tax_factor' => "An average tax figure for your purchase might be about [tax_rate_display] for every [thousand_dollars_display] assessed value per year. If the assessed value of your home is 85%, this would make your home's assessed value [assessed_display] and your monthly tax around [taxes_display].",
			'insurance_factor' => 'Homeowners Insurance is another factor of a loan.  An average estimate of your monthly insurance could be about [insurance_display].',
			'factor_summary' => 'With these factors, your total monthly payment estimate would be around [total_payment_display].',
			'disclaimer' => 'Calculations by this calculator are estimates only. There is no warranty for the accuracy of the results or the relationship to your financial situation.',
			
            'allow_email'         => 'no',
            'email_bcc'           => '',
            'email_from'          => '',
            'email_subject'       => 'Amortization calculator results from us',
            'email_text'          => 'Attached are your custom results from our Calculator.  Feel free to contact us with any questions!',
            'logo_attachment_url' => SHMAC_ROOT_URL . '/assets/img/wpcontacts.png',
            'logo_attachment_id'  => '',
            'pdf_color'           => '#00bfa5',
			'brought_by'		  => 'Brought to you by',
            'pdf_header'          => 'Amortization Calculator Results',
            'ltr_rtl'             => 'ltr',
            'pdf_font'            => 'helvetica',
        ), $this->second_tab );

		$this->info_tab = array();

    }
    
    /*
     * Registers the main settings via the Settings API,
     * appends the setting to the tabs array of the object.
     */
    function register_first_tab() {
        $this->plugin_settings_tabs[$this->first_tab_key] = __('Calculator Global Settings', 'shmac');
        
        register_setting( $this->first_tab_key, $this->first_tab_key );
        add_settings_section( 'section_general', __('Calculator Global Settings', 'shmac'), array( &$this, 'section_general_desc' ), 
                $this->first_tab_key, 'section_general' );

        add_settings_field( 'calc_title', __('Calculator Title', 'shmac'), array( &$this, 'field_calc_title'),
                $this->first_tab_key, 'section_general' );

        add_settings_field( 'send_email_text', __('Email Text', 'shmac'), array( &$this, 'field_send_email_text'),
                $this->first_tab_key, 'section_general' );

        add_settings_field( 'email_placeholder', __('Email Label', 'shmac'), array( &$this, 'field_email_placeholder'),
                $this->first_tab_key, 'section_general' );

		add_settings_field( 'email_error', __('Email Error Msg', 'shmac'), array( &$this, 'field_email_error'),
                $this->first_tab_key, 'section_general' );	
        
        add_settings_field( 'enable_email', __('Require Email Entry', 'shmac'), array( &$this, 'field_enable_email' ),          $this->first_tab_key, 'section_general' );
        //Slider Settings
        add_settings_field( 'enable_slider', __('Enable Slider', 'shmac'), array( &$this, 'field_enable_slider' ),          $this->first_tab_key, 'section_general' );
        
        add_settings_field( 'enable_input_readonly', __('Slider Inputs Readonly', 'shmac'), array( &$this, 'field_enable_input_readonly' ),  $this->first_tab_key, 'section_general' );

        add_settings_field( 'slider_theme', __('Choose Slider Theme', 'shmac'), array( &$this, 'field_slider_theme' ),  $this->first_tab_key, 'section_general' );
        
        //Amount 
        add_settings_field( 'purchase_price_label', __('Amount Label', 'shmac'), array( &$this, 'field_purchase_price_label'),
                $this->first_tab_key, 'section_general' );

		add_settings_field( 'purchase_price_error', __('Amount Error Msg', 'shmac'), array( &$this, 'field_purchase_price_error'),
                $this->first_tab_key, 'section_general' );

        add_settings_field( 'purchase_price_info', __('Amount Info Bubble', 'shmac'), array( &$this, 'field_purchase_price_info'), $this->first_tab_key, 'section_general' );

        add_settings_field( 'purchase_price', __('Default Purchase Price', 'shmac'), array( &$this, 'field_purchase_price'),
                $this->first_tab_key, 'section_general' );      

        add_settings_field( 'amount_min_value', __('Minimum Purchase Price', 'shmac'), array( &$this, 'field_amount_min_value' ),   $this->first_tab_key, 'section_general' );

        add_settings_field( 'amount_max_value', __('Maximum Purchase Price', 'shmac'), array( &$this, 'field_amount_max_value' ),   $this->first_tab_key, 'section_general' );

        add_settings_field( 'amount_slider_step', __('Steps in Purchase Slider', 'shmac'), array( &$this, 'field_amount_slider_step' ), $this->first_tab_key, 'section_general' );

        //Interest Rate     
        add_settings_field( 'interest_rate_label', __('Interest Label', 'shmac'), array( &$this, 'field_interest_rate_label'),
                $this->first_tab_key, 'section_general' );

		add_settings_field( 'interest_error', __('Interest Error Msg', 'shmac'), array( &$this, 'field_interest_error'),
                $this->first_tab_key, 'section_general' );

        add_settings_field( 'interest_rate_info', __('Interest Info Bubble', 'shmac'), array( &$this, 'field_interest_rate_info'),
                $this->first_tab_key, 'section_general' );

        add_settings_field( 'interest_rate', __('Default Interest Rate (Percent)', 'shmac'), array( &$this, 'field_interest_rate'), $this->first_tab_key, 'section_general' );
        add_settings_field( 'interest_min_value', __('Minimum Interest Rate', 'shmac'), array( &$this, 'field_interest_min_value' ),    $this->first_tab_key, 'section_general' );

        add_settings_field( 'interest_max_value', __('Maximum Interest Rate', 'shmac'), array( &$this, 'field_interest_max_value' ),    $this->first_tab_key, 'section_general' );

        add_settings_field( 'interest_slider_step', __('Steps in Interest Rate', 'shmac'), array( &$this, 'field_interest_slider_step' ),   $this->first_tab_key, 'section_general' );

        //~ add_settings_field( 'interest_slider_start', __('Start Interest Rate in Slider', 'shmac'), array( &$this, 'field_interest_slider_start' ),  $this->first_tab_key, 'section_general' );

        //Down Payment 
        add_settings_field( 'down_payment_show', __('Show Down Payment', 'shmac'), array( &$this, 'field_down_payment_show'), $this->first_tab_key, 'section_general' );
        
        add_settings_field( 'down_payment_label', __('Down Payment Label', 'shmac'), array( &$this, 'field_down_payment_label'), $this->first_tab_key, 'section_general' );

		add_settings_field( 'down_error', __('Down Payment Error Msg', 'shmac'), array( &$this, 'field_down_error'), $this->first_tab_key, 'section_general' );

        add_settings_field( 'down_payment_info', __('Down Payment Info Bubble', 'shmac'), array( &$this, 'field_down_payment_info'), $this->first_tab_key, 'section_general' );

        add_settings_field( 'down_payment_type', __('Down Payment Type', 'shmac'), array( &$this, 'field_down_payment_type'),
                $this->first_tab_key, 'section_general' );
    
        add_settings_field( 'down_payment', __('Default Down Payment', 'shmac'), array( &$this, 'field_down_payment'),
                $this->first_tab_key, 'section_general' );
        
        add_settings_field( 'dwnpay_min_value', __('Minimum Down Payment', 'shmac'), array( &$this, 'field_dwnpay_min_value' ), $this->first_tab_key, 'section_general' );

        add_settings_field( 'dwnpay_max_value', __('Maximum Down Payment', 'shmac'), array( &$this, 'field_dwnpay_max_value' ), $this->first_tab_key, 'section_general' );

        add_settings_field( 'dwnpay_slider_step', __('Steps in Down Payment', 'shmac'), array( &$this, 'field_dwnpay_slider_step' ),    $this->first_tab_key, 'section_general' );

        //~ add_settings_field( 'dwnpay_slider_start', __('Start Down Payment in Slider', 'shmac'), array( &$this, 'field_dwnpay_slider_start' ),   $this->first_tab_key, 'section_general' );

        //Term
        add_settings_field( 'loan_term_label', __('Loan Term Label', 'shmac'), array( &$this, 'field_loan_term_label'),
                $this->first_tab_key, 'section_general' );

		add_settings_field( 'loan_term_error', __('Loan Term Error Msg', 'shmac'), array( &$this, 'field_loan_term_error'),
                $this->first_tab_key, 'section_general' );

        add_settings_field( 'loan_term_info', __('Loan Term Info Bubble', 'shmac'), array( &$this, 'field_loan_term_info'),
                $this->first_tab_key, 'section_general' );

        add_settings_field( 'loan_term', __('Default Loan Term (Years)', 'shmac'), array( &$this, 'field_loan_term'),
                $this->first_tab_key, 'section_general' );  
        
        add_settings_field( 'term_min_value', __('Minimum Term', 'shmac'), array( &$this, 'field_term_min_value' ), $this->first_tab_key, 'section_general' );

        add_settings_field( 'term_max_value', __('Maximum Term', 'shmac'), array( &$this, 'field_term_max_value' ), $this->first_tab_key, 'section_general' );

        add_settings_field( 'term_slider_step', __('Steps in Term', 'shmac'), array( &$this, 'field_term_slider_step' ),    $this->first_tab_key, 'section_general' );
        
        add_settings_field( 'term_type', __('Term Type', 'shmac'), array( &$this, 'field_term_type' ),  $this->first_tab_key, 'section_general' );
		add_settings_field( 'year_label', __('Year Label', 'shmac'), array( &$this, 'field_year_label' ),  $this->first_tab_key, 'section_general' );
		add_settings_field( 'month_label', __('Month Label', 'shmac'), array( &$this, 'field_month_label' ),  $this->first_tab_key, 'section_general' );

        add_settings_field( 'enable_insurance', __('Insurance Cost Estimate', 'shmac'), 
                array( &$this, 'field_enable_insurance'), $this->first_tab_key, 'section_general' );
        add_settings_field( 'insurance_amount_percent', __('Is insurance a set amount or a percent of loan per month?', 'shmac'),
                array( &$this, 'field_insurance_amount_percent'), $this->first_tab_key, 'section_general' );
        add_settings_field( 'insurance', __('Monthly Insurance Cost Estimate', 'shmac'), array( &$this, 'field_insurance'),
                $this->first_tab_key, 'section_general' );
        add_settings_field( 'enable_pmi', __('Enable PMI Cost Estimate', 'shmac'), array( &$this, 'field_enable_pmi'),
                $this->first_tab_key, 'section_general' );
        add_settings_field( 'pmi', __('Monthly PMI Cost Estimate', 'shmac'), array( &$this, 'field_pmi'),
                $this->first_tab_key, 'section_general' );
        add_settings_field( 'enable_taxes', __('Enable Tax Cost Estimate', 'shmac'), array( &$this, 'field_enable_taxes'),
                $this->first_tab_key, 'section_general' );
        add_settings_field( 'tax_rate', __('Taxes Per 1000 Assessed', 'shmac'), array( &$this, 'field_tax_rate'),
                $this->first_tab_key, 'section_general' );
        add_settings_field( 'currency', __('Currency Symbol', 'shmac'), array( &$this, 'field_currency'),
                $this->first_tab_key, 'section_general' );
        add_settings_field( 'currency_format', __('Currency Format', 'shmac'), array( &$this, 'field_currency_format'),
                $this->first_tab_key, 'section_general' );
        add_settings_field( 'currency_side', __('Currency Symbol Side', 'shmac'), array( &$this, 'field_currency_side'),
                $this->first_tab_key, 'section_general' );

        add_settings_field( 'page_color', __('Primary Color', 'shmac'), array( &$this, 'field_color_option' ), 
                $this->first_tab_key, 'section_general' );
		add_settings_field( 'bg_color', __('Background Color', 'shmac'), array( &$this, 'field_bg_option' ),
				$this->first_tab_key, 'section_general' );
		add_settings_field( 'background_img_url', __('Calculator Background Image', 'shmac'), array( &$this, 'field_bg_image' ),
            	$this->first_tab_key, 'section_general' );
		add_settings_field( 'location', __('Output Location', 'shmac'), array( &$this, 'field_location' ),
                $this->first_tab_key, 'section_general' );
		add_settings_field( 'buttons', __('Submit and Reset Text', 'shmac'), array( &$this, 'field_buttons' ),
				$this->first_tab_key, 'section_general' );
        add_settings_field( 'custom_code', __('Custom Code', 'shmac'), array( &$this, 'field_custom_code' ),
                $this->first_tab_key, 'section_general' );
                
    }
    
    /*
     * Register second tab info
     *
     */
    function register_second_tab() {
        $this->plugin_settings_tabs[$this->second_tab_key] = __('Report & Email Settings', 'shmac');

        register_setting( $this->second_tab_key, $this->second_tab_key );

		add_settings_section( 'section_email', __('Report & Email Settings', 'shmac'), array( &$this, 'section_email_desc' ),
                $this->second_tab_key, 'section_email_desc' );

		add_settings_field( 'loan_details', __('Report - Loan Details', 'shmac'), array( &$this, 'section_loan_details' ),
                $this->second_tab_key, 'section_email' );

		add_settings_field( 'schedule_headers', __('Report - Schedule Headers', 'shmac'), array( &$this, 'section_schedule_headers' ),
                $this->second_tab_key, 'section_email' );	

		add_settings_field( 'report_texts', __('Report - Text Section', 'shmac'), array( &$this, 'field_report_texts'),
                $this->second_tab_key, 'section_email' );

		add_settings_field( 'down_factor', __('Report - Down Payment Factors', 'shmac'), array( &$this, 'field_down_factor'),
                $this->second_tab_key, 'section_email' );

		add_settings_field( 'tax_factor', __('Report - Tax Factors', 'shmac'), array( &$this, 'field_tax_factor'),
                $this->second_tab_key, 'section_email' );

		add_settings_field( 'insurance_factor', __('Report - Insurance Factors', 'shmac'), array( &$this, 'field_insurance_factor'),
                $this->second_tab_key, 'section_email' );

		add_settings_field( 'factor_summary', __('Report - Factors Summary', 'shmac'), array( &$this, 'field_factor_summary'),
                $this->second_tab_key, 'section_email' );

		add_settings_field( 'disclaimer', __('Disclaimer Notice', 'shmac'), array( &$this, 'field_disclaimer'),
                $this->second_tab_key, 'section_email' );

        add_settings_field( 'allow_email', __('Allow Email Report', 'shmac'), array( &$this, 'field_allow_email'),
                $this->second_tab_key, 'section_email' );
        add_settings_field( 'email_bcc', __('BCC Email Address', 'shmac'), array( &$this, 'field_email_bcc'),
                $this->second_tab_key, 'section_email' );
        add_settings_field( 'email_from', __('From Email Address', 'shmac'), array( &$this, 'field_email_from'),
                $this->second_tab_key, 'section_email' );
        add_settings_field( 'email_subject', __('Custom Email Subject', 'shmac'), array( &$this, 'field_email_subject'),
                $this->second_tab_key, 'section_email' );
        add_settings_field( 'email_text', __('Custom Email Text', 'shmac'), array( &$this, 'field_email_text'),
                $this->second_tab_key, 'section_email' );

        add_settings_field( 'pdf_color', __('Primary PDF Color', 'shmac'), array( &$this, 'field_pdf_color'),
                $this->second_tab_key, 'section_email' );

		add_settings_field( 'brought_by', __('PDF Intro Text', 'shmac'), array( &$this, 'field_brought_by'),
                $this->second_tab_key, 'section_email' );

        add_settings_field( 'logo_attachment_url', __('PDF Logo or Header Image (jpeg or png)', 'shmac'), array( &$this, 'field_logo_image' ),
            $this->second_tab_key, 'section_email' );
        add_settings_field( 'pdf_header', __('PDF Header Large Text', 'shmac'), array( &$this, 'field_pdf_header' ),
            $this->second_tab_key, 'section_email' );
        add_settings_field( 'pdf_font', __('PDF Font to use', 'shmac'), array( &$this, 'field_pdf_font' ),
                             $this->second_tab_key, 'section_email' );
		add_settings_field( 'ltr_rtl', __('Enable RTL', 'shmac'), array( &$this, 'field_ltr_rtl' ),
                             $this->second_tab_key, 'section_email' );
    }

	/*
	 * Informational tab on the plugin
	 *
	 */
	 function register_info_tab() {
		$this->plugin_settings_tabs[$this->info_tab_key] = __('Information', 'shmac');
		$banner_img = "<img src='" . SHMAC_ROOT_URL . '/assets/img/logo-350.png' . "' /><br /><br />";

        register_setting( $this->info_tab_key, $this->info_tab_key );
        add_settings_section( 'section_info', $banner_img . __('Information', 'shmac'), array( &$this, 'section_site_desc' ),
                $this->info_tab_key, 'section_info_desc' );
        add_settings_field( 'site_info', __('Info', 'shmac'), array( &$this, 'field_section_info'),
                $this->info_tab_key, 'section_info' );	
    
	}

    /*
     * The following methods provide descriptions
     * for their respective sections, used as callbacks
     * with add_settings_section
     */
    function section_general_desc() { echo __('Set up WP Amortization Calculator settings.  In this section you can change field labels, info bubble contents, default values, form color, and other settings.  Be sure to save settings on This tab and Email before using the calculator.', 'shmac'); }
    function section_email_desc() { echo __('In this section you can change report settings, enable and disable email reports for visitors and send yourself a copy (bcc) of the reports as well.  This is good for lead capture and further contacting the lead.  Set your logo and colors used in the PDF report.', 'shmac');
    }

	function section_site_desc() { 
    echo '<br /><br /><u>' . __('You are running version', 'shmac') . ' <span class="shmac-version">' . SHMAC_PLUGIN_VERSION . '</span> ' . __('of WP Amortization Calculator', 'shmac') . '</u><br /><p> You can use the <b>[shmac_calc_sc]</b> shortcode, the widget, Visual Composer or Elementor to display the calculator on the front end.</p>

<p>' . __('Have a question? Take a look at our', 'shmac') . ' <a href="https://www.scripthat.com/support/" target="_blank">' . __('Online Documentation', 'shmac') . '</a></p>';

    echo '<p>' . __('Need some help? Visit our', 'shmac') . ' <a href="https://scripthat.ticksy.com" target=_blank">' . __('Support System', 'shmac') . '</a></p>
';

    echo '<p>' . __('If this calculator is a great tool for you we would really appreciate a quick rating.  Ratings help our product grow and encourage us to improve and add features.', 'shmac') . '<br />' . __('You can give us a rating in your ', 'shmac') . ' <a href="https://codecanyon.net/downloads" target=_blank">' . __(
'downloads area at codecanyon.net', 'shmac') . '</a></p>';

    }

    /*
     * Main Settings field callback, front page accessibility
     */

    /* New Fields */
    // Calculator Title
    function field_calc_title() {
        ?>
        <input class="shmac-calc-title" name="<?php echo $this->first_tab_key; ?>[calc_title]" value="<?php echo esc_attr( $this->first_tab['calc_title'] ); ?>" />
        <p><?php echo __("Set the title to display for the Calculator", "shmac"); ?></p>
        <?php
    }

    // Send Email Text
    function field_send_email_text() {
        ?>
		<hr />
        <input class="shmac-email-text" name="<?php echo $this->first_tab_key; ?>[send_email_text]" value="<?php echo esc_attr( $this->first_tab['send_email_text'] ); ?>" />
        <p><?php echo __("Set the description for sending an email with PDF report.", "shmac"); ?></p>
        <?php
    }

    // Email Placeholder Text
    function field_email_placeholder() {
        ?>
         <input class="shmac-email-placeholder" name="<?php echo $this->first_tab_key; ?>[email_placeholder]" value="<?php echo esc_attr( $this->first_tab['email_placeholder'] ); ?>" />
        <p><?php echo __("Set the placeholder text for the email field.", "shmac"); ?></p>
        <?php
    }

	// Email Error Message
	function field_email_error() {
        ?>
         <input class="shmac-email-error" name="<?php echo $this->first_tab_key; ?>[email_error]" value="<?php echo esc_attr( $this->first_tab['email_error'] ); ?>" />
        <p><?php echo __("Set the error text for invalid or no email entered.", "shmac"); ?></p>
        <?php
    }

    // Purchase Price Label
    function field_purchase_price_label() {
        ?>
		<hr />
        <input class="shmac-purchase-label" name="<?php echo $this->first_tab_key; ?>[purchase_price_label]" value="<?php echo esc_attr( $this->first_tab['purchase_price_label'] ); ?>" />
        <p><?php echo __("Set the text for the Amount Field.", "shmac"); ?></p>
        <?php
    }

	// Purchase Price Error message
	function field_purchase_price_error() {
        ?>
        <input class="shmac-purchase-error" name="<?php echo $this->first_tab_key; ?>[purchase_price_error]" value="<?php echo esc_attr( $this->first_tab['purchase_price_error'] ); ?>" />
        <p><?php echo __("Set the Error message for the Amount Field.", "shmac"); ?></p>
        <?php
    }

    // Purchase Price bubble
    function field_purchase_price_info() {
        ?>
        <textarea class="shmac-purchase-info" name="<?php echo $this->first_tab_key; ?>[purchase_price_info]"><?php echo esc_attr( $this->first_tab['purchase_price_info'] ); ?></textarea>
        <p><?php echo __("Set the information bubble text for the Amount Field.", "shmac"); ?></p>
        <?php
    }

    // Purchase Price
    function field_purchase_price() {
        ?>
        <input class="shmac-purchase-price" name="<?php echo $this->first_tab_key; ?>[purchase_price]" value="<?php echo esc_attr( $this->first_tab['purchase_price'] ); ?>" />
        <p><?php echo __("Set a default purchase price for the calculator", "shmac"); ?></p>
        <?php
    }

    // Interest Rate Label
    function field_interest_rate_label() {
        ?>
		<hr />
        <input class="shmac-interest-label" name="<?php echo $this->first_tab_key; ?>[interest_rate_label]" value="<?php echo esc_attr( $this->first_tab['interest_rate_label'] ); ?>" />
        <p><?php echo __("Set the text for the Interest Rate Field.", "shmac"); ?></p>
        <?php
    }

	// Interest Error Message
	function field_interest_error() {
        ?>
        <input class="shmac-interest-error" name="<?php echo $this->first_tab_key; ?>[interest_error]" value="<?php echo esc_attr( $this->first_tab['interest_error'] ); ?>" />
        <p><?php echo __("Set the Error message for the Interest field.", "shmac"); ?></p>
        <?php
    }

    // Interest Rate bubble
    function field_interest_rate_info() {
        ?>
        <textarea class="shmac-interest-info" name="<?php echo $this->first_tab_key; ?>[interest_rate_info]"><?php echo esc_attr( $this->first_tab['interest_rate_info'] ); ?></textarea>
        <p><?php echo __("Set the information bubble text for the Interest Rate Field.", "shmac"); ?></p>
        <?php
    }

    function field_interest_rate() {
        ?>
        <input class="shmac-interest-rate" name="<?php echo $this->first_tab_key; ?>[interest_rate]" value="<?php echo esc_attr( $this->first_tab['interest_rate'] ); ?>" />
        <p><?php echo __("Set the default interest rate for the calculator", "shmac"); ?></p>
        <?php
    }

    // Down Payment Label
    function field_down_payment_label() {
        ?>
         <input class="shmac-down-label" name="<?php echo $this->first_tab_key; ?>[down_payment_label]" value="<?php echo esc_attr( $this->first_tab['down_payment_label'] ); ?>" />
        <p><?php echo __("Set the text for the Down Payment Field.", "shmac"); ?></p>
        <?php
    }

	// Down Payment Error Message
	function field_down_error() {
        ?>
         <input class="shmac-down-error" name="<?php echo $this->first_tab_key; ?>[down_error]" value="<?php echo esc_attr( $this->first_tab['down_error'] ); ?>" />
        <p><?php echo __("Set the Error message for the Down Payment field.", "shmac"); ?></p>
        <?php
    }

    // Down Payment bubble
    function field_down_payment_info() {
        ?>
        <textarea class="shmac-down-info" name="<?php echo $this->first_tab_key; ?>[down_payment_info]"><?php echo esc_attr( $this->first_tab['down_payment_info'] ); ?></textarea>
        <p><?php echo __("Set the information bubble text for the Down Payment Field.", "shmac"); ?></p>
        <?php
    }

    // Down Payment Type (percent or amount)
    function field_down_payment_type() {
        ?>
         <select class="shmac-down-type" name="<?php echo $this->first_tab_key; ?>[down_payment_type]">
            <option value="percent" <?php selected( $this->first_tab['down_payment_type'], "percent");?>
                    ><?php echo __("Percent", "shmac");?></option>
            <option value="amount" <?php selected( $this->first_tab['down_payment_type'], "amount");?>
                    ><?php echo __("Amount", "shmac");?></option>
        </select>
        <p><?php echo __("Choose whether to use percent or amount input for the down payment", "shmac"); ?></p>
        <?php
    }
    
    // Show Down Payment
    function field_down_payment_show() {
        ?>
		<hr />
		<!-- Rounded switch -->
        <input type="hidden" class="shmac-down-alt" name="<?php echo $this->first_tab_key; ?>[down_payment_show]" value="no">
        <label class="switch">
        <input type="checkbox" class="shmac-down-show" name="<?php echo $this->first_tab_key; ?>[down_payment_show]" value="yes"
            <?php checked($this->first_tab['down_payment_show'], 'yes');?>>
        <span class="slider round"></span>
        </label>
        <p><?php echo __("Choose whether to show down payment or not", "shmac"); ?>.</p>
        <?php
    }

    // Down Payment
    function field_down_payment() {
        ?>
        <input class="shmac-down_payment" name="<?php echo $this->first_tab_key; ?>[down_payment]" value="<?php echo esc_attr( $this->first_tab['down_payment'] ); ?>" />
        <p><?php echo __("Set the default down payment amount", "shmac"); ?></p>
        <?php
    }

    // Loan Term Label
    function field_loan_term_label() {
        ?>
		<hr />
        <input class="shmac-term-label" name="<?php echo $this->first_tab_key; ?>[loan_term_label]" value="<?php echo esc_attr( $this->first_tab['loan_term_label'] ); ?>" />
        <p><?php echo __("Set the text for the Loan Term Field.", "shmac"); ?></p>
        <?php
    }

	// Term Error Message
	function field_loan_term_error() {
        ?>
        <input class="shmac-term-error" name="<?php echo $this->first_tab_key; ?>[loan_term_error]" value="<?php echo esc_attr( $this->first_tab['loan_term_error']
); ?>" />
        <p><?php echo __("Set the Error message for the Loan Term field.", "shmac"); ?></p>
        <?php
    }

	// Term Year label
    function field_year_label() {
        ?>
        <input class="shmac-year-label" name="<?php echo $this->first_tab_key; ?>[year_label]" value="<?php echo esc_attr( $this->first_tab['year_label']
); ?>" />
        <p><?php echo __("The text for years.", "shmac"); ?></p>
        <?php
    }

	// Term Month label
    function field_month_label() {
        ?>
        <input class="shmac-month-label" name="<?php echo $this->first_tab_key; ?>[month_label]" value="<?php echo esc_attr( $this->first_tab['month_label']
); ?>" />
        <p><?php echo __("The text for months.", "shmac"); ?></p>
        <?php
    }

    // Loan Term Bubble
    function field_loan_term_info() {
        ?>
        <textarea class="shmac-term-info" name="<?php echo $this->first_tab_key; ?>[loan_term_info]"><?php echo esc_attr( $this->first_tab['loan_term_info'] ); ?></textarea>
        <p><?php echo __("Set the information bubble text for the Loan Term Field.", "shmac"); ?></p>
        <?php
    }

    function field_loan_term() {
        ?>
        <input class="shmac-loan-term" name="<?php echo $this->first_tab_key; ?>[loan_term]" value="<?php echo esc_attr( $this->first_tab['loan_term'] ); ?>" />
        <p><?php echo __("Set the default loan term for the calculator", "shmac"); ?></p>
        <?php
    }

    // Enable Insurance
    function field_enable_insurance() {
        ?>
		<hr />
		<!-- Rounded switch -->
        <input type="hidden" class="shmac-insurance-alt" name="<?php echo $this->first_tab_key; ?>[enable_insurance]" value="no">
        <label class="switch">
        <input type="checkbox" class="shmac-enable-insurance" name="<?php echo $this->first_tab_key; ?>[enable_insurance]" value="yes"
            <?php checked($this->first_tab['enable_insurance'], 'yes');?>>
        <span class="slider round"></span>
        </label>	
        <p><?php echo __("Enables homeowners insurance estimate", "shmac"); ?></p>
        <?php
    }
    
    // Insurance monthly price or percent
    function field_insurance_amount_percent() {
        ?>
        <select class="shmac-insurance-amount-percent" name="<?php echo $this->first_tab_key; ?>[insurance_amount_percent]">
            <option value="amount" <?php selected( $this->first_tab['insurance_amount_percent'], "amount");?>
                ><?php echo __("Amount", "shmac");?></option>
            <option value="percent" <?php selected( $this->first_tab['insurance_amount_percent'], "percent");?>
                ><?php echo __("Percent", "shmac");?></option>
        </select>
        <p><?php echo __("This affects the value below as to whether it is a set amount or percent of the loan (divided by 12) per month", "shmac"); ?>
        </p>
        <?php
    }
        
    // Insurance
    function field_insurance() {
        ?>
        <input class="shmac-insurance" name="<?php echo $this->first_tab_key; ?>[insurance]" value="<?php echo esc_attr( $this->first_tab['insurance'] ); ?>" />
        <p><?php echo __("Set the monthly insurance cost or the percent (depending on what whas chosen above) for the calculator", "shmac"); ?></p>
        <?php
    }

    // Enable PMI
    function field_enable_pmi() {
        ?>
		<hr />
		<!-- Rounded switch -->
        <input type="hidden" class="shmac-pmi-alt" name="<?php echo $this->first_tab_key; ?>[enable_pmi]" value="no">
        <label class="switch">
        <input type="checkbox" class="shmac-enable-pmi" name="<?php echo $this->first_tab_key; ?>[enable_pmi]" value="yes"
            <?php checked($this->first_tab['enable_pmi'], 'yes');?>>
        <span class="slider round"></span>
        </label>
        <p><?php echo __("Choose whether to give a PMI estimate", "shmac"); ?></p>
        <?php
    }

    // PMI
    function field_pmi() {
        ?>
        <input class="shmac-pmi" name="<?php echo $this->first_tab_key; ?>[pmi]" value="<?php echo esc_attr( $this->first_tab['pmi'] ); ?>" />
        <p><?php echo __("Set the monthly PMI cost for the calculator.  If you don't know, leave this as-is.  This is just an average cost per 100,000 dollars borrowed.", "shmac"); ?></p>
        <?php
    }

    // Enable Taxes
    function field_enable_taxes() {
        ?>
		<hr />
		<!-- Rounded switch -->
        <input type="hidden" class="shmac-taxes-alt" name="<?php echo $this->first_tab_key; ?>[enable_taxes]" value="no">
        <label class="switch">
        <input type="checkbox" class="shmac-enable-taxes" name="<?php echo $this->first_tab_key; ?>[enable_taxes]" value="yes"
            <?php checked($this->first_tab['enable_taxes'], 'yes');?>>
        <span class="slider round"></span>
        </label>
<!--
        <select class="shmac-enable-taxes" name="<?php echo $this->first_tab_key; ?>[enable_taxes]">
            <option value="yes" <?php selected( $this->first_tab['enable_taxes'], "yes");?>
                    ><?php echo __("Yes", "shmac");?></option>
            <option value="no" <?php selected( $this->first_tab['enable_taxes'], "no");?>
                    ><?php echo __("No", "shmac");?></option>
        </select>
-->
        <p><?php echo __("Choose whether to give a tax estimate or not", "shmac"); ?></p>
        <?php
    }

    
    // PMI
    function field_tax_rate() {
        ?>
        <input class="shmac-tax-rate" name="<?php echo $this->first_tab_key; ?>[tax_rate]" 
          value="<?php echo esc_attr( $this->first_tab['tax_rate']); ?>" />
        <p><?php echo __("Set the tax rate per 1000 dollars assessed.  If you don't know, leave this as-is.  This is just an average cost based on assessed value.", "shmac"); ?></p>
        <?php
    }
	
	/* Second Tab Fields */
	// Loan Details headings in reports 
	function section_loan_details() {
		?>
		<table class="section-loan-details">
		<tr class="head-row">
			<th><?php echo __('Original Loan Amount', 'shmac');?></th>
			<th><?php echo __('Down Payment', 'shmac');?></th>
			<th><?php echo __('Interest', 'shmac');?></th>
			<th><?php echo __('Term', 'shmac');?></th>
		</tr>
		<tr>
			<td><input class="shmac-detail-original" name="<?php echo $this->second_tab_key; ?>[detail_original]" value="<?php echo esc_attr( $this->second_tab['detail_original'] ); ?>" /></td>
			<td><input class="shmac-detail-down" name="<?php echo $this->second_tab_key; ?>[detail_down_payment]" value="<?php echo esc_attr( $this->second_tab['detail_down_payment'] ); ?>" /></td>
			<td><input class="shmac-detail-interest" name="<?php echo $this->second_tab_key; ?>[detail_interest]" value="<?php echo esc_attr( $this->second_tab['detail_interest'] ); ?>" /></td>
			<td><input class="shmac-detail-term" name="<?php echo $this->second_tab_key; ?>[detail_term]" value="<?php echo esc_attr( $this->second_tab['detail_term'] ); ?>" /></td>
		</tr>
		<tr class="head-row">
			<th><?php echo __('Loan After Down', 'shmac');?></th>
            <th><?php echo __('Down Payment Amount', 'shmac');?></th>
            <th><?php echo __('Monthly Payment (P & I)', 'shmac');?></th>
            <th><?php echo __('Total Payments', 'shmac');?></th>
		</tr>
		<tr>
			<td><input class="shmac-detail-loan-after" name="<?php echo $this->second_tab_key; ?>[detail_loan_after_down]" value="<?php echo esc_attr( $this->second_tab['detail_loan_after_down'] ); ?>" /></td>
			<td><input class="shmac-detail-down-payment-amount" name="<?php echo $this->second_tab_key; ?>[detail_down_payment_amount]" value="<?php echo esc_attr( $this->second_tab['detail_down_payment_amount'] ); ?>" /></td>
			<td><input class="shmac-detail-monthly-payment" name="<?php echo $this->second_tab_key; ?>[detail_monthly_payment]" value="<?php echo esc_attr( $this->second_tab['detail_monthly_payment'] ); ?>" /></td>
			<td><input class="shmac-detail-total-payments" name="<?php echo $this->second_tab_key; ?>[detail_total_payments]" value="<?php echo esc_attr( $this->second_tab['detail_total_payments'] ); ?>" /></td>
		</tr>
	    </table>
		<p><?php echo __('Set the Loan Details section texts to what you like', 'shmac');?></p>
		<?php
	}

	// Schedule Headers
	function section_schedule_headers() {
		?>
		        <table class="section-schedule-header">
        <tr class="head-row">
            <th><?php echo __('Payment', 'shmac');?></th>
            <th><?php echo __('Payment Amount', 'shmac');?></th>
            <th><?php echo __('Interest', 'shmac');?></th>
            <th><?php echo __('Total Interest', 'shmac');?></th>
        </tr>
        <tr>
            <td><input class="shmac-header-payment" name="<?php echo $this->second_tab_key; ?>[header_payment]" value="<?php echo esc_attr( $this->second_tab['header_payment'] ); ?>" /></td>
            <td><input class="shmac-header-payment-amount" name="<?php echo $this->second_tab_key; ?>[header_payment_amount]" value="<?php echo esc_attr( $this->second_tab['header_payment_amount'] ); ?>" /></td>
            <td><input class="shmac-header-interest" name="<?php echo $this->second_tab_key; ?>[header_interest]" value="<?php echo esc_attr( $this->second_tab['header_interest'] ); ?>" /></td>
            <td><input class="shmac-header-total-interest" name="<?php echo $this->second_tab_key; ?>[header_total_interest]" value="<?php echo esc_attr( $this->second_tab['header_total_interest'] ); ?>" /></td>
        </tr>
        <tr class="head-row">
            <th><?php echo __('Principal', 'shmac');?></th>
            <th><?php echo __('Balance', 'shmac');?></th>
            <th><?php echo __('Loan Details', 'shmac');?></th>
            <th><?php echo __('Amortization Schedule (P & I)', 'shmac');?></th>
        </tr>
        <tr>
            <td><input class="shmac-header-principal" name="<?php echo $this->second_tab_key; ?>[header_principal]" value="<?php echo esc_attr( $this->second_tab['header_principal']); ?>" /></td>
            <td><input class="shmac-header-balance" name="<?php echo $this->second_tab_key; ?>[header_balance]" value="<?php echo esc_attr( $this->second_tab['header_balance'] ); ?>" /></td>
            <td><input class="shmac-header-loan-text" name="<?php echo $this->second_tab_key; ?>[header_loan_text]" value="<?php echo esc_attr( $this->second_tab['header_loan_text'] ); ?>" /></td>
            <td><input class="shmac-header-schedule-text" name="<?php echo $this->second_tab_key; ?>[header_schedule_text]" value="<?php echo esc_attr( $this->second_tab['header_schedule_text'] ); ?>" /></td>
        </tr>
        </table>
        <p><?php echo __('Set the Loan headers and titles texts to what you like', 'shmac');?></p>
		<?php
	}

    // Allow Emails
    function field_allow_email() {
        ?>
		<!-- Rounded switch -->
        <input type="hidden" class="shmac-allow-email-alt" name="<?php echo $this->second_tab_key; ?>[allow_email]" value="no">
        <label class="switch">
        <input type="checkbox" class="shmac-allow-email" name="<?php echo $this->second_tab_key; ?>[allow_email]" value="yes"
            <?php checked($this->second_tab['allow_email'], 'yes');?>>
        <span class="slider round"></span>
        </label>
        <!-- End switch -->	
        <p><?php echo __("Choose to allow Email Reports to users", "shmac"); ?></p>
        <?php
    }

    // Email BCC
    function field_email_bcc() {
        ?>
        <input class="shmac-email-bcc" name="<?php echo $this->second_tab_key; ?>[email_bcc]" value="<?php echo esc_attr( $this->second_tab['email_bcc'] ); ?>" />
        <p><?php echo __("Set the email to receive a hidden copy (BCC - Blind Carbon Copy) of the user generated report", "shmac"); ?></p>
        <?php
    }

    // Email From
    function field_email_from() {
        ?>
        <input class="shmac-email-from" name="<?php echo $this->second_tab_key; ?>[email_from]" value="<?php echo esc_attr( $this->second_tab['email_from'] ); ?>" />
        <p><?php echo __("Set the 'From Address' for the email sent to the user.  Make sure that your server will allow the email you choose as the from address.", "shmac"); ?></p>
        <?php
    }

    // Email Subject
    function field_email_subject() {
        ?>
         <textarea class="email-subject" name="<?php echo $this->second_tab_key; ?>[email_subject]"><?php echo esc_attr( $this->second_tab['email_subject'] ); ?></textarea>
        <?php
    }
    
    // Email Text
    function field_email_text() {
        ?>
         <textarea class="email-text" name="<?php echo $this->second_tab_key; ?>[email_text]"><?php echo esc_attr( $this->second_tab['email_text'] ); ?></textarea>
        <?php
    }
    // PDF Color
    function field_pdf_color() {
        ?>
         <input type="text" name="<?php echo $this->second_tab_key; ?>[pdf_color]" class="color-field" value="<?php echo esc_attr($this->second_tab['pdf_color'] ); ?>" />
        <p>
            <?php echo __('Set your custom primary color for the PDF Results', 'shmac');?>
        </p>
        <?php
    }

	// Brought To you by
	function field_brought_by() {
        ?>
         <input type="text" name="<?php echo $this->second_tab_key; ?>[brought_by]" class="brought-by" value="<?php echo esc_attr($this->second_tab['brought_by'] ); ?>" />
        <p>
            <?php echo __('Brought to you by greeting', 'shmac');?>
        </p>
        <?php
    }

    function field_disclaimer() {
        ?>
        <textarea class="disclaimer" name="<?php echo $this->second_tab_key; ?>[disclaimer]"><?php echo esc_attr( $this->second_tab['disclaimer'] ); ?></textarea>
        <?php
    }

	function field_report_texts() {
        ?>
		<textarea class="shmac-otherfactors" name="<?php echo $this->second_tab_key; ?>[otherfactors]"><?php echo esc_attr( $this->second_tab['otherfactors'] ); ?></textarea>
		<p>
			<?php echo __('Other Factors initial text', 'shmac');?>
		<p/>
		<?php
	}

	// Down Payment (PMI) Factor
	function field_down_factor() {
		?>
		<textarea class="shmac-down-factor-1" name="<?php echo $this->second_tab_key; ?>[down_factor_1]"><?php echo esc_attr( $this->second_tab['down_factor_1'] ); ?></textarea>
		<p>
			<?php echo __('Must pay PMI.  Bracketted texts are placeholders for values ([pmi_display], [hundredthou], and [pmi]) leave those intact and you can rephrase as needed.', 'shmac'); ?>
		</p>
		<p></p><br />
		<textarea class="shmac-down-factor-2" name="<?php echo $this->second_tab_key; ?>[down_factor_2]"><?php echo esc_attr( $this->second_tab['down_factor_2'] ); ?></textarea>
        <p>
            <?php echo __('Not paying PMI since 20% of loan reached.', 'shmac'); ?>
        </p>
        <?php
    }

	// Tax Factor
	function field_tax_factor() {
        ?>
        <textarea class="tax-factor" name="<?php echo $this->second_tab_key; ?>[tax_factor]"><?php echo esc_attr( $this->second_tab['tax_factor'] ); ?></textarea>
        <p>
            <?php echo __('Bracketted texts are placeholders for values ([tax_rate_display], [thousand_dollars_display] and [assessed_display] and [taxes_display]) leave those intact and you can rephrase.', 'shmac'); ?>
        </p>
        <?php
    }

	// Insurance Factor
	function field_insurance_factor() {
        ?>
        <textarea class="insurance-factor" name="<?php echo $this->second_tab_key; ?>[insurance_factor]"><?php echo esc_attr( $this->second_tab['insurance_factor'] ); ?></textarea>
        <p>
            <?php echo __('Bracketted texts are placeholders for values ([insurance_display]) leave those intact and you can rephrase.', 'shmac'); ?>
        </p>
        <?php
    }

	// Factor Summary
	function field_factor_summary() {
        ?>
        <textarea class="factor-summary" name="<?php echo $this->second_tab_key; ?>[factor_summary]"><?php echo esc_attr( $this->second_tab['factor_summary'] ); ?></textarea>
        <p>
            <?php echo __('Bracketted texts are placeholders for values ([total_payment_display]) leave those intact and you can rephrase.', 'shmac'); ?>
        </p>
        <?php
    }

    // Currency
    function field_currency() {
        ?>
        <input type="text" name="<?php echo $this->first_tab_key; ?>[currency]" class="shmac-currency" value="<?php echo esc_attr($this->first_tab['currency'] ); ?>" />
        <p><?php echo __("Set the currency symbol used in the Calculator (default $)", "shmac"); ?></p>
        <?php
    }

    // Currency Format
    function field_currency_format() {
        ?>
        <select class="shmac-currency-format" name="<?php echo $this->first_tab_key; ?>[currency_format]">
            <option value="1" <?php selected( $this->first_tab['currency_format'], "1");?>
                    ><?php echo __("Standard Format (e.g. 100,000.00)", "shmac");?></option>
            <option value="2" <?php selected( $this->first_tab['currency_format'], "2");?>
                    ><?php echo __("Switched Format (e.g. 100.000,00)", "shmac");?></option>
            <option value="3" <?php selected( $this->first_tab['currency_format'], "3");?>
                    ><?php echo __("Spaces and Comma Format (e.g. 100 000,00)", "shmac");?></option>
			<option value="4" <?php selected( $this->first_tab['currency_format'], "4");?>
                    ><?php echo __("Indian Rupee (e.g. 10,00,00,000.00)", "shmac");?></option>
			<option value="5" <?php selected( $this->first_tab['currency_format'], "5");?>
                    ><?php echo __("Swiss (e.g. 10'345'567.00)", "shmac");?></option>
        </select>
        <p><?php echo __("Choose the currency format you would like to use.", "shmac"); ?></p>
        <?php
    }

    // Currency Symbol Side 
    function field_currency_side() {
        ?>
        <select class="shmac-currency-side" name="<?php echo $this->first_tab_key; ?>[currency_side]">
            <option value="left" <?php selected( $this->first_tab['currency_side'], "left");?>
                    ><?php echo __("Left (e.g. $100,000.00)", "shmac");?></option>
            <option value="right" <?php selected( $this->first_tab['currency_side'], "right");?>
                    ><?php echo __("Right (e.g. 100,000.00 $)", "shmac");?></option>
        </select>
        <p><?php echo __("Choose the side for the currency symbol.", "shmac"); ?></p>
        <?php
    }   

    /*
     * Custom CSS Overrides
     */
    function field_custom_code() {
        ?>
		<hr />
		<table class="shmac_custom">
		<tr>
		<th><?php echo __('Custom CSS', 'shmac');?></th><th><?php echo __('Custom JS', 'shmac');?></th>
		</tr>
		<tr>
		<td>
        <textarea class="custom-css" id="code_editor_page_css" name="<?php echo $this->first_tab_key; ?>[custom_css]"><?php echo esc_attr( $this->first_tab['custom_css'] ); ?></textarea>
        <p><?php echo __("Add your own custom css to further control styling of the calculator", "shmac"); ?></p>
		</td>

		<td>
	
		 <textarea class="custom-js" id="code_editor_page_js" name="<?php echo $this->first_tab_key; ?>[custom_js]"><?php echo esc_attr( $this->first_tab['custom_js'] ); ?></textarea>
        <p><?php echo __("Add your own custom javascript to further control functionality of the calculator", "shmac"); ?></p>
		</td>
		</tr>
		</table>
        <?php
    }

    /* 
     * Color picker field callback, renders a 
     * color picker
     */
    function field_color_option() {
        ?>
        <input type="text" name="<?php echo $this->first_tab_key; ?>[page_color]" data-alpha="true" class="color-picker color-field" value="<?php echo esc_attr( $this->first_tab['page_color'] ); ?>" />
        <p>
            <?php echo __('This sets the primary color for the Calculator', 'shmac');?>
        </p>

        <?php
    }

    /* 
     * Color picker field callback, renders a 
     * color picker
     */
    function field_bg_option() {
        ?>
        <input type="text" name="<?php echo $this->first_tab_key; ?>[bg_color]" data-alpha="true" class="color-picker color-field" value="<?php echo esc_attr( $this->first_tab['bg_color'] ); ?>" />
        <p>
            <?php echo __('Sets the background color for the Calculator', 'shmac');?>
        </p>

        <?php
    }


	/*
     * Background image upload
     */
    function field_bg_image() {
        ?>
        <button id="upload_now" class="button button-primary custom_bg_upload"><?php echo __("Upload", "shmac");?></button>
        <button class="button button-primary bg_clear"><?php echo __("Clear Image", "shmac");?></button>
        <img class="shmac_bg_image" style="display:none;" src="<?php echo esc_attr( $this->first_tab['bg_attachment_url']);?>" />
        <input class="custom_bg_url" type="text" style="display:none;" name="<?php echo $this->first_tab_key;?>[bg_attachment_url]" value="<?php echo esc_attr( $this->first_tab['bg_attachment_url']);?>">
        <input class="custom_bg_id" type="text" style="display:none;" name="<?php echo $this->first_tab_key;?>[bg_attachment_id]" value="<?php echo esc_attr($this->first_tab['bg_attachment_id']);?>">
        <?php
    }

	/*
	 * Output location choices are below calculator or in popup window
	 */
	function field_location() {
        ?>
		<select class="shmac-location" name="<?php echo $this->first_tab_key; ?>[location]">
            <option value="inline" <?php selected( $this->first_tab['location'], "inline");?>
                    ><?php echo __("Below Calculator", "shmac");?></option>
            <option value="modal" <?php selected( $this->first_tab['location'], "modal");?>
                    ><?php echo __("In popup (modal)", "shmac");?></option>
        </select>
		<p>
			<?php echo __('Sets the output location desired, it can be below the calculator or in a popup window (modal)', 'shmac'); ?>
		</p>
		<?php
    }

	/*
	 * Submit and Reset Buttons
	 */
	function field_buttons() {
		// Term Month label
        ?>
        <input class="shmac-submit-label" name="<?php echo $this->first_tab_key; ?>[calcsubmit]" value="<?php echo esc_attr( $this->first_tab['calcsubmit']); ?>" />
        <p><?php echo __("The text for the Submit Button.", "shmac"); ?></p>
		
		<input class="shmac-reset-label" name="<?php echo $this->first_tab_key; ?>[calcreset]" value="<?php echo esc_attr( $this->first_tab['calcreset']); ?>" />
        <p><?php echo __("The text for the Reset Button.", "shmac"); ?></p>
        <?php
    }


    /*
     * Logo image upload
     */
    function field_logo_image() {
        ?>
        <button id="upload_now" class="button button-primary custom_media_upload"><?php echo __("Upload", "shmac");?></button>
        <button class="button button-primary logo_clear"><?php echo __("Clear Image", "shmac");?></button>
        <img class="shmac_logo_image" style="display:none;" src="<?php echo esc_attr( $this->second_tab['logo_attachment_url']);?>" />
        <input class="custom_media_url" type="text" style="display:none;" name="<?php echo $this->second_tab_key;?>[logo_attachment_url]" value="<?php echo esc_attr( $this->second_tab['logo_attachment_url']);?>">
        <input class="custom_media_id" type="text" style="display:none;" name="<?php echo $this->second_tab_key;?>[logo_attachment_id]" value="<?php echo esc_attr( $this->second_tab['logo_attachment_id']);?>">
        <?php
    }

    /*
     * PDF Header large text
     */
    function field_pdf_header() {
        ?>
         <input type="text" name="<?php echo $this->second_tab_key; ?>[pdf_header]" class="pdf-header" value="<?php echo esc_attr( $this->second_tab['pdf_header'] ); ?>" />
        <p>
            <?php echo __('Large Text at the top of the PDF', 'shmac');?>
        </p>

        <?php
    }

    /*
     * LTR or RTL PDF and styling
     */
    function field_ltr_rtl() {
        ?>
		<!-- Rounded switch -->
		<input type="hidden" class="shmac-ltr-rtl-alt" name="<?php echo $this->second_tab_key; ?>[ltr_rtl]" value="ltr">
		<label class="switch">
		<input type="checkbox" class="shmac-ltr-rtl" name="<?php echo $this->second_tab_key; ?>[ltr_rtl]" value="rtl"
			<?php checked($this->second_tab['ltr_rtl'], 'rtl');?>>
		<span class="slider round"></span>
		</label>
		<!-- End switch -->
        <p><?php echo __("Enables the Right to Left PDF format for Right to Left languages (Hebrew, Arabic etc.).", "shmac"); ?></p>
        <?php
    }

    /*
     * PDF Font Selection
     */
    function field_pdf_font() {
        ?>
        <select class="shmac-pdf-font" name="<?php echo $this->second_tab_key; ?>[pdf_font]">
            <option value="helvetica" <?php selected( $this->second_tab['pdf_font'], "helvetica");?>
                    ><?php echo __("Helvetica - Default, smaller generated PDF file", "shmac");?></option>
            <option value="dejavusans" <?php selected( $this->second_tab['pdf_font'], "dejavusans");?>
                    ><?php echo __("Dejavu sans - Persian / English", "shmac");?></option>
            <option value="DroidSansFallback" <?php selected( $this->second_tab['pdf_font'], "DroidSansFallback");?>
                    ><?php echo __("Droid Sans Fallback - Chinese / English", "shmac");?></option>
            <option value="aefurat" <?php selected( $this->second_tab['pdf_font'], "aefurat");?>
                    ><?php echo __("Aefurat - Arabic / English", "shmac");?></option>
            <option value="aealarabiya" <?php selected( $this->second_tab['pdf_font'], "aealarabiya");?>
                    ><?php echo __("Aealarabiya - Arabic / English", "shmac");?></option>
            <option value="courier" <?php selected( $this->second_tab['pdf_font'], "courier");?>
                    ><?php echo __("Courier", "shmac");?></option>  
            <option value="times" <?php selected( $this->second_tab['pdf_font'], "times");?>
                    ><?php echo __("Times", "shmac");?></option>
        </select>
        <p><?php echo __("Choose the font to use.  Helvetica is going to be the smallest but does not support many RTL languages.", "shmac"); 
?></p>
        <?php
    }

    //Slider Fields Body
    function field_enable_email() {
        ?>
		<!-- Rounded switch -->
        <input type="hidden" class="enable-email-alt" name="<?php echo $this->first_tab_key; ?>[enable_email]" value="no">
        <label class="switch">
        <input type="checkbox" class="shmac-enable-email" name="<?php echo $this->first_tab_key; ?>[enable_email]" value="yes"
            <?php checked($this->first_tab['enable_email'], 'yes');?>>
        <span class="slider round"></span>
        </label>

        <p><?php echo __("Enabling requires user to add email address. It will only work if Allow Email Report option is selected.", "shmac"); ?></p>
        <?php
    }
    //Slider Fields Body
    function field_enable_slider() {
        ?>
		<!-- Rounded switch -->
        <input type="hidden" class="enable-slider-alt" name="<?php echo $this->first_tab_key; ?>[enable_slider]" value="no">
        <label class="switch">
        <input type="checkbox" class="shmac-enable-slider" name="<?php echo $this->first_tab_key; ?>[enable_slider]" value="yes"
            <?php checked($this->first_tab['enable_slider'], 'yes');?>>
        <span class="slider round"></span>
        </label>	

        <p><?php echo __("Choose to show a slider with input", "shmac"); ?></p>
        <?php
    }

    function field_enable_input_readonly() {
        ?>
		<!-- Rounded switch -->
        <input type="hidden" class="enable-readonly-alt" name="<?php echo $this->first_tab_key; ?>[enable_input_readonly]" value="no">
        <label class="switch">
        <input type="checkbox" class="shmac-enable-input-readonly" name="<?php echo $this->first_tab_key; ?>[enable_input_readonly]" value="yes"
            <?php checked($this->first_tab['enable_input_readonly'], 'yes');?>>
        <span class="slider round"></span>
        </label>
        <p><?php echo __("Slider input fields disabled if this is on.", "shmac"); ?></p>
        <?php
    }
    
    function field_slider_theme() {
        ?>
         <select class="shmac-slider-theme" name="<?php echo $this->first_tab_key; ?>[slider_theme]">
            <option value="broad" <?php selected( $this->first_tab['slider_theme'], "broad");?>
                    ><?php echo __("Broad", "shmac");?></option>
            <option value="narrow" <?php selected( $this->first_tab['slider_theme'], "narrow");?>
                    ><?php echo __("Narrow", "shmac");?></option>
        </select>
        <p><?php echo __("Choose slider theme", "shmac"); ?></p>
        <?php
    }
    
    //Amount
    function field_amount_min_value() {
        ?>
        <input class="shmac-purchase-min-price" name="<?php echo $this->first_tab_key; ?>[amount_min_value]" value="<?php echo esc_attr( $this->first_tab['amount_min_value'] ); ?>" />
        <p><?php echo __("Set a minimum purchase price for the calculator", "shmac"); ?></p>
        <?php
    }
    function field_amount_max_value() {
        ?>
        <input class="shmac-purchase-max-price" name="<?php echo $this->first_tab_key; ?>[amount_max_value]" value="<?php echo esc_attr( $this->first_tab['amount_max_value'] ); ?>" />
        <p><?php echo __("Set a maximum purchase price for the calculator", "shmac"); ?></p>
        <?php
    }
    function field_amount_slider_step() {
        ?>
        <input class="shmac-purchase-slider-step" name="<?php echo $this->first_tab_key; ?>[amount_slider_step]" value="<?php echo esc_attr( $this->first_tab['amount_slider_step'] ); ?>" />
        <p><?php echo __("Set a slider step of purchase price for the calculator", "shmac"); ?></p>
        <?php
    }
    function field_amount_slider_start() {
        ?>
        <input class="shmac-purchase-slider-start" name="<?php echo $this->first_tab_key; ?>[amount_slider_start]" value="<?php echo esc_attr( $this->first_tab['amount_slider_start'] ); ?>" />
        <p><?php echo __("Set a slider start value of purchase price for the calculator", "shmac"); ?></p>
        <?php
    }
    //Interest Rate
    function field_interest_min_value() {
        ?>
        <input class="shmac-interest-min-rate" name="<?php echo $this->first_tab_key; ?>[interest_min_value]" value="<?php echo esc_attr( $this->first_tab['interest_min_value'] ); ?>" />
        <p><?php echo __("Set a minimum interest rate for the calculator", "shmac"); ?></p>
        <?php
    }
    function field_interest_max_value() {
        ?>
        <input class="shmac-interest-max-rate" name="<?php echo $this->first_tab_key; ?>[interest_max_value]" value="<?php echo esc_attr( $this->first_tab['interest_max_value'] ); ?>" />
        <p><?php echo __("Set a maximum interest rate for the calculator", "shmac"); ?></p>
        <?php
    }
    function field_interest_slider_step() {
        ?>
        <input class="shmac-interest-slider-step" name="<?php echo $this->first_tab_key; ?>[interest_slider_step]" value="<?php echo esc_attr( $this->first_tab['interest_slider_step'] ); ?>" />
        <p><?php echo __("Set a slider step of interest rate for the calculator", "shmac"); ?></p>
        <?php
    }
    function field_interest_slider_start() {
        ?>
        <input class="shmac-interest-slider-start" name="<?php echo $this->first_tab_key; ?>[interest_slider_start]" value="<?php echo esc_attr( $this->first_tab['interest_slider_start'] ); ?>" />
        <p><?php echo __("Set a slider start value of interest rate for the calculator", "shmac"); ?></p>
        <?php
    }
    //Down Payment
    function field_dwnpay_min_value() {
        ?>
        <input class="shmac-dwnpay-min-rate" name="<?php echo $this->first_tab_key; ?>[dwnpay_min_value]" value="<?php echo esc_attr( $this->first_tab['dwnpay_min_value'] ); ?>" />
        <p><?php echo __("Set a minimum down payment for the calculator", "shmac"); ?></p>
        <?php
    }
    function field_dwnpay_max_value() {
        ?>
        <input class="shmac-dwnpay-max-rate" name="<?php echo $this->first_tab_key; ?>[dwnpay_max_value]" value="<?php echo esc_attr( $this->first_tab['dwnpay_max_value'] ); ?>" />
        <p><?php echo __("Set a maximum down payment for the calculator", "shmac"); ?></p>
        <?php
    }
    function field_dwnpay_slider_step() {
        ?>
        <input class="shmac-dwnpay-slider-step" name="<?php echo $this->first_tab_key; ?>[dwnpay_slider_step]" value="<?php echo esc_attr( $this->first_tab['dwnpay_slider_step'] ); ?>" />
        <p><?php echo __("Set a slider step of down payment for the calculator", "shmac"); ?></p>
        <?php
    }
    function field_dwnpay_slider_start() {
        ?>
        <input class="shmac-dwnpay-slider-start" name="<?php echo $this->first_tab_key; ?>[dwnpay_slider_start]" value="<?php echo esc_attr( $this->first_tab['dwnpay_slider_start'] ); ?>" />
        <p><?php echo __("Set a slider start value of down payment for the calculator", "shmac"); ?></p>
        <?php
    }
    
    //Term
    function field_term_min_value() {
        ?>
        <input class="shmac-term-min-value" name="<?php echo $this->first_tab_key; ?>[term_min_value]" value="<?php echo esc_attr( $this->first_tab['term_min_value'] ); ?>" />
        <p><?php echo __("Set a minimum term for the calculator", "shmac"); ?></p>
        <?php
    }
    function field_term_max_value() {
        ?>
        <input class="shmac-term-max-value" name="<?php echo $this->first_tab_key; ?>[term_max_value]" value="<?php echo esc_attr( $this->first_tab['term_max_value'] ); ?>" />
        <p><?php echo __("Set a maximum term for the calculator", "shmac"); ?></p>
        <?php
    }
    function field_term_slider_step() {
        ?>
        <input class="shmac-term-slider-step" name="<?php echo $this->first_tab_key; ?>[term_slider_step]" value="<?php echo esc_attr( $this->first_tab['term_slider_step'] ); ?>" />
        <p><?php echo __("Set a slider step of term for the calculator", "shmac"); ?></p>
        <?php
    }
    function field_term_slider_start() {
        ?>
        <input class="shmac-term-slider-start" name="<?php echo $this->first_tab_key; ?>[term_slider_start]" value="<?php echo esc_attr( $this->first_tab['term_slider_start'] ); ?>" />
        <p><?php echo __("Set a slider start value of term for the calculator", "shmac"); ?></p>
        <?php
    }
    // Term Type - Month/ Year / Both
    function field_term_type() {
        ?>
        <select class="shmac-term-type" name="<?php echo $this->first_tab_key; ?>[term_type]">
            <option value="both" <?php selected( $this->first_tab['term_type'], "both");?>
                    ><?php echo __("Both", "shmac");?></option>
            <option value="year" <?php selected( $this->first_tab['term_type'], "year");?>
                    ><?php echo __("Year Only", "shmac");?></option>
            <option value="month" <?php selected( $this->first_tab['term_type'], "month");?>
                    ><?php echo __("Month Only", "shmac");?></option>
        </select>
        <p><?php echo __("Choose whether to show year or month or both", "shmac"); ?></p>
        <?php
    }

	/*
     * Site information field callback
     */
    function field_section_info() {
		echo __('Note that these server settings will affect the size, amount, and time taken allowed for PDF generation and scripts.  <br />These PHP settings may need
 to be adjusted on your server according to your requirements.', 'shmac');
        ?>
        <table class="wcp_site_info">
            <tr>
                <th<?php __('Variable', 'shmac'); ?></th>
                <th><?php __('Value', 'shmac'); ?></th>
            </tr>
            <tr class="row1"><td><?php echo __('PHP version running', 'shmac');?></td><td><?php echo phpversion(); ?></td></tr>
            <tr class="row0"><td>post_max_size</td><td><?php echo ini_get('post_max_size'); ?></td></tr>
            <tr class="row1"><td>memory_limit</td><td><?php echo ini_get('memory_limit'); ?></td> </tr>
            <tr class="row0"><td>upload_max_filesize</td><td><?php echo ini_get('upload_max_filesize'); ?></td></tr>
            <tr class="row1"><td>max_execution_time</td><td><?php echo ini_get('max_execution_time'); ?></td></tr>
            <tr class="row0"><td>max_file_uploads</td><td><?php echo ini_get('max_file_uploads'); ?></td></tr>
            <tr class="row1"><td>max_input_vars</td><td><?php echo ini_get('max_input_vars'); ?></td></tr>
        </table>
    <?php }

    
    /*
     * Called during admin_menu, adds an options
     * page under Settings called My Settings, rendered
     * using the plugin_options_page method.
     */
    function add_admin_menus() {
        $plugin_options = add_menu_page( __('SHMAC', 'shmac'), __('WP Amortization Calculator', 'shmac'), 
                'manage_options', $this->plugin_options_key, array($this, 'plugin_options_page'), 
                SHMAC_ROOT_URL . '/assets/img/shmac-logo16x16.png', '27.338');

        // loaded only in our Contacts menu
        add_action( 'load-' . $plugin_options, array($this, 'load_admin_scripts') );
    }

    /* Only called on our plugin tabs page */
    function load_admin_scripts() {
        /* Can't enqueue scripts here, it's too early, so register against proper action hook first */
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
    }

    /**
     * Load admin Javascript.
     * @access public
     * @since 1.0.0
     * @return void
     */
    public function admin_enqueue_scripts ( $hook = '' ) {
        wp_enqueue_media();

        wp_register_script( 'shmac-admin',
        SHMAC_ROOT_URL . '/assets/js/admin.js', array( 'wp-color-picker' ), SHMAC_PLUGIN_VERSION, true );
        wp_enqueue_script( 'shmac-admin' );
        wp_localize_script(  'shmac-admin', 'SHMAC_Ajax_Admin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nextNonce' => wp_create_nonce( 'myajax-next-nonce' )
        ));
		wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

		wp_register_script( 'wp-color-picker-alpha', SHMAC_ROOT_URL . '/assets/js/wp-color-picker-alpha.min.js', array( 'wp-color-picker'), '2.1.4', true);
		wp_enqueue_script('wp-color-picker-alpha');

    } // End admin_enqueue_scripts

    
    /**
     * Load admin CSS.
     * @access public
     * @since 1.0.0
     * @return void
     */
    public function admin_enqueue_styles ( $hook = '' ) {
        // Add the color picker css file      
        wp_enqueue_style( 'wp-color-picker' );

        wp_register_style( 'shmac-admin', SHMAC_ROOT_URL . '/assets/css/admin.css', array(), SHMAC_PLUGIN_VERSION );
        wp_enqueue_style( 'shmac-admin' );
    } // End admin_enqueue_styles



    
    /*
     * Plugin Options page rendering goes here, checks
     * for active tab and replaces key with the related
     * settings key. Uses the plugin_options_tabs method
     * to render the tabs.
     */
    function plugin_options_page() {
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->first_tab_key;
        ?>
        <div class="wrap">
            <?php $this->plugin_options_tabs(); ?>
            <form method="post" action="options.php" class="shmac-form <?php echo $tab; ?>">
                <?php wp_nonce_field( 'update-options' ); ?>
                <?php settings_fields( $tab ); ?>
                <?php do_settings_sections( $tab ); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /*
     * Renders our tabs in the plugin options page,
     * walks through the object's tabs array and prints
     * them one by one. Provides the heading for the
     * plugin_options_page method.
     */
    function plugin_options_tabs() {
        $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->first_tab_key;

        echo '<h2 class="nav-tab-wrapper">';
        foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>'; 
        }
        echo '</h2>';
    }
};

// Initialize the plugin
add_action( 'plugins_loaded', function() {$settings_api_tabs_shmac = new SHMAC_API_Tabs;} );
