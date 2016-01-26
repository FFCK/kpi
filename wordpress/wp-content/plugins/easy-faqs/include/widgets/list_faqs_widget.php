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
*/

class listFAQsWidget extends WP_Widget
{
	function __construct(){
		$widget_ops = array('classname' => 'listFAQsWidget', 'description' => 'Displays a list of FAQs.' );
		parent::__construct('listFAQsWidget', 'Easy FAQs List', $widget_ops);
	}
	
	function listFAQsWidget(){		
		$this->__construct();
	}

	function form($instance){
		global $easy_faqs;
		$widget_defaults = array( 
			'title' => '',
			'count' => 10,
			'show_faq_image' => get_option('faqs_image'),
			'faq_read_more_link_text' => get_option('faqs_read_more_text', 'View All'),
			'faq_read_more_link' => get_option('faqs_link'),
			'order' => 'ASC',
			'order_by' => 'date',
			'category' => '',
			'group_by_category' => false,
			'quick_links' => false,
			'quick_links_columns' => 2,
			'accordion_style' => 'normal',
			'theme' => get_option('faqs_style'),
		);
		
		$instance = wp_parse_args( (array) $instance, $widget_defaults );
		
		$title = $instance['title'];
		$theme = $instance['theme'];
		$count = $instance['count'];
		$faq_read_more_link_text = $instance['faq_read_more_link_text'];
		$faq_read_more_link = $instance['faq_read_more_link'];
		$show_faq_image = $instance['show_faq_image'];
		$order = $instance['order'];
		$order_by = $instance['order_by'];
		$category = $instance['category'];
		$group_by_category = $instance['group_by_category'];
		$quick_links = $instance['quick_links'];
		$quick_links_columns = $instance['quick_links_columns'];
		$accordion_style = $instance['accordion_style'];
		
		?>
		<div class="gp_widget_form_wrapper">
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">Widget Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('theme'); ?>">Theme: </label>
				<?php EasyFAQs_Config::output_theme_selector($this->get_field_id('theme'), $this->get_field_name('theme'), $theme, isValidFAQKey()); ?>
			</p>
			<fieldset class="radio_text_input">
				<legend>Filter FAQs:</legend> 
				<p><label for="<?php echo $this->get_field_id('category'); ?>">Category:</label><br/>
				<?php $categories = get_terms( 'easy-faq-category', 'orderby=title&hide_empty=0' ); ?>			
				<select id="<?php echo $this->get_field_id('category'); ?>">
					<option value="all">All Categories</option>
					<?php foreach($categories as $cat):?>
					<option value="<?php echo $cat->slug; ?>" <?php if($category == $cat->slug):?>selected="SELECTED"<?php endif; ?>><?php echo htmlentities($cat->name); ?></option>
					<?php endforeach; ?>
				</select><br/><em><a href="<?php echo admin_url('edit-tags.php?taxonomy=easy-faq-category&post_type=faq'); ?>">Manage Categories</a></em></p>
				<p><label for="<?php echo $this->get_field_id('count'); ?>">Count: <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>" /></label><br/><em>The number of FAQs to display.  Set to -1 to show All FAQs.</em></p>
				<p><label for="<?php echo $this->get_field_id('order'); ?>">Order:</label><br/>
				<select id="<?php echo $this->get_field_id('order_by'); ?>" name="<?php echo $this->get_field_name('order_by'); ?>" class="multi_left">
					<option value="title" <?php if($order_by == "title"): ?>selected="SELECTED"<?php endif; ?>>Title</option>
					<option value="random" <?php if($order_by == "random"): ?>selected="SELECTED"<?php endif; ?>>Random</option>
					<option value="id" <?php if($order_by == "id"): ?>selected="SELECTED"<?php endif; ?>>ID</option>
					<option value="author" <?php if($order_by == "author"): ?>selected="SELECTED"<?php endif; ?>>Author</option>
					<option value="name" <?php if($order_by == "name"): ?>selected="SELECTED"<?php endif; ?>>Name</option>
					<option value="date" <?php if($order_by == "date"): ?>selected="SELECTED"<?php endif; ?>>Date</option>
					<option value="last_modified" <?php if($order_by == "last_modified"): ?>selected="SELECTED"<?php endif; ?>>Last Modified</option>
					<option value="parent_id" <?php if($order_by == "parent_id"): ?>selected="SELECTED"<?php endif; ?>>Parent ID</option>
				</select>
				<select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>" class="multi_right">
					<option value="ASC" <?php if($order == "ASC"): ?>selected="SELECTED"<?php endif; ?>>Ascending (ASC)</option>
					<option value="DESC" <?php if($order == "DESC"): ?>selected="SELECTED"<?php endif; ?>>Descending (DESC)</option>
				</select></p>
			</fieldset>
			<fieldset class="radio_text_input">
				<legend>Fields to Display:</legend> 
				<p><label for="<?php echo $this->get_field_id('faq_read_more_link'); ?>">View All Link URL: <input class="widefat" id="<?php echo $this->get_field_id('faq_read_more_link'); ?>" name="<?php echo $this->get_field_name('faq_read_more_link'); ?>" type="text" value="<?php echo esc_attr($faq_read_more_link); ?>" /></label></em></p>
				<p><label for="<?php echo $this->get_field_id('faq_read_more_link_text'); ?>">View All Link Text: <input class="widefat" id="<?php echo $this->get_field_id('faq_read_more_link_text'); ?>" name="<?php echo $this->get_field_name('faq_read_more_link_text'); ?>" type="text" value="<?php echo esc_attr($faq_read_more_link_text); ?>" /></label></p>			
				<p><input class="widefat" id="<?php echo $this->get_field_id('show_faq_image'); ?>" name="<?php echo $this->get_field_name('show_faq_image'); ?>" type="checkbox" value="1" <?php if($show_faq_image){ ?>checked="CHECKED"<?php } ?>/><label for="<?php echo $this->get_field_id('show_faq_image'); ?>">Show FAQ Image</label></p>
			</fieldset>
			<fieldset class="radio_text_input">
				<legend>Quick Links:</legend>				
				<p><input class="widefat" id="<?php echo $this->get_field_id('quick_links'); ?>" name="<?php echo $this->get_field_name('quick_links'); ?>" type="checkbox" value="1" <?php if($quick_links){ ?>checked="CHECKED"<?php } ?>/><label for="<?php echo $this->get_field_id('quick_links'); ?>">Quick Links</label></p>
				<p><label for="<?php echo $this->get_field_id('quick_links_columns'); ?>">Number of Columns: <input class="widefat" id="<?php echo $this->get_field_id('quick_links_columns'); ?>" name="<?php echo $this->get_field_name('quick_links_columns'); ?>" type="text" value="<?php echo esc_attr($quick_links_columns); ?>" /></label></p>
			</fieldset>
			<fieldset class="radio_text_input">
				<legend>Display Style:</legend>
				<label title="Normal Style">
					<input type="radio" value="normal" id="sc_gen_style_normal" name="<?php echo $this->get_field_name('accordion_style'); ?>" <?php if($accordion_style == "normal"): ?>checked="CHECKED"<?php endif; ?>>
					<span>Normal Style</span>
				</label><br/>
				<label title="Accordion Style - First FAQ Visible">
					<input type="radio" value="accordion" id="sc_gen_style_accordion_first_open" name="<?php echo $this->get_field_name('accordion_style'); ?>" <?php if(!$easy_faqs->is_pro): ?>disabled="disabled"<?php endif; ?> <?php if($accordion_style == "accordion"): ?>checked="CHECKED"<?php endif; ?>>
					<span>Accordion Style - First FAQ Visible <?php if(!$easy_faqs->is_pro): ?><br/><strong>Easy FAQs Pro Required.</strong> <a href="https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_source=list_faqs_widget&utm_campaign=up
					grade" target="_blank"><?php echo FAQ_UPGRADE_TEXT; ?></a><?php endif; ?> </span>
				</label><br/>
				<label title="Accordion Style - All FAQs Start Collapsed">
					<input type="radio" value="accordion-collapsed" id="sc_gen_style_accordion_closed" name="<?php echo $this->get_field_name('accordion_style'); ?>" <?php if(!$easy_faqs->is_pro): ?>disabled="disabled"<?php endif; ?> <?php if($accordion_style == "accordion-collapsed"): ?>checked="CHECKED"<?php endif; ?>>
					<span>Accordion Style - All FAQs Start Collapsed <?php if(!$easy_faqs->is_pro): ?><br/><strong>Easy FAQs Pro Required.</strong> <a href="https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_source=list_faqs_widget&utm_campaign=up
					grade" target="_blank"><?php echo FAQ_UPGRADE_TEXT; ?></a><?php endif; ?> </span>
				</label></p>
			</fieldset>			
		</div>
		<?php
	}

	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['theme'] = $new_instance['theme'];
		$instance['count'] = $new_instance['count'];
		$instance['faq_read_more_link_text'] = $new_instance['faq_read_more_link_text'];
		$instance['faq_read_more_link'] = $new_instance['faq_read_more_link'];
		$instance['show_faq_image'] = $new_instance['show_faq_image'];
		$instance['order'] = $new_instance['order'];
		$instance['order_by'] = $new_instance['order_by'];
		$instance['category'] = $new_instance['category'];
		$instance['group_by_category'] = $new_instance['group_by_category'];
		$instance['quick_links'] = $new_instance['quick_links'];
		$instance['quick_links_columns'] = $new_instance['quick_links_columns'];
		$instance['accordion_style'] = $new_instance['accordion_style'];
		return $instance;
	}

	function widget($args, $instance){
		global $easy_faqs;
		
		extract($args, EXTR_SKIP);

		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$faq_read_more_link_text = empty($instance['faq_read_more_link_text']) ? null : $instance['faq_read_more_link_text'];
		$faq_read_more_link = empty($instance['faq_read_more_link']) ? null : $instance['faq_read_more_link'];
		$show_faq_image = empty($instance['show_faq_image']) ? null : $instance['show_faq_image'];
		
		$title = empty($instance['title']) ? null : $instance['title'];
		$theme = empty($instance['theme']) ? '' : $instance['theme'];
		$count = empty($instance['count']) ? null : $instance['count'];
		$faq_read_more_link_text =empty( $instance['faq_read_more_link_text']) ? null : $instance['faq_read_more_link_text'];
		$faq_read_more_link = empty($instance['faq_read_more_link']) ? null : $instance['faq_read_more_link'];
		$show_faq_image = empty($instance['show_faq_image']) ? null : $instance['show_faq_image'];
		$order = empty($instance['order']) ? null : $instance['order'];
		$order_by = empty($instance['order_by']) ? null : $instance['order_by'];
		$category = empty($instance['category']) ? null : $instance['category'];
		$group_by_category = empty($instance['group_by_category']) ? null : $instance['group_by_category'];
		$quick_links = empty($instance['quick_links']) ? null : $instance['quick_links'];
		$quick_links_columns = empty($instance['quick_links_columns']) ? null : $instance['quick_links_columns'];
		$accordion_style = empty($instance['accordion_style']) ? null : $instance['accordion_style'];

		if (!empty($title)){
			echo $before_title . $title . $after_title;;
		}
		
		$atts = array(
			'theme' => $theme,
			'count' => $count,
			'read_more_link_text' => $faq_read_more_link_text,
			'read_more_link' => $faq_read_more_link,
			'show_thumbs' => $show_faq_image,
			'order' => $order,
			'orderby' => $order_by,
			'quicklinks' => $quick_links,
			'colcount' => $quick_links_columns,
			'style' => $accordion_style,
			'category' => $category
		);
		echo $easy_faqs->outputFAQs($atts);

		echo $after_widget;
	} 
}
?>