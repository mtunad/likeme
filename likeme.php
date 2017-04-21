<?php
/*
Plugin Name: Likeme
Plugin URI: https://github.com/entriol/likeme
Description: Add like button to your posts.
Author: entriol
Version: 0.1
Author URI: http://twitter.com/entriol
Text Domain: likeme
*/

/* Widget initialization */
class Likeme_Widget extends WP_Widget {
    // Register widget to Wordress
    function __construct() {
        parent::__construct(
            'likeme_widget',
            esc_html__( 'Likeme - Liked posts', 'text_domain' ),
            array( 'description' => esc_html__( 'A widget to show most liked posts', 'text_domain' ), )
        );
    }

    // Front end display of widget
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }

        $meta_key = '_likeme_up';

        $arguments = array (
            'post_type'				=> 'any',
            'post_status'			=> 'publish',
            'pagination'			=> false,
            'posts_per_page'		=> 10,
            'cache_results'			=> true,
            'meta_key'				=> $meta_key,
            'order'					=> 'DESC',
            'orderby'				=> 'meta_value_num',
            'ignore_sticky_posts'	=> true
        );

        $likeme_query = new WP_Query($arguments);

        if($likeme_query->have_posts()) {
            $return = '<ul class="likeme-top-list">';

            while ($likeme_query->have_posts()) {
                $likeme_query->the_post();

                $return .= '<li>';
                $return .= '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';



                $meta_values = get_post_meta(get_the_ID(), $meta_key);

                $return .= ' (+';

                if( sizeof($meta_values) > 0){
                    $return .= $meta_values[0];
                } else {
                    $return .= "0";
                }
                $return .= ')';

            }

            $return .= '</li></ul>';

            wp_reset_postdata();
        }

        echo $return;

        echo $args['after_widget'];
    }

    // A form to ask widget title
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Please, provide a title', 'text_domain' );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }

    // Update widget title from retrieved information in form()
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        return $instance;
    }
} // class Likeme_Widget

/* Register Likeme_Widget widget */
function register_likeme_widget() {
    register_widget( 'Likeme_Widget' );
}
add_action( 'widgets_init', 'register_likeme_widget' );


/* Some definitions to use later. */
define('likeme_url', plugins_url() ."/".dirname( plugin_basename( __FILE__ ) ) );
define('likeme_path', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );

/* Init scripts */
if  ( ! function_exists( 'likeme_scripts' ) ):
	function likeme_scripts() {
		wp_enqueue_script('likeme_scripts', likeme_url . '/js/likeme.js', array('jquery'), '4.0.1');
		wp_localize_script(
		        'likeme_scripts',
                'likeme_ajax',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( 'likeme-nonce' )
                )
        );
	}
	add_action('wp_enqueue_scripts', 'likeme_scripts');
endif;

/* Init styles */
if  ( ! function_exists( 'likeme_styles' ) ):
	function likeme_styles()	{
	    wp_register_style( "likeme_styles",  likeme_url . '/css/style.css' , "", "1.0.0");
	    wp_enqueue_style( 'likeme_styles' );
	}
	add_action('wp_enqueue_scripts', 'likeme_styles');
endif;

/* Create likeme link for the content */
if  ( ! function_exists( 'likeme_getlink' ) ):
	function likeme_getlink($post_ID = '', $type_of_vote = '') {
		$post_ID = intval( sanitize_text_field( $post_ID ) );
		$type_of_vote = intval ( sanitize_text_field( $type_of_vote ) );

		if( $post_ID == '' ) $post_ID = get_the_ID();

		$likeme_up_count = get_post_meta($post_ID, '_likeme_up', true) != '' ? get_post_meta($post_ID, '_likeme_up', true) : '0';

		$link_up = '<span class="likeme-up" data-vote="1">👍 <strong>' . $likeme_up_count . '</strong></span>';

		$likeme_link = '<div  class="likeme-container" id="likeme-'.$post_ID.'" data-content-id="'.$post_ID.'">' . $link_up . '</div>';

		return $likeme_link;
	}
endif;

if  ( ! function_exists( 'add_like_button' ) ):
    function add_like_button($content) {
        global $post;
        if ($post->post_type == 'post') {
            ob_start();
            if (is_single()) {
                $content .= ob_get_contents();
                $content .= likeme_getlink();
            }
            ob_end_clean();
        }
        return $content;
    }
endif;

add_filter('the_content', 'add_like_button');

/* Handle the ajax request */
if  ( ! function_exists( 'likeme_add_vote_callback' ) ):
	function likeme_add_vote_callback() {
		check_ajax_referer( 'likeme-nonce', 'nonce' );

		global $wpdb;

		$post_ID = intval( $_POST['postid'] );
		$type_of_vote = intval( $_POST['type'] );

		$meta_name = "_likeme_up";
		$likeme_count = get_post_meta($post_ID, $meta_name, true) != '' ? get_post_meta($post_ID, $meta_name, true) : '0';

		if ( $type_of_vote == 1 || $type_of_vote == -1) {
		    $likeme_count = $likeme_count + $type_of_vote ;
		}

		update_post_meta($post_ID, $meta_name, $likeme_count);

		$results = likeme_getlink($post_ID, $type_of_vote);

		die($results);
	}

	add_action( 'wp_ajax_likeme_add_vote', 'likeme_add_vote_callback' );
	add_action('wp_ajax_nopriv_likeme_add_vote', 'likeme_add_vote_callback');
endif;

/* Create admin page for the likes */
if  ( ! function_exists( 'load_custom_wp_admin_style' ) ):
    function load_custom_wp_admin_style() {
            wp_enqueue_style( 'likeme_styles',   likeme_url . '/css/jquery.DataTables.min.css' , false, "1.0.0" );
            wp_enqueue_script('custom_wp_admin_js', likeme_url . '/js/jquery.DataTables.min.js',  array('jquery'), '1.0.0');
    }
endif;
add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );

if  ( ! function_exists( 'likeme_menu' ) ):
    function likeme_menu() {
        add_options_page( 'Likeme Options', 'Likeme', 'manage_options', 'likeme-plugin', 'likeme_options' );
    }
endif;

add_action( 'admin_menu', 'likeme_menu' );

if  ( ! function_exists( 'likeme_options' ) ):
    function likeme_options() {
        global $wpdb;

        echo '<div class="wrap">';
        echo '<p>Here is most liked tags.</p>';
        echo '</div>';
        ?>
    <table id='myTable'>
        <thead>
            <tr>
                <td>Etiket</td>
                <td>Beğenilme</td>
            </tr>
        </thead>

        <tbody>
    <?php
        $tags = get_tags();
        foreach($tags as $tag) {
            $sum = 0;
            echo '<tr><td><strong>'.$tag->name. '</strong></td>';
            $args=array(
                'tag__in' => array($tag->term_id),
                'showposts'=>-1
            );
            $my_query = new WP_Query($args);
            if( $my_query->have_posts() ) {
                while ($my_query->have_posts()) : $my_query->the_post();
                    if (get_post_meta(get_the_ID(), '_likeme_up', true) != '') {
                        $sum += get_post_meta(get_the_ID(), '_likeme_up', true);
                    }
                endwhile;
                echo '<td>' . $sum . '</td>' ;
            }
            echo '</tr>';
        }
    ?>
        </tbody>
    </table>
    <?php
    } // likeme_options();
endif;

