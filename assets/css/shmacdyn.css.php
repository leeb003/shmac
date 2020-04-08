<?php
/* Frontend dynamic css generation
 *
 */
class shmac_dynamc_css {

	public function __construct() {
		$shmac_settings = get_option('shmac_settings');
		$primary_color = $shmac_settings['page_color'];
		$custom_css = trim($shmac_settings['custom_css']);

		header('Content-type: text/css');

		/* Include all other css into one output */
		$css_files = array(
			SHMAC_ROOT_PATH . "/assets/css/frontend.css",
			SHMAC_ROOT_PATH . "/assets/css/jquery.mCustomScrollbar.min.css",
			SHMAC_ROOT_PATH .  '/assets/css/mprogress.min.css',
			SHMAC_ROOT_PATH .  '/assets/css/nouislider.min.css',
		);
		$buffer = "";
		foreach ($css_files as $css_file) {
			$buffer .= file_get_contents($css_file);
		}
		/* minify files */
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
    	$buffer = str_replace(': ', ':', $buffer);
    	$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

		/* Output CSS files first before adding dynamic and custom styles */
		echo $buffer; 
		?>



/* Dynamic Styles For WP Mortgage Calculator */
.shmac-form .mui-form-group > .mui-form-control:focus ~ label {
	color: <?php echo $primary_color; ?>;
}
.shmac-form .mui-select:focus > select {
	border-color: <?php echo $primary_color; ?>;
}
.shmac-form .mui-btn.submit-shmac,
.shmac-form .mui-btn.submit-shmac:hover {
	background-image:none;
	background-color: <?php echo $primary_color; ?>;
	color: #ffffff;
}
.shmac-form .mui-btn.shmac-reset,
.shmac-form .mui-btn.shmac-reset:hover {
    background-image:none;
    background-color: transparent;
}

/* for read only fields fallback color */
.shmac-form input[type=text]:focus,
.shmac-form input[type=email]:focus,
.shmac-form input[type=password]:focus {
	border-bottom: 1px solid rgba(0, 0, 0, 0.26);
}


.shmac-form input[type=text]:focus:not([readonly]),
.shmac-form input[type=email]:focus:not([readonly]),
.shmac-form input[type=password]:focus:not([readonly]) {
	border:none;
    border-bottom: 1px solid <?php echo $primary_color; ?>;
    box-shadow: 0 1px 0 0 <?php echo $primary_color; ?>;
}
.shmac-form [type="checkbox"]:checked + label:before {
  border: none;
  border-right: 2px solid <?php echo $primary_color; ?>;
  border-bottom: 2px solid <?php echo $primary_color; ?>;
}
.shmac-div {
	border: 5px solid <?php echo $primary_color; ?>;
}
.shmac-div .schedule-table th {
	color: <?php echo $primary_color; ?>;
}

.ui-mprogress .deter-bar,
.ui-mprogress .indeter-bar,
.ui-mprogress .query-bar,
.ui-mprogress .bar-bg,
.ui-mprogress .buffer-bg,
.ui-mprogress .mp-ui-dashed {
  background: <?php echo $primary_color; ?>;
}
.ui-mprogress .bar-bg,
.ui-mprogress .buffer-bg {
  background: rgba(0, 0, 0, 0.34);
}

/* Custom CSS Below */
<?php echo $custom_css;?>
	<?php
	} // end construct
} // end class


$shmac_dynamc_css = new shmac_dynamc_css();
