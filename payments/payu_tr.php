<?php
/***************************************************************************
*                                                                          *
*   This file is part of the PayU TR Payment Processor for CS Cart.		   *
*   Copyright (c) 2013, Yasin Kuyu @yasinkuyu    						   *
*                                                                          *                                                                         *
* 	PayU/TR Payment Processor for CS-Cart 3.x							   *
*                                                                          *                                                                         *
* 	https://secure.payu.com.tr/docs/alu/			                       *
*                                                                          *
****************************************************************************/

if ( !defined('AREA') ) { die('Access denied'); }

	/*
	Install (README.md)
	*/

	if($mode == 'install') {
		
		// Check if it's already installed
		$is_installed = (boolean) db_get_field(
				"SELECT processor_id FROM ?:payment_processors WHERE processor = 'payu_tr'");
		
		if($is_installed){
			echo "PayU TR is already installed. Please configure it in your store administrator area."; 
			exit;
		}
		
		// It's not installed, register as a payment processor in the database
		
		echo 'Installing PayU TR...';
		
		if(db_query(
			"INSERT INTO ?:payment_processors "
				."(`processor`, `processor_script`, `processor_template`, `admin_template`, `callback`, `type`) "
				."VALUES ('payu_tr', 'payu_tr.php', 'payu_tr.tpl', 'payu_tr.tpl', 'Y', 'P')"
		)){
			
		   echo "Done. Please configure it in your store administrator area."; 
		}
		else {
			echo " a database error has occurred :(";
		}
		
		exit;
	}
	
	/*
	Status Codes
	*/
	$response_codes = array(
		"AUTHORIZED" => "If the payment was authorized",
		"3DS_ENROLLED" => "The payment authorization needs to be confirmed by the Shopper with his BANK using 3DS",
		"ALREADY_AUTHORIZED" => "If the Shopper tries to place a new order with the same ORDER_REF and HASH as a previous one",
		"AUTHORIZATION_FAILED" => "The payment was NOT authorized",
		"INVALID_CUSTOMER_INFO" => "Required data from the Shopper is missing or if malformed",
		"INVALID_PAYMENT_INFO" => "Card data is NOT correct",
		"INVALID_ACCOUNT" => "The Merchant name is NOT correct",
		"INVALID_PAYMENT_METHOD_CODE" => "Payment method code is NOT recognized",
		"INVALID_CURRENCY" => "Payment currency is NOT recognized",
		"REQUEST_EXPIRED" => "If between ORDER_DATE is and payment date has passed more than 10 minutes or more than ORDER_TIMEOUT set by the merchant",
		"HASH_MISMATCH" => "If HASH sent by the Merchant does NOT match the HASH calculated by PayU"
	);	
	
	$payu_return_codes = array(
		"GW_ERROR_GENERIC" => "An error occurred during processing. Please retry the operation",
		"GW_ERROR_GENERIC_3D" => "An error occurred during 3DS processing",
		"GWERROR_-9" => "Error in card expiration date field",
		"GWERROR_-3" => "Call acquirer support call number",
		"GWERROR_-2" => "An error occurred during processing. Please retry the operation",
		"GWERROR_05" => "Authorization declined",
		"GWERROR_08" => "Invalid amount",
		"GWERROR_13" => "Invalid amount",
		"GWERROR_14" => "No such card",
		"GWERROR_15" => "No such card/issuer",
		"GWERROR_19" => "Re-enter transaction",
		"GWERROR_34" => "Credit card number failed the fraud",
		"GWERROR_41" => "Lost card",
		"GWERROR_43" => "Stolen card, pick up",
		"GWERROR_51" => "Yetersiz bakiye",
		"GWERROR_54" => "Expired card",
		"GWERROR_57" => "Transaction not permitted on card",
		"GWERROR_58" => "Not permitted to merchant",
		"GWERROR_61" => "Exceeds amount limit",
		"GWERROR_62" => "Restricted card",
		"GWERROR_65" => "Exceeds frequency limit",
		"GWERROR_75" => "PIN tries exceeded",
		"GWERROR_82" => "Time-out at issuer",
		"GWERROR_84" => "Invalid cvv",
		"GWERROR_91" => "A technical problem occurred. Issuer cannot process",
		"GWERROR_96" => "System malfunction",
		"GWERROR_2204" => "No permission to process the card installment.",
		"GWERROR_2304" => "There is an ongoing process your order.",
		"GWERROR_5007" => "Debit cards only supports 3D operations.",
		"ALREADY_AUTHORIZED" => "Re-enter transaction",
		"NEW_ERROR" => "Message flow error",
		"WRONG_ERROR" => "Re-enter transaction",
		"-9999" => "Banned operation",
		"1" => "Call acquirer support call number"
	);
	
	/*
	 PayU Settings
	*/
	$secretKey 			= $processor_data['params']['secure_hash']; // PayU Key coding
	$merchant 			= $processor_data['params']['gid']; 		// PayU Vendor code
	$test_mode			= $processor_data['params']['mode']; 		// test or live

	/*
	 Order
	*/
	$order_id 			= $order_info['order_id'];
	$order_date 		= gmdate('Y-m-d H:i:s');

	/*
	 Customer Cart Info
	*/
	$card 				= $paypal_card_types[$order_info['payment_info']['card']];
	$card_number 		= $order_info['payment_info']['card_number'];
	$card_exp_month		= $order_info['payment_info']['expiry_month'];
	$card_exp_year 		= '20' . $order_info['payment_info']['expiry_year'];
	$card_cvv2 			= !empty($order_info['payment_info']['cvv2']) ? $order_info['payment_info']['cvv2'] : '';
	$card_name 			= $order_info['firstname'] . ' ' . $order_info['lastname'];
	$installment		= $order_info['payment_info']['installment'];

	/*
	 Global
	*/
	$payment_url 		= "https://secure.payu.com.tr/order/alu.php";
	
	// ToDo 3D secure
	$payment_url_3d 	= "/3ds_return.php";
	
	$client_ip			= $_SERVER['REMOTE_ADDR'];
	$pp_response 		= array();
	
	$arParams = array(
		"MERCHANT" => $merchant,
		"ORDER_REF" => rand(1000,9999),
		"ORDER_DATE" => $order_date,

		"ORDER_PNAME[0]" => "Test ürünler",
		"ORDER_PCODE[0]" => "Kod135",
		"ORDER_PINFO[0]" => "Test ürünü açıklamalar",
		"ORDER_PRICE[0]" => $order_info['total'], // => 0.00
		"ORDER_QTY[0]" => "1",		
		
		"PRICES_CURRENCY" => "TRY",
		"PAY_METHOD" => "CCVISAMC",
		"SELECTED_INSTALLMENTS_NUMBER" => $installment,
		"CC_NUMBER" => $card_number,
		"EXP_MONTH" => $card_exp_month,
		"EXP_YEAR" => $card_exp_year,
		"CC_CVV" => $card_cvv2,
		"CC_OWNER" => $card_name,
		 
		"BACK_REF" => $payment_url_3d,
		"CLIENT_IP" => $client_ip,
		"BILL_LNAME" => $order_info['lastname'],
		"BILL_FNAME" => $order_info['firstname'],
		"BILL_EMAIL" => $order_info['email'],
		"BILL_PHONE" => $order_info['phone'],
		"BILL_COUNTRYCODE" => "TR",
		"BILL_ZIPCODE" => $order_info['b_zipcode'], //optional
		"BILL_ADDRESS" => $order_info['b_address'], //optional
		"BILL_ADDRESS2"=> $order_info['b_address_2'], //optional
		"BILL_CITY" => $order_info['b_country'], //optional
		"BILL_STATE" => $order_info['b_state'], //optional
		"BILL_FAX" => $order_info['fax'], //optional
		
		"DELIVERY_LNAME" => $order_info['s_lastname'], //optional
		"DELIVERY_FNAME" => $order_info['s_firstname'], //optional
		"DELIVERY_EMAIL" => $order_info['email'], //optional
		"DELIVERY_PHONE" => $order_info['phone'], //optional
		"DELIVERY_COMPANY" => "", //optional
		"DELIVERY_ADDRESS" => $order_info['s_address'], //optional
		"DELIVERY_ADDRESS2" => $order_info['s_address_2'], //optional
		"DELIVERY_ZIPCODE" => $order_info['s_zipcode'], //optional
		"DELIVERY_CITY" => $order_info['s_country'], //optional
		"DELIVERY_STATE" => $order_info['s_state'], //optional
		"DELIVERY_COUNTRYCODE" => "TR", //optional
	);
		 
	//begin HASH calculation
	ksort($arParams);
	 
	$hashString = "";
	 
	foreach ($arParams as $key=>$val) {
		$hashString .= strlen($val) . $val;
	}
	 
	$arParams["ORDER_HASH"] = hash_hmac("md5", $hashString, $secretKey);
	 
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $payment_url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arParams));
	$response = curl_exec($ch);
	 
	$curlerrcode = curl_errno($ch);
	$curlerr = curl_error($ch);
	 
	if (empty($curlerr) && empty($curlerrcode)) {

		$parsedXML = @simplexml_load_string($response);
		
		if ($parsedXML !== FALSE) {
								
			$returnCode = $parsedXML->RETURN_CODE;

			switch ($parsedXML->STATUS){
				
				case "SUCCESS" :

					if (($parsedXML->RETURN_CODE == "3DS_ENROLLED") && (!empty($parsedXML->URL_3DS))) {
						//ToDo 3D secure
						//header("Location:" . $parsedXML->URL_3DS);
						//die();
						$pp_response['order_status'] = 'F';
						$pp_response['reason_text'] = "3D secure not support";
					}
					
					$pp_response['order_status'] = 'P';
					$pp_response['transaction_id'] = $parsedXML->REFNO;
					
					break;
			 
				case "INPUT_ERROR" :
				
					$pp_response['order_status'] = 'F';

					if($test_mode=="test")
					{
						$pp_response['reason_text'] = $response;
					}
					else
					{
						$pp_response['reason_text'] = $parsedXML->RETURN_MESSAGE;
					}
					
					break;
			
				case "FAILED" :
				
					$pp_response['order_status'] = 'F';

					if($test_mode=="test")
					{
						$pp_response['reason_text'] = $response;
					}
					else
					{
						$pp_response['reason_text'] = $parsedXML->RETURN_MESSAGE;
					}
					
					break;
			
				default:
						
					$pp_response['order_status'] = 'F';

					if($test_mode=="test")
					{
						$pp_response['reason_text'] = $response;
					}
					else
					{
						$pp_response['reason_text'] = $parsedXML->RETURN_MESSAGE;
					}
					break;

			}
		}
		
	} else {
		$pp_response['order_status'] = 'F';
		fn_set_notification('E', fn_get_lang_var('error'), "cURL error: " . $curlerr);
	}
	
?>