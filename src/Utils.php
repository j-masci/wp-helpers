<?php

namespace JMasci\WP;

/**
 * Generic WP utils
 *
 * Class Utils
 * @package JMasci\WP
 */
Class Utils{

    /**
     * Gets the current URL in a WordPress install.
     *
     * Strips out anything after ?=. You can use add_query_arg()
     * if you want to add them back.
     *
     * This is generally more reliable than using $_SERVER vars, especially
     * if you're in a dev environment and WordPress is not installed
     * on the domain root.
     *
     * @return string|void
     */
    function get_current_wordpress_url(){
        global $wp;
        return \home_url( $wp->request );
    }

    /**
     * Get the URL to an image via its attachment ID and the size.
     *
     * @param $attachment_id
     * @param string $size
     * @return mixed
     */
    function get_image_src( $attachment_id, $size = 'large' ){
        $src = \wp_get_attachment_image_src( $attachment_id, $size );
        // better not to prefix the function call with @
        return @$src[0];
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
    function get_page_template_name( $filename, $default = null ) {

        static $templates;

        if ( $templates === null ) {
            $templates = \wp_get_theme()->get_page_templates( null, 'page' );
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
    function get_page_template( $post_id ) {
        return \get_post_meta( $post_id, '_wp_page_template', true );
    }

}