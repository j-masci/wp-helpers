<?php
/**
 * Helper functions for strictly WordPress related things
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * In some cases, you might want to just pass in the filename twice.
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
 * @param $post_id
 * @return mixed
 */
function jm_get_page_template( $post_id ) {
    return get_post_meta( $post_id, '_wp_page_template', true );
}

/**
 * @param $attachment_id
 * @param string $size
 * @return mixed
 */
function jm_get_image_src( $attachment_id, $size = 'large' ){
    // better not to prefix the function call with @
    $src = wp_get_attachment_image_src( $attachment_id, $size );
    return @$src[0];
}