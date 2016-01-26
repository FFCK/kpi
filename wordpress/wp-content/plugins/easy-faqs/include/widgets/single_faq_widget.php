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

class singleFAQWidget extends WP_Widget
{
	function __construct(){
		$widget_ops = array('classname' => 'singleFAQWidget', 'description' => 'Displays a random FAQ.' );
		parent::__construct('singleFAQWidget', 'Easy FAQs Single FAQ', $widget_ops);
	}
	
	function singleFAQWidget(){		
		$this->__construct();
	}

	function form($instance){
		$widget_defaults = array( 
			'title' => '',
			'faqid' => null,
			'show_faq_image' => get_option('faqs_image'),
			'faq_read_more_link_text' => get_option('faqs_read_more_text',	'Read More'),
			'faq_read_more_link' => get_option('faqs_link')
		);
		$instance = wp_parse_args( (array) $instance, $widget_defaults );
		$title = $instance['title'];
		$faqid = $instance['faqid'];
		$faq_read_more_link_text = $instance['faq_read_more_link_text'];
		$faq_read_more_link = $instance['faq_read_more_link'];
		$show_faq_image = $instance['show_faq_image'];
		$theme = $instance['theme'];
		?>
		<div class="gp_widget_form_wrapper">		
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">Widget Title: </label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('theme'); ?>">Theme: </label>
				<?php EasyFAQs_Config::output_theme_selector($this->get_field_id('theme'), $this->get_field_name('theme'), $theme, isValidFAQKey()); ?>
			</p>			
			<?php
				$faqs = get_posts('post_type=faq&posts_per_page=-1&nopaging=true');
			?>
				<label for="<?php echo $this->get_field_id('faqid'); ?>">FAQ to Display</label>
				<select id="<?php echo $this->get_field_id('faqid'); ?>" name="<?php echo $this->get_field_name('faqid'); ?>">
				<?php if($faqs) : foreach ( $faqs as $faq  ) : ?>
					<option value="<?php echo $faq->ID; ?>"  <?php if($faqid == $faq->ID): ?> selected="SELECTED" <?php endif; ?>><?php echo $faq->post_title; ?></option>
				<?php endforeach; endif;?>
				 </select>
			<fieldset class="radio_text_input">
				<legend>Fields to Display:</legend>
				<p><label for="<?php echo $this->get_field_id('faq_read_more_link'); ?>">View All Link Destination: <input class="widefat" id="<?php echo $this->get_field_id('faq_read_more_link'); ?>" name="<?php echo $this->get_field_name('faq_read_more_link'); ?>" type="text" value="<?php echo esc_attr($faq_read_more_link); ?>" /></label></p>
				<p><label for="<?php echo $this->get_field_id('faq_read_more_link_text'); ?>">View All Link Text: <input class="widefat" id="<?php echo $this->get_field_id('faq_read_more_link_text'); ?>" name="<?php echo $this->get_field_name('faq_read_more_link_text'); ?>" type="text" value="<?php echo esc_attr($faq_read_more_link_text); ?>" /></label></p>
				<p><input class="widefat" id="<?php echo $this->get_field_id('show_faq_image'); ?>" name="<?php echo $this->get_field_name('show_faq_image'); ?>" type="checkbox" value="1" <?php if($show_faq_image){ ?>checked="CHECKED"<?php } ?>/><label for="<?php echo $this->get_field_id('show_faq_image'); ?>">Show FAQ Image</label></p>
			</fieldset>
		</div>
		<?php
	}

	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['theme'] = $new_instance['theme'];
		$instance['faqid'] = $new_instance['faqid'];
		$instance['faq_read_more_link_text'] = $new_instance['faq_read_more_link_text'];
		$instance['faq_read_more_link'] = $new_instance['faq_read_more_link'];
		$instance['show_faq_image'] = $new_instance['show_faq_image'];
		return $instance;
	}

	function widget($args, $instance){
		global $easy_faqs;
		
		extract($args, EXTR_SKIP);

		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$theme = empty($instance['theme']) ? '' : $instance['theme'];
		$faqid = empty($instance['faqid']) ? null : $instance['faqid'];
		$faq_read_more_link_text = empty($instance['faq_read_more_link_text']) ? null : $instance['faq_read_more_link_text'];
		$faq_read_more_link = empty($instance['faq_read_more_link']) ? null : $instance['faq_read_more_link'];
		$show_faq_image = empty($instance['show_faq_image']) ? null : $instance['show_faq_image'];

		if (!empty($title)){
			echo $before_title . $title . $after_title;;
		}
		
		$atts = array(
			'id' => $faqid,
			'theme' => $theme,
			'read_more_link_text' => $faq_read_more_link_text,
			'read_more_link' => $faq_read_more_link,
			'show_thumbs' => $show_faq_image,
		);
		echo $easy_faqs->outputSingleFAQ($atts);

		echo $after_widget;
	} 
}
