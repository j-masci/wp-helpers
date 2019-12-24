<?php

namespace JM;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * todo: could add more "get_add_callback" methods to give more control over the order of columns.
 *
 * Class Admin_Columns
 * @package JM
 */
Class Admin_Columns{

    /**
     * Insert an admin column with just this function call.
     *
     * @param array $post_types
     * @param $key
     * @param $label
     * @param $count_from_end
     * @param callable $render_callback
     * @param int $add_priority
     * @param int $render_priority
     */
    public static function insert( array $post_types, $key, $label, callable $render_callback, $count_from_end = 0, $add_priority = 20, $render_priority = 20 ) {
        $add_callback = self::get_add_callback_via_count_from_end( $key, $label, $count_from_end );
        self::insert_the_hard_way( $post_types, $key, $add_callback, $render_callback, $add_priority, $render_priority );
    }

    /**
     * There should exist wrappers for this method which are easier to call than this one directly,
     * nevertheless, there may be times that you do need to call this.
     *
     * The render callback takes in only a post ID and does not need to worry about the key, which
     * is more intuitive, although it makes it hard (impossible?) to use the same callback
     * for many "columns" (note that the one column can apply to several post types however).
     *
     * It may seem a bit odd that we require $key to be passed into this function for the sake
     * of the render callback, and yet, the $add_callback needs to know about the $key, but we don't pass
     * it along to the $add_callback. Instead, the $add_callback should have the same key bound to
     * its closure. Ideally I would like to omit the $key parameter from this function but then it
     * kind of makes the render callback less intuitive.
     *
     * @param array $post_types
     * @param $key
     * @param callable $add_callback
     * @param callable $render_callback
     * @param int $add_priority
     * @param int $render_priority
     */
    public static function insert_the_hard_way( array $post_types, $key, callable $add_callback, callable $render_callback, $add_priority = 20, $render_priority = 20 ) {

        foreach ( $post_types as $post_type ) {

            // add the column
            add_action( "manage_{$post_type}_posts_columns", $add_callback, $add_priority );

            // render the column (contents).
            add_action( "manage_{$post_type}_posts_custom_column", function( $col, $post_id ) use ( $render_callback, $key ){
                if ( $key && $key == $col ) {
                    // take the $key away from $render_callback and give it only post ID
                    call_user_func_array( $render_callback, [ $post_id ] );
                }
            }, $render_priority, 2 );
        }
    }

    /**
     * Returns the function which adds a column to an array of existing columns,
     * based on the number of columns from the end, ie. 0 means add to the last
     * column.
     *
     * This slightly less intuitive way happens to be useful as date is often
     * the last column, and it turns out to be reasonable to add additional
     * columns before the date.
     *
     * Anchoring the column to always be before the date works too in some cases,
     * but then you have to do ugly fallbacks if the date column does not exist
     * for some reason.
     *
     * @param $key
     * @param $label
     * @param int $count_from_end
     * @return \Closure
     */
    public static function get_add_callback_via_count_from_end( $key, $label, $count_from_end = 0 ){

        return function( $cols ) use( $key, $label, $count_from_end ){

            // adding the column directly to the end is the easiest.
            // I suppose we check <= 0 rather than === 0, this means that
            // we don't promote a potentially bad practice of using -1 to mean
            // that the column shouldn't show up. This would be bad for existing
            // codebases if we had to change how this works.
            if ( $count_from_end <= 0 ) {
                $cols[$key] = $label;
                return $cols;
            }

            $new_cols = [];
            $count = is_array( $cols ) ? count( $cols ) : 0;

            // possibly append the new column as the first element
            if ( $count_from_end >= $count ) {
                $new_cols[$key] = $label;
                // don't return yet
            }

            // loop through existing columns
            $index = -1;
            foreach ( $cols as $ex_key => $col_label ) {

                $index++;

                // append the new column - somewhere in the middle.
                // note: ( $count - $index ) is 1 on the last iteration
                if ( ( $count - $index ) === $count_from_end ) {
                    $new_cols[$key] = $label;
                }

                // add the existing column to the new columns
                $new_cols[$ex_key] = $col_label;
            }

            return $new_cols;
        };
    }
}