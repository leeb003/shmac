<?php
/**
 * SHMAC Widget
 */
class shmac_widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            // Base ID of your widget
            'shmac_widget',
            // Widget name will appear in UI
            __('WP Amortization Calculator', 'shmac'),

            // Widget description
            array( 'description' => __( 'WP Mortgage and Amortization Calculator', 'shmac' ), )
        );
    }

	public function widget( $args, $instance ) {
		extract( $args );
		$title       = apply_filters('widget_title', $instance['title'] );
		$shmac_override = $instance['shmac_override'];
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( $title ) {
            echo $before_title . $title . $after_title;
        }
		$calculator = '[shmac_calc_sc ' . $shmac_override . ']';
		echo do_shortcode($calculator);
		echo $args['after_widget'];
	}
	         
	// Widget Backend
	public function form( $instance ) {
		$defaults = array(
			'title' => ' ',
			'shmac_override' => '',
            'shmac_class' => ''
        );
        $instance = wp_parse_args( (array) $instance, $defaults );
	?>
	
	<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title','shmac'); ?></label>
    <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" type="text" />
	</p>
	<p>
    <label for="<?php echo $this->get_field_id( 'shmac_override' ); ?>"><?php _e( 'Calculator Overrides:', 'shmac' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'shmac_override' ); ?>" name="<?php echo $this->get_field_name( 'shmac_override' ); ?>" type="text" value="<?php echo esc_attr( $instance['shmac_override'] ); ?>" />
    </p>
	<?php
	}
	     
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']       = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['shmac_override'] = ( ! empty( $new_instance['shmac_override'] ) ) ? sanitize_text_field( $new_instance['shmac_override'] ) : '';
		return $instance;
	}
}

function register_shmac_widget() {
    register_widget('shmac_widget');
}

add_action('widgets_init', 'register_shmac_widget');
