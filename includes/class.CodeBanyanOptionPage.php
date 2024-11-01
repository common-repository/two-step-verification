<?php
/**
* @package WordPress
* @subpackage CodeBanyanOptionPage
* @author Nazmul Ahsan
* @since 1.0.0
*/
class CodeBanyanOptionPage{
	
	public $pro_url = 'http://medhabi.com/product/two-step-verification-pro/';

	public function __construct(){
		add_action( 'admin_enqueue_scripts', array($this, 'mdc_enqueue_scripts') );
		add_action( 'admin_menu', array($this, 'plugin_settings') );
	}

	public function cb_get_option($id){
		$options = get_option('TwoStepVerification');
		$option = $options[$id];
		return $option;
	}

	public function mdc_enqueue_scripts(){
		wp_enqueue_style( 'option-page-tab', plugins_url('../assets/top.css', __FILE__));
		wp_enqueue_style( 'option-page-custom', plugins_url('../assets/admin.css', __FILE__));
		wp_enqueue_script( 'jquery', 'jquery' );
		wp_enqueue_script( 'jquery-ui-tabs', 'jquery-ui-tabs' );
		wp_enqueue_script( 'skinable_tabs', plugins_url('../assets/skinable_tabs.min.js', __FILE__) );
		wp_enqueue_script( 'cb_custom', plugins_url('../assets/admin.js', __FILE__), array(), '1.0.0', true );
	}

	public function plugin_settings() {
		add_menu_page('Two Step Verification', '2 Step Verification', 'manage_options', 'two_step_settings', array($this, 'two_step_settings'), 'dashicons-admin-network', '71.15' );
	}

	public function two_step_settings() {
?>
<div class="wrap">
	<form action="options.php" method="post" name="options" class="cb_option_form">
	<?php echo wp_nonce_field('update-options'); ?>
	<h2>Two Step Verification Settings</h2>
		<div class="tabs_holder">

			<ul>
				<li class="tab_selected"><a href="#general_settings">General</a></li>
				<li><a href="#method_method">Configuration</a></li>
				<li><a href="#security_settings">Security</a></li>
				<li><a href="#customization">Customization</a></li>
			</ul>
			<div class="content_holder">
				<div id="general_settings">
					<table class="form-table" width="100%" cellpadding="10">
						<tbody>
							<tr>
								<td scope="row" align="left">
									<label for="verification_page">Verification Page</label>
									<select name="TwoStepVerification[verification_page]" id="verification_page">
										<option>Select a Page</option>
										<?php
										$pages = get_pages();
										foreach ($pages as $page) {
											$page_selected = ($this->cb_get_option('verification_page') == $page->ID) ? 'selected="selected"' : '';
											echo '<option value="'.$page->ID.'" '.$page_selected.'>'.$page->post_title.'</option>';
										}
										?>
									</select>
									<span>The page that contains verification form.</span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="verification_method">Verification Method</label>
									<select name="TwoStepVerification[verification_method]" id="verification_method">
										<option>Select an Option</option>
										<option value="Mail"<?php echo ($this->cb_get_option('verification_method') == 'Mail') ? ' selected': ''; ?>>Mail</option>
										<option disabled>SMS  (PRO Feature)</option>
									</select>
									<span class="pro_feature"> <a href="<?php echo $this->pro_url; ?>" target="_blank">Send verification code via SMS! Get PRO now.</a></span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div id="method_method">
					<table class="form-table" width="100%" cellpadding="10">
						<?php if($this->cb_get_option('verification_method') != 'Mail'){
							$is_mail_hidden = 'hidden';
						} if($this->cb_get_option('verification_method') != ''){
							$is_other_hidden = 'hidden';
						} ?>
						<!-- Mail Fields -->
						<tbody class="<?php echo $is_mail_hidden?>">
							<tr>
								<td scope="row" align="left">
									<label for="from_email">From email</label>
									<input type="email" id="from_email" name="TwoStepVerification[from_email]" value="<?php echo $this->cb_get_option('from_email'); ?>">
									<span>'Sender Email' of the verification mail.</span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="from_name">From Name</label>
									<input type="text" id="from_name" name="TwoStepVerification[from_name]" value="<?php echo $this->cb_get_option('from_name'); ?>">
									<span>'Sender Name' of the verification mail.</span>
								</td>
							</tr>
						</tbody>
						<!-- NOT SELECTED Fields -->
						<tbody class="<?php echo $is_other_hidden?>">
							<tr>
								<td scope="row" align="left">
								<a href="#general_settings">Please choose a Verification Method.</a>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div id="security_settings">
					<table class="form-table" width="100%" cellpadding="10">
						<tbody>
							<tr>
								<td scope="row" align="left">
									<label for="code_expiry">Code Expires</label>
									<input type="number" min="1" id="code_expiry" name="TwoStepVerification[code_expiry][digit]" value="<?php echo $this->cb_get_option('code_expiry')['digit']; ?>">
									<select name="TwoStepVerification[code_expiry][unit]">
										<option>Select a Unit</option>
										<?php
										$code_units = array('Seconds', 'Minutes', 'Hours', 'Days', 'Weeks', 'Months', 'Years');
										foreach ($code_units as $code_unit) {
											$code_unit_selected = ($this->cb_get_option('code_expiry')['unit'] == $code_unit) ? 'selected="selected"' : '';
											echo '<option '.$code_unit_selected.'>'.$code_unit.'</option>';
										}
										?>
									</select>
									<span>Users need to verify within this time period. Minimum is 5 minutes.</span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="verify_expiry">Verification Expires</label>
									<input type="number" min="1" id="verify_expiry" name="TwoStepVerification[verify_expiry][digit]" value="<?php echo $this->cb_get_option('verify_expiry')['digit']; ?>">
									<select name="TwoStepVerification[verify_expiry][unit]">
										<option>Select a Unit</option>
										<?php
										$verify_units = array('Seconds', 'Minutes', 'Hours', 'Days', 'Weeks', 'Months', 'Years');
										foreach ($verify_units as $verify_unit) {
											$verify_unit_selected = ($this->cb_get_option('verify_expiry')['unit'] == $verify_unit) ? 'selected="selected"' : '';
											echo '<option '.$verify_unit_selected.'>'.$verify_unit.'</option>';
										}
										?>
									</select>
									<span>Verification expires after this time period. Minimum is 1 hour.</span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="logout_expires">Expires upon Logout</label>
									<input type="checkbox" id="logout_expires" name="TwoStepVerification[logout_expires]" value="1" checked disabled >
									<span>If enabled, users need to verify every time they login. If disabled, users will have options to choose themselves.</span><span class="pro_feature"> <a href="<?php echo $this->pro_url; ?>" target="_blank">(PRO Feature)</a></span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="code_length">Verification Code Length</label>
									<input type="number" id="code_length" min="4" name="TwoStepVerification[code_length]" value="4" disabled >
									<span>Minimum is 4</span><span class="pro_feature"> <a href="<?php echo $this->pro_url; ?>" target="_blank">(PRO Feature)</a></span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="code_cap">Capital Letters in Code</label>
									<input type="checkbox" id="code_cap" name="TwoStepVerification[code_cap]" value="1" disabled >
									<span class="pro_feature"> <a href="<?php echo $this->pro_url; ?>" target="_blank">(PRO Feature)</a></span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="code_sml">Small Letters in Code</label>
									<input type="checkbox" id="code_sml" name="TwoStepVerification[code_sml]" value="1" disabled >
									<span class="pro_feature"> <a href="<?php echo $this->pro_url; ?>" target="_blank">(PRO Feature)</a></span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="code_num">Numbers in Code</label>
									<input type="checkbox" id="code_num" name="TwoStepVerification[code_num]" value="1" checked disabled >
									<span >Default</span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="code_sym">Special characters in Code</label>
									<input type="checkbox" id="code_sym" name="TwoStepVerification[code_sym]" value="1" disabled >
									<span>Include <code>!@#$%^&amp;*()</code> in verification code</span><span class="pro_feature"> <a href="<?php echo $this->pro_url; ?>" target="_blank">(PRO Feature)</a></span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div id="customization">
					<table class="form-table" width="100%" cellpadding="10">
						<tbody>
							<tr>
								<td scope="row" align="left">
									<label for="form_text">Verification Form Text</label>
									<textarea id="form_text" name="TwoStepVerification[form_text]"><?php echo $this->cb_get_option('form_text'); ?></textarea>
									<span>Texts to be shown on verification page</span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="wrong_code_text">Wrong Code Text</label>
									<textarea id="wrong_code_text" name="TwoStepVerification[wrong_code_text]"><?php echo $this->cb_get_option('wrong_code_text'); ?></textarea>
									<span>Texts to be shown if a user submits wrong or expired verification code</span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="verification_msg_body">Verification Message Body</label>
									<textarea id="verification_msg_body" name="TwoStepVerification[verification_msg_body]"><?php echo $this->cb_get_option('verification_msg_body'); ?></textarea>
									<span>Main body of verification message to be sent via email/SMS. Must contain the string <code>%%CODE%%</code> in it</span>
								</td>
							</tr>
							<tr class="<?php echo ($this->cb_get_option('verification_method') != 'Mail') ? 'hidden ' : ''; ?>">
								<td scope="row" align="left">
									<label for="verification_msg_sub">Message Subject</label>
									<input type="text" id="verification_msg_sub" name="TwoStepVerification[verification_msg_sub]" value="<?php echo $this->cb_get_option('verification_msg_sub'); ?>">
									<span>Subject of the mail to be sent</span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="custom_error_msg">Error Message</label>
									<textarea id="custom_error_msg" name="TwoStepVerification[custom_error_msg]"><?php echo $this->cb_get_option('custom_error_msg'); ?></textarea>
									<span>Example: If users logout in case of verification expiry. Using <code>%%logout_url%%</code> is recommended</span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="login_to_see_msg">Ask to Login</label>
									<textarea id="login_to_see_msg" name="TwoStepVerification[login_to_see_msg]"><?php echo $this->cb_get_option('login_to_see_msg'); ?></textarea>
									<span>If a logged out user visits the verification page. Using <code>%%login_url%%</code> is recommended</span>
								</td>
							</tr>
							<tr>
								<td scope="row" align="left">
									<label for="verify_success_msg">Success Message</label>
									<textarea id="verify_success_msg" name="TwoStepVerification[verify_success_msg]"><?php echo $this->cb_get_option('verify_success_msg'); ?></textarea>
									<span>Texts on verification page if they succeded.</span>
								</td>
							</tr>
							<tr>
								<td><strong>*</strong> Keep blank to use default values.</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div><!-- /.content_holder -->
		</div><!-- /.tabs_holder -->

	<?php if($this->cb_get_option('complete_installation') != null){?>
		<input type="hidden" name="TwoStepVerification[complete_installation]" value="<?php echo $this->cb_get_option('complete_installation'); ?>" />
	<?php } ?>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="TwoStepVerification" />
	<p class="submit"><input type="submit" value="Save Changes" class="button button-primary" id="submit" name="submit"></p>
	</form>
 </div>

<?php
	}
}

$code_banyan_option_page = new CodeBanyanOptionPage;