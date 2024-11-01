<?php
/**
* @package WordPress
* @subpackage CodeBanyanLoginVerification
* @author Nazmul Ahsan
* @since 1.0.0
*/
class CodeBanyanLoginVerification{

	public $code_expires_after = 300; //5 minutes
	public $verification_expires_after = 3600; //1 hour
	public $verification_code_length = 4;
	
	public function __construct(){
		add_shortcode('2_step_form', array($this, 'submit_verify_code_form'));
		add_action( 'wp_enqueue_scripts', array($this, 'mdc_enqueue_scripts') );
		
		if (!$this->is_shortcode_found()) {
			add_action('admin_notices', array($this, 'shortcode_error_notice') );
		}
		elseif( $this->cb_get_option('verification_msg_body') != null && !$this->is_code_found_in_msg()) {
			add_action('admin_notices', array($this, 'code_not_found_error_notice') );
		}
		else{
			add_action('init', array($this, 'logged_in_not_verified'));
		}

		add_action('wp_login', array($this, 'code_has_been_sent'), 10, 2);

		add_action('wp_logout', array($this, 'reset_cookies') );
		
		if(isset($_POST['code']) && $this->encrypt($_POST['code']) == $_COOKIE['verification_code']){
			add_action('init', array($this, 'set_verified_cookie'));
		}
	}

	public function cb_get_option($id){
		$options = get_option('TwoStepVerification');
		$option = $options[$id];
		return $option;
	}

	public function mdc_enqueue_scripts(){
		wp_enqueue_style( 'two-step-verification', plugins_url('../assets/custom.css', __FILE__));
	}

	public function encrypt($code){
		return md5($code);
	}

	public function wp_setcookie($cookie_name, $cookie_value, $expiry){
		$path = '/';//parse_url(get_option('siteurl'), PHP_URL_PATH);
		$host = parse_url(get_option('siteurl'), PHP_URL_HOST);
		setcookie($cookie_name, $cookie_value, $expiry, $path, $host);
	}

	public function send_secret_code($user_id, $code){
		$user_email = get_userdata($user_id)->user_email;
		$user_phone = get_userdata($user_id)->user_phone;
		$verification_msg_sub = $this->cb_get_option('verification_msg_sub');
		$subject = ($verification_msg_sub != '') ? $verification_msg_sub : 'Verification Code';
		$verification_msg_body = $this->cb_get_option('verification_msg_body');
		$verification_msg_body = ($verification_msg_body != '') ? str_replace('%%CODE%%', $code, $verification_msg_body) : '';
		$message = ($verification_msg_body != '') ? $verification_msg_body : 'Your verification code is '.$code;
		$from_name = ($this->cb_get_option('from_name') != '') ? $this->cb_get_option('from_name') : get_bloginfo();
		$from_email = ($this->cb_get_option('from_email') != '') ? $this->cb_get_option('from_email') : get_bloginfo('admin_email');
		$headers = 'From: '.$from_name.' <'.$from_email.'>' . "\r\n";
		
		wp_mail( $user_email, $subject, $message, $headers);
	}

	public function code_has_been_sent($user_login, $user){
		$user = get_userdatabylogin($user_login);
		$user_id = $user->ID;

		$code_len = $this->verification_code_length;
		$str = '1234567890';
		$sfl = str_shuffle($str);
		$code = substr($sfl, (count($str) - ($code_len + 1))); //1 to fix array index issue
		$this->send_secret_code($user_id, $code);
		$expiry = strtotime($this->cb_get_option('code_expiry')['digit'] ." ". strtolower($this->cb_get_option('code_expiry')['unit']));
		$expiry_default = time() + $this->code_expires_after; //Default is 5 minutes
		$expiry = ($expiry < $expiry_default) ? $expiry_default : $expiry; 
		$this->wp_setcookie('verification_code', $this->encrypt($code), $expiry);
	}
	
	public function is_code_found_in_msg(){
		$verification_msg_body = $this->cb_get_option('verification_msg_body');
		if (strpos($verification_msg_body,'%%CODE%%') !== false) {
			return true;
		}
		else{
			return false;
		}
	}

	public function code_not_found_error_notice(){
		$error_fix_page = admin_url('admin.php?page=two_step_settings')."#customization";
		$msg = '
			<div class="error" id="message">
				<p><strong>Warning: </strong> The string <code>%%CODE%%</code> is not found in custom message.</p>
				<p class="submit"><a class="button-primary" href="'.$error_fix_page.'">Go to Settings Page</a></p>
			</div>
		';
		echo $msg;
	}

	public function logged_in_not_verified(){
		$redirect_page = $this->cb_get_option('verification_page');
		if ($_COOKIE['2_step_verified'] != $this->encrypt('yes')) {
			show_admin_bar( false );
			if (is_admin()) {
				wp_redirect(get_permalink($redirect_page));
			}
		}
	}

	public function is_shortcode_found(){
		$redirect_page = $this->cb_get_option('verification_page');
		$content =  get_post_field('post_content', $redirect_page);
		if (strpos($content,'[2_step_form]') !== false) {
			return true;
		}
		else{
			return false;
		}
	}

	public function shortcode_error_notice(){
		$redirect_page = $this->cb_get_option('verification_page');
		if($redirect_page != null){
			$error_fix_page = get_edit_post_link($redirect_page);
			$text = '<strong>Warning: </strong> <i>Verification page</i> does not contain the shortcode <code>[2_step_form]</code> in it.';
			$button = 'Edit Verification Page';
		}
		else{
			$error_fix_page = admin_url('admin.php?page=two_step_settings')."#general_settings";
			$text = '<strong>Warning: </strong> You did not set <i>Verification page</i>.';
			$button = 'Go to Settings Page';
		}
		$msg = '
			<div class="error" id="message">
				<p>'.$text.'</p>
				<p class="submit"><a class="button-primary" href="'.$error_fix_page.'">'.$button.'</a></p>
			</div>
		';
		echo $msg;
	}

	public function set_verified_cookie(){
		$expiry = strtotime($this->cb_get_option('verify_expiry')['digit'] ." ". strtolower($this->cb_get_option('verify_expiry')['unit']));
		$expiry_default = time() + $this->verification_expires_after;
		$expiry = ($expiry < $expiry_default) ? $expiry_default : $expiry; 
		$this->wp_setcookie('2_step_verified', $this->encrypt('yes'), $expiry);
	}

	public function reset_cookies(){
		$this->wp_setcookie('verification_code', null, -1);
		$this->wp_setcookie('2_step_verified', null, -1);
	}

	public function submit_verify_code_form(){
		$shortcode;
		if(is_user_logged_in()){
			if ($_COOKIE['2_step_verified'] != $this->encrypt('yes') && $_COOKIE['verification_code'] != $this->encrypt($_POST['code'])) {
				
				if ($this->cb_get_option('complete_installation') != 1) {
					$shortcode .= 'If you have just configured the plugin and seen this page for first time, then you\'ll not get any verification code this time. Please <a href="'.wp_logout_url().'">logout</a> and login again.';
					$options = get_option('TwoStepVerification');
					$options['complete_installation'] = 1;
					update_option( 'TwoStepVerification', $options);
				}
				else{
					if(isset($_COOKIE['verification_code'])){
						$sent_to = ($this->cb_get_option('verification_method') != 'SMS') ? 'mail' : 'phone';
						$form_text = ($this->cb_get_option('form_text') != '') ? $this->cb_get_option('form_text') : 'A verification code has been sent to your '.$sent_to.'. Please submit the code in the form below.';
						$shortcode .= $form_text;
						if (isset($_POST['code'])) {
							$wrong_code_text = ($this->cb_get_option('wrong_code_text') != '') ? $this->cb_get_option('wrong_code_text') : 'The verification code you submitted was wrong.';
							$shortcode .= '<br />'.$wrong_code_text;
						}
						$shortcode .= '<form action="" method="POST" class="verify_code_form">';
						$shortcode .= '<div class="form_label"><label for="verify_code_input">Input Code:</label></div>';
						$shortcode .= '<div class="form_input"><input id="verify_code_input" type="text" name="code" /></div>';
						$shortcode .= '<div class="form_submit"><input type="submit" value="Verify" /></div>';
						$shortcode .= '</form>';
					} else{
						$shortcode = ($this->cb_get_option('custom_error_msg')) ? str_replace('%%logout_url%%', '<a href="'.wp_logout_url().'">logout</a>', $this->cb_get_option('custom_error_msg')) : 'Something was wrong. Your verification may be expired. Please <a href="'.wp_logout_url().'">logout</a> and login again';
					}
				}
			}
			else{
				$shortcode .= $this->cb_get_option('verify_success_msg') ? $this->cb_get_option('verify_success_msg') : "You have been verified! <a href=''>Click here</a> to refresh the page.";
			}
		}
		else{
			$shortcode = ($this->cb_get_option('login_to_see_msg')) ? str_replace('%%login_url%%', '<a href="'.wp_login_url(get_permalink()).'">login</a>', $this->cb_get_option('login_to_see_msg')) : 'You need to <a href="'.wp_login_url(get_permalink()).'">login</a> to see this page.';
		}
		return '<div class="two-step-verification">' . $shortcode . '</div>';
	}
}

$mdc_2_step_login_verification = new CodeBanyanLoginVerification;