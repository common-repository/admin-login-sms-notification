<?php

/*
	Plugin Name: Admin login SMS notification
	Plugin URI: http://cyber-notes.net
	Description: Sends SMS notification when admin logged in blog (for sending SMS using API from service SMS.RU)
	Author: Santiaga
	Version: 1.0
	Author URI: http://cyber-notes.net
	License: GPLv2 or later
*/

/* For all functions in plugin using prefix wp_smsrunot_ */


/* Localization */
add_action('plugins_loaded','wp_smsrunot_text_domain',1);

function wp_smsrunot_text_domain() {
	load_plugin_textdomain('wp_smsrunot',false,dirname(plugin_basename(__FILE__)).'/lang/');
}

/* Admin Interface and Options */
if(is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX)) {
	
	add_action('admin_menu','wp_smsrunot_options');
	
	function wp_smsrunot_options() {
		/* Add new submenu */
		add_options_page('SMS Login Notification','SMS Login Notification','manage_options','wp_smsrunot','wp_smsrunot_admin_interface');
		/* Add options */
		add_option('wp_smsrunot_enable','');
		add_option('wp_smsrunot_apikey','');
		add_option('wp_smsrunot_number','');
	}

	function wp_smsrunot_admin_interface() {
		/* Start Options Form */
		echo "
			<h3>".__('SMS notification settings','wp_smsrunot').":</h3>\n
			<form method=\"post\" action=\"options.php\" id=\"options\">\n
			";
		wp_nonce_field('update-options');
		/* Enable or Disable notifications */
		if(get_option('wp_smsrunot_enable')=="on") {
			echo "<label>".__('Enable notifications','wp_smsrunot').": </label><input type=\"checkbox\" name=\"wp_smsrunot_enable\" checked=\"checked\" /><br><br>\n";
		} else {
			echo "<label>".__('Enable notifications','wp_smsrunot').": </label><input type=\"checkbox\" name=\"wp_smsrunot_enable\" /><br><br>\n";
		}
		/* Set API key */
		echo "<label>".__('Your sms.ru api key','wp_smsrunot').":</label><input type=\"text\" size=\"50\" name=\"wp_smsrunot_apikey\" value=\"".get_option('wp_smsrunot_apikey')."\" /><br>\n";
		/* Set phone number */
		echo "<label>".__('Phone number on which you want receive sms notifications','wp_smsrunot').":</label><input type=\"text\" size=\"30\" name=\"wp_smsrunot_number\" value=\"".get_option('wp_smsrunot_number')."\" /><br>\n";
		/* Form validation options */
		echo "<input type=\"hidden\" name=\"action\" value=\"update\" />\n";
		echo "<input type=\"hidden\" name=\"page_options\" value=\"wp_smsrunot_apikey,wp_smsrunot_number,wp_smsrunot_enable\" />\n";
		/* Send button and end of options form */
		echo "<br><input type=\"submit\" class=\"button-primary\" name=\"submit\" value=\"".__('Save Changes','wp_smsrunot')."\">\n
			</form>\n
			<br>\n
			";
	}

}

/* Start session if not already started */
if(session_id() == ''){
	session_start();
}

/* SMS sending function */
function wp_smsrunot_sendsms() {
	/* Check is user is admin, user not already logged in and notifications enabled */
	if(current_user_can('manage_options') && $_SESSION['logged_in_once']!="1" && get_option('wp_smsrunot_enable')=="on") {
		/* Blog URL */
		$blog_url=get_site_url();
		/*Phone number */
		$phone_number=get_option('wp_smsrunot_number');
		/* API key */
		$api_key=get_option('wp_smsrunot_apikey');
		/* Get admin login time */
		$login_time=date('H:i+j+F+Y');
		/* Get admin ip */
		$ip=getenv('HTTP_CLIENT_IP')?:getenv('HTTP_X_FORWARDED_FOR')?:getenv('HTTP_X_FORWARDED')?:getenv('HTTP_FORWARDED_FOR')?:getenv('HTTP_FORWARDED')?:getenv('REMOTE_ADDR');
		/* Text of SMS notification */
		$notification=$blog_url."+-+admin+login+on+".$login_time."+from+ip+".$ip;
		/* Send SMS */
		$sms=file_get_contents("http://sms.ru/sms/send?api_id=".$api_key."&to=".$phone_number."&text=".$notification);
		/* Set info to session that notification sended and in this session not need to send more notifications */
		$_SESSION['logged_in_once']=1;
	}
}
add_action('admin_notices','wp_smsrunot_sendsms');

?>