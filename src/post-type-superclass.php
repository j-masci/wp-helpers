<?php

namespace JM;

/**
 * A superclass to group static methods related to a custom post type.
 *
 * This class is not meant to be instantiated (nor its subclasses).
 *
 * If you want to make your own instance object to wrap a WP_Post
 * object which wraps a post ID, then go ahead. But in my experience,
 * everything is much simpler then you pass the WP_Post object or post ID
 * into pure static methods instead.
 *
 * Class Post_Type_Static_Methods
 * @package JM
 */
Abstract Class Post_Type_Static_Methods{

    /**
     * Define your post type here.
     */
    const POST_TYPE = "";

    /**
     * An object which maps meta keys to the string values
     * used in the post meta table. 3 options:
     *
     * - Use a stdClass
     * - Create your own class with defined properties (might be better
     * for code completion).
     * - Don't use this at all.
     *
     * Only define your meta keys here if you plan to use this
     * elsewhere in your code.
     *
     * @var null|object
     */
    public static $meta_keys;

    /**
     * There are often injected as defaults into queries.
     *
     * This does not include the post type constant which is also
     * often injected.
     *
     * @var array
     */
    protected static $default_query_args = [
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ];

    /**
     * You might want to call this on your subclasses. But,
     * it might not be necessary (depends on what your subclasses do).
     */
    public static function init(){

        if ( is_null( self::$meta_keys ) ) {
            self::$meta_keys = new \stdClass();
        }
    }

    /**
     * Simply wraps get_post_meta and defaults the 3rd parameter
     * to the more logical value of true.
     *
     * @param $post_id
     * @param $key
     * @param bool $single
     * @return mixed
     */
    public static function get_meta( $post_id, $key, $single = true ){
        return \get_post_meta( $post_id, $key, $single );
    }

    /**
     * Wraps set_post_meta and does practically nothing else. Since
     * we have a get_meta method, I figured I would include this.
     *
     * @param $post_id
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function set_meta( $post_id, $key, $value ){
        return \set_post_meta( $post_id, $key, $value );
    }

    /**
     * Wrapper for get_posts(). Injects some default arguments.
     *
     * @param $args
     * @return \WP_Post[]
     */
    public static function get_posts( array $args ) {
        return \get_posts( array_merge( self::get_default_query_args(), $args ) );
    }

    /**
     * Wrapper for WP_Query(). Injects some default arguments.
     *
     * @param array $args
     * @return \WP_Query
     */
    public static function wp_query( array $args ) {
        return new \WP_Query( array_merge( self::get_default_query_args(), $args ) );
    }

    /**
     * @return array
     */
    public static function get_default_query_args(){

        $ret = self::$default_query_args;

        if ( static::POST_TYPE ) {
            $ret['post_type'] = static::POST_TYPE;
        }

        return $ret;
    }
}
