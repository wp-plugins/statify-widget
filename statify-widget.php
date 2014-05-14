<?php
/*
Plugin Name: Statify Widget: Beliebte Inhalte
Description: Widget für populäre Seiten, Artikel und andere Inhaltstypen auf der Grundlage des datenschutzkonformen Statistik Plugin Statify von Sergej Müller.
Author: Finn Dohrn
Author URI: http://www.bit01.de/
Plugin URI: http://www.bit01.de/blog/statify-widget/
Version: 1.1.2
*/

require( 'Statify_Posts.class.php' );

define('DEFAULT_AMOUNT', 5);
define('DEFAULT_POST_TYPE','post');
define('DEFAULT_SUFFIX', '(%ANZAHL% Aufrufe)');

class StatifyWidget extends WP_Widget {
	/*
	* Register StatifyWidget to Wordpress
	*/
	function __construct() {
		$widget_ops = array('classname' => 'statify-widget', 'description' => 'Zeigt beliebte Inhalte auf der Grundlage von Statify.' );
		parent::__construct(
			'StatifyWidget',
			__('Statify Widget', 'text_domain'), 
			$widget_ops
		);
	}
	
	/*
	* Generating a from for settings
	*/
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
			'amount' => DEFAULT_AMOUNT,
			'post_type' => DEFAULT_POST_TYPE,
			'show_visits' => 0,
			'suffix' => DEFAULT_SUFFIX) );
    	$title = $instance['title'];
		$amount = $instance['amount'];
		$post_type = $instance['post_type'];
		$show_visits = $instance['show_visits'];
		$suffix = $instance['suffix'];
?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>">Titel:
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
          </label>
        </p>
        <p>
        <label for="<?php echo $this->get_field_id('post_type'); ?>">Inhaltstyp:
        <select class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>">
          <?php
                $post_types = get_post_types( array('public'=>true, 'show_ui'=>true), 'objects' ); 
                foreach ( $post_types as $type ) {
                    echo '<option value="'. esc_attr($type->name) . '" '. selected( $post_type, $type->name ) .'>' . esc_attr($type->labels->name) . '</option>';
                }
          ?>
        </select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('amount'); ?>">Anzahl:
            <input id="<?php echo $this->get_field_id('amount'); ?>" name="<?php echo $this->get_field_name('amount'); ?>" type="text" size="3" value="<?php echo esc_attr($amount); ?>" />
          </label>
          <label for="<?php echo $this->get_field_id('show_visits'); ?>"> 
            <input class="checkbox widget-description" style="margin-left:15px;" type="checkbox" id="<?php echo $this->get_field_id('show_visits'); ?>" name="<?php echo $this->get_field_name('show_visits'); ?>" value="1" <?php checked($show_visits,1); ?>>
            Aufrufe anzeigen?</label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('suffix'); ?>">Benutzerdefinierter Text:
            <input id="<?php echo $this->get_field_id('suffix'); ?>" class="widefat" name="<?php echo $this->get_field_name('suffix'); ?>" type="text" value="<?php echo esc_attr($suffix); ?>" />
            </label>
            <small>%ANZAHL% = Anzahl der Aufrufe</small>
        </p>
<?php
	}
	
	/*
	* Override old instance with new instance.
	*/
	function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['post_type'] = ( ! empty( $new_instance['post_type'] ) ) ? $new_instance['post_type'] : DEFAULT_POST_TYPE;
		$instance['amount'] = ( ! empty( $new_instance['amount'] ) ) ? sanitize_text_field( $new_instance['amount'] ) : DEFAULT_AMOUNT;
		$instance['show_visits'] = ( ! empty( $new_instance['show_visits'] ) ) ? $new_instance['show_visits'] : 0;
		$instance['suffix'] = ( ! empty( $new_instance['suffix'] ) ) ? sanitize_text_field( $new_instance['suffix'] ) : DEFAULT_SUFFIX;
		return $instance;
	}
	
	/*
	* Print the widget
	*/
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
	 
		echo $before_widget;
		
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$amount = empty($instance['amount']) ? DEFAULT_AMOUNT : $instance['amount'];
		$post_type = empty($instance['post_type']) ? DEFAULT_POST_TYPE : $instance['post_type'];
		$show_visits = empty($instance['show_visits']) ? 0 : 1;
		$suffix = empty($instance['suffix']) ? DEFAULT_SUFFIX : $instance['suffix'];

		if (!empty($title)) echo $before_title . $title . $after_title;
		
		$popular_content = Statify_Posts::get_posts($post_type, $amount);
		if (empty( $popular_content )) {
			echo "<p>Es gibt noch keine Einträge.</p>";
		} else {
			echo '<ol class="statify-widget">'."\n";
			foreach($popular_content as $post) {
				$_suffix = ($show_visits) ? ' <span>' . str_replace("%ANZAHL%", intval($post['visits']), $suffix) . '</span>' : '';
				echo '<li><a title="' . esc_html($post['title']) . '" href="' . esc_url($post['url']) . '">' . esc_html($post['title']) . '</a>'. $_suffix .'</li>'."\n";
			}
			echo "</ol>"."\n";
		}
	 
		echo $after_widget;
	}
}

/*
* Print error message in admin interface
*/
function showErrorMessages() {
	$html = '<div class="error"><p>';
	$html .= __( 'Bitte installieren und aktivieren Sie zuerst das <a target="_blank" href="http://wordpress.org/plugins/statify/">Statify</a> Plugin von Sergej M&uuml;ller.', 'error-statify-widget' );
	$html .= '</p></div>';
	echo $html;
}

/*
* Check if Statify is acitivated
*/
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