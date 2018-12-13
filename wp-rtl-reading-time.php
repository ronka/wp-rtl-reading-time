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
    public function __construct(){
        // add settings page to customizer
        add_action( 'customize_register', array( $this, 'add_customizer_controllers') );
        
        // // load popup view in footer
        // add_action( 'wp_footer', array( $this, 'get_popup_view' ) );
        // // load assets files 
        // add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_popup_scripts') );
        // add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_popup_styles') );
	}

	/**
	 * Calucalte the reading time
	 *
	 * @param Int $post_id - post id to calculate from
	 * @return Int the time in minutes
	 */
	private function calculate_reading_time($post_id){
		$time = 0;
		$content = get_post_field( 'post_content', $post_id );
		$content_no_images = self::strip_images( $content );
		$images_count = self::images_count( $content );

		$wpm = intval( get_option( 'wp-rtl-reading-time--wpm', 275 ) );

		$time = $content_no_images / $wpm;

		$time_per_image = 12;
		for ( $i=0 ; $i < $images_count && $time_per_image > 0 ; $i++ ) { 
			$time += $time_per_image;
			$time_per_image--;
		}

		$time = floor( $time );
		
		return $time;
	}

	/**
	 * strip images from content
	 *
	 * @param String $content
	 * @return String $content without images
	 */
	public static function strip_images ( $content ){
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
	public static function images_count ( $content ){
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
			'default' => 'Reading time:'
		));
        $wp_customize->add_control("wp-rtl-reading-time--prefix", array(
            'label' => __('Reading time label prefix', 'wp-rtl-reading-time'),
            'section' => 'wp-rtl-reading-time',
            'description' => __('The text label shown before the time', 'wp-rtl-reading-time'),
            'settings' => 'wp-rtl-reading-time--prefix',
            'type' => 'text'
		));
		
        $wp_customize->add_setting("wp-rtl-reading-time--suffix", array(
			'default' => 'minutes'
		));
        $wp_customize->add_control("wp-rtl-reading-time--suffix", array(
            'label' => __('Reading time label suffix', 'wp-rtl-reading-time'),
            'section' => 'wp-rtl-reading-time',
            'description' => __('The text label shown after the time', 'wp-rtl-reading-time'),
            'settings' => 'wp-rtl-reading-time--suffix',
            'type' => 'text'
		));
		
        $wp_customize->add_setting("wp-rtl-reading-time--suffix-singular", array(
			'default' => 'minute'
		));
        $wp_customize->add_control("wp-rtl-reading-time--suffix-singular", array(
            'label' => __('Reading time label suffix(singular)', 'wp-rtl-reading-time'),
            'section' => 'wp-rtl-reading-time',
            'description' => __('The text label shown after the time in case of 1 minute', 'wp-rtl-reading-time'),
            'settings' => 'wp-rtl-reading-time--suffix-singular',
            'type' => 'text'
		));
		
        $wp_customize->add_setting("wp-rtl-reading-time--wpm", array(
			'default' => '275'
		));
        $wp_customize->add_control("wp-rtl-reading-time--wpm", array(
            'label' => __('Words per minute', 'wp-rtl-reading-time'),
            'section' => 'wp-rtl-reading-time',
            'description' => __('Default value is 275 words per minute', 'wp-rtl-reading-time'),
            'settings' => 'wp-rtl-reading-time--wpm',
            'type' => 'number'
        ));
    }
}

new WP_RTL_Reading_Time();

?>