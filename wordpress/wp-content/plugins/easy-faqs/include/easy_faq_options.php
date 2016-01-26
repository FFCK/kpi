<?php
/*
This file is part of Easy FAQs.

Easy FAQs is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Easy FAQs is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with The Easy FAQs.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once("easy_faqs_config.php");
include("lib/easy_faqs_importer.php");
include("lib/easy_faqs_exporter.php");

class easyFAQOptions
{
	var $textdomain = 'easy-faqs';
	var $shed = false;
	var $root = false;

	function __construct($root = false){
		//may be running in non WP mode (for example from a notification)
		if(function_exists('add_action')){
			//add a menu item
			add_action('admin_menu', array($this, 'add_admin_menu_item'));		
		}
		
		// create the BikeShed object now, so that BikeShed can add its hooks
		$this->shed = new Easy_FAQs_GoldPlugins_BikeShed();
		
		if($root) {
			$this->root = $root;
		}
	}
	
	function add_admin_menu_item(){
		$title = "Easy FAQs Settings";
		$page_title = "Easy FAQs Settings";
		$top_level_slug = "easy-faqs-settings";
		
		//create new top-level menu
		add_menu_page($page_title, $title, 'administrator', $top_level_slug , array($this, 'basic_settings_page'));
		add_submenu_page($top_level_slug , 'Basic Options', 'Basic Options', 'administrator', $top_level_slug, array($this, 'basic_settings_page'));
		add_submenu_page($top_level_slug , 'Themes', 'Themes', 'administrator', 'easy-faqs-themes', array($this, 'themes_page'));
		add_submenu_page($top_level_slug , 'Shortcode Generator', 'Shortcode Generator', 'administrator', 'easy-faqs-shortcode-generator', array($this, 'shortcode_generator_page'));
		if (isValidFAQKey()) {
			add_submenu_page($top_level_slug , 'Question Form Options', 'Question Form', 'administrator', 'easy-faqs-submission-form-options', array($this, 'submission_form_options'));
			add_submenu_page($top_level_slug , 'Import & Export', 'Import & Export', 'administrator', 'easy-faqs-import-export', array($this, 'import_export_page'));
			add_submenu_page($top_level_slug , 'Recent Searches', 'Recent Searches', 'administrator', 'easy-faqs-recent-searches', array($this, 'recent_searches_page'));
		} else {
			add_submenu_page($top_level_slug , 'Question Form Options (Pro)', 'Question Form (Pro)', 'administrator', 'easy-faqs-submission-form-options', array($this, 'submission_form_options'));
			add_submenu_page($top_level_slug , 'Import & Export (Pro)', 'Import & Export (Pro)', 'administrator', 'easy-faqs-import-export', array($this, 'import_export_page'));
			add_submenu_page($top_level_slug , 'Recent Searches (Pro)', 'Recent Searches (Pro)', 'administrator', 'easy-faqs-recent-searches', array($this, 'recent_searches_page'));
		}
		add_submenu_page($top_level_slug , 'Help & Instructions', 'Help & Instructions', 'administrator', 'easy-faqs-help', array($this, 'help_settings_page'));

		//call register settings function
		add_action( 'admin_init', array($this, 'register_settings'));	
	}
	
	//function to produce tabs on admin screen
	function admin_tabs( $current = 'homepage' ) {
	
		if (isValidFAQKey()) {
			$tabs = array( 'easy-faqs-settings' => __('Basic Options', $this->textdomain),
						   'easy-faqs-themes' => __('Themes', $this->textdomain),
						   'easy-faqs-shortcode-generator' => __('Shortcode Generator', $this->textdomain),
						   'easy-faqs-submission-form-options' => __('Question Form', $this->textdomain),
						   'easy-faqs-import-export' => __('Import & Export FAQs', $this->textdomain),
						   'easy-faqs-recent-searches' => __('Recent Searches', $this->textdomain),
						   'easy-faqs-help' => __('Help & Instructions', $this->textdomain)
						 );
		} else {
			$tabs = array( 'easy-faqs-settings' => __('Basic Options', $this->textdomain),
						   'easy-faqs-themes' => __('Themes', $this->textdomain),
						   'easy-faqs-shortcode-generator' => __('Shortcode Generator', $this->textdomain),
						   'easy-faqs-submission-form-options' => __('Question Form (Pro)', $this->textdomain),
						   'easy-faqs-import-export' => __('Import & Export FAQs (Pro)', $this->textdomain),
						   'easy-faqs-recent-searches' => __('Recent Searches (Pro)', $this->textdomain),
						   'easy-faqs-help' => __('Help & Instructions', $this->textdomain)
						 );
		}
		
		echo '<div id="icon-themes" class="icon32"><br></div>';
		echo '<h2 class="nav-tab-wrapper">';
			foreach( $tabs as $tab => $name ){
				$class = ( $tab == $current ) ? ' nav-tab-active' : '';
				echo "<a class='nav-tab$class' href='?page=$tab'>$name</a>";
			}
		echo '</h2>';
	}

	function register_settings(){
		//register our settings
		register_setting( 'easy-faqs-settings-group', 'faqs_link' );
		register_setting( 'easy-faqs-settings-group', 'faqs_read_more_text' );
		register_setting( 'easy-faqs-settings-group', 'faqs_image' );
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_custom_css' );	
		
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_question_font_size' );	
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_question_font_color' );	
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_question_font_style' );	
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_question_font_family' );	
		
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_answer_font_size' );	
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_answer_font_color' );	
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_answer_font_style' );	
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_answer_font_family' );	
		
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_read_more_link_font_size' );	
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_read_more_link_font_color' );	
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_read_more_link_font_style' );	
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_read_more_link_font_family' );			
		
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_registered_name' );
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_registered_url' );
		register_setting( 'easy-faqs-settings-group', 'easy_faqs_registered_key' );
		
		// theme options
		register_setting( 'easy-faqs-theme-settings-group', 'faqs_style' );
		register_setting( 'easy-faqs-theme-settings-group', 'easy_faqs_preview_window_background' );


		//submission form options
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_use_captcha' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faq_submit_notification_address' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_question_label' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_question_description' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_name_label' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_name_description' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_submit_label' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_submit_success_redirect_url' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_submit_success_message' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_submit_notification_address' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_submit_notification_include_question' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_submit_button_label' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_name_error_message' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_question_error_message' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_captcha_error_message' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_general_error_message' );
	}

	function settings_page_top(){
		$title = "Easy FAQs Settings";
		$message = "Easy FAQs Settings Updated.";
		
		global $pagenow;
		global $current_user;
		get_currentuserinfo();
	?>
	<script type="text/javascript">
	jQuery(function () {
		if (typeof(gold_plugins_init_coupon_box) == 'function') {
			gold_plugins_init_coupon_box();
		}
	});
	</script>
	<?php if(isValidFAQKey()): ?>
	<div class="wrap easy_faqs_wrapper gold_plugins_settings">
	<?php else: ?>
	<div class="wrap easy_faqs_wrapper gold_plugins_settings not-pro">
	<?php endif; ?>
		<h2><?php echo $title; ?></h2>
		<?php if(!isValidFAQKey()): ?>
			<!-- Begin MailChimp Signup Form -->
			<style type="text/css">
			</style>
			<div id="signup_wrapper">
				<div class="topper">
					<h3>Save 20% on Easy FAQs Pro!</h3>
					<p class="pitch">Submit your name and email and we’ll send you a coupon for 20% off your upgrade to the Pro version.</p>
				</div>
				<div id="mc_embed_signup">
					<form action="" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
						<div class="fields_wrapper">
							<label for="mce-NAME">Your Name:</label>
							<input type="text" value="<?php echo (!empty($current_user->display_name) ?  $current_user->display_name : ''); ?>" name="NAME" class="name" id="mce-NAME" placeholder="Your Name">
							<label for="mce-EMAIL">Your Email:</label>
							<input type="email" value="<?php echo (!empty($current_user->user_email) ?  $current_user->user_email : ''); ?>" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
							<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
							<div style="position: absolute; left: -5000px;"><input type="text" name="b_403e206455845b3b4bd0c08dc_6ad78db648" tabindex="-1" value=""></div>
						</div>
						<div class="clear"><input type="submit" value="Send Me The Coupon Now" name="subscribe" id="mc-embedded-subscribe" class="smallBlueButton"></div>
						<p class="secure"><img src="<?php echo plugins_url( 'img/lock.png', __FILE__ ); ?>" alt="Lock" width="16px" height="16px" />We respect your privacy.</p>
						
						<input type="hidden" name="PRODUCT" value="Easy FAQs Pro" />
						<input type="hidden" id="mc-upgrade-plugin-name" value="Easy FAQs Pro" />
						<input type="hidden" id="mc-upgrade-link-per" value="https://goldplugins.com/purchase/easy-faqs/single?promo=newsub20" />
						<input type="hidden" id="mc-upgrade-link-biz" value="https://goldplugins.com/purchase/easy-faqs/business?promo=newsub20" />
						<input type="hidden" id="mc-upgrade-link-dev" value="https://goldplugins.com/purchase/easy-faqs/developer?promo=newsub20" />

						<div class="features">
							<strong>When you upgrade to Pro, you'll instantly unlock:</strong>
							<ul>
								<li>100+ Professionally Designed Themes</li>
								<li>Question Submission forms for your users</li>
								<li>Accordion-Style FAQ pages</li>
								<li>Quick Links for your FAQ pages</li>
								<li>Search Forms for your FAQs</li>
								<li>Import/Export your FAQs</li>
								<li>Remove all banners from the admin area</li>							
								<li>And more!</li>								
							</ul>
							<a class="learn_more_link" href="https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_campaign=upgrade_sidebar&utm_source=learn_more_link" target="_blank">Click Here To Learn More! &raquo;</a>
						</div>
					</form>
				</div>
				<p class="u_to_p"><a href="https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/">Upgrade to Easy FAQs Pro now</a> to remove banners like this one.</p>
			</div>
			<!--End mc_embed_signup-->
		<?php endif; ?>
		
		<?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') : ?>
		<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
		<?php endif;
		
		$this->get_and_output_current_tab($pagenow);	
	}
	
	function get_and_output_current_tab($pagenow){
		$tab = $_GET['page'];
		
		$this->admin_tabs($tab); 
				
		return $tab;
	}
	
	function basic_settings_page()
	{
		$this->settings_page_top(); ?>	
		
		<form method="post" action="options.php">		
			<?php settings_fields( 'easy-faqs-settings-group' ); ?>			
			
			<h3>Basic Options</h3>			
			<p>Use these options to customize the display of your FAQs.</p>
			
			<table class="form-table">
				<?php
					$faqs_themes_page_url = admin_url('admin.php?page=easy-faqs-themes');
					$themes_moved_msg = 'Themes now have their own tab!';
					$themes_moved_link_text = 'Click here to check them out.';
					$message_html = sprintf('<em>%s <a href="%s">%s</a></em>', $themes_moved_msg, $faqs_themes_page_url, $themes_moved_link_text);
					$this->shed->message( array('label' =>'FAQs Theme', 'message' => $message_html ) );

					// Question Font (typography)
					$values = array(
						'font_size' => get_option('easy_faqs_question_font_size'),
						'font_family' => get_option('easy_faqs_question_font_family'),
						'font_style' => get_option('easy_faqs_question_font_style'),
						'font_color' => get_option('easy_faqs_question_font_color'),
					);
					$this->shed->typography( array('name' => 'easy_faqs_question_*', 'label' =>'Question Font', 'description' => 'Choose a font size, family, style, and color.', 'google_fonts' => true, 'default_color' => '#878787', 'values' => $values) );

					// Answer Font (typography)
					$values = array(
						'font_size' => get_option('easy_faqs_answer_font_size'),
						'font_family' => get_option('easy_faqs_answer_font_family'),
						'font_style' => get_option('easy_faqs_answer_font_style'),
						'font_color' => get_option('easy_faqs_answer_font_color'),
					);
					$this->shed->typography( array('name' => 'easy_faqs_answer_*', 'label' =>'Answer Font', 'description' => 'Choose a font size, family, style, and color.', 'google_fonts' => true, 'default_color' => '#878787', 'values' => $values) );

					// View All Link Font (typography)
					$values = array(
						'font_size' => get_option('easy_faqs_read_more_link_font_size'),
						'font_family' => get_option('easy_faqs_read_more_link_font_family'),
						'font_style' => get_option('easy_faqs_read_more_link_font_style'),
						'font_color' => get_option('easy_faqs_read_more_link_font_color'),
					);
					$this->shed->typography( array('name' => 'easy_faqs_read_more_link_*', 'label' =>'View All Link Font', 'description' => 'Choose a font size, family, style, and color.', 'google_fonts' => true, 'default_color' => '#878787', 'values' => $values) );
					
				
					// Custom CSS (textarea)
					$this->shed->textarea( array('name' => 'easy_faqs_custom_css', 'label' =>'Custom CSS', 'value' => get_option('easy_faqs_custom_css'), 'description' => 'Input any Custom CSS you want to use here.  The plugin will work without you placing anything here - this is useful in case you need to edit any styles for it to work with your theme, though.') );
					
					// FAQS - View All Link (text)
					$this->shed->text( array('name' => 'faqs_link', 'label' =>'FAQs View All Link', 'value' => get_option('faqs_link'), 'description' => 'Enter the URL of your FAQs page here. If you do, a \'View All\' link will displayed after each of your FAQs which directs visitors to this URL. ') );

					// FAQS - View All Text (text)
					$this->shed->text( array('name' => 'faqs_read_more_text', 'label' =>'FAQs View All Text', 'value' => get_option('faqs_read_more_text'), 'description' => 'This is the Text of the \'View All\' Link.  Default text is "View All."  This is only displayed if a URL is set in the above field, FAQs View All Link.') );
					
					// Hide Images in Feed (checkbox)
					$checked = (get_option('faqs_image') == '1');
					$this->shed->checkbox( array('name' => 'faqs_image', 'label' =>'Show FAQ Images', 'value' => 1, 'checked' => $checked, 'description' => 'If checked, the Featured Image for each FAQ will be shown before the FAQ\'s answer.', 'inline_label' => 'Show FAQ Images') );
				?>
			</table>
			<?php include('registration_options.php'); ?>
			
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
		</div>						
		<?php
	}
	
	function themes_page()
	{
		wp_enqueue_style( 'easy_faqs_style' );
		// themes page	
		$this->settings_page_top(); ?>	
		
		<form method="post" action="options.php">		
			<?php settings_fields( 'easy-faqs-theme-settings-group' ); ?>			
			
			<h3>Easy FAQs Themes</h3>			
			<p>Please select a theme to use with your FAQs. This theme will become  your default choice, but you can always specify a different theme for each widget if you like!</p>
			
			<?php if (!isValidFAQKey()): ?>
				<?php 
					$upgrade_link = '<a class="button" target="_blank" href="http://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_source=plugin_settings&utm_campaign=themes_upgrade_box">Upgrade Now</a>';
				?>
				<p style="color:green; font-weight: bold;"><em>Note: You are using the free edition of Easy FAQs, which includes a limited number of themes. <?php echo $upgrade_link ?> to unlock all 100+ themes!</em></p>
			<?php  endif; ?>
			<table class="form-table easy-faqs-options-table">
				<?php
					// FAQs Theme (select)
					$themes = array(
						'default_style' => 'Default Theme',
						'no_style' => 'No Theme',
					);
					$current_theme = get_option('faqs_style');
					$themes = EasyFAQs_Config::all_themes(isValidFAQKey(), false);
					$desc = 'Select a theme to see how it would look with your FAQs. <br /><br /> If \'No Theme\' is selected, only your theme\'s own CSS, and any Custom CSS you\'ve added, will be applied to your FAQs.';
					if (!isValidFAQKey())
					{
						$desc = 'Select a theme to see how it would look with your FAQs. You can preview the Pro themes as well, although you will not be able to select them.<br /><br /> If \'No Theme\' is selected, only your theme\'s own CSS, and any Custom CSS you\'ve added, will be applied to your FAQs.';						
					}
					$this->shed->grouped_select( array('name' => 'faqs_style', 'options' => $themes, 'label' =>'FAQs Theme', 'value' => $current_theme, 'description' => $desc) );

				?>
			</table>
			
			<div id="easy_faqs_theme_preview">			
				<h4 id="easy_faqs_theme_preview_title">Preview:</h4>
				<div id="easy_faqs_theme_preview_color_picker">
					<table class="form-table">
					<?php
						$cur_prev_bg = get_option('easy_faqs_preview_window_background', '#fff');
						$this->shed->color( array('name' => 'easy_faqs_preview_window_background', 'label' =>'Set Background Color:', 'value' => $cur_prev_bg, 'description' => '') );
					?>
					</table>
				</div>
				<div id="easy_faqs_theme_preview_browser"></div>
				<div id="easy_faqs_theme_preview_content">
					<?php
						$faqs_shortcode = 'faqs';
						$preview_shortcode = sprintf('[%s theme="%s" style="accordion-collapsed" count="5"]', $faqs_shortcode, $current_theme);
						echo do_shortcode($preview_shortcode);
					?>
				</div>
			</div>
			
			<?php if(!isValidFAQKey()): ?>			
			<div id="easy_faqs_themes_pro_warning">
				<h3>This Theme Requires Easy FAQs Pro</h3>
				<p>You can preview it here, but you must upgrade before you can use it on your website.</p>
				<p class="click_to_upgrade">
					<a class="button" target="_blank" href="http://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_source=plugin_settings&utm_campaign=themes_upgrade_box">Upgrade Now</a>
				</p>
			</div>
			<?php endif; ?>
			
			<p class="submit" id="easy_faqs_theme_preview_submit_button">
				<input type="submit" class="button-primary" value="<?php _e('Set Theme') ?>" />
			</p>
		</form>
		</div>						
		<?php
	}
	
	function submission_form_options() {
		/*
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_use_captcha' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faq_submit_notification_address' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_question_label' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_question_description' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_name_label' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_name_description' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_email_label' );
		register_setting( 'easy-faqs-submission-form-options-group', 'easy_faqs_email_description' );
		*/
		
		$this->settings_page_top(); ?>	
		
		<form method="post" action="options.php">		
			<?php settings_fields( 'easy-faqs-submission-form-options-group' ); ?>			
			<h3>Question Form Settings</h3>
			<?php if(!isValidFAQKey()):?>
			<p class="easy_faq_not_registered"><strong>These settings require Easy FAQs Pro.</strong>&nbsp;&nbsp;&nbsp;<a class="button" target="blank" href="https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/">Upgrade Now</a></p>
			<?php endif;?>
			
			<p>Use the below options to control the look and feel of the question submission form.</p>
		
			<fieldset>
				<legend>Name Field</legend>			
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="easy_faqs_name_label">Label</label></th>
						<td><input type="text" name="easy_faqs_name_label" id="easy_faqs_name_label" <?php if(!isValidFAQKey()): ?>disabled="disabled"<?php endif; ?> value="<?php echo get_option('easy_faqs_name_label', 'Your Name'); ?>" />
						<p class="description">This is the label of the first field in the form, which defaults to "Your Name".</p>
						</td>
					</tr>
				</table>
				
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="easy_faqs_name_description">Description</label></th>
						<td><textarea name="easy_faqs_name_description" id="easy_faqs_name_description" <?php if(!isValidFAQKey()): ?>disabled="disabled"<?php endif; ?>><?php echo get_option('easy_faqs_name_description', 'Please enter your name.'); ?></textarea>
						<p class="description">This is the description below the first field in the form, which defaults to "Please enter your name".</p>
						</td>
					</tr>
				</table>
			</fieldset>
						
			<fieldset>
				<legend>Question Field</legend>			
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="easy_faqs_question_label">Label</label></th>
						<td><input type="text" name="easy_faqs_question_label" id="easy_faqs_question_label" <?php if(!isValidFAQKey()): ?>disabled="disabled"<?php endif; ?> value="<?php echo get_option('easy_faqs_question_label', 'Your Question'); ?>" />
						<p class="description">This is the label of the second field in the form, which defaults to "Your Question".</p>
						</td>
					</tr>
				</table>
							
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="easy_faqs_question_description">Description</label></th>
						<td><textarea name="easy_faqs_question_description" id="easy_faqs_question_description" <?php if(!isValidFAQKey()): ?>disabled="disabled"<?php endif; ?>><?php echo get_option('easy_faqs_question_description', 'Please enter your Question.'); ?></textarea>
						<p class="description">This is the description below the second field in the form, which defaults to "Please enter your Question".</p>
						</td>
					</tr>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Submission Options</legend>
						
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="easy_faqs_submit_button_label">Submit Button Label</label></th>
						<td><input type="text" name="easy_faqs_submit_button_label" id="easy_faqs_submit_button_label" <?php if(!isValidFAQKey()): ?>disabled="disabled"<?php endif; ?> value="<?php echo get_option('easy_faqs_submit_button_label', 'Ask Question'); ?>" />
						<p class="description">This is the label of the submit button at the bottom of the form.</p>
						</td>
					</tr>
				</table>
							
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="easy_faqs_submit_success_message">Submission Success Message</label></th>
						<td><textarea name="easy_faqs_submit_success_message" id="easy_faqs_submit_success_message" <?php if(!isValidFAQKey()): ?>disabled="disabled"<?php endif; ?>><?php echo get_option('easy_faqs_submit_success_message', 'Thank You For Your Question!'); ?></textarea>
						<p class="description">This is the text that appears after a successful submission.</p>
						</td>
					</tr>
				</table>
							
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="easy_faqs_submit_success_redirect_url">Submission Success Redirect URL</label></th>
						<td><input type="text" name="easy_faqs_submit_success_redirect_url" id="easy_faqs_submit_success_redirect_url" <?php if(!isValidFAQKey()): ?>disabled="disabled"<?php endif; ?> value="<?php echo get_option('easy_faqs_submit_success_redirect_url', ''); ?>"/>
						<p class="description">If you want the user to be taken to a specific URL on your site after asking their Question, enter it into this field.  If the field is empty, they will stay on the same page and see the Success Message, instead.</p>
						</td>
					</tr>
				</table>
				
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="easy_faqs_submit_notification_address">Submission Success Notification E-Mail Address</label></th>
						<td><input type="text" name="easy_faqs_submit_notification_address" id="easy_faqs_submit_notification_address" <?php if(!isValidFAQKey()): ?>disabled="disabled"<?php endif; ?> value="<?php echo get_option('easy_faqs_submit_notification_address'); ?>" />
						<p class="description">If set, we will attempt to send an e-mail notification to this address upon a successful submission.  If not set, submission notifications will be sent to the site's Admin E-mail address.  You can include multiple, comma-separated e-mail addresses here.</p>
						</td>
					</tr>
				</table>
				
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="easy_faqs_submit_notification_include_question">Include Question In Notification E-mail</label></th>
						<td><input type="checkbox" name="easy_faqs_submit_notification_include_question" id="easy_faqs_submit_notification_include_question" <?php if(!isValidFAQKey()): ?>disabled="disabled"<?php endif; ?> value="1" <?php if(get_option('easy_faqs_submit_notification_include_question')){ ?> checked="CHECKED" <?php } ?>/>
						<p class="description">If checked, the notification e-mail will include the Question asked.</p>
						</td>
					</tr>
				</table>
			</fieldset>			
			
			<fieldset>
				<legend>Spam Prevention</legend>
				<table class="form-table">
				<?php
						// Submission Form CAPTCHA (checkbox)
						$desc = 'If checked, and a compatible plugin is installed (such as <a href="https://wordpress.org/plugins/really-simple-captcha/" target="_blank">Really Simple Captcha</a>) then we will output a Captcha on the Submission Form.  This is useful if you are having SPAM problems.';
						$disabled =  !isValidFAQKey();
						if(!class_exists('ReallySimpleCaptcha')) {
							$desc .= '</p><p class="alert"><strong>ALERT: Really Simple Captcha is NOT active.  Captcha feature will not function.</strong>';
						}
						$checked = (get_option('easy_faqs_use_captcha') == '1');
						$this->shed->checkbox( array('name' => 'easy_faqs_use_captcha', 'label' =>'Enable Really Simple Captcha', 'value' => 1, 'checked' => $checked, 'description' => $desc, 'inline_label' => 'Show a CAPTCHA on form submissions to prevent spam', 'disabled' => $disabled) );
				?>
				</table>
			</fieldset>
			
			<fieldset>
				<legend>Error Messages</legend>
				
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="easy_faqs_name_error_message">Name Error Message</label></th>
						<td><textarea name="easy_faqs_name_error_message" id="easy_faqs_name_error_message" <?php if(!isValidFAQKey()): ?>disabled="disabled"<?php endif; ?>><?php echo get_option('easy_faqs_name_error_message', 'Please enter your name.'); ?></textarea>
						<p class="description">This is the message shown when this field isn't filled out correctly.</p>
						</td>
					</tr>
				</table>
				
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="easy_faqs_question_error_message">Question Error Message</label></th>
						<td><textarea name="easy_faqs_question_error_message" id="easy_faqs_question_error_message" <?php if(!isValidFAQKey()): ?>disabled="disabled"<?php endif; ?>><?php echo get_option('easy_faqs_question_error_message', 'Please enter your question.'); ?></textarea>
						<p class="description">This is the message shown when this field isn't filled out correctly.</p>
						</td>
					</tr>
				</table>
						
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="easy_faqs_captcha_error_message">Captcha Error Message</label></th>
						<td><textarea name="easy_faqs_captcha_error_message" id="easy_faqs_captcha_error_message" <?php if(!isValidFAQKey()): ?>disabled="disabled"<?php endif; ?>><?php echo get_option('easy_faqs_captcha_error_message', 'Captcha did not match.'); ?></textarea>
						<p class="description">This is the message shown when this field isn't filled out correctly.</p>
						</td>
					</tr>
				</table>
				
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="easy_faqs_general_error_message">General Error Message</label></th>
						<td><textarea name="easy_faqs_general_error_message" id="easy_faqs_general_error_message" <?php if(!isValidFAQKey()): ?>disabled="disabled"<?php endif; ?>><?php echo get_option('easy_faqs_general_error_message', 'There was an error with your submission.  Please check the fields and try again..'); ?></textarea>
						<p class="description">This is the message shown when this field isn't filled out correctly.</p>
						</td>
					</tr>
				</table>
			</fieldset>
			
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
		</div>		
		<?php
	}
	
	function help_settings_page(){
		$this->settings_page_top();
		include('pages/help.html');					
		?></div><?php			
	}	
		
	function shortcode_generator_page() {
		$this->settings_page_top();
		$categories = get_terms( 'easy-faq-category', 'orderby=title&hide_empty=0' );
		?>
		<div id="gold_plugins_shortcode_generator">
			<h3>Shortcode Generator</h3>			
			<p>Select the options you'd like, and then click the "Build My Shortcode!" button. You'll get a shortcode that you can copy and paste into any post or page.</p>
			
			<form id="easy_faqs_shortcode_generator">
				<table class="form-table">
					<tbody>										
<?php
					// FAQs Theme (select)
					$themes = array(
						'default_style' => 'Default Theme',
						'no_style' => 'No Theme',
					);
					$themes = EasyFAQs_Config::all_themes(isValidFAQKey());
					$desc = 'Select which theme you\'d like to use.  If \'No Theme\' is selected, only your Theme\'s CSS, and any Custom CSS you\'ve added, will be used.';
					$this->shed->grouped_select( array('name' => 'sc_gen_theme', 'options' => $themes, 'label' =>'FAQs Theme', 'value' => get_option('faqs_style'), 'description' => $desc) );

?>
						<tr>
							<th scope="row">
								<div class="sc_gen_control_group">
									<label for="sc_gen_count">Count</label>
								</div>
							</th>
							<td>
								<input type="text" class="valid_int" id="sc_gen_count" value="10" />
								<p class="description">How many FAQs would you like to show? If you have more than this number, we'll show a View All link.</p>
								<p class="description tip"><strong>Tip:</strong> Leave this blank to show all FAQs (unlimited)</p>
							</td>
						</tr>
												
						<tr>
							<th scope="row">
								<div class="sc_gen_control_group">
									<label for="sc_gen_read_more_url">View All URL</label>
								</div>
							</th>
							<td>
								<input type="text" id="sc_gen_read_more_url" value="" />
								<p class="description">The URL of your FAQs page. If you have more FAQs than are currently displayed, we'll show this link.</p>
								<p class="description"><strong>Tip:</strong> leave this blank to hide the View All link entirely.</p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<div class="sc_gen_control_group">
									<label for="sc_gen_read_more_text">View All Link Text</label>
								</div>
							</th>
							<td>
								<input type="text" id="sc_gen_read_more_text" value="View All FAQs" />
								<p class="description">The anchor text for the 'View All' link. If you have more FAQs than are currently displayed, we'll show this link.</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<div class="sc_gen_control_group">
									<label for="sc_gen_order_by">Order By</label>
								</div>
							</th>
							<td>
								<div class="inline-select-wrapper">
									<select id="sc_gen_order_by">
										<option value="rand">Random</option>
										<option value="id">ID</option>
										<option value="author">Author</option>
										<option value="title" selected="selected">Title</option>
										<option value="name">Name</option>
										<option value="date">Date</option>
										<option value="modified">Last Modified</option>
										<option value="parent">Parent ID</option>								
									</select>
								</div>
								<div class="inline-select-wrapper">
									<select id="sc_gen_order_dir">
										<option value="asc">Ascending (ASC)</option>
										<option value="desc">Descending (DESC)</option>
									</select>
								</div>
								<p class="description">How should we order your FAQs?</p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<div class="sc_gen_control_group">
									<label for="sc_gen_category">Filter By Category</label>
								</div>
							</th>
							<td>
								<select id="sc_gen_category">
									<option value="all">All Categories</option>
									<?php foreach($categories as $cat):?>
									<option value="<?=$cat->slug?>"><?=htmlentities($cat->name)?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><a href="<?php echo admin_url('edit-tags.php?taxonomy=easy-faq-category&post_type=faq'); ?>">Manage Categories</a></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								Featured Images
							</th>
							<td>							
								<div class="sc_gen_control_group">
									<label for="sc_gen_show_thumbs">
										<input type="checkbox" class="checkbox" id="sc_gen_show_thumbs" value="yes" />
										Show Featured Images with each FAQ?
									</label>
								</div>
							</td>
						</tr>
						
						<?php if (!isValidFAQKey()):?>
						<tr>
							<td colspan="2">
								<div class="upgrade_notice">
									<p class="easy_faq_not_registered"><strong>These settings require Easy FAQs Pro.</strong>&nbsp;&nbsp;&nbsp;<a class="button" target="blank" href="https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_source=shortcode_generator&utm_campaign=pro_upgrade">Upgrade Now</a></p>
								</div>
							</td>
						</tr>
						<?php endif; ?>
						
						<?php if (!isValidFAQKey()):?>
						<tr class="disabled">
						<?php else: ?>
						<tr>
						<?php endif; ?>
							<th scope="row">
								Quick Links
							</th>
							<td>
								<div class="sc_gen_control_group">
									<label for="sc_gen_quick_links">
										<input type="checkbox" class="checkbox" id="sc_gen_quick_links" value="yes" />
										Include a Quick Links section?
									</label>
								</div>
							</td>
						</tr>
						
						<?php if (!isValidFAQKey()):?>
						<tr class="disabled">
						<?php else: ?>
						<tr>
						<?php endif; ?>
							<th scope="row">
								<label for="sc_gen_quick_links_cols">Quick Links Columns</label>
							</th>
							<td>
								<input type="text" class="valid_int" id="sc_gen_quick_links_cols" value="2" />
								<p class="description">How many columns should your Quick Links section have?</p>
							</td>
						</tr>
						
						
						<?php if (!isValidFAQKey()):?>
						<tr class="disabled">
						<?php else: ?>
						<tr>
						<?php endif; ?>
							<th scope="row">
								Accordion Style
							</th>
							<td>
								<div class="sc_gen_control_group sc_gen_control_group_radio">
									<label title="Normal Style">
										<input type="radio" value="normal" id="sc_gen_style_normal" name="sc_gen_style" checked="checked">
										<span>Normal Style</span>
									</label>
									<label title="Accordion Style - First FAQ Visible">
										<input type="radio" value="accordion" id="sc_gen_style_accordion_first_open" name="sc_gen_style">
										<span>Accordion Style - First FAQ Visible</span>
									</label>
									<label title="Accordion Style - All FAQs Start Collapsed">
										<input type="radio" value="accordion-collapsed" id="sc_gen_style_accordion_closed" name="sc_gen_style">
										<span>Accordion Style - All FAQs Start Collapsed</span>
									</label>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				
				<?php if (!isValidFAQKey()):?>
				<input type="hidden" name="is_pro" id="is_pro" value="0" />
				<?php else: ?>
				<input type="hidden" name="is_pro" id="is_pro" value="1" />
				<?php endif; ?>
				
				
				<p class="submit">
					<button id="sc_generate" class="button button-primary" type="button">Build My Shortcode!</button>
				</p>
				
				<div id="sc_gen_output_wrapper">
					<label for="sc_gen_output">Here is your Shortcode!</label>
					<p class="description">Copy and paste this shortcode into any page or post to display your FAQs!</p>
					<textarea id="sc_gen_output" rows="4" cols="80"></textarea>
				</div>
				
			</form>
		</div><!-- end #gold_plugins_shortcode_generator -->
		</div><!--end settings_page-->
		<?php 
	}


	function import_export_page(){
		//import export yang		
		$this->settings_page_top();
		if(!isValidFAQKey()){ //not pro
		?>
		<h3>FAQs Importer</h3>	
		<p class="easy_faq_not_registered"><strong>These features require Easy FAQs Pro.</strong>&nbsp;&nbsp;&nbsp;<a class="button" target="blank" href="https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_campaign=upgrade&utm_source=plugin&utm_banner=import_upgrade">Upgrade Now</a></p>
		
		<h3>FAQs Exporter</h3>		
		<p class="easy_faq_not_registered"><strong>These features require Easy FAQs Pro.</strong>&nbsp;&nbsp;&nbsp;<a class="button" target="blank" href="https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_campaign=upgrade&utm_source=plugin&utm_banner=export_upgrade">Upgrade Now</a></p>
		<?php 
			} else { //is pro
		?>
			<form method="POST" action="" enctype="multipart/form-data">
				<h3>FAQs Importer</h3>	
				<?php 
					//CSV Importer
					$importer = new FAQsPlugin_Importer($this);
					$importer->csv_importer(); // outputs form and handles input. TODO: break into 2 functions (one to show form, one to process input)
				?>
				<h3>FAQs Exporter</h3>	
				<?php 
					//CSV Exporter
					FAQsPlugin_Exporter::output_form();
				?>
			</form>
		<?php	} ?>
		</div><?php			
	}
	
	function recent_searches_page() {
		$this->settings_page_top();
		$categories = get_terms( 'easy-faq-category', 'orderby=title&hide_empty=0' );
		?>
		<div id="easy_faqs_recent_searches">
			<h3>Recent Searches</h3>
			<?php if (isValidFAQKey()): ?>
			<?php
				global $wpdb;		
				$table_name = $wpdb->prefix . 'easy_faqs_search_log';
				$limit = 25;
				$page = (isset($_GET['results_page']) && intval($_GET['results_page']) > 0) ? intval($_GET['results_page']) : 1;
				$offset = $page > 1 ? ( ($page - 1) * $limit ) : 0;
				$sql_template = 'SELECT * from %s ORDER BY time DESC LIMIT %d,%d';
				$sql = sprintf($sql_template, $table_name, $offset, $limit);				
				$recent_searches = $wpdb->get_results($sql);
				
				
				// get the total count				
				$count_sql_template = 'SELECT count(id) from %s';
				$count_sql = sprintf($count_sql_template, $table_name);
				$record_count = $wpdb->get_var($count_sql);
				
				if (is_array($recent_searches)) {
					echo '<table id="easy_faqs_recent_searches" class="wp-list-table widefat fixed pages">';
						echo '<thead>';
							echo '<tr>';
								echo '<th>Time</th>';
								echo '<th>Query</th>';
								echo '<th>Results</th>';
								echo '<th>Visitor IP</th>';
								echo '<th>Location</th>';
							echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
							foreach($recent_searches as $i => $search)
							{
								$row_class = ($i % 2 == 0) ? 'alternate' : '';
							echo '<tr class="'.$row_class.'">';
								$friendly_time = date('Y-m-d H:i:s', strtotime($search->time));
								if ($this->root !== false) {
									$friendly_time = $this->root->time_elapsed_string($friendly_time);
								}
								printf ('<td>%s</td>', htmlentities($friendly_time));
								printf ('<td>%s</td>', htmlentities($search->query));
								printf ('<td>%s</td>', htmlentities($search->result_count));
								printf ('<td>%s</td>', htmlentities($search->ip_address));
								printf ('<td>%s</td>', htmlentities($search->friendly_location));
							echo '</tr>';				
							}
						echo '</tbody>';
					echo '</table>';
					
					if ($record_count > $limit)
					{
						$link_template = '<li><a href="%s">%s</a></li>';
						$href_template = admin_url('admin.php?page=easy-faqs-recent-searches&results_page=') . '%d';
						$last_page = ceil($record_count / $limit);
						echo '<div class="tablenav bottom">';
						echo '<div class="tablenav-pages">';
						echo '<ul class="search_result_pages">';

						// first page link
						$href = sprintf($href_template, 1);
						printf($link_template, $href, '&laquo;');

						// output page links
						for($i = 1; $i <= $last_page; $i++)
						{
							$href = sprintf($href_template, ($i));
							printf($link_template, $href, $i);
						}						
						
						// last page link
						$href = sprintf($href_template, $last_page);
						printf($link_template, $href, '&raquo;');						
						
						echo '</ul>';
						echo '</div>'; // end tablenav-pages
						echo '</div>'; // end tablenav
					}
				}		
			?>	
		</div><!-- end #easy_faqs_recent_searches -->
		<?php else: ?>
		<p class="easy_faq_not_registered"><strong>This feature requires Easy FAQs Pro.</strong>&nbsp;&nbsp;&nbsp;<a class="button" target="blank" href="https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_campaign=upgrade_search&utm_source=plugin&utm_banner=recent_searches">Upgrade Now</a></p>
		<?php endif; ?>
		</div><!--end settings_page-->
	<?php 
	}
	
} // end class