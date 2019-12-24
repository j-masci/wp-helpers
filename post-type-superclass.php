<?php

namespace JM;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class MetaKeyMap
 * @package JM
 */
Class MetaKeyMap{}

/**
 * A superclass to extend for each of your post types in order to define
 * static methods for post meta getters etc.
 *
 * It's sort of like an active record, but only static methods instead (what do we call this?)
 *
 * WP_Post is already basically an active record. Therefore, (in my experience), it's quite
 * ugly to make an object which you instantiate from a post ID or WP_Post object, which then
 * wraps a WP_Post object. Instead, pass a post ID or WP_Post object into each static method,
 * then the class can be useful for organizing your code.
 *
 * todo: I don't like this class name.
 *
 * Class Post_Type_Superclass
 * @package JM
 */
Abstract Class Post_Type_Superclass{

    /**
     * Define your post type here.
     */
    const POST_TYPE = "";

    /**
     * A dictionary to explicitly declare your meta keys.
     *
     * Doing so has many potential advantages, including code completion,
     * prevention of typos, finding usages in code, and performing dynamic
     * actions by looping through the list of keys.
     *
     * The default is an empty object. You can use a stdClass if you want, but,
     * an explicitly defined class might be easier for your IDE to understand.
     *
     * @var object
     */
    public static $meta_keys = (object) [];

    /**
     * Define more logical default query args (than the WP default),
     * which saves a few lines of code in some instances.
     *
     * In addition, the post type constant is normally also injected.
     *
     * @var array
     */
    public static $default_query_args = [
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ];

    /**
     * Need to call this once and once only per script.
     *
     * This exists because we cannot write:
     *
     * public static $meta_keys = new Explicitly_Named_Class()
     *
     * If you wanted to, you could register your post type in here, but I'll
     * leave that up to you.
     */
    public static function init(){}

    /**
     * Simply wraps get_post_meta.
     *
     * It's debatable whether or not we should even use such a method.
     *
     * All it does is: have a shorter name (by literally a few characters), and
     * defaults the 3rd parameters to true instead of false.
     *
     * @param $post_id
     * @param $key
     * @param bool $single
     * @return mixed
     */
    public static function meta( $post_id, $key, $single = true ){
        return get_post_meta( $post_id, $key, $single );
    }

    /**
     * Wrapper for get_posts(). Injects some default arguments.
     *
     * @param $args
     * @return \WP_Post[]
     */
    public static function query( array $args ) {
        return get_posts( static::build_query_args( $args ) );
    }

    /**
     * Wrapper for WP_Query(). Injects some default arguments.
     *
     * @param array $args
     * @return WP_Query
     */
    public static function query_via_wp_query( array $args ) {
        return new WP_Query( static::build_query_args( $args ) );
    }

    /**
     * Injects logical default elements into your array, and the post type
     * constant from the class from which you invoke this method.
     *
     * Makes your code a little cleaner in some instances.
     *
     * This exists in its own method so you can choose between get_posts()
     * and new WP_Query()
     *
     * @param $args
     * @return array
     */
    public static function build_query_args( array $args ) {

        $defaults = self::$default_query_args;

        // in case this is called from the super class, do not inject
        // the post type.
        if( static::POST_TYPE ) {
            $defaults['post_type'] = static::POST_TYPE;
        }

        return array_merge( $defaults, $args );
    }

}
