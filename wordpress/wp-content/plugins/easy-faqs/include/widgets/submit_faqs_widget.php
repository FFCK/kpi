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
along with Easy FAQs.  If not, see <http://www.gnu.org/licenses/>.

Shout out to http://www.makeuseof.com/tag/how-to-create-wordpress-widgets/ for the help
*/

class submitFAQsWidget extends WP_Widget
{
	function __construct(){
		$widget_ops = array('classname' => 'submitFAQsWidget', 'description' => 'Displays an FAQs Submission Form.' );
		parent::__construct('submitFAQsWidget', 'Easy FAQs Submit a Question', $widget_ops);
	}
	
	function submitFAQsWidget(){		
		$this->__construct();
	}

	function form($instance){
		global $easy_faqs;
	
		if($easy_faqs->is_pro){
			$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
			$title = $instance['title'];
			?>
			<div class="gp_widget_form_wrapper">
				<p><label for="<?php echo $this->get_field_id('title'); ?>">Widget Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
			</div>
			<?php
		} else {
			?>
			<div class="gp_widget_form_wrapper">
				<p><strong>Please Note:</strong><br/> This Feature Requires Easy FAQs Pro.</p>
				<p><a href="https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_source=submit_faqs_widget&utm_campaign=upgrade" target="_blank"><?php echo $easy_faqs->get_str('FAQ_UPGRADE_TEXT'); ?></a></p>
			</div>
			<?php
		}
	}

	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		return $instance;
	}

	function widget($args, $instance){
		global $easy_faqs;
		global $easy_faqs_in_widget;
		$easy_faqs_in_widget = true;
		
		
		
		extract($args, EXTR_SKIP);

		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

		if (!empty($title)){
			echo $before_title . $title . $after_title;;
		}
		
		//currently accepts no arguments, but expects this empty array
		$atts = array();
		
		echo $easy_faqs->submitFAQForm($atts);

		echo $after_widget;
	} 
}
?>