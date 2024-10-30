<?php
/*
Plugin Name: miniOrange SAML Cloud SSO 
Plugin URI: http://miniorange.com/
Description: Single Sign-On into 4500+ cloud applications using SAML 2.0
Version: 5.0.4
Author: miniOrange
Author URI: http://miniorange.com/
*/


include_once dirname( __FILE__ ) . '/mo_login_saml_sso_widget.php';
require('mo-saml-class-customer.php');
require('mo_saml_settings_page.php');
class saml_mo_login {
	
	function __construct() {
		add_action( 'admin_menu', array( $this, 'miniorange_sso_menu' ) );
		add_action( 'admin_init', array( $this, 'miniorange_login_widget_saml_save_settings' ) );		
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_style' ) );
		register_deactivation_hook(__FILE__, array( $this, 'mo_sso_saml_deactivate'));	
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_script' ) );	
		remove_action( 'admin_notices', array( $this, 'mo_saml_success_message') );
		remove_action( 'admin_notices', array( $this, 'mo_saml_error_message') );
		add_action('login_form', array( $this, 'mo_saml_modify_login_form' ) );
		add_shortcode( 'MO_SAML_FORM', array($this, 'mo_get_saml_shortcode') );
		add_option( 'mo_saml_enable_cloud_broker',true);
		add_filter('upgrader_post_install', array($this, 'mo_saml_plugin_update'), 10, 3);
	}

	function mo_saml_plugin_update( $response, $options, $result){
		update_option('mo_saml_cloud_update', 'true');
		if(isset($result['destination_name']) and strpos($result['destination_name'], 'miniorange-saml-cloud') !== false){
			$sp_base_url = get_option( 'mo_saml_sp_base_url' );
			if ( empty( $sp_base_url ) ) {
				$sp_base_url = site_url();
			}
			$sp_entity_id = get_option('mo_saml_cloud');
			if(empty($sp_entity_id)) {
				$sp_entity_id = $sp_base_url.'/wp-content/plugins/miniorange-saml-cloud/';
			}
			update_option('mo_saml_cloud', $sp_entity_id);
		}
	}
	
	function  mo_login_widget_saml_options () {
		global $wpdb;
		update_option( 'mo_saml_host_name', 'https://login.xecurify.com' );
		$host_name = get_option('mo_saml_host_name');
		
		$brokerService = get_option('mo_saml_enable_cloud_broker');
		$token = get_option('saml_x509_certificate');
		
		update_option('mo_saml_enable_cloud_broker', 'true');
		mo_register_saml_sso();
	}
	
	function mo_saml_success_message() {
		$class = "error";
		$message = get_option('mo_saml_message');
		echo "<div class='" . $class . "'> <p>" . $message . "</p></div>";
	}

	function mo_saml_error_message() {
		$class = "updated";
		$message = get_option('mo_saml_message');
		echo "<div class='" . $class . "'> <p>" . $message . "</p></div>";
	}
		
	public function mo_sso_saml_deactivate() {
		if(!is_multisite()) {
			
			delete_option('mo_saml_host_name');
			delete_option('mo_saml_new_registration');
			delete_option('mo_saml_admin_phone');
			delete_option('mo_saml_admin_password');
			delete_option('mo_saml_verify_customer');
			delete_option('mo_saml_admin_customer_key');
			delete_option('mo_saml_admin_api_key');
			delete_option('mo_saml_customer_token');
			delete_option('mo_saml_message');
			delete_option('mo_saml_registration_status');		
			delete_option('mo_saml_idp_config_complete');
			delete_option('mo_saml_transactionId');
		} else {
			global $wpdb;
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			$original_blog_id = get_current_blog_id();
			
			foreach ( $blog_ids as $blog_id )
			{
				switch_to_blog( $blog_id );
				delete_option('mo_saml_host_name');
				delete_option('mo_saml_new_registration');
				delete_option('mo_saml_admin_phone');
				delete_option('mo_saml_admin_password');
				delete_option('mo_saml_verify_customer');
				delete_option('mo_saml_admin_customer_key');
				delete_option('mo_saml_admin_api_key');
				delete_option('mo_saml_customer_token');
				delete_option('mo_saml_message');
				delete_option('mo_saml_registration_status');
				delete_option('mo_saml_idp_config_complete');
				delete_option('mo_saml_transactionId');
			}
			switch_to_blog( $original_blog_id );
		}
	}	
	
	private function mo_saml_show_success_message() {
		remove_action( 'admin_notices', array( $this, 'mo_saml_success_message') );
		add_action( 'admin_notices', array( $this, 'mo_saml_error_message') );
	}
	function mo_saml_show_error_message() {
		remove_action( 'admin_notices', array( $this, 'mo_saml_error_message') );
		add_action( 'admin_notices', array( $this, 'mo_saml_success_message') );
	}
	function plugin_settings_style() {
		wp_enqueue_style( 'mo_saml_admin_settings_style', plugins_url( 'includes/css/style_settings.css?ver=3.7', __FILE__ ) );
		wp_enqueue_style( 'mo_saml_admin_settings_phone_style', plugins_url( 'includes/css/phone.css', __FILE__ ) );
	}
	function plugin_settings_script() {
		wp_enqueue_script( 'mo_saml_admin_settings_script', plugins_url( 'includes/js/settings.js', __FILE__ ) );
		wp_enqueue_script( 'mo_saml_admin_settings_phone_script', plugins_url('includes/js/phone.js', __FILE__ ) );
	}
	function miniorange_login_widget_saml_save_settings(){
		if ( current_user_can( 'manage_options' )){ 
			
		if(isset($_POST['option']) and $_POST['option'] == "login_widget_saml_save_settings"){
			if(!mo_saml_is_curl_installed()) {
				update_option( 'mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Save Identity Provider Configuration failed.');
				$this->mo_saml_show_error_message();
				return;
			}
			
			$saml_identity_name = '';
			$saml_login_url = '';
			$saml_issuer = '';
			$saml_x509_certificate = '';
			if( $this->mo_saml_check_empty_or_null( $_POST['saml_identity_name'] ) || $this->mo_saml_check_empty_or_null( $_POST['saml_login_url'] ) || $this->mo_saml_check_empty_or_null( $_POST['saml_issuer'] )  ) {
				update_option( 'mo_saml_message', 'All the fields are required. Please enter valid entries.');
				$this->mo_saml_show_error_message();
				return;
			} else if(!preg_match("/^\w*$/", $_POST['saml_identity_name'])) {
				update_option( 'mo_saml_message', 'Please match the requested format for Identity Provider Name. Only alphabets, numbers and underscore is allowed.');
				$this->mo_saml_show_error_message();
				return;
			} else{
				$saml_identity_name = trim( $_POST['saml_identity_name'] );
				$saml_login_url = trim( $_POST['saml_login_url'] );
				$saml_issuer = trim( $_POST['saml_issuer'] );
				$saml_x509_certificate = trim( $_POST['saml_x509_certificate'] );
			}
			
			update_option('saml_identity_name', $saml_identity_name);
			update_option('saml_login_url', $saml_login_url);
			update_option('saml_issuer', $saml_issuer);
			update_option('saml_x509_certificate', $saml_x509_certificate);	
			if(isset($_POST['saml_response_signed']))
				{
				update_option('saml_response_signed' , 'checked');
				}
			else
				{
				update_option('saml_response_signed' , 'Yes');
				}
			if(isset($_POST['saml_assertion_signed']))
				{
				update_option('saml_assertion_signed' , 'checked');
				}
			else
				{
				update_option('saml_assertion_signed' , 'Yes');
				}
			
			$saveSaml = new Customersaml();
			$outputSaml = json_decode( $saveSaml->save_external_idp_config(), true );

			if(isset($outputSaml['customerId'])) {
				update_option('saml_x509_certificate', $outputSaml['samlX509Certificate']);
				update_option('mo_saml_message', 'Identity Provider details saved successfully.');
				$this->mo_saml_show_success_message();
			}
			else {
				update_option('mo_saml_message', 'Identity Provider details could not be saved. Please try again.');
				$this->mo_saml_show_error_message();
			}
		}
		
		if(isset($_POST['option']) and $_POST['option'] == "login_widget_saml_attribute_mapping"){
			
			if(!mo_saml_is_curl_installed()) {
				update_option( 'mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Save Attribute Mapping failed.');
				$this->mo_saml_show_error_message();
				return;
			}
		
			update_option('saml_am_first_name', stripslashes($_POST['saml_am_first_name']));
			update_option('saml_am_last_name', stripslashes($_POST['saml_am_last_name']));
			update_option('saml_am_account_matcher', stripslashes($_POST['saml_am_account_matcher']));
			update_option('mo_saml_message', 'Attribute Mapping details saved successfully');
			$this->mo_saml_show_success_message();
		
		}
		
		if(isset($_POST['option']) and $_POST['option'] == "login_widget_saml_role_mapping"){
			
			if(!mo_saml_is_curl_installed()) {
				update_option( 'mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Save Role Mapping failed.');
				$this->mo_saml_show_error_message();
				return;
			}
		
			update_option('saml_am_default_user_role', $_POST['saml_am_default_user_role']);
			update_option('mo_saml_message', 'Role Mapping details saved successfully.');
			$this->mo_saml_show_success_message();
		}
		
		if( isset( $_POST['option'] ) and $_POST['option'] == "mo_saml_register_customer" ) {	//register the admin to miniOrange
		
			if(!mo_saml_is_curl_installed()) {
				update_option( 'mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Registration failed.');
				$this->mo_saml_show_error_message();
				return;
			}
			
			$email = '';
			$company = '';
			$first_name = '';
			$last_name = '';
			$phone = '';
			$password = '';
			$confirmPassword = '';
			if( $this->mo_saml_check_empty_or_null( $_POST['email'] ) || $this->mo_saml_check_empty_or_null( $_POST['password'] ) || $this->mo_saml_check_empty_or_null( $_POST['confirmPassword'] ) || $this->mo_saml_check_empty_or_null( $_POST['company'] )) {
				update_option( 'mo_saml_message', 'Please enter the required fields.');
				$this->mo_saml_show_error_message();
				return;
			} else if( strlen( $_POST['password'] ) < 6 || strlen( $_POST['confirmPassword'] ) < 6){
				update_option( 'mo_saml_message', 'Choose a password with minimum length 6.');
				$this->mo_saml_show_error_message();
				return;
			} else{
				$email = sanitize_email( $_POST['email'] );
				$company = sanitize_text_field( $_POST['company'] );
				$first_name = sanitize_text_field( $_POST['first_name'] );
				$last_name = sanitize_text_field( $_POST['last_name'] );
				$phone = sanitize_text_field( $_POST['phone'] );
				$password = sanitize_text_field( $_POST['password'] );
				$confirmPassword = sanitize_text_field( $_POST['confirmPassword'] );
			}
			update_option( 'mo_saml_admin_email', $email );
			update_option( 'mo_saml_admin_phone', $phone );
			update_option( 'mo_saml_admin_company', $company );
			update_option( 'mo_saml_admin_first_name', $first_name );
			update_option( 'mo_saml_admin_last_name', $last_name );
			if( strcmp( $password, $confirmPassword) == 0 ) {
				update_option( 'mo_saml_admin_password', $password );
				$email = get_option('mo_saml_admin_email');
				$customer = new CustomerSaml();
				$content = json_decode($customer->check_customer(), true);
				if( strcasecmp( $content['status'], 'CUSTOMER_NOT_FOUND') == 0 ){
					$content = json_decode($customer->send_otp_token($email, ''), true);
					if(strcasecmp($content['status'], 'SUCCESS') == 0) {
						update_option( 'mo_saml_message', ' A one time passcode is sent to ' . get_option('mo_saml_admin_email') . '. Please enter the otp here to verify your email.');
						update_option('mo_saml_transactionId',$content['txId']);
						update_option('mo_saml_registration_status','MO_OTP_DELIVERED_SUCCESS_EMAIL');
						$this->mo_saml_show_success_message();
					}else{
						update_option('mo_saml_message','There was an error in sending email. Please verify your email and try again.');
						update_option('mo_saml_registration_status','MO_OTP_DELIVERED_FAILURE_EMAIL');
						$this->mo_saml_show_error_message();
					}
				}else{
					$this->get_current_customer();
				}
				
			} else {
				update_option( 'mo_saml_message', 'Passwords do not match.');
				delete_option('mo_saml_verify_customer');
				$this->mo_saml_show_error_message();
			}
	
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_saml_validate_otp"){
			
			if(!mo_saml_is_curl_installed()) {
				update_option( 'mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Validate OTP failed.');
				$this->mo_saml_show_error_message();
				return;
			}

			$otp_token = '';
			if( $this->mo_saml_check_empty_or_null( $_POST['otp_token'] ) ) {
				update_option( 'mo_saml_message', 'Please enter a value in otp field.');
				$this->mo_saml_show_error_message();
				return;
			} else{
				$otp_token = sanitize_text_field( $_POST['otp_token'] );
			}

			$customer = new CustomerSaml();
			$content = json_decode($customer->validate_otp_token(get_option('mo_saml_transactionId'), $otp_token ),true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) {

					$this->create_customer();
			}else{
				update_option( 'mo_saml_message','Invalid one time passcode. Please enter a valid otp.');
				$this->mo_saml_show_error_message();
			}
		}
		if( isset( $_POST['option'] ) and $_POST['option'] == "mo_saml_verify_customer" ) {	
		
			if(!mo_saml_is_curl_installed()) {
				update_option( 'mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Login failed.');
				$this->mo_saml_show_error_message();
				return;
			}
			
			$email = '';
			$password = '';
			if( $this->mo_saml_check_empty_or_null( $_POST['email'] ) || $this->mo_saml_check_empty_or_null( $_POST['password'] ) ) {
				update_option( 'mo_saml_message', 'All the fields are required. Please enter valid entries.');
				$this->mo_saml_show_error_message();
				return;
			} else{
				$email = sanitize_email( $_POST['email'] );
				$password = sanitize_text_field( $_POST['password'] );
			}
		
			update_option( 'mo_saml_admin_email', $email );
			update_option( 'mo_saml_admin_password', $password );
			$customer = new Customersaml();
			$content = $customer->get_customer_key();
			$customerKey = json_decode( $content, true );
			if( json_last_error() == JSON_ERROR_NONE ) {
				update_option( 'mo_saml_admin_customer_key', $customerKey['id'] );
				update_option( 'mo_saml_admin_api_key', $customerKey['apiKey'] );
				update_option( 'mo_saml_customer_token', $customerKey['token'] );
				update_option( 'mo_saml_admin_phone', $customerKey['phone'] );
				$certificate = get_option('saml_x509_certificate');
				
				update_option('mo_saml_admin_password', '');
				update_option( 'mo_saml_message', 'Customer retrieved successfully');
				update_option('mo_saml_registration_status' , 'Existing User');
				delete_option('mo_saml_verify_customer');
				$this->mo_saml_show_success_message(); 
			} else {
				update_option( 'mo_saml_message', 'Invalid username or password. Please try again.');
				$this->mo_saml_show_error_message();		
			}
			update_option('mo_saml_admin_password', '');
		}else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_saml_contact_us_query_option" ) {
			
			if(!mo_saml_is_curl_installed()) {
				update_option( 'mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Query submit failed.');
				$this->mo_saml_show_error_message();
				return;
			}
			
			$email = $_POST['mo_saml_contact_us_email'];
			$phone = $_POST['mo_saml_contact_us_phone'];
			$query = $_POST['mo_saml_contact_us_query'];
			$customer = new CustomerSaml();
			if ( $this->mo_saml_check_empty_or_null( $email ) || $this->mo_saml_check_empty_or_null( $query ) ) {
				update_option('mo_saml_message', 'Please fill up Email and Query fields to submit your query.');
				$this->mo_saml_show_error_message();
			} else {
				$submited = $customer->submit_contact_us( $email, $phone, $query );
				if ( $submited == false ) {
					update_option('mo_saml_message', 'Your query could not be submitted. Please try again.');
					$this->mo_saml_show_error_message();
				} else {
					update_option('mo_saml_message', 'Thanks for getting in touch! We shall get back to you shortly.');
					$this->mo_saml_show_success_message();
				}
			}
		}
		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_saml_resend_otp_email" ) {
			
			if(!mo_saml_is_curl_installed()) {
				update_option( 'mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Resend OTP failed.');
				$this->mo_saml_show_error_message();
				return;
			}
			$email = get_option ( 'mo_saml_admin_email' );
		    $customer = new CustomerSaml();
			$content = json_decode($customer->send_otp_token($email, ''), true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) {
					update_option( 'mo_saml_message', ' A one time passcode is sent to ' . get_option('mo_saml_admin_email') . ' again. Please check if you got the otp and enter it here.');
					update_option('mo_saml_transactionId',$content['txId']);
					update_option('mo_saml_registration_status','MO_OTP_DELIVERED_SUCCESS_EMAIL');
					$this->mo_saml_show_success_message();
			}else{
					update_option('mo_saml_message','There was an error in sending email. Please click on Resend OTP to try again.');
					update_option('mo_saml_registration_status','MO_OTP_DELIVERED_FAILURE_EMAIL');
					$this->mo_saml_show_error_message();
			}
		} else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_saml_resend_otp_phone" ) {
			
			if(!mo_saml_is_curl_installed()) {
				update_option( 'mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Resend OTP failed.');
				$this->mo_saml_show_error_message();
				return;
			}
			$phone = get_option('mo_saml_admin_phone');
		    $customer = new CustomerSaml();
			$content = json_decode($customer->send_otp_token('', $phone, FALSE, TRUE), true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) {
					update_option( 'mo_saml_message', ' A one time passcode is sent to ' . $phone . ' again. Please check if you got the otp and enter it here.');
					update_option('mo_saml_transactionId',$content['txId']);
					update_option('mo_saml_registration_status','MO_OTP_DELIVERED_SUCCESS_PHONE');
					$this->mo_saml_show_success_message();
			}else{
					update_option('mo_saml_message','There was an error in sending email. Please click on Resend OTP to try again.');
					update_option('mo_saml_registration_status','MO_OTP_DELIVERED_FAILURE_PHONE');
					$this->mo_saml_show_error_message();
			}
		} 
		else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_saml_go_back" ){
				update_option('mo_saml_registration_status','');
				update_option('mo_saml_verify_customer', '');
				delete_option('mo_saml_new_registration');
				delete_option('mo_saml_admin_email');
				delete_option('mo_saml_admin_phone');
		} else if( isset( $_POST['option'] ) and $_POST['option'] == "mo_saml_register_with_phone_option" ) {
			if(!mo_saml_is_curl_installed()) {
				update_option( 'mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Resend OTP failed.');
				$this->mo_saml_show_error_message();
				return;
			}
			$phone = sanitize_text_field($_POST['phone']);
			$phone = str_replace(' ', '', $phone);
			$phone = str_replace('-', '', $phone);
			update_option('mo_saml_admin_phone', $phone);
			$customer = new CustomerSaml();
			$content = json_decode($customer->send_otp_token('', $phone, FALSE, TRUE), true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) {
				update_option( 'mo_saml_message', ' A one time passcode is sent to ' . get_option('mo_saml_admin_phone') . '. Please enter the otp here to verify your email.');
				update_option('mo_saml_transactionId',$content['txId']);
				update_option('mo_saml_registration_status','MO_OTP_DELIVERED_SUCCESS_PHONE');
				$this->mo_saml_show_success_message();
			}else{
				update_option('mo_saml_message','There was an error in sending SMS. Please click on Resend OTP to try again.');
				update_option('mo_saml_registration_status','MO_OTP_DELIVERED_FAILURE_PHONE');
				$this->mo_saml_show_error_message();
			}
		} 
		else if( isset( $_POST['option']) and $_POST['option'] == "mo_saml_force_authentication_option") {
			if(mo_saml_is_sp_configured()) {
				if(array_key_exists('mo_saml_force_authentication', $_POST)) {
					$enable_redirect = $_POST['mo_saml_force_authentication'];
				} else {
					$enable_redirect = 'false';
				}				
				if($enable_redirect == 'true') {
					update_option('mo_saml_force_authentication', 'true');
				} else {
					update_option('mo_saml_force_authentication', '');
				}
				update_option( 'mo_saml_message', 'Sign in options updated.');
				$this->mo_saml_show_success_message();
			} else {
				update_option( 'mo_saml_message', 'Please complete <a href="' . add_query_arg( array('tab' => 'save'), $_SERVER['REQUEST_URI'] ) . '" />Service Provider</a> configuration first.');
				$this->mo_saml_show_error_message();
			}
		}else if(isset($_POST['option']) && $_POST['option'] == 'mo_saml_forgot_password_form_option'){
			if(!mo_saml_is_curl_installed()) {
				update_option( 'mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Resend OTP failed.');
				$this->mo_saml_show_error_message();
				return;
			}
			
			$email = get_option('mo_saml_admin_email');
			
			$customer = new Customersaml();
			$content = json_decode($customer->mo_saml_forgot_password($email),true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0){
				update_option( 'mo_saml_message','Your password has been reset successfully. Please enter the new password sent to ' . $email . '.');
				$this->mo_saml_show_success_message();
			}else{
				update_option( 'mo_saml_message','An error occured while processing your request. Please Try again.');
				$this->mo_saml_show_error_message();
			}
		}
		}
	}
	
	function create_customer(){
		$customer = new CustomerSaml();
		$customerKey = json_decode( $customer->create_customer(), true );
		if( strcasecmp( $customerKey['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS') == 0 ) {
					$this->get_current_customer();
		} else if( strcasecmp( $customerKey['status'], 'SUCCESS' ) == 0 ) {
			update_option( 'mo_saml_admin_customer_key', $customerKey['id'] );
			update_option( 'mo_saml_admin_api_key', $customerKey['apiKey'] );
			update_option( 'mo_saml_customer_token', $customerKey['token'] );
			update_option('mo_saml_admin_password', '');
			update_option( 'mo_saml_message', 'Thank you for registering with miniorange.');
			update_option('mo_saml_registration_status','');
			delete_option('mo_saml_verify_customer');
			delete_option('mo_saml_new_registration');
			$this->mo_saml_show_success_message();
			wp_redirect(admin_url().'admin.php?page=mo_saml_settings&tab=licensing');
		}
		update_option('mo_saml_admin_password', '');
	}

	function get_current_customer(){
		$customer = new CustomerSaml();
		$content = $customer->get_customer_key();
		$customerKey = json_decode( $content, true );
		if( json_last_error() == JSON_ERROR_NONE ) {
			update_option( 'mo_saml_admin_customer_key', $customerKey['id'] );
			update_option( 'mo_saml_admin_api_key', $customerKey['apiKey'] );
			update_option( 'mo_saml_customer_token', $customerKey['token'] );
			update_option('mo_saml_admin_password', '' );
			$certificate = get_option('saml_x509_certificate');
	
			update_option( 'mo_saml_message', 'Your account has been retrieved successfully.' );
			delete_option('mo_saml_verify_customer');
			delete_option('mo_saml_new_registration');
			$this->mo_saml_show_success_message();
			wp_redirect(admin_url().'admin.php?page=mo_saml_settings&tab=licensing');
		} else {
			update_option( 'mo_saml_message', 'You already have an account with miniOrange. Please enter a valid password.');
			update_option('mo_saml_verify_customer', 'true');
			delete_option('mo_saml_new_registration');
			$this->mo_saml_show_error_message();
		}
	}

	public function mo_saml_check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}
	
	function miniorange_sso_menu() {
		$page = add_menu_page( 'MO SAML Settings ' . __( 'Configure SAML Identity Provider for SSO', 'mo_saml_settings' ), 'miniOrange SAML Cloud SSO', 'administrator', 'mo_saml_settings', array( $this, 'mo_login_widget_saml_options' ), plugin_dir_url(__FILE__) . 'images/miniorange.png' );
	}

	
	function mo_saml_redirect_for_authentication( $relay_state ) {
		
			$mo_redirect_url = get_option('mo_saml_host_name') . "/moas/rest/saml/request?id=" . get_option('mo_saml_admin_customer_key') . "&returnurl=" . urlencode( site_url() . "/?option=readsamllogin&redirect_to=" . urlencode ($relay_state) );
			header('Location: ' . $mo_redirect_url);
			exit();
	
	}
	
	function mo_saml_modify_login_form() {
		echo '<input type="hidden" name="saml_sso" value="false">'."\n";
	}
	
	function mo_get_saml_shortcode(){
		if(!is_user_logged_in()){
			if(mo_saml_is_sp_configured()){
				
				$html =	"<a href=".get_option('mo_saml_host_name')."/moas/rest/saml/request?id=".get_option('mo_saml_admin_customer_key')."&returnurl=".urlencode( site_url() . '/?option=readsamllogin' )." />Login with ".get_option('saml_identity_name')."</a>";
			}else
				$html = 'SP is not configured.';
		}
		else
			$html = 'Hello, '.wp_get_current_user()->display_name.' | <a href='.wp_logout_url(site_url()).'>Logout</a>';
		return $html;
	}
}
new saml_mo_login;