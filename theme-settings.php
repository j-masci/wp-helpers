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
 * Helps you easily declare some settings for your
 * theme.
 *
 * Settings with values of NULL must mean no-op. We need to
 * distinguish between false-like values and no value specified.
 *
 * Note that I am forcing you to declare the setting before we
 * act upon it, so that we have the option to check the setting
 * later on.
 *
 * To use the class, extend it and override the values of constants.
 * Then call the commit method statically.
 *
 * Class Theme_Settings
 * @package JM
 */Abstract Class Theme_Settings{

    // todo: add more settings
    const HIDE_ADMIN_BAR = null;
    const SUPPORT_FEATURED_IMAGES = null;

    /**
     * @param Theme_Settings $self
     */
    public static function commit(){

        // todo: ensure that this is the proper check
        if ( static::class === self::class ) {
            throw new Exception( "The commit method must only be called on a class which extends this one." );
        }

        if ( static::HIDE_ADMIN_BAR ) {
            add_filter( 'show_admin_bar', '__return_false' );
        }

        if ( static::SUPPORT_FEATURED_IMAGES ) {
            add_theme_support( 'post-thumbnails' );
        }
    }
}
