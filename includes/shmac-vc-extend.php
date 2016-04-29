<?php
/**
 * Visual Composer shortcode addition
 */

class shmacVC {
    // Properties

    // Methods

    public function __construct() {

        if (function_exists('vc_map')) {  // test for visual composer first
            add_action('vc_before_init', array(&$this, 'shmac_sc') );
		}
	}

	// Alert Message Boxes
    public function shmac_sc() {
        vc_map( array(
            "name" => __("Amortization Calculator", "shmac"),
            "base" => "shmac_calc_sc",
            "description" => __("Mortgage & Amortization Calculator", "shmac"),
            "category" => __('SH-Themes', 'shmac'),
            "params" => array(
                array(
                    "type" => "textfield",
                    "holder" => "p",
                    "heading"  => __("Extra Class", "shmac"),
                    "param_name" => "extraclass",
					"description" => __("Add an extra css class to this calculator for customizing styles", "shmac"),
                    "value" => '',
                ),
                array(
                    "type" => "dropdown",
                    "heading" => __("Override Calculator Settings", "shmac"),
					"description" => __('Enable if you want to override any of the settings set in the main calculator settings for this specific calculator.  Any fields left blank will inherit the main settings applied to the calculator.', 'shmac'),
                    "param_name" => "allowoverrides",
                    "value" => array(
						__("Disable", "shmac") => "disable",
                        __("Enable", "shmac") => "enable",
                    )
                ),
				array(
                    "type" => "colorpicker",
                    "heading" => __("Primary Color", "shmac"),
                    "param_name" => "primarycolor",
                    "value" => '#828282',
                    "description" => __("Set The Primary Color for the form and results.", "shmac"),
					"dependency" => array (
                    	"element" => "allowoverrides",
                    	"value" => array("enable"),
					),
                ),
				array(
                    "type" => "textfield",
                    "holder" => "p",
                    "heading"  => __("Calculator Title", "shmac"),
                    "param_name" => "calctitle",
                    "description" => __("The Title", "shmac"),
                    "value" => '',
					"dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textfield",
                    "heading"  => __("Send PDF Text", "shmac"),
                    "param_name" => "emailtext",
                    "description" => __("Text next to the checkbox for sending an email", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textfield",
                    "heading"  => __("Email Label", "shmac"),
                    "param_name" => "emaillabel",
                    "description" => __("Label for the email field", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textfield",
                    "heading"  => __("Amount Label", "shmac"),
                    "param_name" => "amountlabel",
                    "description" => __("Label above the amount input", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textarea",
                    "heading"  => __("Amount Info", "shmac"),
                    "param_name" => "amountinfo",
                    "description" => __("Text in the info bubble description for the amount field", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textfield",
                    "heading"  => __("Default Amount", "shmac"),
                    "param_name" => "defaultpurchase",
                    "description" => __("The default purchase price (e.g. 200,000.00) for the field.", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textfield",
                    "heading"  => __("Interest Field Label", "shmac"),
                    "param_name" => "interestlabel",
                    "description" => __("Label text for the interest field", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textarea",
                    "heading"  => __("Interest Info", "shmac"),
                    "param_name" => "interestinfo",
                    "description" => __("Text in the info bubble description for the interest field", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textfield",
                    "heading"  => __("Default Interest", "shmac"),
                    "param_name" => "defaultinterest",
                    "description" => __("The default interest value (e.g. 5.2)", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textfield",
                    "heading"  => __("Down Payment Label", "shmac"),
                    "param_name" => "downpaylabel",
                    "description" => __("Label text for the down payment field", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textarea",
                    "heading"  => __("Down Payment Info", "shmac"),
                    "param_name" => "downpayinfo",
                    "description" => __("Text in the info bubble description for the down payment field", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "dropdown",
                    "heading"  => __("Down Payment Type", "shmac"),
                    "param_name" => "downpaytype",
                    "description" => __("Down Payment input - percent or amount", "shmac"),
                    "value" => array(
                        __('Settings Default', 'shmac') => '',
                        __("Percent", "shmac") => "percent",
                        __("Amount", "shmac") => "amount",
                    ),
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textfield",
                    "heading"  => __("Default Down Payment", "shmac"),
                    "param_name" => "defaultdown",
                    "description" => __("Default percent down payment (e.g. 10 for percent or 10000.00 for amount)", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textfield",
                    "heading"  => __("Term Label", "shmac"),
                    "param_name" => "termlabel",
                    "description" => __("Label text for the term field", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textarea",
                    "heading"  => __("Term Info", "shmac"),
                    "param_name" => "terminfo",
                    "description" => __("Text in the info bubble description for the term field", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textfield",
                    "heading"  => __("Default Term", "shmac"),
                    "param_name" => "defaultterm",
                    "description" => __("The default term in years (e.g. 30)", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "dropdown",
                    "heading"  => __("Enable Insurance", "shmac"),
                    "param_name" => "enableinsurance",
                    "description" => __("Enable Homeowners insurance information in results?", "shmac"),
                    "value" => array(
						__('Settings Default', 'shmac') => '',
                        __("Disable", "shmac") => "no",
                        __("Enable", "shmac") => "yes",
                    ),
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
					"type" => "dropdown",
					"heading" => __("Insurance cost or percent", "shmac"),
					"param_name" => "insuranceamountpercent",
					"description" => __("This affects the value below as to whether it is a set amount or percent of the loan (divided by 12) per month", "shmac"),
					"value" => array (
						__('Settings Default', 'shmac') => '',
						__("Amount", "shmac") => "amount",
						__("Percent", "shmac") => "percent"
					),
					"dependency" => array (
						"element" => "enableinsurance",
						"value" => array("yes"),
					),
				),
				array(
                    "type" => "textfield",
                    "heading"  => __("Monthly Insurance", "shmac"),
                    "param_name" => "monthlyinsurance",
                    "description" => __("The monthly cost of home owners insurance (e.g. 50.00) or percent of loan", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "enableinsurance",
                        "value" => array("yes"),
                    ),
                ),
				array(
                    "type" => "dropdown",
                    "heading"  => __("Enable PMI", "shmac"),
                    "param_name" => "enablepmi",
                    "description" => __("Enable PMI information in results?", "shmac"),
                    "value" => array(
                        __('Settings Default', 'shmac') => '',
                        __("Disable", "shmac") => "no",
                        __("Enable", "shmac") => "yes",
                    ),
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
                array(
                    "type" => "textfield",
                    "heading"  => __("Monthly PMI", "shmac"),
                    "param_name" => "monthlypmi",
                    "description" => __("If you don't know, leave at 55.00.  This is just an average cost per 100,000 dollars borrowed (e.g. 55.00)", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "enablepmi",
                        "value" => array("yes"),
                    ),
                ),
				array(
                    "type" => "dropdown",
                    "heading"  => __("Enable Taxes", "shmac"),
                    "param_name" => "enabletaxes",
                    "description" => __("Choose whether to give a tax estimate in results?", "shmac"),
                    "value" => array(
                        __('Settings Default', 'shmac') => '',
                        __("Disable", "shmac") => "no",
                        __("Enable", "shmac") => "yes",
                    ),
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
                array(
                    "type" => "textfield",
                    "heading"  => __("Taxes per 1000 assessed", "shmac"),
                    "param_name" => "taxesperthou",
                    "description" => __("The tax rate per 1000 dollars assessed.  If you don't know, leave this at 10.00.  This is just an average cost based on assessed value (e.g. 10.00)", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "enabletaxes",
                        "value" => array("yes"),
                    ),
                ),
				array(
                    "type" => "textarea",
                    "heading"  => __("Disclaimer", "shmac"),
                    "param_name" => "disclaimer",
                    "description" => __("Set the text for your disclaimer that goes at the bottom of the schedule", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textfield",
                    "heading"  => __("Currency Symbol", "shmac"),
                    "param_name" => "currencysymbol",
                    "description" => __("Set the currency symbol for this calculator (e.g. $)", "shmac"),
                    "value" => '',
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "dropdown",
                    "heading"  => __("Currency Format", "shmac"),
                    "param_name" => "currencyformat",
                    "description" => __("Set the currency format for fields and results", "shmac"),
                    "value" => array(
                        __('Settings Default', 'shmac') => '',
                        __("Standard Format (e.g. 100,000.00)", "shmac") => "1",
						__("Switched Format (e.g. 100.000,00)", "shmac") => "2",
						__("Spaces and Comma Format (e.g. 100 000,00)", "shmac") => "3",
                    ),
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "dropdown",
                    "heading"  => __("Currency Symbol Side", "shmac"),
                    "param_name" => "currencyside",
                    "description" => __("Set the side for the currency symbol", "shmac"),
                    "value" => array(
                        __('Settings Default', 'shmac') => '',
                        __("Left (e.g. $100,000.00)", "shmac") => "left",
                        __("Right (e.g. 100,000.00 $)", "shmac") => "right",
                    ),
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),				
				array(
                    "type" => "dropdown",
                    "heading"  => __("Allow Email", "shmac"),
                    "param_name" => "allowemail",
                    "description" => __("Choose to enable email results or not", "shmac"),
                    "value" => array(
                        __('Settings Default', 'shmac') => '',
                        __("Yes", "shmac") => "yes",
                        __("No", "shmac") => "no",
                    ),
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
			 	array(
                    "type" => "textfield",
                    "heading"  => __("BCC Email", "shmac"),
                    "param_name" => "bccemail",
                    "description" => __("Set the email to receive a hidden copy (BCC - Blind Carbon Copy) of the user generated report", "shmac"),
                    "value" => "",
                    "dependency" => array (
                        "element" => "allowemail",
                        "value" => array("yes"),
                    ),
                ),	
				array(
                    "type" => "textfield",
                    "heading"  => __("From Email", "shmac"),
                    "param_name" => "fromemail",
                    "description" => __("Set the 'From Address' for the email sent to the user.  Make sure that your server will allow the email you choose as the from address.", "shmac"),
                    "value" => "",
                    "dependency" => array (
                        "element" => "allowemail",
                        "value" => array("yes"),
                    ),
                ),  
				array(
                    "type" => "textarea",
                    "heading"  => __("Email Subject", "shmac"),
                    "param_name" => "emailsubject",
                    "description" => __("Set the email subject for the user generated report", "shmac"),
                    "value" => "",
                    "dependency" => array (
                        "element" => "allowemail",
                        "value" => array("yes"),
                    ),
                ),  
				array(
                    "type" => "textarea",
                    "heading"  => __("Email Message Content", "shmac"),
                    "param_name" => "emailcontent",
                    "description" => __("Set the email message text for the user generated report", "shmac"),
                    "value" => "",
                    "dependency" => array (
                        "element" => "allowemail",
                        "value" => array("yes"),
                    ),
                ),  
				array(
                    "type" => "colorpicker",
                    "heading"  => __("PDF Color", "shmac"),
                    "param_name" => "pdfcolor",
                    "description" => __("Set you custom primary color for the PDF Results", "shmac"),
                    "value" => "#00bfa5",
                    "dependency" => array (
                        "element" => "allowemail",
                        "value" => array("yes"),
                    ),
                ),  
				array(
                    "type" => "attach_image",
                    "heading" => __("PDF Logo", "shmac"),
                    "param_name" => "pdflogo",
                    "description" => __("Set the image to use for your logo in the generated PDF", "shmac"),
                    "dependency" => array (
                        "element" => "allowemail",
                        "value" => array("yes"),
                    ),
                ),
				array(
                    "type" => "textarea",
                    "heading"  => __("PDF Header", "shmac"),
                    "param_name" => "pdfheader",
                    "description" => __("Large Text at the top of the PDF", "shmac"),
                    "value" => "",
                    "dependency" => array (
                        "element" => "allowemail",
                        "value" => array("yes"),
                    ),
                ),
				// Extras
			    array(
                    "type" => "textfield",
                    "heading"  => __("Submit button text", "shmac"),
                    "param_name" => "calcsubmit",
                    "description" => __("Override the Calculate button text if you want.", "shmac"),
                    "value" => "",
					"dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
				array(
                    "type" => "textfield",
                    "heading"  => __("Reset button text", "shmac"),
                    "param_name" => "calcreset",
                    "description" => __("Override the Reset button text if you want.", "shmac"),
                    "value" => "",
                    "dependency" => array (
                        "element" => "allowoverrides",
                        "value" => array("enable"),
                    ),
                ),
			),
		) );
    }
}  // End Class


new shmacVC();
