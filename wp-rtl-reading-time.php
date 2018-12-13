<?php
/**
 * Plugin Name:       WP-RTL - Reading Time
 * Description:       תוסף פשוט להצגת זמן משוער לקריאת הפוסט
 * Version:           1.0.0
 * Author:            WP-RTL.co.il
 * Author URI:        http://wp-rtl.co.il
 * Text Domain:       wp-rtl-reading-time
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: #
 */

class WP_RTL_Reading_Time
{
	private $default_config;

    public function __construct(){

		$this->default_config = array(
			'prefix' => 'Reading Time:',
			'suffix' => 'Minutes',
			'suffix-singular' => 'Minute',
			'less-than-one' => '< 1',
			'wpm' => 275,
			'template' => '<div class="wp-rtl-readingtime"><span class="wp-rtl-readingtime--prefix">%1$s</span> <span class="wp-rtl-readingtime--time">%2$s</span> <span class="wp-rtl-readingtime--suffix">%3$s</span></div>'
		);

		apply_filters( 'wp-rtl-reading-time-template', $this->default_config['template'] );

        // add settings page to customizer
		add_action( 'customize_register', array( $this, 'add_customizer_controllers') );
		
		// add a shortcode
		add_shortcode( 'wp-rtl-readingtime', array( $this, 'reading_time_shortcode' ) );
        
	}

	/**
	 * Reading time shortcode setup
	 *
	 * @param Array $atts
	 * @return String $string html template with the data
	 */
	public function reading_time_shortcode( $atts ) {
		$post_id = get_the_ID();

		if( ! isset( $post_id ) || empty( $post_id )  ){
			return;
		}

		$args = shortcode_atts( array(
			'prefix'          => get_option('wp-rtl-reading-time--prefix', $this->default_config['prefix']),
			'suffix'          => get_option('wp-rtl-reading-time--suffix', $this->default_config['suffix']),
		), $atts );

		$time = $this->get_reading_time( $post_id );

		$string = wp_sprintf( $this->default_config['template'] , $args['prefix'], $time, $args['suffix'] );

		return $string;
	}

	/**
	 * Calucalte the reading time
	 *
	 * @param Int $post_id - post id to calculate from
	 * @return Int the time in minutes
	 */
	private function get_reading_time($post_id){
		$time              = 0;
		$content           = get_post_field( 'post_content', $post_id );
		$content_no_images = $this->strip_images( $content );
		$word_count        = preg_match_all('/\s+/', $content_no_images);
		$images_count      = $this->images_count( $content );

		$wpm = intval( get_option( 'wp-rtl-reading-time--wpm', $this->default_config['wpm'] ) );

		// TODO: for debugging
		$word_count = 100;

		$time = $word_count / $wpm;

		$time_per_image = 12;
		for ( $i=0 ; $i < $images_count && $time_per_image > 0 ; $i++ ) { 
			$time += $time_per_image;
			$time_per_image--;
		}

		$time = round( $time );

		if($time < 1){
			$time = get_option( 'wp-rtl-reading-time--less-than-one', $this->default_config['less-than-one'] );
		}

		apply_filters( 'wp-rtl-reading-time-time', $time, $word_count );
		
		return $time;
	}

	/**
	 * strip images from content
	 *
	 * @param String $content
	 * @return String $content without images
	 */
	private function strip_images ( $content ){
		$content = preg_replace("/<img[^>]+\>/i", " ", $content);          
		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]>', $content);

		return $content;
	}

	/**
	 * Count how many images are in the post
	 *
	 * @param String $content
	 * @return Int Images in post
	 */
	private function images_count ( $content ){
		return preg_match_all('/<img[^>]+\>/i', $content);
	}
	
	/**
     * Add popup settings page to the customizer page
     *
     * @param WP_Customize_Manager $wp_customize
     * @return void
     */
    public function add_customizer_controllers($wp_customize){
        // Global theme settings
        $wp_customize->add_section("wp-rtl-reading-time", array(
            "title" => __('Reading Time Settings', 'wp-rtl-reading-time'),
            "priority" => 30,
        ));
        // Add control and output for select field
        $wp_customize->add_setting("wp-rtl-reading-time--checkbox");
        $wp_customize->add_control("wp-rtl-reading-time--checkbox", array(
            'label' => __('Enable popup', 'wp-rtl-reading-time'),
            'section' => 'wp-rtl-reading-time',
            'settings' => 'wp-rtl-reading-time--checkbox',
            'type' => 'checkbox'
        ));

        $wp_customize->add_setting("wp-rtl-reading-time--prefix", array(
			'default' => $this->default_config['prefix']
		));
        $wp_customize->add_control("wp-rtl-reading-time--prefix", array(
            'label' => __('Reading time label prefix', 'wp-rtl-reading-time'),
            'section' => 'wp-rtl-reading-time',
            'description' => __('The text label shown before the time', 'wp-rtl-reading-time'),
            'settings' => 'wp-rtl-reading-time--prefix',
            'type' => 'text'
		));
		
        $wp_customize->add_setting("wp-rtl-reading-time--suffix", array(
			'default' => $this->default_config['suffix']
		));
        $wp_customize->add_control("wp-rtl-reading-time--suffix", array(
            'label' => __('Reading time label suffix', 'wp-rtl-reading-time'),
            'section' => 'wp-rtl-reading-time',
            'description' => __('The text label shown after the time', 'wp-rtl-reading-time'),
            'settings' => 'wp-rtl-reading-time--suffix',
            'type' => 'text'
		));
		
        $wp_customize->add_setting("wp-rtl-reading-time--suffix-singular", array(
			'default' => $this->default_config['suffix-singular']
		));
        $wp_customize->add_control("wp-rtl-reading-time--suffix-singular", array(
            'label' => __('Reading time label suffix(singular)', 'wp-rtl-reading-time'),
            'section' => 'wp-rtl-reading-time',
            'description' => __('The text label shown after the time in case of 1 minute', 'wp-rtl-reading-time'),
            'settings' => 'wp-rtl-reading-time--suffix-singular',
            'type' => 'text'
		));
		
        $wp_customize->add_setting("wp-rtl-reading-time--wpm", array(
			'default' => $this->default_config['wpm']
		));
        $wp_customize->add_control("wp-rtl-reading-time--wpm", array(
            'label' => __('Words per minute', 'wp-rtl-reading-time'),
            'section' => 'wp-rtl-reading-time',
            'description' => __('Default value is ' . $this->default_config["wpm"] . ' words per minute', 'wp-rtl-reading-time'),
            'settings' => 'wp-rtl-reading-time--wpm',
            'type' => 'number'
        ));
		
        $wp_customize->add_setting("wp-rtl-reading-time--less-than-one", array(
			'default' => $this->default_config['less-than-one']
		));
        $wp_customize->add_control("wp-rtl-reading-time--less-than-one", array(
            'label' => __('Time if less than 1', 'wp-rtl-reading-time'),
            'section' => 'wp-rtl-reading-time',
            'description' => __('What to print if the time estimation is less than a minute', 'wp-rtl-reading-time'),
            'settings' => 'wp-rtl-reading-time--less-than-one',
            'type' => 'text'
		));
		
        $wp_customize->add_setting("wp-rtl-reading-time--shortcode", array(
			'default' => '[wp-rtl-readingtime]'
		));
		
        $wp_customize->add_control("wp-rtl-reading-time--shortcode", array(
            'label' => __('The shortcode', 'wp-rtl-reading-time'),
			'section' => 'wp-rtl-reading-time',
			'description' => __('You can pass `prefix` and `suffix` attributes to the shortcode', 'wp-rtl-reading-time'),
            'settings' => 'wp-rtl-reading-time--shortcode',
            'input_attrs' => array( 'readonly' => true )
        ));
    }
}

new WP_RTL_Reading_Time();

?>