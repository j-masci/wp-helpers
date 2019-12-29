<?php

namespace JM;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * A class which you can extend for each of your custom post types. You can
 * then define static methods related to the post type (ie. getters, setters,
 * or w/e).
 *
 * In my experience, instantiating via a post ID and lazy loading a WP_Post
 * into the object adds much more complexity than it's worth. Instead, just
 * pass an ID or WP_Post object into each static method.
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
     * A map (object) to define your meta keys and map them to the
     * string values used in the database.
     *
     * This helps with code completion, code lookup, etc.
     *
     * For example, if you have a meta key like "name" and put that
     * string everywhere in your code, you can't just find all usages
     * of "name" in your code and expect to find it.
     *
     * Example 1: self::$meta_keys::$name = "_name"
     *
     * Example 2: self::$meta_keys->name = "_name".
     *
     * todo: ... see below...
     *
     * I forget the name of the error but example 2 can cause
     * issues in older version of PHP. For the time being, I'm not
     * forcing you to do anything with this. However, if we define
     * a class with all static methods then now we'll probably have to use
     * reflection to loop through all statically defined properties. I don't
     * want to handle the mess of sometimes static and sometimes not, so,
     * maybe I'll look into this more before using it.
     *
     * @var object
     */
    public static $meta_keys;

    /**
     * Call this on each subclass that you extend...
     *
     * Recommend that you call parent::init() inside the method.
     *
     * You could register your post type in here if you wanted. I'll leave it
     * up to you whether or not you think you want that code in the same place or not.
     */
    public static function init(){

        if ( is_null( self::$meta_keys ) ) {
            self::$meta_keys = new stdClas();
        }
    }

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
        return get_posts( array_merge( self::get_default_query_args(), $args ) );
    }

    /**
     * Wrapper for WP_Query(). Injects some default arguments.
     *
     * @param array $args
     * @return WP_Query
     */
    public static function query_via_wp_query( array $args ) {
        return new WP_Query( array_merge( self::get_default_query_args(), $args ) );
    }

    /**
     * @return array
     */
    public static function get_default_query_args(){

        $ret = [
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ];

        if ( static::POST_TYPE ) {
            $ret['post_type'] = static::POST_TYPE;
        }

        return $ret;
    }
}
