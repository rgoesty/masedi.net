<?php
/*
Plugin Name: WordPress.com Popular Posts
Plugin URI: http://polpoinodroidi.netsons.org/wordpress-plugins/wordpresscom-popular-posts/
Description: Shows the most popular posts, using data collected by <a href='http://wordpress.org/extend/plugins/stats/'>WordPress.com stats</a> plugin.
Version: 1.1.2
Author: Frasten
Author URI: http://polpoinodroidi.netsons.org
*/

/* Created by Frasten (email : frasten@gmail.com) under a GPL licence. */


$WPPP_defaults = array('title'   => __('Popular Posts')
	                     ,'number' => '5'
	                     ,'days'   => '0'
	                     ,'show'   => 'both'
	                     ,'format' => "<a href='%post_permalink%' title='%post_title_attribute%'>%post_title%</a>"
	);

class WPPP {
	
	function generate_widget() {
		global $WPPP_defaults;
		if ( false && !function_exists( 'stats_get_options' ) || !function_exists( 'stats_get_csv' ) )
			return;
		
		$opzioni = WPPP::get_impostazioni();
		
		$args = func_get_args();
		if ( isset( $args[0] ) ) {
			$args = $args[0];
			// Called with arguments
			if ( !is_array( $args ) )
				$args = wp_parse_args( $args );
			
			foreach ( $args as $key => $value ) {
				$opzioni[$key] = $value;
			}
		}
			
		// Tags before and after the title (as called by WordPress)
		if ( $opzioni['before_title'] || $opzioni['after_title'] ) {
			$opzioni['title'] = $opzioni['before_title'] . $opzioni['title'] . $opzioni['after_title'];
		}
		
		
		// Check against malformed values
		$opzioni['days'] = intval( $opzioni['days'] );
		$opzioni['number'] = intval( $opzioni['number'] );
		
		if ( $opzioni['days'] <= 0 )
			$opzioni['days'] = '-1';
		
		// A little hackish, but "could" work!
		$howmany = $opzioni['number'];
		if ( $opzioni['show'] == 'posts' )
			$howmany *= 2;
		else if ( $opzioni['show'] == 'pages' )
			$howmany *= 4; // pages are usually less, let's try more!
		
		
		/* TEMPORARY FIX FOR WP_STATS PLUGIN */
		$stats_cache = get_option( 'stats_cache' );
		if ( !$stats_cache || !is_array( $stats_cache ) )
			update_option( 'stats_cache', "");
		/* END FIX */
		
		$top_posts = stats_get_csv( 'postviews', "days={$opzioni['days']}&limit=$howmany" );
		
		echo $opzioni['title'] . "\n";
		echo "<ul class='wppp_list'>\n";
		
		if ( $opzioni['show'] != 'both') {
			// I want to show only posts or only pages
			$id_list = array();
			foreach ( $top_posts as $p ) {
				$id_list[] = $p['post_id'];
			}
			global $wpdb;

			// If no top-posts, just do nothing gracefully
			if ( sizeof( $id_list ) ) {
				$results = $wpdb->get_results("
				SELECT id FROM {$wpdb->posts} WHERE id IN (" . implode(',', $id_list) . ") AND post_type = '" .
				( $opzioni['show'] == 'pages' ? 'page' : 'post' ) . "'
				");
				$valid_list = array();
				foreach ( $results as $valid ) {
					$valid_list[] = $valid->id;
				}
				
				$temp_list = array();
				foreach ( $top_posts as $p ) {
					if ( in_array( $p['post_id'], $valid_list ) )
						$temp_list[] = $p;
					if ( sizeof( $temp_list ) >= $opzioni['number'] )
						break;
				}
				$top_posts = $temp_list;
				unset($temp_list);
			} // end if (I have posts)
		} // end if (I chose to show only posts or only pages)
		
		foreach ( $top_posts as $post ) {
			echo "<li>";
			
			// Replace format with data
			$replace = array(
				'%post_permalink%'       => $post['post_permalink'],
				'%post_title%'           => $post['post_title'],
				'%post_title_attribute%' => htmlspecialchars( $post['post_title'], ENT_QUOTES ),
				'%post_views%'           => number_format_i18n( $post['views'] )
			);
			
			echo strtr( $opzioni['format'], $replace );
			
			echo "</li>\n";
		}
		echo "</ul>\n";
	}
	
	function init() {
		if ( !function_exists( 'register_sidebar_widget' ) || !function_exists( 'register_widget_control' ) )
			return;
		
		function print_widget( $args ) {
			extract( $args );
			echo $before_widget;
			echo WPPP::generate_widget( "before_title=$before_title&after_title=$after_title" );
			echo $after_widget;
		}
		register_sidebar_widget( array( __( 'Popular Posts' ), 'widgets' ), 'print_widget' );
		register_widget_control( array( __( 'Popular Posts' ), 'widgets' ), array( 'WPPP', 'impostazioni_widget' ), 350, 20 );
	}
	
	function get_impostazioni() {
		global $WPPP_defaults;
		$opzioni = get_option( 'widget_wppp' );

		$opzioni['title'] = $opzioni['title'] !== NULL ? $opzioni['title'] : $WPPP_defaults['title'];
		$opzioni['number'] = $opzioni['number'] !== NULL ? $opzioni['number'] : $WPPP_defaults['number'];
		$opzioni['days'] = $opzioni['days'] !== NULL ? $opzioni['days'] : $WPPP_defaults['days'];
		$opzioni['show'] = $opzioni['show'] !== NULL ? $opzioni['show'] : $WPPP_defaults['show'];
		$opzioni['format'] = $opzioni['format'] !== NULL ? $opzioni['format'] : $WPPP_defaults['format'];
		return $opzioni;
	}
	
	function impostazioni_widget() {
		global $WPPP_defaults;

		$opzioni = WPPP::get_impostazioni();
		
		
		if ( isset( $_POST['wppp-titolo'] ) ) {
			$opzioni['title'] = strip_tags( stripslashes( $_POST['wppp-titolo'] ) );
		}
		if ( isset( $_POST['wppp-numero-posts'] ) ) {
			$opzioni['number'] = intval( $_POST['wppp-numero-posts'] );
		}
		if ( isset( $_POST['wppp-days'] ) ) {
			$opzioni['days'] = intval( $_POST['wppp-days'] );
		}
		if ( isset( $_POST['wppp-show'] ) ) {
			if ( !in_array( $opzioni['show'], array( 'both','posts','pages' ) ) )
				$_POST['wppp-show'] = $WPPP_defaults['show'];
			$opzioni['show'] = strip_tags( $_POST['wppp-show'] );
		}
		if ( isset( $_POST['wppp-days'] ) ) {
			$opzioni['format'] = stripslashes( $_POST['wppp-format'] );
		}
		update_option( 'widget_wppp', $opzioni );
		
		
		// WP < 2.5 needed this
		global $wp_version;
		if ( version_compare( $wp_version, '2.5', '<' ) ) {
			$opzioni['title'] = utf8_decode( $opzioni['title'] );
		}
		
		echo '<p style="text-align:right;"><label for="wppp-titolo">';
		echo __( 'Title' );
		echo ': <input style="width: 180px;" id="wppp-titolo" name="wppp-titolo" type="text" value="' . htmlspecialchars( $opzioni['title'], ENT_QUOTES ) . '" /></label></p>';
		
		echo '<p style="text-align:right;"><label for="wppp-numero-posts">';
		echo __( 'Number of links shown' );
		echo ': <input style="width: 180px;" id="wppp-numero-posts" name="wppp-numero-posts" type="text" value="' . $opzioni['number'] . '" /></label></p>';
		
		echo '<p style="text-align:right;"><label for="wppp-days">';
		echo __( 'The length (in days) of the desired time frame.<br />0 means unlimited' );
		echo ': <input style="width: 180px;" id="wppp-days" name="wppp-days" type="text" value="' . $opzioni['days'] . '" /></label></p>';
		
		echo '<p style="text-align:right;"><label for="wppp-show">';
		echo __( 'Show: ' );
		
		$opt = array(
			'both'  => __( 'posts and pages' ),
			'posts' => __( 'only posts' ),
			'pages' => __( 'only pages' )
		);
		if ( !$opzioni['show'] )
			$opzioni['show'] = $WPPP_defaults['show'];
		echo "<select name='wppp-show' id='wppp-show'>\n";
		foreach ( $opt as $key => $value ) {
			$sel = ( $opzioni['show'] == $key ) ? ' selected="selected"' : '';
			echo "<option value='$key'$sel>$value</option>\n";
		}
		echo '</select></label></p>';
		
		echo '<p style="text-align:right;"><label for="wppp-format">';
		echo __( 'Format of the links. See <a href="http://polpoinodroidi.netsons.org/wordpress-plugins/wordpresscom-popular-posts/">docs</a> for help' );
		echo ': <input style="width: 300px;" id="wppp-format" name="wppp-format" type="text" value="' . htmlspecialchars( $opzioni['format'], ENT_QUOTES ) . '" /></label></p>';
	}
	
	
}

/* You can call this function if you want to integrate the plugin in a theme
 * that doesn't support widgets.
 * 
 * Just insert this code: 
 * <?php if ( function_exists( 'WPPP_show_popular_posts' ) ) WPPP_show_popular_posts();?>
 * 
 * Optionally you can add some parameters to the function, in this format:
 * name=value&name=value etc.
 * 
 * Possible names are:
 * - title (title of the widget, you can add tags (e.g. <h3>Popular Posts</h3>) default: Popular Posts)
 * - number (number of links shown, default: 5)
 * - days (length of the time frame of the stats, default 0, i.e. infinite)
 * - show (both, posts, pages, default both)
 * - format (the format of the links shown, default: <a href='%post_permalink%' title='%post_title%'>%post_title%</a>)
 * 
 * Example: if you want to show the widget without any title, the 3 most viewed
 * articles, in the last week, and in this format: My Article (123 views)
 * you will use this:
 * WPPP_show_popular_posts( "title=&number=3&days=7&format=<a href='%post_permalink%' title='%post_title_attribute%'>%post_title% (%post_views% views)</a>" );
 * 
 * You don't have to fill every field, you can insert only the values you
 * want to change from default values.
 * 
 * You can use these special markers in the `format` value:
 * %post_permalink% the link to the post
 * %post_title% the title the post
 * %post_title_attribute% the title of the post; use this in attributes, e.g. <a title='%post_title_attribute%'
 * %post_views% number of views
 * 
 * */
function WPPP_show_popular_posts( $user_args = '' ) {
	global $WPPP_defaults;
	$args = wp_parse_args( $user_args, $WPPP_defaults );
	// remove slashes in format
	if ( isset( $args['format'] ) ) {
		$args['format'] = stripslashes( $args['format'] );
	}	
	
	WPPP::generate_widget( $args );
}

add_action( 'widgets_init', array( 'WPPP', 'init' ) );

?>
