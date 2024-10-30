<?php
function mo_register_saml_sso() {
	if( isset( $_GET[ 'tab' ] ) ) {
		$active_tab = $_GET[ 'tab' ];
	} else if(mo_saml_is_customer_registered_saml() && mo_saml_is_sp_configured()) {
		$active_tab = 'general';
	} else if(mo_saml_is_customer_registered_saml()) {
		$active_tab = 'config';
	} else {
		$active_tab = 'login';
	}
	?>
	<?php
		if(!mo_saml_is_curl_installed()) {
			?>
			<p><font color="#FF0000">(Warning: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled)</font></p>
			<?php
		}
		
		if(!mo_saml_is_openssl_installed()) {
			?>
			<p><font color="#FF0000">(Warning: <a href="http://php.net/manual/en/openssl.installation.php" target="_blank">PHP openssl extension</a> is not installed or disabled)</font></p>
			<?php
		}
		
	?>
<div id="mo_saml_settings">
	<div class="miniorange_container">
	<table style="width:100%;">
		<tr>
			<h2 class="nav-tab-wrapper">
				<?php if(!mo_saml_is_customer_registered_saml()) {?>
				<a class="nav-tab <?php echo $active_tab == 'login' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'login'), $_SERVER['REQUEST_URI'] ); ?>">Account Setup</a>
				<?php }?>
				<a class="nav-tab <?php echo $active_tab == 'config' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'config'), $_SERVER['REQUEST_URI'] ); ?>">Identity Provider</a>
				<a class="nav-tab <?php echo $active_tab == 'save' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'save'), $_SERVER['REQUEST_URI'] ); ?>">Service Provider</a>
				<?php if(mo_saml_is_customer_registered_saml()) {?>
				<a class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'general'), $_SERVER['REQUEST_URI'] ); ?>">Sign in Settings</a>
				<?php }?>
				<a class="nav-tab <?php echo $active_tab == 'opt' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'opt'), $_SERVER['REQUEST_URI'] ); ?>">Attribute/Role Mapping</a>
				<a class="nav-tab <?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'help'), $_SERVER['REQUEST_URI'] ); ?>">Help & FAQ</a>
				<a class="nav-tab <?php echo $active_tab == 'licensing' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'licensing'), $_SERVER['REQUEST_URI'] ); ?>">Licensing Plans</a>
			</h2>
			<td style="vertical-align:top;width:65%;">
			<?php
				if($active_tab == 'save') {
					mo_saml_apps_config_saml();
				} else if($active_tab == 'opt') {
					mo_saml_save_optional_config();
				} else if($active_tab == 'help') {
					mo_saml_save_plugin_config();
				} else if($active_tab == 'config'){
					mo_saml_configuration_steps();
				} else if($active_tab == 'general'){
					mo_saml_general_login_page();
				} else if($active_tab == 'licensing'){
					mo_saml_show_pricing_page();
				}else {
					if (get_option ( 'mo_saml_verify_customer' ) == 'true') {
						mo_saml_show_verify_password_page_saml();
					} else if (trim ( get_option ( 'mo_saml_admin_email' ) ) != '' && trim ( get_option ( 'mo_saml_admin_api_key' ) ) == '' && get_option ( 'mo_saml_new_registration' ) != 'true') {
						mo_saml_show_verify_password_page_saml();
					}else if(get_option('mo_saml_registration_status') == 'MO_OTP_DELIVERED_SUCCESS_EMAIL' || get_option('mo_saml_registration_status') == 'MO_OTP_DELIVERED_SUCCESS_PHONE' || get_option('mo_saml_registration_status') == 'MO_OTP_VALIDATION_FAILURE_EMAIL' || get_option('mo_saml_registration_status') == 'MO_OTP_VALIDATION_FAILURE_PHONE' || get_option('mo_saml_registration_status') == 'MO_OTP_DELIVERED_FAILURE' ){
						mo_saml_show_otp_verification();
					}	else if (! mo_saml_is_customer_registered_saml()) {
						delete_option ( 'password_mismatch' );
						mo_saml_show_new_registration_page_saml();
					} else {
						mo_saml_general_login_page();
					}
				}
			?>
			</td>
			<td style="vertical-align:top;padding-left:1%;">
				<?php if(!($active_tab == 'licensing')) {echo miniorange_support_saml(); }?>
			</td>
		</tr>
	</table>
	</div>
			
<?php			
}

function mo_saml_is_curl_installed() {
    if  (in_array  ('curl', get_loaded_extensions())) {
        return 1;
    } else 
        return 0;
}
function mo_saml_is_openssl_installed() {
	if  (in_array  ('openssl', get_loaded_extensions())) {
		return 1;
	} else
		return 0;
}


function mo_saml_show_new_registration_page_saml() {
	update_option ( 'mo_saml_new_registration', 'true' );
	$user = wp_get_current_user();
	?>
			<!--Register with miniOrange-->
		<form name="f" method="post" action="">
			<input type="hidden" name="option" value="mo_saml_register_customer" />
			<div class="mo_saml_table_layout">
				<div id="toggle1" class="panel_toggle">
					<h3>Register with miniOrange</h3>
				</div>
				<div id="panel1">
					<p><a href="#" id="help_register_link">[ Why should I register? ]</a></p>
					<div hidden id="help_register_desc" class="mo_saml_help_desc">
						You should register so that in case you need help, we can help you with step by step instructions. We support all known IdPs - <b>ADFS, Okta, Salesforce, Shibboleth, SimpleSAMLphp, OpenAM, Centrify, Ping, RSA, IBM, Oracle, OneLogin, Bitium, WSO2 etc</b>.
					</div>
					</p>
					<table class="mo_saml_settings_table">
						<tr>
							<td><b><font color="#FF0000">*</font>Email:</b></td>
							<td><input class="mo_saml_table_textbox" type="email" name="email"
								required placeholder="person@example.com"
								value="<?php echo (get_option('mo_saml_admin_email') == '') ? get_option('admin_email') : get_option('mo_saml_admin_email');?>" /></td>
						</tr>
						<tr>
							<td><b><font color="#FF0000">*</font>Company/Organisation:</b></td>
							<td><input class="mo_saml_table_textbox" type="text" name="company"
								required placeholder="Your company name"
								value="<?php echo (get_option('mo_saml_admin_company') == '') ? site_url() : get_option('mo_saml_admin_company');?>" /></td>
						</tr>
						<tr>
							<td><b>First Name:</b></td>
							<td><input class="mo_saml_table_textbox" type="text" name="first_name"
								placeholder="First Name"
								value="<?php echo (get_option('mo_saml_admin_first_name') == '') ? $user->first_name : get_option('mo_saml_admin_first_name');?>" /></td>
						</tr>
						<tr>
							<td><b>Last Name:</b></td>
							<td><input class="mo_saml_table_textbox" type="text" name="last_name"
								placeholder="Last Name"
								value="<?php echo (get_option('mo_saml_admin_last_name') == '') ? $user->last_name : get_option('mo_saml_admin_last_name');?>" /></td>
						</tr>
						<tr>
							<td><b>Phone number:</b></td>
							<td><input class="mo_saml_table_textbox" type="tel" id="phone_contact" style="width:80%;"
								pattern="[\+]\d{11,14}|[\+]\d{1,4}([\s]{0,1})(\d{0}|\d{9,10})" class="mo_saml_table_textbox" name="phone" 
								title="Phone with country code eg. +1xxxxxxxxxx"
								placeholder="Phone with country code eg. +1xxxxxxxxxx"
								value="<?php echo get_option('mo_saml_admin_phone');?>" /></td>
						</tr>
							<tr>
								<td></td>
								<td>We will call only if you need support.</td>
							</tr> 
						<tr>
							<td><b><font color="#FF0000">*</font>Password:</b></td>
							<td><input class="mo_saml_table_textbox" required type="password"
								name="password" placeholder="Choose your password (Min. length 6)" /></td>
						</tr>
						<tr>
							<td><b><font color="#FF0000">*</font>Confirm Password:</b></td>
							<td><input class="mo_saml_table_textbox" required type="password"
								name="confirmPassword" placeholder="Confirm your password" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><br><input type="submit" name="submit" value="Register"
								class="button button-primary button-large" /></td>
						</tr>
					</table>
				</div>
			</div>
		</form>
		<?php
}
function mo_saml_show_verify_password_page_saml() {
	?>
			<!--Verify password with miniOrange-->
		<form name="f" method="post" action="">
			<input type="hidden" name="option" value="mo_saml_verify_customer" />
			<div class="mo_saml_table_layout">
				<div id="toggle1" class="panel_toggle">
					<h3>Login with miniOrange</h3>
				</div>
				<div id="panel1">
					<p><b>It seems you already have an account with miniOrange. Please enter your miniOrange email and password.<br/> <a href="#mo_saml_forgot_password_link">Click here if you forgot your password?</a></b></p>
					<br/>
					<table class="mo_saml_settings_table">
						<tr>
							<td><b><font color="#FF0000">*</font>Email:</b></td>
							<td><input class="mo_saml_table_textbox" type="email" name="email"
								required placeholder="person@example.com"
								value="<?php echo get_option('mo_saml_admin_email');?>" /></td>
						</tr>
						<tr>
						<td><b><font color="#FF0000">*</font>Password:</b></td>
						<td><input class="mo_saml_table_textbox" required type="password"
							name="password" placeholder="Choose your password" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
							<input type="submit" name="submit"
								class="button button-primary button-large" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<input type="button" name="mo_saml_goback" id="mo_saml_goback" value="Back" class="button button-primary button-large" />
						</tr>
					</table>
				</div>
			</div>
		</form>
		<form name="f" method="post" action="" id="mo_saml_goback_form">
				<input type="hidden" name="option" value="mo_saml_go_back"/>
		</form>
		<form name="f" method="post" action="" id="mo_saml_forgotpassword_form">
				<input type="hidden" name="option" value="mo_saml_forgot_password_form_option"/>
		</form>
		<script>
			jQuery('#mo_saml_goback').click(function(){
				jQuery('#mo_saml_goback_form').submit();
			});
			jQuery("a[href=\"#mo_saml_forgot_password_link\"]").click(function(){
				jQuery('#mo_saml_forgotpassword_form').submit();
			});
		</script>
		<?php
}

function mo_saml_show_otp_verification(){
	?>
		<!-- Enter otp -->
		<form name="f" method="post" id="otp_form" action="">
			<input type="hidden" name="option" value="mo_saml_validate_otp" />
			<div class="mo_saml_table_layout">
				<table class="mo_saml_settings_table">
					<h3>Verify Your Email</h3>
					<tr>
						<td><b><font color="#FF0000">*</font>Enter OTP:</b></td>
						<td colspan="2"><input class="mo_saml_table_textbox" autofocus="true" type="text" name="otp_token" required placeholder="Enter OTP" style="width:61%;" />
						 &nbsp;&nbsp;<a style="cursor:pointer;" onclick="document.getElementById('resend_otp_form').submit();">Resend OTP</a></td>
					</tr>
					<tr><td colspan="3"></td></tr>
					<tr>

						<td>&nbsp;</td>
						<td style="width:17%">
						<input type="submit" name="submit" value="Validate OTP" class="button button-primary button-large" /></td>

		</form>
		<form name="f" method="post">
						<td style="width:18%">
							<input type="hidden" name="option" value="mo_saml_go_back"/>
							<input type="submit" name="submit"  value="Back" class="button button-primary button-large" />
						</td>
		</form>
		<form name="f" id="resend_otp_form" method="post" action="">
						<td>
			<?php if(get_option('mo_saml_registration_status') == 'MO_OTP_DELIVERED_SUCCESS_EMAIL' || get_option('mo_saml_registration_status') == 'MO_OTP_VALIDATION_FAILURE_EMAIL') { ?>
				<input type="hidden" name="option" value="mo_saml_resend_otp_email"/>
			<?php } else { ?>
				<input type="hidden" name="option" value="mo_saml_resend_otp_phone"/>
			<?php } ?>
						</td>

		</form>
		</tr>
			</table>
		<?php if(get_option('mo_saml_registration_status') == 'MO_OTP_DELIVERED_SUCCESS_EMAIL' || get_option('mo_saml_registration_status') == 'MO_OTP_VALIDATION_FAILURE_EMAIL') { ?>
			<hr>

				<h3>I did not recieve any email with OTP . What should I do ?</h3>
				<form id="mo_saml_register_with_phone_form" method="post" action="">
					<input type="hidden" name="option" value="mo_saml_register_with_phone_option" />
					 If you can't see the email from miniOrange in your mails, please check your <b>SPAM</b> folder. If you don't see an email even in the SPAM folder, verify your identity with our alternate method.
					 <br><br>
						<b>Enter your valid phone number here and verify your identity using one time passcode sent to your phone.</b><br><br>
						<input class="mo_saml_table_textbox" type="tel" id="phone_contact" style="width:40%;"
								pattern="[\+]\d{11,14}|[\+]\d{1,4}([\s]{0,1})(\d{0}|\d{9,10})" class="mo_saml_table_textbox" name="phone" 
								title="Phone with country code eg. +1xxxxxxxxxx" required
								placeholder="Phone with country code eg. +1xxxxxxxxxx"
								value="<?php echo get_option('mo_saml_admin_phone');?>" />
						<br /><br /><input type="submit" value="Send OTP" class="button button-primary button-large" />
				
				</form>
		<?php } ?>
	</div>
	
<?php
}

function mo_saml_general_login_page() {
	?>
		<?php if(mo_saml_is_customer_registered_saml()){ ?>
			<div style="background-color:#FFFFFF; border:1px solid #CCCCCC; padding:0px 2% 0px 2%;">
					<h3>Sign in options</h3>
					<input type="checkbox" style="background: #DCDAD1;" disabled /> <span style="color: red;">*</span>Redirect to IdP if user not logged in.
					<a href="#" id="registered_only_access">[What does this mean?]</a>
					<br><div hidden id="registered_only_access_desc" class="mo_saml_help_desc">
					<span>Select this option if you want to restrict your site to only logged in users. Selecting this option will redirect the users to your IdP if logged in session is not found.</span>
					</div>
					<br />
					<form id="mo_saml_force_authentication_form" method="post" action="">
						<input type="hidden" name="option" value="mo_saml_force_authentication_option"/>
						<input type="checkbox" name="mo_saml_force_authentication" value="true" <?php if(!mo_saml_is_sp_configured()) echo 'disabled title="Disabled. Configure your Service Provider"'?> <?php checked(get_option('mo_saml_force_authentication') == 'true');?> onchange="document.getElementById('mo_saml_force_authentication_form').submit();"/> Force authentication with your IdP on each login attempt.
						<a href="#" id="force_authentication_with_idp">[What does this mean?]</a>
						<br><div hidden id="force_authentication_with_idp_desc" class="mo_saml_help_desc">
						<span>It will force user to provide credentials on your IdP on each login attempt even if the user is already logged in to IdP. This option may require some additional setting in your IdP to force it depending on your Identity Provider.</span>
						</div>
					</form><br/>
					Choose how you want users to log into your WordPress website. You can choose any or all of the three options below.<br/><br/>
					<span style="font-size:15px;"><b>Option 1: Use Default WordPress LogIn</b></span>
					<div style="margin-left:17px;margin-top:2%;">
						
							<input type="checkbox" style="background: #DCDAD1;" disabled /> <span style="color: red;">*</span>Check this option if you want to <b>auto redirect the user to IdP</b>.
							<a href="#" id="redirect_to_idp">[What does this mean?]</a>
							<br><div hidden id="redirect_to_idp_desc" class="mo_saml_help_desc">
							<span>Users visiting any of the following URLs will get redirected to your configured IdP for authentication:</span>
							<br/><code><b><?php echo wp_login_url(); ?></b></code> or <code><b><?php echo admin_url(); ?></b></code>
							</div>
					
						
							<p>
								<input type="checkbox" style="background: #DCDAD1;" disabled /> <span style="color: red;">*</span>Checking this option creates a backdoor to login to your Website using WordPress credentials incase you get locked out of your IdP.
							
							</p>
					
					</div>
					<span style="font-size:15px;"><b>Option 2: Use a Widget</b></span>
					<div style="margin:2% 0 2% 17px;">
					<input type="checkbox" name="mo_saml_add_widget" id="mo_saml_add_widget" <?php if(!mo_saml_is_sp_configured()) echo 'disabled title="Disabled. Configure your Service Provider"'?> value="true"> Check this option if you want to add a Widget to your page.
						<div id="mo_saml_add_widget_steps"  hidden >
						<ol>
							<li>Go to Appearances > Widgets.</li>
							<li>Select "Login with <?php echo get_option('saml_identity_name'); ?>". Drag and drop to your favourite location and save.</li>
						</ol>
						</div>
					</div>
					
						<span style="font-size:15px;"><b>Option 3: Use a ShortCode</b></span>
						<div style="margin:2% 0 2% 17px;">
								<input type="checkbox" style="background: #DCDAD1;" disabled <?php if(!mo_saml_is_sp_configured()) echo 'disabled title="Disabled. Configure your Service Provider"'?> value="true"> <span style="color: red">*</span>Check this option if you want to add a shortcode to your page.
								<br />
						</div>
					<div style="display:block;text-align:center;margin:2%;">
					 <input type="button" onclick="window.location.href='<?php echo wp_logout_url(site_url());?>'" <?php if(!mo_saml_is_sp_configured()) echo 'disabled title="Disabled. Configure your Service Provider"'?> class="button button-primary button-large" value="Log Out and Test"></input>
					</div>
						<span style="color:red;">*</span>These options are configurable in the <a href="<?php echo admin_url('admin.php?page=mo_saml_settings&tab=licensing');?>"><b>premium</b></a> version of the plugin.</h3>
						<br /><br />
			</div>
			<br />
			<div style="background-color:#FFFFFF; border:1px solid #CCCCCC; padding:0px 0px 0px 10px;">
					<h3>Your Profile</h3>
					<table border="1" style="background-color:#FFFFFF; border:1px solid #CCCCCC; border-collapse: collapse; padding:0px 0px 0px 10px; margin:2px; width:85%">
						<tr>
							<td style="width:45%; padding: 10px;">miniOrange Account Email</td>
							<td style="width:55%; padding: 10px;"><?php echo get_option('mo_saml_admin_email')?></td>
						</tr>
						<tr>
							<td style="width:45%; padding: 10px;">Customer ID</td>
							<td style="width:55%; padding: 10px;"><?php echo get_option('mo_saml_admin_customer_key')?></td>
						</tr>
					</table><br/>
			</div>
	<?php }
}

function mo_saml_configuration_steps() {
	?>
		<input type="hidden" name="option" value="mo_saml_idp_config" />
		<div id="instructions_idp"></div>
		<table width="98%" border="0" style="background-color:#FFFFFF; border:1px solid #CCCCCC; padding:2%;">
		<tr>
			<?php if(!mo_saml_is_customer_registered_saml()) { ?>
				<td colspan="2"><div style="display:block;margin-top:10px;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">Please <a href="<?php echo add_query_arg( array('tab' => 'login'), $_SERVER['REQUEST_URI'] ); ?>">Register or Login with miniOrange</a> to configure the miniOrange SAML Cloud SSO Plugin.</div></td>
			<?php } ?>
		</tr>
		
		<tr>
			<td colspan="2">
			 <div>
			 	<?php if(mo_saml_is_customer_registered_saml()){?>
					<p>
					
					<b>You are using miniOrange Single Sign on service.</b> ( <a href="#" id="help_working_title1" >Click Here<a></a> to know how the plugin works for this case and when should you use this plugin. )<br/><br />
					<div hidden id="help_working_desc1" class="mo_saml_help_desc">
						<h3>Option 1: Using miniOrange Single Sign On service:</h3>
						<div style="display:block;text-align:center;">
						<img src="<?php echo plugin_dir_url(__FILE__) . 'images/saml_working.png'?>" alt="Working of miniOrange SAML plugin" style="width: 85%;"/>
						</div>
						<ol>
							<li>miniOrange SAML SSO plugin sends a login request to miniOrange SSO Service.</li>
							<li>miniOrange SSO Service creates a SAML Request and redirects the user to your Identity Provider for authentication.</li>
							<li>Upon successful authentication, your Identity Provider sends a SAML Response back to miniOrange SSO Service.</li>
							<li>miniOrange SSO Service verifies the SAML Response and sends a response status (along with the logged in user's information) back to miniOrange SAML SSO plugin. Plugin then reads the response and logins the user.</li>
						</ol>
						<div>
							<b>Advantages:</b>
							<ol>
								<li>If you are an enterprise or business user then on using this service you will be able to take full advantage of all of miniOrange SSO features. ( For a complete list of these features <a href="http://miniorange.com/single-sign-on-sso" target="_blank">Click Here</a>)</li>
								<li>If you have multiple websites then you can use the same IdP configuration for all of them. You don't have to make seperate configurations in your IdP for each website.</li>
								<li>You can use Non-SAML Identity Providers for Single Sign On. If your IDP does not support SAML, but it supports other protocols like OAUTH, OpenID, JWT, RADIUS or any other proprietary protocol like Amazon Cognito then this plugin can be used to achive SSO between your app and Non-SAML IDP.</li>
								<li>Some Identity Providers like ADFS do not support HTTP endpoints ( i.e. your wordpress site needs to be on HTTPS ). So, if your wordpress site is not on HTTPS then you can use this service for such IdPs.</li>
								<li>If you are an enterprise or business user and stored your identities (users) in multiple IDPs then miniOrnage SAML Cloud SSO plugin can be used to have SSO between your application and multiple identity sources.</li>
								<li>If you need to achieve SSO between multiple apps (not just wordpress), then this plugin can be useful. We have support for 4500+ cloud and on prem apps. To see the complete list, <a href="http://miniorange.com/other_sol" target="_blank">look here.</a></li>
							</ol>
						</div>
					</div>
					<b>Do not want to use miniOrange Single Sign on service.</b><br />
					We have a separate plugin for this.( <a href="#" id="help_working_title2" >Click Here<a></a> to know how the plugin works for this case. )&nbsp;<a href="https://wordpress.org/plugins/miniorange-saml-20-single-sign-on/" target="_blank">Click here</a> to learn more about SAML 2.0 based Single Sign On plugin.<br/>
					<div hidden id="help_working_desc2" class="mo_saml_help_desc">
						<h3>Option 2 : Not Using miniOrange Single Sign On service:</h3>
						<div style="display:block;text-align:center;">
						<img src="<?php echo plugin_dir_url(__FILE__) . 'images/saml_working_womo.png'?>" alt="Working of miniOrange SAML plugin" style="width: 55%;"/>
						</div>
						<ol>
							<li>miniOrange SAML SSO plugin sends a login request to your Identity Provider for authentication.</li>
							<li>Upon successful authentication, your Identity Provider sends a SAML Response back to miniOrange SAML SSO plugin. Plugin then reads the response and logins the user.</li>
						</ol>
						<div>
							<b>Advantages:</b>
							<ol>
								<li>The number of messages required for Single Sign On is less as your website is directly interacting with the Identity Provider.</li>
							</ol>
						</div>
					</div>
					</p>
				<?php }?></div>
				<h3><b>Step 1:</b></h3><h4>You will need the following information to configure your IdP. Copy it and keep it handy:</h4>
				<table border="1" style="background-color:#FFFFFF; border:1px solid #CCCCCC; padding:0px 0px 0px 10px; margin:2px; border-collapse: collapse; width:98%">
					<tr>
						<td style="width:40%; padding: 15px;"><b>SP-EntityID / Issuer</b></td>
						<?php if(mo_saml_is_customer_registered_saml()){?>
						<td style="width:60%; padding: 15px;"><?php echo get_option('mo_saml_host_name') . '/moas'; ?></td>
						<?php }else{?>
						<td style="width:60%; padding: 15px;">Please register with miniOrange.</td>
						<?php }?>
					</tr>
					<tr>
						<td style="width:40%; padding: 15px;"><b>ACS (AssertionConsumerService) URL</b></td>
						<?php if(mo_saml_is_customer_registered_saml()){?>
						<td style="width:60%;  padding: 15px;"><?php echo get_option('mo_saml_host_name') . '/moas/rest/saml/acs'; ?></td>
						<?php }else{?>
							<td style="width:60%;  padding: 15px;">Please register with miniOrange.</td>
						<?php }?>
					</tr>
					<tr>
						<td style="width:40%; padding: 15px;"><b>Audience URI</b></td>
						<?php if(mo_saml_is_customer_registered_saml()){?>
						<td style="width:60%; padding: 15px;"><?php echo get_option('mo_saml_host_name') . '/moas'; ?></td>
						<?php }else{?>
						<td style="width:60%; padding: 15px;">Please register with miniOrange.</td>
						<?php }?>
					</tr>
					<tr>
						<td style="width:40%; padding: 15px;"><b>NameID format</b></td>
						<?php if(mo_saml_is_customer_registered_saml()){?>
						<td style="width:60%; padding: 15px;">urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress</td>
						<?php }else{?>
						<td style="width:60%; padding: 15px;">Please register with miniOrange.</td>
						<?php }?>
					</tr>
					<tr>
						<td style="width:40%; padding: 15px;"><b>Recipient URL</b></td>
						<?php if(mo_saml_is_customer_registered_saml()){?>
						<td style="width:60%;  padding: 15px;"><?php echo get_option('mo_saml_host_name') . '/moas/rest/saml/acs'; ?></td>
						<?php }else{?>
						<td style="width:60%;  padding: 15px;">Please register with miniOrange.</td>
						<?php }?>
					</tr>
					<tr>
						<td style="width:40%; padding: 15px;"><b>Destination URL</b></td>
						<?php if(mo_saml_is_customer_registered_saml()){?>
						<td style="width:60%;  padding: 15px;"><?php echo get_option('mo_saml_host_name') . '/moas/rest/saml/acs'; ?></td>
						<?php }else{?>
						<td style="width:60%;  padding: 15px;">Please register with miniOrange.</td>
						<?php }?>
					</tr>
						<tr>
							<td style="width:40%; padding: 15px;"><b>Default Relay State (Optional)</b></td>
							<td style="width:60%;  padding: 15px;">Available in the <a href="<?php echo admin_url('admin.php?page=mo_saml_settings&tab=licensing');?>"><b>premium</b></a> version</td>
						</tr>
				</table>
					<p style="text-align: center;font-size: 13pt;font-weight: bold;">OR</p>
					<p>Provide this metadata URL to your Identity Provider:</p>
					<code>Metadata URL is available in the <a href="<?php echo admin_url('admin.php?page=mo_saml_settings&tab=licensing');?>"><b>premium</b></a> version of the plugin.</code>
			  </tr>					
				</td>
				</tr>

				<tr>
					<td colspan="2">
						<p><h3><b>Step 2:</b></h3>
							With the help of information given in <b>Step 1</b>, configure your IdP. Then come back to <b>Step 3</b>. 
							<br/>
						</p>
						<div style="background-color:#CBCBCB;padding:5px;">We also have step by step <b>do-it-yourself guides</b> available for all known IdPs like <b>ADFS, Centrify, Okta, OneLogin, OpenAM, Oracle Identity Manager, JBoss Keycloak, Salesforce, Shibboleth, SimpleSAML, WSO2</b> etc. <a href="<?php echo admin_url().'admin.php?page=mo_saml_settings&tab=licensing'; ?>">( Supported in premium version )</a></div>
						<br>
						If you can't find your Idp in this list, write us the name of your Idp via <b>support form</b> on right.
						
						<p><b>For more help, checkout the <a href="<?php echo add_query_arg( array('tab' => 'help'), $_SERVER['REQUEST_URI'] ); ?>">Help section</a>.</b></p>
					</td>
				</tr>
		
				<tr>
				<td colspan="2">
				<h3><b>Step 3:</b></h3><h4> Assuming that you are now done with Step 2, please note down the following information from your IdP admin screen and keep it handy to configure your Service provider.</h4>
				<ol>
					<li><b>X.509 certificate</b></li>
					<li><b>SAML Login URL (Single Sign On URL)</b></li>
					<li><b>IdP Entity ID (IdP Issuer)</b></li>
					<li><b>Is Response signed</b> by your IdP?</li>
					<li><b>Is Assertion signed</b> by your IdP?</li>
					</li>
				</ol>
				<a href="#" id="idp_details_link" >[ Cannot find the above information? ]</a>
				<div hidden id="idp_details_desc" class="mo_saml_help_desc">
				  <ol><li>X.509 certificate is enclosed in <code>X509Certificate</code> tag in IdP-Metadata XML file. (parent tag: <code>KeyDescriptor use="signing"</code>)</li> 
				  <li>SAML Login URL is enclosed in <code>SingleSignOnService</code> tag (Binding type: HTTP-Redirect) in IdP-Metadata XML file.</li>
				  <li>EntityID is the value of the <code>entityID</code> attribute of <code>EntityDescriptor</code> tag in IdP-Metadata XML file. </li></ol>
					Still Cannot find the above information?<br/> You can contact us using the support form on the right and we will help you.
				</div><br/><br/>
				<input type="checkbox"  <?php if(!mo_saml_is_customer_registered_saml()) echo 'disabled'?> onchange="window.location='<?php echo admin_url(); ?>/admin.php?page=mo_saml_settings&tab=save'" /> Check this option if you have the above information. You will be redirected to configure the Service Provider. 
			</td>
			</tr>
		</table>
	<?php
}

function mo_saml_apps_config_saml() {
	
		global $wpdb;
		$entity_id = get_option('entity_id');
		if(!$entity_id) { 
			$entity_id = get_option('mo_saml_host_name') . '/moas';
		}
		$sso_url = get_option('sso_url');
		$cert_fp = get_option('cert_fp');
		
		//Broker Service
		$saml_identity_name = get_option('saml_identity_name');
		$saml_login_url = get_option('saml_login_url');
		$saml_issuer = get_option('saml_issuer');
		$saml_x509_certificate = get_option('saml_x509_certificate');
		$saml_response_signed = get_option('saml_response_signed');
		if($saml_response_signed == NULL) {$saml_response_signed = 'checked'; }
		$saml_assertion_signed = get_option('saml_assertion_signed');
		if($saml_assertion_signed == NULL) {$saml_assertion_signed = 'Yes'; }
		
		$idp_config = get_option('mo_saml_idp_config_complete');
		?>
		<form width="98%" border="0" style="background-color:#FFFFFF; border:1px solid #CCCCCC; padding:0px 0px 0px 10px;" name="saml_form" method="post" action="">
		<input type="hidden" name="option" value="login_widget_saml_save_settings" />
		<table style="width:100%;">
			<tr>
				<td colspan="2">
					<h3>Configure Service Provider
					</h3>
				</td>
			</tr>
			<?php if(!mo_saml_is_customer_registered_saml()) { ?>
				<tr>
					<td colspan="2"><div style="display:block;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">Please <a href="<?php echo add_query_arg( array('tab' => 'login'), $_SERVER['REQUEST_URI'] ); ?>">Register or Login with miniOrange</a> to configure the miniOrange SAML Cloud SSO Plugin.</div></td>
				</tr>
			<?php } ?>
			
			<tr>
				<td colspan="2">Enter the information gathered from your Identity Provider<br /><br /></td>
			</tr>
			<tr>
				<td style="width:200px;"><strong>Identity Provider Name <span style="color:red;">*</span>:</strong></td>
				<td><input type="text" name="saml_identity_name" placeholder="Identity Provider name like ADFS, SimpleSAML, Salesforce" style="width: 95%;" value="<?php echo $saml_identity_name;?>" required <?php if(!mo_saml_is_customer_registered_saml()) echo 'disabled'?> pattern="^\w*$" title="Only alphabets, numbers and underscore is allowed"/></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td><strong>IdP Entity ID or Issuer <span style="color:red;">*</span>:</strong></td>
				<td><input type="text" name="saml_issuer" placeholder="Identity Provider Entity ID or Issuer" style="width: 95%;" value="<?php echo $saml_issuer;?>" required <?php if(!mo_saml_is_customer_registered_saml()) echo 'disabled'?>/></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td><strong>SAML Login URL <span style="color:red;">*</span>:</strong></td>
				<td><input type="url" name="saml_login_url" placeholder="Single Sign On Service URL (HTTP-Redirect binding) of your IdP" style="width: 95%;" value="<?php echo $saml_login_url;?>" required <?php if(!mo_saml_is_customer_registered_saml()) echo 'disabled'?>/></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td><strong>X.509 Certificate <span style="color:red;">*</span>:</strong></td>
				<td><textarea rows="4" cols="5" name="saml_x509_certificate" placeholder="Copy and Paste the content from the downloaded certificate or copy the content enclosed in 'X509Certificate' tag (has parent tag 'KeyDescriptor use=signing') in IdP-Metadata XML file" style="width: 95%;" <?php if(!mo_saml_is_customer_registered_saml()) echo 'disabled'?>><?php echo $saml_x509_certificate;?></textarea></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><b>NOTE:</b> Format of the certificate:<br/><b>-----BEGIN CERTIFICATE-----<br/>XXXXXXXXXXXXXXXXXXXXXXXXXXX<br/>-----END CERTIFICATE-----</b></i><br/>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td><strong>Response Signed:</strong></td>
				<td><input type="checkbox" name="saml_response_signed" value="Yes" <?php echo $saml_response_signed; ?> <?php if(!mo_saml_is_customer_registered_saml()) echo 'disabled'?>/>
				Check if your IdP is signing the SAML Response. Leave checked by default.</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td><strong>Assertion Signed:</strong></td>
				<td><input type="checkbox" name="saml_assertion_signed" value="Yes" <?php echo $saml_assertion_signed; ?> <?php if(!mo_saml_is_customer_registered_saml()) echo 'disabled'?>/>
				Check if the IdP is signing the SAML Assertion. Leave unchecked by default.</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><br /><input type="submit" name="submit" style="width:100px;" value="Save" class="button button-primary button-large" <?php if(!mo_saml_is_customer_registered_saml()) echo 'disabled'?>/> &nbsp; 
				<input type="button" name="test" title="You can only test your Configuration after saving your Service Provider Settings." onclick="showTestWindow();" <?php if(!mo_saml_is_sp_configured()) echo 'disabled'?> value="Test configuration" class="button button-primary button-large" style="margin-right: 3%;"/>
				</td>
			</tr>
		</table><br/>
		<input type="checkbox"  <?php if(!mo_saml_is_sp_configured()) echo 'disabled title="Disabled. Configure your Service Provider"'?> onchange="window.location='<?php echo admin_url(); ?>/admin.php?page=mo_saml_settings&tab=general'" />Check this option if you have Configured and Tested your Service Provider settings.	
		<br/><br/>
		</form>
	<?php
}

function mo_saml_save_optional_config(){
	global $wpdb;
	$entity_id = get_option('entity_id');
	if(!$entity_id) { 
		$entity_id = get_option('mo_saml_host_name') . '/moas';
	}
	$sso_url = get_option('sso_url');
	$cert_fp = get_option('cert_fp');
	
	$saml_identity_name = get_option('saml_identity_name');
	
	//Attribute mapping
	$saml_am_username = get_option('saml_am_username');	
	if($saml_am_username == NULL) {$saml_am_username = 'NameID'; }
	$saml_am_email = get_option('saml_am_email');
	if($saml_am_email == NULL) {$saml_am_email = 'NameID'; }
	$saml_am_first_name = get_option('saml_am_first_name');
	$saml_am_last_name = get_option('saml_am_last_name');
	$saml_am_group_name = get_option('saml_am_group_name');
	?>
		<form name="saml_form_am" method="post" action="">
		<input type="hidden" name="option" value="login_widget_saml_attribute_mapping" />
		<table width="98%" border="0" style="background-color:#FFFFFF; border:1px solid #CCCCCC; padding:0px 0px 0px 10px;">
		  <tr>
			<td colspan="2">
				<h3>Attribute Mapping (Optional)</h3>
			</td>
		  </tr>
		  <?php if(!mo_saml_is_customer_registered_saml()) { ?>
		  <tr>
			<td colspan="2"><div style="display:block;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">Please <a href="<?php echo add_query_arg( array('tab' => 'login'), $_SERVER['REQUEST_URI'] ); ?>">Register or Login with miniOrange</a> to configure the miniOrange SAML Cloud SSO Plugin.</div></td>
		  </tr>
		  <?php } ?>
		  <tr>
		  	<td colspan="2">[ <a href="#" id="attribute_mapping">Click Here</a> to know how this is useful. ]
		 		<div hidden id="attribute_mapping_desc" class="mo_saml_help_desc">
					<ol>
						<li>Attributes are user details that are stored in your Identity Provider.</li>
						<li>Attribute Mapping helps you to get user attributes from your IdP and map them to WordPress user attributes like firstname, lastname etc.</li>
						<li>While auto registering the users in your WordPress site these attributes will automatically get mapped to your WordPress user details.</li>
					</ol>
				</div>
				</td>
		  </tr>
		  <tr>
			 <td colspan="2"><br/><b>NOTE: </b>Use attribute name <code>NameID</code> if Identity is in the <i>NameIdentifier</i> element of the subject statement in SAML Response.<br /><br /></td>
		  </tr>
			
			  <tr>
			  <td style="width:200px;"><strong>Login/Create Wordpress account by: </strong></td>
			  <td><select name="saml_am_account_matcher" id="saml_am_account_matcher" <?php if(!mo_saml_is_customer_registered_saml()) echo 'disabled'?>>
				  <option value="email"<?php if(get_option('saml_am_account_matcher') == 'email') echo 'selected="selected"' ; ?> >Email</option>
				  <option value="username"<?php if(get_option('saml_am_account_matcher') == 'username') echo 'selected="selected"' ; ?> >Username</option>
				</select>
			  </td>
			  </tr>
			  <tr>
				<td>&nbsp;</td>
				<td><i>Users in Wordpress will be searched (existing wordpress users) or created (new users) based on this attribute. Use Email by default.</i></td>
			  </tr>
				  <tr>
					<td style="width:150px;"><span style="color:red;">*</span><strong>Username (required):</strong></td>
					<td><b>NameID</b></td>
				  </tr>
				  <tr>
					<td><span style="color:red;">*</span><strong>Email (required):</strong></td>
					<td><b>NameID</b></td>
				  </tr>	  			
			  <tr>
				<td><strong>First Name:</strong></td>
				<td><input type="text" name="saml_am_first_name" placeholder="Enter attribute name for First Name" style="width: 350px;" value="<?php echo $saml_am_first_name;?>" <?php if(!mo_saml_is_customer_registered_saml()) echo 'disabled'?>/></td>
			  </tr>
			  <tr>
				<td><strong>Last Name:</strong></td>
				<td><input type="text" name="saml_am_last_name" placeholder="Enter attribute name for Last Name" style="width: 350px;" value="<?php echo $saml_am_last_name;?>" <?php if(!mo_saml_is_customer_registered_saml()) echo 'disabled'?>/></td>
			  </tr>
			  	  <tr>
					<td><span style="color:red;">*</span><strong>Group/Role:</strong></td>
					<td><input type="text" disabled placeholder="Enter attribute name for Group/Role" style="width: 350px;background: #DCDAD1;"/></td>
				  </tr>
				  <tr>
			  		<td colspan="2"><br /><span style="color:red;">*</span>Customized Attribute Mapping is configurable in the <a href="<?php echo admin_url('admin.php?page=mo_saml_settings&tab=licensing');?>"><b>premium</b></a> version of the plugin.</td>
			 	  </tr>
			  <tr>
				<td>&nbsp;</td>
				<td><br /><input type="submit" style="width:100px;" name="submit" value="Save" class="button button-primary button-large" <?php if(!mo_saml_is_customer_registered_saml()) echo 'disabled'?>/> &nbsp; 
				<br /><br />
				</td>
			  </tr>
			 </table>
			 </form>
			 <br />
			 <form name="saml_form_am_role_mapping" method="post" action="">
				<input type="hidden" name="option" value="login_widget_saml_role_mapping" />
				<table width="98%" border="0" style="background-color:#FFFFFF; border:1px solid #CCCCCC; padding:0px 0px 0px 10px;">
					<tr>
						<td colspan="2">
							<h3>Role Mapping (Optional)</h3>
						</td>
					</tr>
					 <tr>
					  	<td colspan="2">[ <a href="#" id="role_mapping">Click Here</a> to know how this is useful. ]
					 		<div hidden id="role_mapping_desc" class="mo_saml_help_desc">
								<ol>
									<li>WordPress uses a concept of Roles, designed to give the site owner the ability to control what users can and cannot do within the site.</li>
									<li>WordPress has six pre-defined roles: Super Admin, Administrator, Editor, Author, Contributor and Subscriber.</li>
									<li>Role mapping helps you to assign specific roles to users of a certain group in your IdP.</li>
									<li>While auto registering, the users are assigned roles based on the group they are mapped to.</li>
								</ol>
							</div>
							</td>
					  </tr>
					<tr><td colspan="2"><br/><b>NOTE: </b>Role will be assigned only to new users. Existing Wordpress users' role remains same.<br /><br/></td></tr>
					<tr><td colspan="2"><input type="checkbox" disabled style="background: #DCDAD1;" />&nbsp;&nbsp;<span style="color:red;">*</span>Do not auto create users if roles are not mapped here.<br /></td></tr>
						<tr><td colspan="2"><input type="checkbox" style="background: #DCDAD1;" disabled />&nbsp;&nbsp;<span style="color:red;">*</span>Do not assign role to unlisted users.<br /><br /></td></tr>	
					<tr>
						<td><strong>Default Role:</strong></td>
						<td>
							<?php 
								$disabled = '';
								if(!mo_saml_is_customer_registered_saml())
									$disabled = 'disabled';
							?>
								<select id="saml_am_default_user_role" name="saml_am_default_user_role" <?php echo $disabled ?> style="width:150px;" >
							 <?php 
								$default_role = get_option('saml_am_default_user_role');
								if(empty($default_role))
									$default_role = get_option('default_role');
								echo wp_dropdown_roles( $default_role );
							?> 
								</select>
							&nbsp;&nbsp;&nbsp;&nbsp;<i>Select the default role to assign to Users.</i>
						</td>
				  	</tr>
					<?php
						$is_disabled = "";
						if(!mo_saml_is_customer_registered_saml()) {
							$is_disabled = "disabled";
						}
						$wp_roles = new WP_Roles();
						$roles = $wp_roles->get_names();
						$roles_configured = get_option('saml_am_role_mapping');
						foreach ($roles as $role_value => $role_name) {
							
							echo '<tr><td><span style="color:red;">*</span><b>' . $role_name .'</b></td><td><input type="text" placeholder="Semi-colon(;) separated Group/Role value for ' . $role_name . '" style="width: 400px;background: #DCDAD1" disabled /></td></tr>';
						}
					?>
					
					<tr>
					  	<td colspan="2"><br /><span style="color:red;">*</span>Customized Role Mapping options are configurable in the <a href="<?php echo admin_url('admin.php?page=mo_saml_settings&tab=licensing');?>"><b>premium</b></a> version of the plugin.</td>
					</tr>
					
					<tr>
						<td>&nbsp;</td>
						<td><br /><input type="submit" style="width:100px;" name="submit" value="Save" class="button button-primary button-large" <?php if(!mo_saml_is_customer_registered_saml()) echo 'disabled'?>/> &nbsp; 
						<br /><br />
						</td>
					</tr>
				</table>
			</form>
	<?php
}

function mo_saml_save_plugin_config() {
	?>
		<form>
		<div id="instructions_idp"></div>
		<table width="98%" border="0" style="background-color:#FFFFFF; border:1px solid #CCCCCC; padding:0px 0px 0px 10px;">
			<tr>
				<?php if(!mo_saml_is_customer_registered_saml()) { ?>
					<td colspan="2"><div style="display:block;margin-top:10px;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">Please <a href="<?php echo add_query_arg( array('tab' => 'login'), $_SERVER['REQUEST_URI'] ); ?>">Register or Login with miniOrange</a> to configure the miniOrange SAML Cloud SSO Plugin.</div></td>
				<?php } ?>
			</tr>
		<tr>
			<td colspan="2">
				<br/>
			<p style="font-size:13px;">miniOrange SAML Cloud SSO Plugin enables login to WordPress through your Identity Provider. <br/>We support all known IdPs - <b>ADFS, Okta, Salesforce, Shibboleth, SimpleSAMLphp, OpenAM, Centrify, Ping, RSA, IBM, Oracle, OneLogin, Bitium etc</b>. If you need detailed instructions on setting up these IdPs, we can give you step by step instructions. Contact us using the support form on the right.</p>
				<h3>Frequently Asked Questions</h3>
				<table class="mo_saml_help">
					<tr>
						<td class="mo_saml_help_cell">
							<div id="help_steps_title" class="mo_saml_title_panel">
								<div class="mo_saml_help_title">Instructions to use miniOrange SAML Cloud SSO plugin</div>
							</div>
							<div hidden id="help_steps_desc" class="mo_saml_help_desc">
								<ul>
									<li>Step 1:&nbsp;&nbsp;&nbsp;&nbsp;Configure your Identity Provider by following <a href="<?php echo add_query_arg( array('tab' => 'config'), $_SERVER['REQUEST_URI'] ); ?>">these steps</a>.</li>
									<li>Step 2:&nbsp;&nbsp;&nbsp;&nbsp;Download X.509 certificate from your Identity Provider.</li>
									<li>Step 3:&nbsp;&nbsp;&nbsp;&nbsp;Enter appropriate values in the fields in <a href="<?php echo add_query_arg( array('tab' => 'save'), $_SERVER['REQUEST_URI'] ); ?>">Configure Service Provider</a>.</li>
									<li>Step 4:&nbsp;&nbsp;&nbsp;&nbsp;After saving your configuration, you will be able to test your configuration using the <b>Test &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Configuration</b> button on the top of the page.</li>
									<li>Step 5:&nbsp;&nbsp;&nbsp;&nbsp;Add "Login with &lt;IdP&gt;" widget to your WordPress page.</li>
								</ul>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
					<tr>
						<td class="mo_saml_help_cell">
							<div id="help_widget_steps_title" class="mo_saml_title_panel">
								<div class="mo_saml_help_title">Add login Link to post/page/blog</div>
							</div>
							<div hidden id="help_widget_steps_desc" class="mo_saml_help_desc">
								<ol>
									<li>Go to Appearances > Widgets.</li>
									<li>Select "Login with &lt;Identity Provider&gt;". Drag and drop to your favourite location and save.</li>
								</ol>								
							</div>
						</td>
					</tr>
					<tr>
						<td class="mo_saml_help_cell">
							<div id="help_faq_idp_config_title" class="mo_saml_title_panel">
								<div class="mo_saml_help_title">I logged in to my Identity Provider and it redirected to WordPress, but I'm not logged in. There was an error - "We could not sign you in.".</div>
							</div>
							<div hidden id="help_faq_idp_config_desc" class="mo_saml_help_desc">
								To know what actually went wrong,
								<ol>
									<li>Login to your Wordpress administrator account. And go to miniOrange SAML Cloud SSO plugin's Configure Service Provider tab.</li>
									<li>Click on <b>Test Configuration</b>. A popup window will open (make sure the popup enabled in your browser).</li>
									<li>Click on <b>Login</b> button. You will be redirected to your IdP for authentication.</li>
									<li>On successful authentication, You will be redirect back with the actual error message.</li>
									<li>Here are the some frequent errors:
									<ul><br />
										<li><b>INVALID_ISSUER</b>: This means that you have NOT entered the correct Issuer or Entity ID value provided by your Identity Provider. You'll see in the error message what was the expected value (that you have configured) and what actually found in the SAML Response.</li>
										<li><b>INVALID_AUDIENCE</b>: This means that you have NOT configured Audience URL in your Identity Provider correctly. It must be set to <b><?php echo get_option('mo_saml_host_name') . '/moas/rest/saml/acs'; ?></b> in your Identity Provider.</li>
										<li><b>INVALID_DESTINATION</b>: This means that you have NOT configured Destination URL in your Identity Provider correctly. It must be set to <b><?php echo get_option('mo_saml_host_name') . '/moas/rest/saml/acs'; ?></b> in your Identity Provider.</li>
										<li><b>INVALID_SIGNATURE</b>: This means that the certificate you provided did NOT match the certificate found in the SAML Response. Make sure you provide the same certificate that you downloaded from your IdP. If you have your IdP's Metadata XML file then make sure you provide certificate enclosed in X509Certificate tag which has an attribute <b>use="signing"</b>.</li>
										<li><b>INVALID_CERTIFICATE</b>: This means that the certificate you provided is NOT in proper format. Make sure you have copied the entire certificate provided by your IdP. If coiped from IdP's Metadata XML file, make sure that you copied the entire value.</li>
									</ul>
								</ol>
								If you need help resolving the issue, please contact us using the support form and we will get back to you shortly.
							</div>
						</td>
					</tr>
					<tr>
						<td class="mo_saml_help_cell">
							<div id="help_faq_idp_redirect_title" class="mo_saml_title_panel">
								<div class="mo_saml_help_title">I clicked on login link but I cannot see the login page of my Identity Provider.</div>
							</div>
							<div hidden id="help_faq_idp_redirect_desc" class="mo_saml_help_desc">
								This could mean that the <b>SAML Login URL</b> you have entered is not correct. Please enter the correct <b>SAML Login URL</b> (with HTTP-Redirect binding) provided by your Identity Provider. <br/><br/>If the problem persists, please contact us using the support form. It would be helpful if you could share your Identity Provider details with us.
							</div>
						</td>
					</tr>
					<tr>
						<td class="mo_saml_help_cell">
							<div id="help_faq_404_title" class="mo_saml_title_panel">
								<div class="mo_saml_help_title">I'm getting a 404 error page when I try to login.</div>
							</div>
							<div hidden id="help_faq_404_desc" class="mo_saml_help_desc">
								This could mean that you have not entered the correct <b>SAML Login URL</b>. Please enter the correct <b>SAML Login URL</b> (with HTTP-Redirect binding) provided by your Identity Provider and try again.<br/><br/>If the problem persists, please contact us using the support form. It would be helpful if you could share your Identity Provider details with us.
							</div>
						</td>
					</tr>
					
					<tr>
						<td class="mo_saml_help_cell">
							<div id="help_curl_enable_title" class="mo_saml_title_panel">
								<div class="mo_saml_help_title">How to enable PHP cURL extension?</div>
							</div>
							<div hidden id="help_curl_enable_desc" class="mo_saml_help_desc">
								<ol>
									<li>Open php.ini file located under php installation folder.</li>
									<li>Search for extension=php_curl.dll.</li>
									<li>Uncomment it by removing the semi-colon(;) in front of it.</li>
									<li>Restart the Apache Server.</li>
								</ol>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
					<tr>
						<td class="mo_saml_help_cell">
							<div id="help_openssl_enable_title" class="mo_saml_title_panel">
								<div class="mo_saml_help_title">How to enable PHP openssl extension?</div>
							</div>
							<div hidden id="help_openssl_enable_desc" class="mo_saml_help_desc">
								<ol>
									<li>Open php.ini file located under php installation folder.</li>
									<li>Search for extension=php_openssl.dll.</li>
									<li>Uncomment it by removing the semi-colon(;) in front of it.</li>
									<li>Restart the Apache Server.</li>
								</ol>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
					<tr>
						<td class="mo_saml_help_cell">
							<div id="help_saml_title" class="mo_saml_title_panel">
								<div class="mo_saml_help_title">What is SAML?</div>
							</div>
							<div hidden id="help_saml_desc" class="mo_saml_help_desc">
								Security Assertion Markup Language(SAML) is an XML-based, open-standard data format for exchanging authentication and authorization data between parties, in particular, between an Identity Provider and a Service Provider. In our case, miniOrange is the Service Provider and the application which manages credentials is the Identity provider.
								<br/><br/>
								The SAML specification defines three roles: the Principal (in this case, your Wordpress user), the Identity provider (IdP), and the Service Provider (SP). The Service Provider requests and obtains an identity assertion from the Identity Provider. On the basis of this assertion, the service provider can make an access control decision - in other words it can decide whether to allow user to login to WordPress.
								<br/><br/>
								For more details please refer to this <a href="https://en.wikipedia.org/wiki/Security_Assertion_Markup_Language" target="_blank">SAML document</a>.
							</div>
						</td>
					</tr>
					<tr>
						<td class="mo_saml_help_cell">
							<div id="help_saml_flow_title" class="mo_saml_title_panel">
								<div class="mo_saml_help_title">SP-Initiated Login vs. IdP-Initiated Login</div>
							</div>
							<div hidden id="help_saml_flow_desc" class="mo_saml_help_desc">
								The user's identity(user profile and credentials) is managed by an Identity Provider(IdP) and the user wants to login to your WordPress site.
								<br/><br/>
								<b>SP-Initiated Login</b>
								<br/>
								<ol>
									<li>The request to login is initiated through the WordPress site.</li>
									<li>Using the miniOrange SAML Plugin, the user is redirected to IdP login page.</li>
									<li>The user authenticates with the IdP.</li>
									<li>With the help of response from IdP, miniOrange SAML Plugin logs in the user to WordPress site.</li>
								</ol>
								<b>IdP-Initiated Login</b>
								<br/>
								<ol>
									<li>The user initiates login through IdP.</li>
									<li>With the help of response from IdP, miniOrange SAML Plugin logs in the user to WordPress site.</li>
								</ol>
								
							</div>
						</td>
					</tr>
				</table>
				<br/>
				
				<br/><br/>
			</td>
		</tr>
		</table>
		</form>
		
	
</div>
<?php
}

function mo_saml_get_test_url(){
	
	$url = get_option('mo_saml_host_name') . '/idptest/?id=' . get_option('mo_saml_admin_customer_key') . '&key=' . get_option('mo_saml_customer_token');
	return $url;
}

function mo_saml_is_customer_registered_saml() {
			$email 			= get_option('mo_saml_admin_email');
			$customerKey 	= get_option('mo_saml_admin_customer_key');
			if( ! $email || ! $customerKey || ! is_numeric( trim( $customerKey ) ) ) {
				return 0;
			} else {
				return 1;
			}
}

function mo_saml_is_sp_configured() {
	$saml_login_url = get_option('saml_login_url');
	if( !empty($saml_login_url)) {
		return 1;
	} else {
		return 0;
	}
}

function miniorange_support_saml(){
?>
	<div class="mo_saml_support_layout">
		<div>
			<h3>Support</h3>
			<p>Need any help? We can help you with configuring your Identity Provider. Just send us a query and we will get back to you soon.</p>
			<form method="post" action="">
				<input type="hidden" name="option" value="mo_saml_contact_us_query_option" />
				<table class="mo_saml_settings_table">
					<tr>
						<td><input style="width:95%" type="email" class="mo_saml_table_textbox" required name="mo_saml_contact_us_email" value="<?php echo get_option("mo_saml_admin_email"); ?>" placeholder="Enter your email"></td>
					</tr>
					<tr>
						<td><input type="tel" style="width:95%" id="contact_us_phone" pattern="[\+]\d{11,14}|[\+]\d{1,4}[\s]\d{9,10}" class="mo_saml_table_textbox" name="mo_saml_contact_us_phone" value="<?php echo get_option('mo_saml_admin_phone');?>" placeholder="Enter your phone"></td>
					</tr>
					<tr>
						<td><textarea class="mo_saml_table_textbox" style="width:95%" onkeypress="mo_saml_valid_query(this)" onkeyup="mo_saml_valid_query(this)" onblur="mo_saml_valid_query(this)" required name="mo_saml_contact_us_query" rows="4" style="resize: vertical;" placeholder="Write your query here"></textarea></td>
					</tr>
				</table>
				<div style="text-align:center;">
					<input type="submit" name="submit" style="margin:15px; width:120px;" class="button button-primary button-large" />
				</div>
			</form>
		</div>
	</div>
	<script>
		jQuery("#contact_us_phone").intlTelInput();
		jQuery("#phone_contact").intlTelInput();
		function mo_saml_valid_query(f) {
			!(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(
					/[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
		}
		function showTestWindow() {
		var myWindow = window.open("<?php echo mo_saml_get_test_url(); ?>", "TEST SAML IDP", "scrollbars=1 width=800, height=600");	
		}
	</script>
<?php }

function mo_saml_show_pricing_page() { ?>
	<div class="mo_saml_table_layout" style="width:130%;">
		<table class="mo_saml_local_pricing_table">
		<h2>Licensing Plans (Cloud Service)
		<span style="float:right;margin-right:15px;"><input type="button" name="ok_btn" class="button button-primary button-large" value="OK, Got It" onclick="window.location.href='admin.php?page=mo_saml_settings'" /></span>
		</h2><hr>
			<tr style="vertical-align:top;">
		
				<td>
				<div class="mo_saml_cloud_local_thumbnail mo_saml_local_pricing_free_tab">
				
				<h3 class="mo_saml_local_pricing_header">Free</h3>
				<h4 class="mo_saml_local_pricing_sub_header">(You are automatically on this plan)</h4>
				<p></p>
				<hr>
				<p class="mo_saml_local_pricing_text">$0<br><br /><br />
				</p>
				<hr>
				
				<p class="mo_saml_local_pricing_text">
				Basic Role Mapping,<br>
				Basic Attribute Mapping,<br>
				Widget to add IDP Login Link on your site<br><br/><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
				</p>
				<hr>
				<p class="mo_saml_local_pricing_text">Basic Support by Email</p>
				</td>
				<td>
				<div class="mo_saml_cloud_local_thumbnail mo_saml_local_pricing_paid_tab" >
				
				<h3 class="mo_saml_local_pricing_header">Do it yourself</h3>
				<p></p>
				<h4 class="mo_saml_local_pricing_sub_header" style="padding-bottom:8px !important;">
				<a class="button button-primary button-large"
				 onclick="upgradeform('wp_saml_cloud_sso_basic_plan')" >Click here to upgrade *</a></h4>
				
				<hr>
				<p class="mo_saml_local_pricing_text">Price Per MAU<br />
				(Monthly Active Users)<br />
				<a style="font-size:13px;color:#fff;" href="http://miniorange.com/pricing" target="_blank">Click here to check pricing</a><br />
				</p>
				
				<hr>
				<p class="mo_saml_local_pricing_text">
					Customized Role Mapping,<br>
					Customized Attribute Mapping,<br>
					Widget, Shortcode to add IDP Login Link on your site,<br>
					Auto-Redirect to IDP,<br>
					Step-by-step guide to setup IDP,<br>
					SAML Single Logout,<br>
					Support for sites behind reverse-proxy,<br>
					Protect your complete site,<br>
					Options to select SAML Request binding type,<br>
					Multi-Site Support,<br>
					Multiple IDP's Supported,<br>
					Integrated Windows Authentication,<br />
					Support Non-SAML Identity Provider for SSO,<br />
					Support HTTP endpoint for every IDP,<br />
					SSO between your application and multiple Identity Sources<br /><br />
				</p>
				<hr>
				
				<p class="mo_saml_local_pricing_text" >Basic Support by Email</p>
				</div></td>
				<td>
				<div class="mo_saml_cloud_local_thumbnail mo_saml_local_pricing_free_tab" >
				<h3 class="mo_saml_local_pricing_header">Premium</h3>
				<p></p>
				<h4 class="mo_saml_local_pricing_sub_header" style="padding-bottom:8px !important;"><a class="button button-primary button-large"
				 onclick="upgradeform('wp_saml_cloud_sso_premium_plan')" >Click here to upgrade *</a>
				 </h4>
				 
				<hr>
				<p class="mo_saml_local_pricing_text">Price Per MAU<br />
				(Monthly Active Users)<br />
				<a style="font-size:13px;color:#fff;" href="http://miniorange.com/pricing" target="_blank">Click here to check pricing</a><br />
				</p>
				<hr>
				
				<p class="mo_saml_local_pricing_text">
					Customized Role Mapping,<br>
					Customized Attribute Mapping,<br>
					Widget, Shortcode to add IDP Login Link on your site,<br>
					Auto-Redirect to IDP,<br>
					Step-by-step guide to setup IDP,<br>
					SAML Single Logout,<br>
					Support for sites behind reverse-proxy,<br>
					Protect your complete site,<br>
					Options to select SAML Request binding type,<br>
					Multi-Site Support,<br>
					Multiple IDP's Supported,<br>
					Integrated Windows Authentication,<br>
					Support Non-SAML Identity Provider for SSO,<br />
					Support HTTP endpoint for every IDP,<br />
					SSO between your application and multiple Identity Sources,<br />
					End to End Identity Provider Configuration ***<br>
				</p>
				
				<hr>
				
				<p class="mo_saml_local_pricing_text">Premium Support Plans Available</p>
				
				</div></td>
			
		</tr>	
		</table>
		<form style="display:none;" id="loginform" action="<?php echo get_option( 'mo_saml_host_name').'/moas/login'; ?>" 
		target="_blank" method="post">
		<input type="email" name="username" value="<?php echo get_option('mo_saml_admin_email'); ?>" />
		<input type="text" name="redirectUrl" value="<?php echo get_option( 'mo_saml_host_name').'/moas/initializepayment'; ?>" />
		<input type="text" name="requestOrigin" id="requestOrigin"  />
		</form>
		<script>
			function upgradeform(planType){
				jQuery('#requestOrigin').val(planType);
				jQuery('#loginform').submit();
			}
		</script>
		<br>
		<h3>*Steps to Upgrade to Premium Plugin -</h3>
		<p>1. You will be redirected to miniOrange Login Console. Enter your password with which you created an account with us. After that you will be redirected to payment page.</p>
		<p>2. Enter you card details and complete the payment. On successful payment completion, you will see the link to download the premium plugin.</p>
		<p>3. When you download the premium plugin, just unzip it and replace the folder with existing plugin. Do not delete and upload again from wordpress admin panel as your already saved settings will get lost.<br><br>
		<b>Note: If you are downloading the Multi-Site Plugin, then first delete existing plugin and then re-install the Multi-Site plugin.</b></p>
		<p>4. From this point on, do not update the premium plugin from the Wordpress store.</p>
		<strong><h4>** If you want to use more than 50k transactions for authentications vis IDP, mail us at <a href="mailto:info@miniorange.com"><b>info@miniorange.com</b></a> or submit a support request using the support from under Identity Provider tab.</h4></strong>
		<h3>*** End to End Identity Provider Integration - </h3>
		<p>We will setup a Conference Call / Gotomeeting and do end to end configuration for you for IDP as well as plugin. We provide services to do the configuration on your behalf. </p>
		<h3>10 Days Return Policy -</h3>
		At miniOrange, we want to ensure you are 100% happy with your purchase. If the premium plugin you purchased is not working as advertised and you've attempted to resolve any issues with our support team, which couldn't get resolved. We will refund the whole amount within 10 days of the purchase. 
		Please email us at <b>info@miniorange.com</b> for any queries regarding the return policy.
		<h2>Licensing Plans (On-Prem)</h2><hr>
		<p>If you <span style="font-size:16px;"><b>do not want to use</b></span> miniOrange Cloud Single Sign on service. <a style="cursor:pointer;" id="help_working_title1" >Click Here</a> to know how the plugin works for this case. Contact us at <b>info@miniorange.com</b> to get its licensing plans info or you can install this plugin which did not use miniOrange Cloud SSO service by <a href="https://wordpress.org/plugins/miniorange-saml-20-single-sign-on/" target="_blank">clicking here</a>. Make sure you deactivate miniOrange SAML Cloud SSO plugin before installing that plugin.</p>
		<br>
		
		<div hidden id="help_working_desc1" class="mo_saml_help_desc">
						<h3>Option 2 : Not Using miniOrange Single Sign On service:</h3>
						<div style="display:block;text-align:center;">
						<img src="<?php echo plugin_dir_url(__FILE__) . 'images/saml_working_womo.png'?>" alt="Working of miniOrange SAML plugin" style="width: 55%;"/>
						</div>
						<ol>
							<li>miniOrange SAML SSO plugin sends a login request to your Identity Provider for authentication.</li>
							<li>Upon successful authentication, your Identity Provider sends a SAML Response back to miniOrange SAML SSO plugin. Plugin then reads the response and logins the user.</li>
						</ol>
						<div>
							<b>Advantages:</b>
							<ol>
								<li>The number of messages required for Single Sign On is less as your website is directly interacting with the Identity Provider.</li>
							</ol>
						</div>
					</div>
		If you have any doubts regarding the licensing plans, you can mail us at <a href="mailto:info@miniorange.com"><i>info@miniorange.com</i></a> or submit a query using the <b>support form</b> on right.
		<br>
		
		<br>
		
	</div>
<?php 
}
?>
