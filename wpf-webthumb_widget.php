<?php
class WebThumbWidget extends WP_Widget {

	function WebThumbWidget() {
		// Instantiate the parent object
		parent::__construct( false, 'WebThumb' );
	}

	function widget( $args, $instance ) {
		extract( $args );
		
		echo $before_widget;
		$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
		$url = empty($instance['url']) ? 'http://coste.mypressonline.com/wp/' : $instance['url'];
		$template = empty($instance['template']) ? 0 : $instance['template'];
		$label = empty($instance['label']) ? '&nbsp;' : $instance['label'];
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		
		// setup attributes
		$atts = array (
		'url'      => $url,
		'template' => $template,
		'size'     => 'small',
		'label'    => $label);
		// showup webthumb
		echo webthumb_shortcode( $atts );
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['url'] = strip_tags( $new_instance['url'] );
		$instance['template'] = strip_tags( $new_instance['template'] );
		$instance['label'] = strip_tags( $new_instance['label'] );
		return $instance;
	}

	// Output admin widget options form
	function form( $instance ) {
		$default = array( 'title'=>'WPF-WebThumb Widget', 'url'=>'http://faina09.it/category/wp-plugins/wpfwebthumb/', 'template'=>0);
		$instance = wp_parse_args( (array) $instance, $default );
		$field_id = $this->get_field_id('title');
		$field_name = $this->get_field_name('title');
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('title').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.attribute_escape( $instance['title'] ).'" /><label></p>';
		$field_id = $this->get_field_id('url');
		$field_name = $this->get_field_name('url');
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('url').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.attribute_escape( $instance['url'] ).'" /><label></p>';
		$field_id = $this->get_field_id('template');
		$field_name = $this->get_field_name('template');
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('template Nr.').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.attribute_escape( $instance['template'] ).'" /><label></p>';
		$field_id = $this->get_field_id('label');
		$field_name = $this->get_field_name('label');
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('label').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.attribute_escape( $instance['label'] ).'" /><label></p>';
	}
}
?>