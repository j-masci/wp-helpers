<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2019-12-22
 * Time: 12:25 AM
 */

namespace JM;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This is a more complicated way to do things but has
 * some advantages.
 *
 * Does not have auto complete either...
 *
 * Forces you to use a singleton or global...
 *
 * Its extensible and theme settings can register callbacks
 * from anywhere.
 *
 * What if a setting requires more than one callback though?
 *
 * Is this just a bunch of complexity for nothing? Why pass
 * all these callbacks into some stupid class just so that we
 * can couple them all together. Why not just do it the normal
 * way and define constants or whatever, and then call the functions
 * based on those however we feel like it.
 *
 * To do it even better we'd have to add more complexity like defining
 * which settings can mutate, or, locking the mutation after handling
 * all the settings, which just feels like a waste of time and added
 * complexity for a very unlikely chance of having a positive result.
 *
 * Class Settings
 * @package JM
 */
Class Settings{

    private $handlers = [];
    private $settings = [];

    /**
     * @param $key
     * @param null $callback
     */
    public function register_handler( $key, $callback = null ){
        $this->handlers[$key] = $callback;
    }

    /**
     * Call the registered callbacks...
     */
    public function handle(){
        foreach ( $this->handlers as $key => $callback ) {
            if ( is_callable( $callback ) ) {
                call_user_func_array( $callback, [ $this->get( $key ), $key ] );
            }
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function set( $key, $value ){

        // should these be mutable? I prefer no, but, I don't know.
        if ( isset( $this->settings[$key] ) ) {

        }

        $this->settings[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get( $key ){
        return @$this->settings[$key];
    }
}

/**
 * Create your own class which extends this one, and then
 * pass an instance of that into self::commit(). Basically,
 * we're just creating a dictionary of immutable constants.
 * I prefer constants in case we read the settings after commit.
 *
 * Violates SRP, but, its also easy to use, declarative, and useful.
 *
 * todo: would it be better to have a function, register_theme_setting,
 * which accepts the name and a callback to handle its value. This would
 * allow extending possible settings for specific themes.
 *
 * Class Theme_Settings
 * @package JM
 */
Abstract Class Theme_Settings{

    // todo: add things to this.
    const HIDE_ADMIN_BAR = null;
    const SUPPORT_FEATURED_IMAGES = null;

    /**
     * @param Theme_Settings $self
     */
    public static function commit( Theme_Settings $self ){

        if ( $self::HIDE_ADMIN_BAR ) {
            add_theme_support( 'post-thumbnails' );
        }

        if ( $self::SUPPORT_FEATURED_IMAGES ) {
            add_filter( 'show_admin_bar', '__return_false' );
        }
    }
}