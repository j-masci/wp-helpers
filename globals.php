<?php
/**
 * Set and get globals.
 */

namespace JM;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Prefer to keep all related global mutable state in the
 * same array, so that we can easily view it or log it if
 * needed.
 *
 * Class Globals
 * @package JM
 */
Class Globals{

    /**
     * Will allow for public access to this variable in case
     * you want to append more easily or w/e.
     *
     * @var
     */
    public static $data = [];

    /**
     * @param $key
     * @param $value
     */
    public static function set( $key, $value ){
        self::$data[$key] = $value;
    }

    /**
     * @param $key
     * @param null $default
     * @return |null
     */
    public static function get( $key, $default = null ) {
        // intentionally use self, not static. I guess.
        return isset( self::$data[$key] ) ? self::$data[$key] : $default;
    }
}




