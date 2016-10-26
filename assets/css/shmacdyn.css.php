<?php
/* Frontend dynamic css generation
 *
 */
class shmac_dynamc_css {

	public function __construct() {
		$shmac_settings = get_option('shmac_settings');
		$primary_color = $shmac_settings['page_color'];
		require_once( SHMAC_ROOT_PATH . '/includes/shmac-utils.php' );
		$shmac_utils = new shmac_utils();
		$primary_color_light = $shmac_utils->hex2rgba($primary_color, $opacity = 0.4);
		$custom_css = trim($shmac_settings['custom_css']);

		// Need to check the rtl setting for some style changes on rtl languages
		$shmac_email = get_option('shmac_email');
		$rtl_css = '';
		if (isset($shmac_email['ltr_rtl']) && $shmac_email['ltr_rtl'] == 'rtl') {
            	$rtl_css = <<<EOT
/* RTL CSS */
.mui-form-group .shmac-tip {
   left:0px;
   right:auto;
}
.shmac-term-years, .shmac-term-months {
    float: right;
    margin-left: 20px;
}
.shmac-tip:hover:after{
    bottom: 26px;
    left: -6px;
	right: auto;
    width: 220px;
}
.shmac-tip:hover:before{
    bottom: 20px;
	right: 3px;
	left: auto;
}
EOT;

            }

		header('Content-type: text/css');
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
.shmac-form input[type=text]:focus:not([readonly]),
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
  background: <?php echo $primary_color_light; ?>;
}

<?php echo $rtl_css; ?>

/* Custom CSS Below */
<?php echo $custom_css;?>
	<?php
	} // end construct
} // end class


$shmac_dynamc_css = new shmac_dynamc_css();
