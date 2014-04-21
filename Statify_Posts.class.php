<?php

class Statify_Posts {
	public static function get_posts($post_type, $amount) {
		$posts = array();
		$counter = 0;
		$wpurl = parse_url(get_bloginfo('wpurl'));
		$data = Statify_Dashboard::get_stats();
		
		foreach ($data['target'] as $entry) {
			if ($counter > $amount) break;
			
			$clear_url = str_replace($wpurl['path']."/","",$entry['url']);
			$id = url_to_postid(home_url( $clear_url ));
			
			if ( $id == 0 ) {
				$page = get_page_by_path( $clear_url );
				if (empty($page) && get_option('show_on_front') == 'page') $page = get_page(get_option('page_on_front'));
			} else $page = get_page($id);

			// Falls noch leer und Artikel als Startseite
			if (empty($page) && $post_type == 'page') {
				$posts[0]['title'] = "Startseite";
				$posts[0]['url'] = home_url("/");
				if ($posts[0]['visits'] == NULL) $posts[0]['visits'] = 0;
				$posts[0]['visits'] += $entry['count'];
				$counter++;
			}
			
			if($page->post_type == $post_type) {
				$posts[$page->ID]['title'] = $page->post_title;
				$posts[$page->ID]['url'] = home_url($clear_url);
				if ($posts[$page->ID]['visits'] == NULL) $posts[$page->ID]['visits'] = 0;
				$posts[$page->ID]['visits'] += $entry['count'];
				$counter++;
			}
		}
		usort($posts, array("Statify_Posts", "visitSort"));
		return $posts;
	}
	
	private static function visitSort($a, $b) {
		if ($a==$b) return 0;
		return ($a['visits']>$b['visits'])?-1:1;
	}
}