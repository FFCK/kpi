<?php
/**
 * widget-social-links.php
 * 
 * Custom Widget for displaying social media links
 * Display links for facebook, twitter, instagram, linkedin, pinterest.
 * @package Theme_Material
 * GPL3 Licensed
*/

class Material_Widget_Social_Links extends WP_Widget {

	/**
	 * Specifies the widget name, description, class name and instantiates it
	 */
	public function __construct() {
		parent::__construct( 
			'widget-social-links',
			__( 'Material: Social Links', 'material' ),
			array(
				'classname'   => 'widget-social-links',
				'description' => __( 'A custom widget that displays social network links.', 'material' )
			) 
		);
	}


	/**
	 * Generates the back-end layout for the widget
	 */
	public function form( $instance ) {
		// Default widget settings
		$defaults = array(
			'title'               => __('Social Links','material'),
			'facebook'   		  => '',
			'twitter' 			  => '',
			'instagram'      	  => '',
			'linkedin'            => '',
			'pinterest'           => ''
		);

		$instance = wp_parse_args( 
			(array) $instance, $defaults );

		// The widget content ?>
		<!-- Title -->
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php _e( 'Title:', 'material' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" value="<?php echo esc_url( $instance['title'] ); ?>">
		</p>

		<!-- Facebook -->
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'facebook' )); ?>"><?php _e( 'Facebook:', 'material' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id( 'facebook' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'facebook' )); ?>" value="<?php echo esc_url( $instance['facebook'] ); ?>">
		</p> 

		<!-- Twitter -->
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'twitter' )); ?>"><?php _e( 'Twitter:', 'material' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id( 'twitter' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'twitter' )); ?>" value="<?php echo esc_url( $instance['twitter'] ); ?>">
		</p>

		<!-- Instagram -->
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'instagram' )); ?>"><?php _e( 'Instagram:', 'material' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id( 'instagram' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'instagram' )); ?>" value="<?php echo esc_url( $instance['instagram'] ); ?>">
		</p>

		<!-- LinkedIn -->
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'linkedin' )); ?>"><?php _e( 'LinkedIn:', 'material' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id( 'linkedin' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'linkedin' )); ?>" value="<?php echo esc_url( $instance['linkedin'] ); ?>">
		</p> 

			<!-- Pinterest -->
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'pinterest' )); ?>"><?php _e( 'Pinterest:', 'material' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr($this->get_field_id( 'pinterest' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'pinterest' )); ?>" value="<?php echo esc_url( $instance['pinterest'] ); ?>">
		</p> 

		<?php
	}


	/**
	 * Processes the widget's values
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance; 
        
        $postfilter =    // set up the filters to be used with the input urls

		// Update values
		$instance['title']               = sanitize_text_field ( $new_instance['title'] );
		$instance['facebook']   =  esc_url_raw( $new_instance['facebook'] );
		$instance['twitter'] =  esc_url_raw( $new_instance['twitter'] );
		$instance['instagram']      =  esc_url_raw( $new_instance['instagram'] );
		$instance['linkedin']        = esc_url_raw( $new_instance['linkedin'] );
		$instance['pinterest']        = esc_url_raw( $new_instance['pinterest'] );
		
        return $instance;
	}


	/**
	 * Output the contents of the widget
	 */
	public function widget( $args, $instance ) {
		// Extract the arguments
		extract( $args );

		$title               = apply_filters( 'widget_title', $instance['title'] );
		$facebook   		 = $instance['facebook'];
		$twitter 			 = $instance['twitter'];
		$instagram      	 = $instance['instagram'];
		$linkedin        	 = $instance['linkedin'];
		$pinterest        	 = $instance['pinterest'];
		// Display the markup before the widget (as defined in functions.php)
		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		echo '<ul class="social-links">';

		if ( $facebook ) : ?>
			<li>
				<a href="<?php echo esc_url($facebook); ?>"><i class="fa fa-facebook-square"></i></a> 
			</li>
		<?php endif;

		if ( $twitter ) : ?>
			<li>
				<a href="<?php echo esc_url($twitter); ?>"><i class="fa fa-twitter-square"></i></a> 
			</li>
		<?php endif;

		if ( $instagram ) : ?>
			<li>
				<a href="<?php echo esc_url($instagram); ?>"><i class="fa fa-instagram"></i></a> 
			</li>
		<?php endif;

		if ( $linkedin ) : ?>
			<li>
				<a href="<?php echo esc_url($linkedin); ?>"><i class="fa fa-linkedin-square"></i></a> 
			</li>
		<?php endif;

		if ( $pinterest ) : ?>
			<li>
				<a href="<?php echo esc_url($pinterest); ?>"><i class="fa fa-pinterest-square"></i></a> 
			</li>
		<?php endif;

		echo '</ul>';

		// Display the markup after the widget (as defined in functions.php)
		echo $after_widget;
	}
}

// Register the widget using an annonymous function
add_action( 'widgets_init', create_function( '', 'register_widget( "Material_Widget_Social_Links" );' ) );
?>