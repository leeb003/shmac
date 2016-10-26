<?php
/**
 * SHMAC Class for ajax requests
 */

    class shmac_ajax {
        // properties
		protected $shmac_settings;
		protected $shmac_email;

        // methods
		public function __construct() {
			require_once( SHMAC_ROOT_PATH . '/includes/class-shmac-options.php' );
            $options = new shmac_options();
            $this->shmac_settings = $options->shmac_settings;
            $this->shmac_email = $options->shmac_email;
		}

        /**
         * Frontend handle request
         **/
        public function myajax_shmacfrontend_callback() {
			$response = array();
			// nonce and logged in check
			$nonce = isset($_POST['nextNonce']) ? $_POST['nextNonce'] : '';
			if (!wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
				$response['bad_nonce'] = 'true';
				header( "Content-Type: application/json" );
            	echo json_encode($response);
				wp_die();
			}

			// Calculator process results
			if (isset($_POST['process']) && $_POST['process'] == 'true') {

				// Details enabled response & others
                // Overrides reset shmac_settings values to the override value
                if (isset($_POST['override']['enableinsurance']) ) {
                    $response['vals']['enable_insurance'] = esc_html($_POST['override']['enableinsurance']);
                    $this->shmac_settings['enable_insurance'] = esc_html($_POST['override']['enableinsurance']);
                } else {
                    $response['vals']['enable_insurance'] = $this->shmac_settings['enable_insurance'];
                }
                if (isset($_POST['override']['enablepmi'])) {
                    $response['vals']['enable_pmi'] = esc_html($_POST['override']['enablepmi']);
                    $this->shmac_settings['enable_pmi'] = esc_html($_POST['override']['enablepmi']);
                } else {
                    $response['vals']['enable_pmi'] = $this->shmac_settings['enable_pmi'];
                }
                if (isset($_POST['override']['enabletaxes'])) {
                    $response['vals']['enable_taxes'] = esc_html($_POST['override']['enabletaxes']);
                    $this->shmac_settings['enable_taxes'] = esc_html($_POST['override']['enabletaxes']);
                } else {
                    $response['vals']['enable_taxes'] = $this->shmac_settings['enable_taxes'];
                }
				if (isset($_POST['override']['insuranceamountpercent'])) {
					$this->shmac_settings['insurance_amount_percent'] = esc_html($_POST['override']['insuranceamountpercent']);
				}
				if (isset($_POST['override']['monthlyinsurance'])) {
					$this->shmac_settings['insurance'] = floatval($_POST['override']['monthlyinsurance']);
				}
				if (isset($_POST['override']['monthlypmi'])) {
                    $this->shmac_settings['pmi'] = esc_html($_POST['override']['monthlypmi']);
                }
				if (isset($_POST['override']['taxesperthou'])) {
                    $this->shmac_settings['tax_rate'] = floatval($_POST['override']['taxesperthou']);
                }
				if (isset($_POST['override']['disclaimer'])) {
					$this->shmac_settings['disclaimer'] = wp_kses_post($_POST['override']['disclaimer']);
				}
				if (isset($_POST['override']['currencysymbol'])) {
					$this->shmac_settings['currency'] = esc_html($_POST['override']['currencysymbol']);
				}
				if (isset($_POST['override']['currencyside'])) {
					$this->shmac_settings['currency_side'] = esc_html($_POST['override']['currencyside']);
				}
				if (isset($_POST['override']['currencyformat'])) {
                    $this->shmac_settings['currency_format'] = intval($_POST['override']['currencyformat']);
                }
				if (isset($_POST['override']['downpaytype'])) {
                    $this->shmac_settings['down_payment_type'] = esc_html($_POST['override']['downpaytype']);
                }
				// mail settings overrides  
				if (isset($_POST['override']['bccemail'])) {
					$this->shmac_email['email_bcc'] = sanitize_email($_POST['override']['bccemail']);
				}	
				if (isset($_POST['override']['fromemail'])) {
					$this->shmac_email['email_from'] = sanitize_email($_POST['override']['fromemail']);
				}
				if (isset($_POST['override']['emailsubject'])) {
					$this->shmac_email['email_subject'] = esc_html($_POST['override']['emailsubject']);
				}
				if (isset($_POST['override']['emailcontent'])) {
					$this->shmac_email['email_text'] = wp_kses_post($_POST['override']['emailcontent']);
				}
				if (isset($_POST['override']['pdfcolor'])) {
					$this->shmac_email['pdf_color'] = esc_html($_POST['override']['pdfcolor']);
				}
				if (isset($_POST['override']['pdflogo'])) {
					$this->shmac_email['pdf_logo_url'] = $_POST['override']['pdflogo'];
				}
				if (isset($_POST['override']['pdfheader'])) {
					$this->shmac_email['pdf_header'] = wp_kses_post($_POST['override']['pdfheader']);
				}
					
				

				$response['headers']['payment'] = __('Payment', 'shmac');
				$response['headers']['payment_amount'] = __('Payment Amt', 'shmac');
				$response['headers']['interest'] = __('Interest', 'shmac');
				$response['headers']['total_interest'] = __('Total Interest', 'shmac');
				$response['headers']['principal'] = __('Principal', 'shmac');
				$response['headers']['balance'] = __('Balance', 'shmac');
				$response['headers']['loan_text'] = __('Loan Details', 'shmac');
				$response['headers']['schedule_text'] = __('Amortization Schedule (P & I)', 'shmac');

				$response['details']['original'] = __('Original Loan Amount', 'shmac');
            	$response['details']['down_payment'] = __('Down Payment', 'shmac');
            	$response['details']['interest'] = __('Interest', 'shmac');
            	$response['details']['term'] = __('Term', 'shmac');
            	$response['details']['loan_after_down'] = __('Loan After Down Payment', 'shmac');
            	$response['details']['down_payment_amount'] = __('Down Payment Amount', 'shmac');
            	$response['details']['monthly_payment'] = __('Monthly Payment (P & I)', 'shmac');
            	$response['details']['total_payments'] = __('Total Payments', 'shmac');
				$response['details']['disclaimer'] = $this->shmac_settings['disclaimer'];

				$response['vals']['shmac_root'] = SHMAC_ROOT_URL;

				$ten_dollars      = $this->format_amount('10.00');
				$thousand_dollars = $this->format_amount('1000.00');
				$price            = $this->getAmount($_POST['amount']);
        		$interest         = floatval($_POST['interest']);

				/* down payment percent or amount */
				if (isset($this->shmac_settings['down_payment_type']) 
					&& $this->shmac_settings['down_payment_type'] == 'amount') { // dollar amount
						$moneydown  = $this->getAmount($_POST['downpay']);
						$down = floatval( ($moneydown / $price) * 100);
						$down = round($down, 2);
				} else {														 // percent
        				$down  = $_POST['downpay'];
						$moneydown = $price * ($down / 100);
				}

        		$term             = intval($_POST['term']);
				$termcycle        = $_POST['termcycle'];
				$years_text       = __('Years', 'shmac');
				$months_text      = __('Months', 'shmac');
				if ($termcycle == 'months') {
					$cycle_text = $months_text;
				} else {
					$cycle_text = $years_text;
					if ($term > 50) {  // limit years to 50 max
						$term = 50;
					}
				}
				$response['vals']['cycle_text'] = $cycle_text;
				// $response['vals']['years_text'] = $years_text;

				$response['vals']['price'] = $price;
				// $response['vals']['price2'] = $this->format_amount($price);
				$price2 = $this->format_amount($price);
				$response['vals']['interest'] = $interest;
				$response['vals']['down'] = $down;
				$response['vals']['term'] = $term;

        		if ($price == 0) {
            		$response['error'] = 1;
					$response['error_field'] = 'amount';
            		$response['message'] = __('Please Enter a valid amount.', 'shmac');
        		} elseif ($interest == 0) {
            		$response['error'] = 1;
					$response['error_field'] = 'interest';
            		$response['message'] = __('Please Enter a valid interest rate.', 'shmac');
        		} elseif (!is_numeric($down)) {
            		$response['error'] = 1;
					$response['error_field'] = 'down';
            		$response['message'] = __('Please Enter a down payment (can be 0).', 'shmac');
        		} elseif ( $term == 0) {
            		$response['error'] = 1;
					$response['error_field'] = 'term';
            		$response['message'] = __('Term cannot be empty.', 'shmac');
        		}

				if (!isset($response['error'])) {          // If no errors, continue

        			$years = $term;


        			//////////////////////////////////////////
        			// P & I
        			// $moneydown = $price * ($down / 100); // figured with down_payment_type now
        			$moneydown2 = $this->format_amount($moneydown);
					//$response['vals']['moneydown2'] = $moneydown2;
        			$mortgage = $price - $moneydown;
        			$mortgage2 = $this->format_amount($mortgage);
					//$response['vals']['mortgage2'] = $mortgage2;
        			$month_interest = ($interest / (12 * 100));

        			//echo "Monthly Interest is (interest / (12 * 100)) = $month_interest<br>";
					if ($termcycle == 'months') {
						$months = $term;
					} else {
        				$months = $term * 12;
					}
        			//echo "Total Months for loan is (term x 12) = $months<br>"; 

        			$monthly_payment = $mortgage * ($month_interest / (1 - pow((1 + $month_interest), -$months) ));

        			$monthly_payment2 = $this->format_amount($monthly_payment);

					// adjust and set currency responses
					if ($this->shmac_settings['currency_side'] == 'right') {	
						$response['vals']['monthly_payment2'] = $monthly_payment2 . ' ' . $this->shmac_settings['currency'];
						$response['vals']['price2']           = $price2           . ' ' . $this->shmac_settings['currency'];
                        $response['vals']['moneydown2']       = $moneydown2       . ' ' . $this->shmac_settings['currency'];
                        $response['vals']['mortgage2']        = $mortgage2        . ' ' . $this->shmac_settings['currency'];
					} else {
						$response['vals']['monthly_payment2'] = $this->shmac_settings['currency'] . $monthly_payment2;
						$response['vals']['price2']           = $this->shmac_settings['currency'] . $price2;
                        $response['vals']['moneydown2']       = $this->shmac_settings['currency'] . $moneydown2;
                        $response['vals']['mortgage2']        = $this->shmac_settings['currency'] . $mortgage2;
					}
        			////////////////////////////////////
        			// PMI and Taxes

					// Check first if at least one of these is set for reporting
					if ($this->shmac_settings['enable_insurance'] == 'yes'
						|| $this->shmac_settings['enable_pmi'] == 'yes'
						|| $this->shmac_settings['enable_taxes'] == 'yes' 
					) {
						$pmi_monthly = 0;
						$taxes_monthly = 0;
						$insurance_monthly = 0;

						$response['vals']['otherfactors'] = __('Since Principal and Interest are not the only factors of a loan we should include an estimate for other costs involved with a loan.', 'shmac');
					} else {
						$response['vals']['otherfactors'] = '';
					}

					// check if tax is enabled
					if ($this->shmac_settings['enable_taxes'] == 'yes') {
						$tax_rate                     = floatval($this->shmac_settings['tax_rate']);
						$assessed                     = ($price * 0.85);
                    	$taxes_monthly                = (($assessed / 1000) * $tax_rate) / 12;
                    	$assessed2                    = $this->format_amount($assessed);
						$taxes2                       = $this->format_amount($taxes_monthly);

						if ($this->shmac_settings['currency_side'] == 'right') {
							$assessed2_display        = $assessed2 . ' ' . $this->shmac_settings['currency'];
							$taxes2_display           = $taxes2 . ' ' . $this->shmac_settings['currency'];
							$tax_rate_display      = $this->format_amount($tax_rate) . ' ' . $this->shmac_settings['currency'];
							$thousand_dollars_display = $thousand_dollars . ' ' . $this->shmac_settings['currency'];;
						} else {
							$assessed2_display        = $this->shmac_settings['currency'] . $assessed2;
							$taxes2_display           = $this->shmac_settings['currency'] . $taxes2;
							$tax_rate_display      = $this->shmac_settings['currency'] . $this->format_amount($tax_rate);
							$thousand_dollars_display = $this->shmac_settings['currency'] . $thousand_dollars;
						}

						$response['vals']['tax_text'] = __('An average tax figure for your purchase might be about', 'shmac')
							. ' ' . $tax_rate_display . __(' for every', 'shmac') 
							. ' ' . $thousand_dollars_display . __(' assessed value per year.', 'shmac');
						$response['vals']['tax_text'] .= '  ' . __("If the assessed value of your home is 85%, this would make your home's assessed value", 'shmac') . ' ' . $assessed2_display . __(' and your monthly tax around', 'shmac') . ' ' . $taxes2_display;
					} else {
						$response['vals']['tax_text'] = '';
					}

					// check if pmi is enabled
					if ($this->shmac_settings['enable_pmi'] == 'yes') {

        				if ($down < 20) {
							$pmi_formatted = $this->format_amount($this->shmac_settings['pmi']);
							$hundredthou = $this->format_amount(100000.00);

							$pmi_monthly = ($mortgage / 100000) * floatval($this->shmac_settings['pmi']);
                            $pmi2        = $this->format_amount($pmi_monthly);

							if ($this->shmac_settings['currency_side'] == 'right') {
								$pmi_display         = $pmi_formatted . ' ' . $this->shmac_settings['currency'];
								$hundredthou_display = $hundredthou . ' ' . $this->shmac_settings['currency'];
								$pmi2_display        = $pmi2 . ' ' . $this->shmac_settings['currency'];
							} else {
								$pmi_display         = $this->shmac_settings['currency'] . $pmi_formatted;
								$hundredthou_display = $this->shmac_settings['currency'] . $hundredthou;
								$pmi2_display        = $this->shmac_settings['currency'] . $pmi2;
							}

							$response['vals']['pmi_text'] =  __('Your down payment was less than 20% of the loan, which means you will be paying PMI. This averages around', 'shmac') . ' ' . $pmi_display . ' ' .  __('for every', 'shmac') . ' ' 
								. $hundredthou_display . __(' borrowed.', 'shmac');


							$response['vals']['pmi_text'] .= '  ' . __('An estimate for PMI will be around', 'shmac') . ' ' 
								.  $pmi2_display  . ' ' . __('per month.', 'shmac');

        				} else {
							$response['vals']['pmi_text'] = __('Since you are putting down 20% or more of the loan, you will not have to pay PMI.', 'shmac');
        				}
					} else {
						$response['vals']['pmi_text'] = '';
					}

					// check if insurance is enabled
					if ($this->shmac_settings['enable_insurance'] == 'yes') {
						// Check if it's amount per month or percent based
						if ($this->shmac_settings['insurance_amount_percent'] == 'percent') {
							$insurance_percent = floatval($this->shmac_settings['insurance']) / 100;
							$insurance = ($mortgage * $insurance_percent) / 12;
						} else {
							$insurance = $this->shmac_settings['insurance'];
						}
						$insurance_format = $this->format_amount($insurance);
						if ($this->shmac_settings['currency_side'] == 'right') {
							$insurance_display = $insurance_format . ' ' . $this->shmac_settings['currency'];
						} else {
							$insurance_display = $this->shmac_settings['currency'] . $insurance_format;
						}

						$response['vals']['insurance_text'] = __('Homeowners Insurance is another factor of a loan.  An average estimate of your monthly insurance could be about', 'shmac') . ' ' . $insurance_display;
						$insurance_monthly = floatval($insurance);

					} else {
						$response['vals']['insurance_text'] = '';
					}

					// Check first if at least one of these is set for reporting
                    if ($this->shmac_settings['enable_insurance'] == 'yes'
                        || $this->shmac_settings['enable_pmi'] == 'yes'
                        || $this->shmac_settings['enable_taxes'] == 'yes'
                    ) {
						$total_payment = $monthly_payment + $taxes_monthly + $pmi_monthly + $insurance_monthly;
						$total_payment_formatted = $this->format_amount($total_payment);
						if ($this->shmac_settings['currency_side'] == 'right') {
							$total_payment_display = '<b>' . $total_payment_formatted . '</b>' 
								. ' ' . $this->shmac_settings['currency'];
						} else {
							$total_payment_display = $this->shmac_settings['currency'] . '<b>' . $total_payment_formatted . '</b>';
						}
						
						$response['vals']['total_monthlies'] = __('With these factors, your total monthly payment estimate would be 
around', 'shmac') . ' ' . $total_payment_display;
					} else {
						$response['vals']['total_monthlies'] = '';
					}

        			/////////////////////////////////////
        			// Amortization Schedule
        			$i = 0;
        			$total_interest = 0;
        			$new_mortgage = $mortgage;
					if ($termcycle == 'months') {   // if this is monthly 
						$month_range = $term;
					} else {                        // if this is yearly
						$years = $term;
        				$month_range = ($years * 12);
					}
        			$month_range2 = range (1, $month_range);
					$response['vals']['total_payments'] = $month_range;

        			foreach ($month_range2 as $value)  {
            			$int_amt         = $month_interest * $new_mortgage;
            			$principal       = $monthly_payment - $int_amt;
            			$new_mortgage    = $new_mortgage - $principal;
            			//$total_principal = $total_principal + $principal;
            			$total_interest  = $total_interest + $int_amt;
            			///////////////////////////////////////////////
            			//formatting
            			$int_amt2        = $this->format_amount($int_amt);
            			$principal2      = $this->format_amount($principal);
            			$new_mortgage2   = $this->format_amount($new_mortgage);
            			$total_interest2 = $this->format_amount($total_interest);
            			$i++;
            			/////////////////////
						if ($this->shmac_settings['currency_side'] == 'right') {
                    		$principal_display      = $principal2 . ' ' . $this->shmac_settings['currency'];
                    		$interest_display       = $int_amt2 . ' ' . $this->shmac_settings['currency'];
                    		$total_interest_display = $total_interest2 . ' ' . $this->shmac_settings['currency'];
                    		$new_mortgage_display   = $new_mortgage2 . ' ' . $this->shmac_settings['currency'];
                		} else {
                    		$principal_display      = $this->shmac_settings['currency'] . $principal2;
                    		$interest_display       = $this->shmac_settings['currency'] . $int_amt2;
                    		$total_interest_display = $this->shmac_settings['currency'] . $total_interest2;
                    		$new_mortgage_display   = $this->shmac_settings['currency'] . $new_mortgage2;
                		}						
						// Responses
            			$response['payment'][$i]['value']          = $value;
            			$response['payment'][$i]['interest']       = $interest_display;
						$response['payment'][$i]['total_interest'] = $total_interest_display;
            			$response['payment'][$i]['principal']      = $principal_display;
            			$response['payment'][$i]['newMortgage']    = $new_mortgage_display;
        			}

        			if ($_POST['sendemail'] == 'true') {
             			if (isset($_POST['mailaddress'])) {

                			// test the email address given
                			if (!filter_var($_POST['mailaddress'], FILTER_VALIDATE_EMAIL)) {
                    			$response['error'] = 1;
								$response['error_field'] = 'email';
                    			$response['message'] = __('Please enter a valid email address.', 'shmac');
                			} else {

                				// Generate PDF and mail it
								$date = strtotime('now');
                                $date = date("m-d-Y", $date);   // date for file printout

								// Get the pdf file, attach it and then delete after mail
                                $upload_dir = wp_upload_dir();
								$pdf_file =  $upload_dir['basedir'] . '/shmac_pdf/' . "amortization-".$date.".pdf";
                				$pdfResults = $this->generatePDF($response, $pdf_file);

                				// Send Email //
                				$eol = PHP_EOL;
								$attachments = array( $pdf_file );
                				$to = $_POST['mailaddress'];

                				$seperator = md5(time());
                				$headers = '';

                				if ($this->shmac_email['email_bcc'] != '') { // bcc the owner (simple empty check
                    				$headers .= "Bcc: " . $this->shmac_email['email_bcc'] . $eol;
                				}
                				$headers .= 'From: ' . $this->shmac_email['email_from'] . $eol;

                				$subject = $this->shmac_email['email_subject'];
                				$message = nl2br($this->shmac_email['email_text']) . "\n\n";

								$msg = $message.$eol;

								add_filter( 'wp_mail_from_name', array(&$this, 'custom_wp_mail_from_name') ); // set name to email
								add_filter( 'wp_mail_content_type', array(&$this, 'set_html_content_type' ) );
                				wp_mail($to, $subject, $msg, $headers, $attachments);
								//mail($to, $subject, $msg, $headers);
								$response['valid'] = 'true';
								// Reset content-type to avoid conflicts 
								// -- https://core.trac.wordpress.org/ticket/23578
								remove_filter( 'wp_mail_content_type', array(&$this, 'set_html_content_type' ) );
								unlink($pdf_file); // remove file when complete
							}
						}
					}
				}  // End No Input Errors
			}  // End process results

			header( "Content-Type: application/json" );
			echo json_encode($response);
            wp_die();
        }

		/**
		 * Custom from name reset to email address instead of default 'WordPress'
		 *
		 */
		public function custom_wp_mail_from_name() {
			return $this->shmac_email['email_from'];
		}

		/**
         * Set message to html
         */
		public function set_html_content_type() {
			return 'text/html';
		}

		/**
		 * Backend Ajax 
		 * @since 1.1.0
		 */
		public function myajax_shmacbackend_callback() {
			$response['message'] = __('Not used at this time.', 'shmac');
			header( "Content-Type: application/json" );
            echo json_encode($response);
            wp_die();


		} // End Backend Ajax

		// PDF export of mortgage results using the tcpdf lib
		public function generatePDF($response, $pdf_file) {

    		$today = date("l F j, Y g:i a");

    		//require_once SHMAC_ROOT_PATH . '/includes/tcpdfi_min/config/lang/eng.php';
    		require_once SHMAC_ROOT_PATH . '/includes/tcpdf_min/tcpdf.php';
			require_once SHMAC_ROOT_PATH . '/includes/tcpdf_custom.php';

            // create new PDF document
            //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			// http://stackoverflow.com/questions/5333702/tcpdf-utf-8-lithuanian-symbols-not-showing-up
			//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false); 
			$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor(esc_url(home_url( '/') ) );
            $pdf->SetTitle('Mortgage Schedule');
            $pdf->SetSubject("Loan Details");
            $pdf->SetKeywords('Loan, Mortgage, Amortization');

			// No header
			$pdf->SetPrintHeader(false);
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            //set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            //set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            //set image scale factor
            // $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			$pdf->SetImageScale(1.53);

            //set some language-dependent strings
            // ---------------------------------------------------------

            // set default font subsetting mode
            //$pdf->setFontSubsetting(true);
			// save resources
			$pdf->setFontSubsetting(false);

            // Set font
            // dejavusans is a UTF-8 Unicode font, if you only need to
            // print standard ASCII chars, you can use core fonts like
            // helvetica or times to reduce file size.

			// Override set text direction RTL
			if (isset($this->shmac_email['ltr_rtl']) && $this->shmac_email['ltr_rtl'] == 'rtl') {  
				$pdf->setRTL(true);
			}

			// Override PDF Font used if set
            if (isset($this->shmac_email['pdf_font']) ) {
				$pdf->SetFont($this->shmac_email['pdf_font'], '', 8, '', true);
            } else {
            	$pdf->SetFont('helvetica', '', 8, '', true);
			}

            // Add a page
            // This method has several options, check the source code documentation for more information.
            $pdf->AddPage();

			$html = '<style>
                  	td.alt {
                       background-color: #eeeeee;
                      }
                      .details td {
                        border: 1px solid #cccccc;
                      }
                      .schedule td {
                       border-bottom: 1px solid #eeeeee;
                      }
                      .border-bottom {
                        border-bottom: 1px solid #cccccc;
                      }
					  .align-center {
						text-align:center;
					  }
                   </style>';

			// Logo
			if (isset($this->shmac_email['pdf_logo_url'])) {  // Override set logo url
				// check for image post id from visual composer
				if (preg_match('/^\d+$/', $this->shmac_email['pdf_logo_url'], $matches)) {
					$logo_id = $this->shmac_email['pdf_logo_url'];
                	$logo_url = wp_get_attachment_url($logo_id);
				} else {
					$logo_url = $this->shmac_email['pdf_logo_url'];
				}
			} else {    // settings set logo
        		$logo_id = isset($this->shmac_email['logo_attachment_id']) ? $this->shmac_email['logo_attachment_id'] : '';
				$logo_url = wp_get_attachment_url($logo_id);
			}

        	if ($logo_url) {
				$html .= '<div style="text-align:center">'
					  . '<img src="' . $logo_url . '" border="0" />'
					  . '</div>';
			}

			// Title
			$html .= '<div class="border-bottom align-center">'
				   . '<h1 style="color:' . $this->shmac_email['pdf_color'] . '";>' 
				   . $this->shmac_email['pdf_header'] . '</h1>';

            $html .= '<h3>' . __('Brought to you by', 'shmac') . ' <a href="' . esc_url(home_url( '/') ) . '" style="color:' . $this->shmac_email['pdf_color'] . ';">' 
				   . get_bloginfo() . '</a></h3>'
				   . '</div>';

			$html .= '<br />';

			// Details
			$html .= '<p></p>';
			$html .= '<h3>' . $response['headers']['loan_text'] . '</h3>'
				   . '<table class="details" cellpadding="5">'
				   . '<tr><td>' . $response['details']['original'] . ': <br /><b>' . $response['vals']['price2'] . '</b></td>'
				   . '<td>' . $response['details']['down_payment'] . ': <br /><b>' . $response['vals']['down'] . ' %</b></td>'
				   . '<td>' . $response['details']['interest'] . ': <br /><b>' . $response['vals']['interest'] . ' %</b></td>'
				   . '<td>' . $response['details']['term'] . ': <br /><b>' . $response['vals']['term'] . ' ' 
				   . $response['vals']['cycle_text'] . '</b></td>'
				   . '</tr>'
				   . '<tr><td>' . $response['details']['loan_after_down'] . ': <br /><b>' . $response['vals']['mortgage2'] 
				   . '</b></td>'
				   . '<td>' . $response['details']['down_payment_amount'] . ': <br /><b>' . $response['vals']['moneydown2'] 
				   . '</b></td>'
				   . '<td>' . $response['details']['monthly_payment'] . ': <br /><b>' . $response['vals']['monthly_payment2'] 
				   . '</b></td>'
				   . '<td>' . $response['details']['total_payments'] . ': <br /><b>' . $response['vals']['total_payments'] 
				   . '</b></td>' . '</tr>'
				   . '</table>';

			// PMI, Taxes & Insurance
			// check for at least one being set to report
			if ($this->shmac_settings['enable_insurance'] == 'yes'
            	|| $this->shmac_settings['enable_pmi'] == 'yes'
                || $this->shmac_settings['enable_taxes'] == 'yes' 
			) {
				$html .= '<p>' . $response['vals']['otherfactors'] . '</p>';
				$html .= '<ul style="font-size:8pt;list-style-type:img|svg|3|3|' . SHMAC_ROOT_URL . '/assets/img/info.svg">';
				// check for pmi enabled
				if ($this->shmac_settings['enable_pmi'] == 'yes') {
					$html .= '<li>' . $response['vals']['pmi_text'] . '</li>';
				}
				// check for taxes enabled
				if ($this->shmac_settings['enable_taxes'] == 'yes') {
					$html .= '<li>' . $response['vals']['tax_text'] . '</li>';
				}
				// check for insurance enabled
				if ($this->shmac_settings['enable_insurance'] == 'yes') {
					$html .= '<li>' . $response['vals']['insurance_text'] . '</li>';
				}
				$html .= '</ul>';
				$html .= '<p class="border-p">' . $response['vals']['total_monthlies'] . '</p>';
				$html .= '<p></p>';
			} else {
				$html .= '<p></p>';
			}

			// Schedule
            $html .= '<h3>' . $response['headers']['schedule_text'] . '</h3>'
                   . '<table class="schedule" cellpadding="5">'
                   . '<thead><tr style="color:' . $this->shmac_email['pdf_color'] . ';">'
				   . '<th style="text-align:right;"><b>' . $response['headers']['payment'] . '</b></th>'
				   . '<th style="text-align:right;"><b>' . $response['headers']['payment_amount'] . '</b></th>'
				   . '<th style="text-align:right;"><b>' . $response['headers']['principal'] . '</b></th>'
				   . '<th style="text-align:right;"><b>' . $response['headers']['interest'] . '</b></th>'
				   . '<th style="text-align:right;"><b>' . $response['headers']['total_interest'] . '</b></th>'
                   . '<th style="text-align:right;"><b>' . $response['headers']['balance'] . '</b></th>'
				   . '</tr></thead>';

			$html .= '<tbody>';
			$inc = 0;
			// Amortization Schedule
            foreach ($response['payment'] as $key => $val) {
			$inc++;
				if ($inc & 1) {
					$class = ' class="alt"';
				} else {
					$class = '';
				}
                $html .= '<tr><td ' . $class . ' style="text-align:right;">' . $val['value'] 
					  . '</td><td ' . $class . ' style="text-align:right;">' . $response['vals']['monthly_payment2']
					  . '</td><td ' . $class . ' style="text-align:right;">' . $val['principal']
					  . '</td><td ' . $class . ' style="text-align:right;">' . $val['interest']
					  . '</td><td ' . $class . ' style="text-align:right;">' . $val['total_interest']
					  . '</td><td ' . $class . ' style="text-align:right;">' . $val['newMortgage']
					  . '</td></tr>';
            }
            $html .= '</tbody></table>'
				   . '<p></p><p style="text-align:center;"><small>' . $response['details']['disclaimer'] . '</small></p>';

            // Print text using writeHTMLCell()
            $pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, 
				$reseth=true, $align='', $autopadding=true);

            // ---------------------------------------------------------

			// write it to a file for attaching
            $upload_dir = wp_upload_dir();
            // upload directory folder creation
            $shmac_dir = $upload_dir['basedir'] . '/shmac_pdf';
            if ( !file_exists($shmac_dir) ) {
                wp_mkdir_p( $shmac_dir );
            }

            // Close and output PDF document
            // This method has several options, check the source code documentation for more information.
            //$pdfString = $pdf->Output('amort.pdf', 'S');

            //return $pdfString;

			$pdf->Output($pdf_file, 'F');
            $pdfCreated = true;
            return $pdfCreated;

        }

		/*
		 * Convert money formats to floats for different currency formats
         *
         */
		public function getAmount($money) {
    		$cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
    		$onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);

    		$separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

    		$stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
    		$removedThousendSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '',  $stringWithCommaOrDot);

    		return (float) str_replace(',', '.', $removedThousendSeparator);
		}

		/*
		 * Money Format for different selections in settings
		 *
		 * Format 1 - standard (100,000.00)
		 * Format 2 - flipped (100.000,00)
		 * Format 3 - Spaced (100 000,00)
		 */
		public function format_amount($amount) {
			$format = isset($this->shmac_settings['currency_format']) ? $this->shmac_settings['currency_format'] : '1';
			if ($format == '1') {
				$formatted = number_format($amount, 2);
			} elseif ($format == '2') {
				$formatted = number_format($amount, 2, ',', '.');
			} elseif ($format == '3') {
				$formatted = number_format($amount, 2, ',', ' ');
			}
			return $formatted;
		}

	} // end class
