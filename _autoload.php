<?php
/**
 * Include just this file to use anything in the library.
 */

// todo: use auto loading instead
include __DIR__ . '/admin-columns.php';
include __DIR__ . '/wp-helper-functions.php';

// perhaps we'll auto load, since some parts of the library might not be useful in many projects.
$autoload_class_map = [
    'Admin_Columns' => __DIR__ . '/admin-columns/index.php'
];

spl_autoload_register( function( $class ) use( &$autoload_class_map ) {

    if ( isset( $autoload_class_map[$class] ) ) {
        include $autoload_class_map[$class];
    }
});