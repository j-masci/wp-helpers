<?php

namespace JMasci\WP;

/**
 * todo: test support for non post type columns (not even sure what these are.. users? anything else? might already work the way we have it.)
 *
 * todo: make this a standalone module for people that want this but not all my other stuff
 *
 * Class Admin_Columns
 * @package JMasci\WP
 */
Class Admin_Columns{

    /**
     * Inserts an admin column (in a way that's much easier than doing it the default WordPress way).
     *
     * @param array $post_types - post or object types to apply this to.
     * @param $key - Column key (make it unique, ie. "my_image_column")
     * @param $label - Column label
     * @param $count_from_end - the number of columns before the last column to insert this one.
     * @param callable $render_callback - accepts a $post_id and echo's HTML output for the table cell.
     * @param int $add_priority - hook priority for adding the column (this can change the resulting order of columns)
     * @param int $render_priority - hook priority for rendering (this likely does not make any difference).
     */
    public static function insert( array $post_types, $key, $label, callable $render_callback, $count_from_end = 0, $add_priority = 20, $render_priority = 20 ) {
        $add_callback = self::get_add_callback_via_count_from_end( $key, $label, $count_from_end );
        self::insert_the_hard_way( $post_types, $key, $add_callback, $render_callback, $add_priority, $render_priority );
    }

    /**
     * Inserts a column but forces you to define the $add_callback. It's better to use
     * a wrapper function rather than calling this directly.
     *
     * If you call this while passing in a custom $add_callback and $render_callback,
     * then that's fine, but you're not making your life any easier when compared to
     * doing it the regular old WordPress way.
     *
     * Note that the render callback accepts only a post ID and not the column key. This is
     * unlike the render callback that you would pass directly to the WordPress hook.
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
     * This is kind of the main purpose of the entire class, so that when you
     * add columns, you don't have to repeat all of this nasty logic to insert
     * a column in some particular oder. P.s. you can pass this to native
     * WordPress hooks and use only this method while ignoring the rest of this
     * class if you prefer.
     *
     * todo: maybe add more column insertion functions like this one (ie. insert a column before/after column with given key).
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