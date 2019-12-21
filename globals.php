<?php
/**
 * Set and get globals.
 */

namespace JM;

if ( ! defined( 'ABSPATH' ) ) exit;

Class Globals{

    public static $data;

    public static function set( $key, $value ){
        self::$data[$key] = $value;
    }
}





