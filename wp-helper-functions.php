<?php
/**
 * Helper functions for strictly WordPress related things
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gets the current URL in a WordPress install.
 *
 * Strips out all $_GET variables. You can wrap this in add_query_arg()
 * if you want to include them.
 *
 * This is generally more reliable than trying to use $_SERVER vars.
 *
 * @return string|void
 */
function jm_get_wp_current_url(){
    global $wp;
    return home_url( $wp->request );
}

/**
 * Simply returns what you pass in. Useful for calling a method
 * on a new class immediately. Ie.
 *
 * jm_return( new Class() )->some_method();
 *
 * @param $thing
 * @return mixed
 */
function jm_return( $thing ){
    return $thing;
}

/**
 * Invokes a function and returns what the function
 * prints.
 *
 * @param callable $function
 * @param array $args
 * @return false|string
 */
function jm_capture( callable $function, $args = [] ){
    ob_start();
    call_user_func_array( $function, $args );
    return ob_get_clean();
}

/**
 * Get the URL to an image via its attachment ID and the size.
 *
 * @param $attachment_id
 * @param string $size
 * @return mixed
 */
function jm_get_image_src( $attachment_id, $size = 'large' ){
    $src = wp_get_attachment_image_src( $attachment_id, $size );
    // better not to prefix the function call with @
    return @$src[0];
}

/**
 * Ie. <div style="{this}"></div>
 *
 * @param $image_url
 * @return string
 */
function jm_get_background_style( $image_url ) {
    // might cause issues with valid URLs containing &
    $i = esc_attr( $image_url );
    return $i ? "background-image: url('" . $i . "');" : "";
}

/**
 * Returns the filename of the page template that you pass in.
 *
 * WordPress figures out this value by scanning for comments in the file. It
 * generally caches this value. If there is discrepancy, you can update the
 * theme version in your style.css.
 *
 * @param $filename
 * @param null $default
 * @return null
 */
function jm_get_page_template_name( $filename, $default = null ) {

    static $templates;

    if ( $templates === null ) {
        $templates = wp_get_theme()->get_page_templates( null, 'page' );
    }

    return isset( $templates[$filename] ) ? $templates[$filename] : $default;
}

/**
 * Returns the name of the PHP file that will be called to render a post
 * whose type is 'page'.
 *
 * @param $post_id
 * @return mixed
 */
function jm_get_page_template( $post_id ) {
    return get_post_meta( $post_id, '_wp_page_template', true );
}

/**
 * @param $key
 * @param $default
 * @return mixed|null
 */
function jm_get_global( $key, $default ) {
    return \JM\Globals::get( $key, $default );
}

/**
 * @param $key
 * @param $value
 */
function jm_set_global( $key, $value ) {
    \JM\Globals::set( $key, $value );
}

/**
 * Safely JSON encode anything into an HTML attribute. Your input
 * might be an array, object, or even HTML.
 *
 * Use $.parseJSON() or JSON.parse() to read from JS.
 *
 * @param $thing
 * @return string
 */
function jm_json_encode_for_attr( $thing ) {
    return htmlspecialchars( json_encode( $thing ), ENT_QUOTES, 'UTF-8' );;
}