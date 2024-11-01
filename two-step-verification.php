<?php
/**
*	Plugin Name: Two Step Verification
*	Description: An awesome tool to enable two step verification system on your WordPress site.
*	Author: Nazmul Ahsan
*	Author URI: http://nazmulahsan.me
*	Plugin URI: https://wordpress.org/plugins/two-step-verification/
*	Version: 1.0.0
*	Textdomain: two-step-verification
*/
if( ! class_exists('CodeBanyanLoginVerification') ){
	require 'includes/class.CodeBanyanLoginVerification.php';
	require 'includes/class.CodeBanyanOptionPage.php';
}