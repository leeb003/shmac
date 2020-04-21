<?php
class Elementor_Shmac_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'shmac';	
	}

	public function get_title() {
		return __('WP Amortization Calculator', 'shmac');
	
	}

	public function get_icon() {
		return 'eicon-checkbox';
	
	}

	public function get_categories() {
		return ['scripthat'];	
	
	}

	protected function _register_controls() {
		$this->start_controls_section(      
            'content_section',                              
            [                                                               
                'label' => __( 'Content', 'shmac' ),                              
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,                              
            ]
        );          
                                        
        $this->add_control(                         
			'calculator',
			[
				'label' => __('Calculator Overrides', 'shmac' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'placeholder' => 'e.g. calc_title="New"',
			]
        );          
                            
        $this->end_controls_section();	
	
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$calculator = '[shmac_calc_sc ' . $settings['calculator'] . ']';
                        
        echo '<div class="shmac-elementor-widget">';
		echo do_shortcode($calculator);
        echo '</div>';
	
	}

	protected function _content_template() {}

}
