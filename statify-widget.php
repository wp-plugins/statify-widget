<?php
/*
Plugin Name: Statify Widget: Beliebte Inhalte
Description: Widget für populäre Seiten, Artikel und andere Inhaltstypen auf der Grundlage des datenschutzkonformen Statistik Plugin Statify von Sergej Müller.
Author: Finn Dohrn
Author URI: http://www.bit01.de/
Plugin URI: http://www.bit01.de/blogstatify-widget
Version: 1.1
*/

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
require_once( 'Statify_Posts.class.php' );

define('DEFAULT_AMOUNT', 5);
define('DEFAULT_POST_TYPE','post');

class StatifyWidget extends WP_Widget {
 
	function StatifyWidget() {
		$widget_ops = array('classname' => 'statify-widget', 'description' => 'Zeigt beliebte Inhalte auf der Grundlage von Statify.' );
		$this->WP_Widget('StatifyWidget', 'Statify Widget', $widget_ops);
	}
	 
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'amount' => DEFAULT_AMOUNT, 'post_type' => DEFAULT_POST_TYPE, 'show_visits' => false ) );
    	$title = $instance['title'];
		$amount = $instance['amount'];
		$post_type = $instance['post_type'];
		$show_visits = $instance['show_visits'];
?>

        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>">Titel:
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
          </label>
        </p>
        <p>
        <label for="<?php echo $this->get_field_id('post_type'); ?>">Inhaltstyp:
        <select class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>">
          <?php
                $post_types = get_post_types( array('public'=>true, 'show_ui'=>true), 'objects' ); 
                foreach ( $post_types as $type ) {
                    $selected = ($post_type == $type->name) ? " selected" : "";
                    echo '<option value="'. $type->name . '"'. $selected .'>' . $type->labels->name . '</option>';
                }
          ?>
        </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('amount'); ?>">Anzahl:
            <input id="<?php echo $this->get_field_id('amount'); ?>" name="<?php echo $this->get_field_name('amount'); ?>" type="text" size="3" value="<?php echo attribute_escape($amount); ?>" />
          </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('show_visits'); ?>">
            <input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('show_visits'); ?>" name="<?php echo $this->get_field_name('show_visits'); ?>" <?php checked($show_visits,'on'); ?>>
            Aufrufe anzeigen?</label>
        </p>
<?php
	}
	 
	function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['post_type'] = ( ! empty( $new_instance['post_type'] ) ) ? strip_tags( $new_instance['post_type'] ) : '';
		$instance['amount'] = ( ! empty( $new_instance['amount'] ) ) ? strip_tags( $new_instance['amount'] ) : '';
		$instance['show_visits'] = ( ! empty( $new_instance['show_visits'] ) ) ? strip_tags( $new_instance['show_visits'] ) : 0;
		return $instance;
	}
	 
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
	 
		echo $before_widget;
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$amount = empty($instance['amount']) ? DEFAULT_AMOUNT : $instance['amount'];
		$post_type = empty($instance['post_type']) ? DEFAULT_POST_TYPE : $instance['post_type'];
		$show_visits = ($instance['show_visits']) ? 1 : 0;

		if (!empty($title)) echo $before_title . $title . $after_title;

		$popular_content = Statify_Posts::get_posts($post_type, $amount);
		if (empty( $popular_content )) {
			echo "<p>Es gibt hier zu keine Einträge.</p>";
		} else {
			echo '<ol class="statify-widget">';
			foreach($popular_content as $post) {
				$visits = ($show_visits) ? " (" . $post['visits'] . ")" : '';
				echo '<li><a title="' . $post['title']. '" href="' . $post['url'] . '">' . $post['title'] . $visits . '</a></li>';
			}
			echo "</ol>";
		}
	 
		echo $after_widget;
	}
}

function showErrorMessages() {
	$html = '<div class="error"><p>';
	$html .= __( 'Bitte installieren und aktivieren Sie zuerst das <a target="_blank" href="http://wordpress.org/plugins/statify/">Statify</a> Plugin von Sergej M&uuml;ller.', 'error-statify-widget' );
	$html .= '</p></div>';
	echo $html;
}

function requires_statify_plugin() {
    $plugin_bcd_plugin = 'statify/statify.php';
    $plugin = plugin_basename( __FILE__ );
    $plugin_data = get_plugin_data( __FILE__, false );
	
	if ( !is_plugin_active( $plugin_bcd_plugin) ) {
        deactivate_plugins ( $plugin );	
		add_action('admin_notices', 'showErrorMessages');  
    }
}

add_action( 'widgets_init', create_function('', 'return register_widget("StatifyWidget");') );
add_action( 'admin_init', 'requires_statify_plugin' );
?>
