<?php
/**
 *
 * Plugin Name: Proof Of Concept - Custom Post Type permalinked to Taxonomy
 * Plugin URI: http://sethcarstens.com
 * Description: Demo use case of custom post types, custom taxnomies, and custom rewrites connecting them together in hierarchy URL structures
 * Author: Seth Carstens
 * Version: 0.1.0
 * Author URI: http://sethcarstens.com
 * License: GPL 3.0
 * Text Domain: none
 *
 * GitHub Plugin URI: https://github.com/TBD
 * GitHub Branch: master
 *
 * @package  		none
 * @category 		proof-of-concept
 * @author   		Seth Carstens <seth.carstens@gmail.com>
 * @dependencies    PHP5.5
 */

class Custom_Post_Type_permalinked_to_Taxonomy{

	public static $cpt_name = 'custom_event';
	public static $tax_name = 'event_type';
	public static $link_by  = 'events';

	function __construct($args = []){
		add_action( 'init', array(static::class, 'create_custom_tax') );
		add_action( 'init', array(static::class, 'create_custom_post_type') );
		add_filter( 'post_type_link', array(static::class, 'proper_custom_permalinks'), 10, 2 );
		self::maybe_debug(static::class, __FUNCTION__, static::$cpt_name);
	}

	static function create_custom_tax(){
		register_taxonomy(
			static::$tax_name, //'event_type'
			static::$cpt_name,//'custom_event'
			array(
				'label' => static::$tax_name,
				'singular_label' => static::$tax_name,
				'hierarchical' => true,
				'query_var' => true,
				'rewrite' => array('slug' => static::$link_by),//'events'
			)
		);
		self::maybe_debug(static::class, __FUNCTION__, static::$cpt_name);
	}

	static function create_custom_post_type() {
		$labels = array(
			'name' => _x(static::$cpt_name, 'many cpts description'),
			'singular_name' => _x(static::$cpt_name, 'single cpt description')
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'query_var' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('title','editor','thumbnail', 'excerpt'),
			'rewrite' => array(
				'slug' => static::$link_by.'/%'.static::$tax_name.'%',//'events/%event_type%'
				'with_front' => false
			),
			'has_archive' => static::$link_by//'events'
		);

		register_post_type( static::$cpt_name , $args );//'custom_event'
		flush_rewrite_rules();
		self::maybe_debug(static::class, __FUNCTION__, static::$cpt_name);
	}

	/**
	 * Creates the awesome linking between CPT and TAX
	 * ie http://local.wordpress.dev/events/awesome-seo/seo-proper-custom-post-event/
	 *
	 * @param $post_link
	 * @param $post
	 *
	 * @return mixed
	 */
	static function proper_custom_permalinks($post_link, $post)
	{
		if ( false !== strpos( $post_link, '%'.static::$tax_name.'%' ) ) {
			$event_type_term = get_the_terms( $post->ID, static::$tax_name );
			@$post_link = str_replace( '%'.static::$tax_name.'%', array_pop( $event_type_term )->slug, $post_link );
		}
		/* how it would look hard coded
		 * if ( false !== strpos( $post_link, '%event_type%' ) ) {
		 * 	$event_type_term = get_the_terms( $post->ID, 'event_type' );
		 * 	@$post_link = str_replace( '%event_type%', array_pop( $event_type_term )->slug, $post_link );
		 * }
		 */
		return $post_link;
		self::maybe_debug(static::class, __FUNCTION__, static::$cpt_name);
	}

	/**
	 * Activate the plugin
	 *
	 */
	static function activate() {
		// flush rewrite rules to ensure permalinks work
		static::create_custom_post_type();
		static::create_custom_tax();
		flush_rewrite_rules();
		//TODO: on activation build custom post and taxonomy items for POC
	} // END public static function activate

	/**
	 * Deactivate the plugin
	 *
	 */
	static function deactivate() {

	} // END public static function deactivate

	static function maybe_debug($__CLASS__, $__FUNCTION__, $cpt_name){
		if( ! empty($_REQUEST['debug']) ) {
			print_r('--------------------------------------------- '.$__CLASS__.':'.$__FUNCTION__.' --- '.$cpt_name.'<br />');
		}
	}
}

/**
 * Build and initialize the plugin
 */
if ( class_exists( 'Custom_Post_Type_permalinked_to_Taxonomy' ) ) {
	// Installation and un-installation hooks
	register_activation_hook( __FILE__, array( 'Custom_Post_Type_permalinked_to_Taxonomy', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'Custom_Post_Type_permalinked_to_Taxonomy', 'deactivate' ) );
	//initialize the plugin
	new Custom_Post_Type_permalinked_to_Taxonomy();
}

/**
 * Wanna get crazy advanced? Be extensible.
 */
class Books_And_Chapters extends Custom_Post_Type_permalinked_to_Taxonomy{
	public static $cpt_name = 'custom_book';
	public static $tax_name = 'book_chapter';
	public static $link_by  = 'chapters';
}
// Installation and un-installation hooks
register_activation_hook( __FILE__, array( 'Books_And_Chapters', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Books_And_Chapters', 'deactivate' ) );
//initialize the plugin
$books = new Books_And_Chapters();