<?php

namespace JM;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Holds configuration variables for ajax requests which will be required both
 * on render and on submission.
 *
 * When you print the hidden input fields (via a method), and submit to admin-ajax-config.php,
 * your global callback will be invoked. The global callback runs for all registered
 * handlers, and is responsible for invoking the individual handler's callback.
 *
 * Hidden input fields include a nonce/csrf token, and the class has a method to validate
 * this token. Each registered handler should specify its own nonce secret (a seed to randomize
 * the nonce token).
 *
 * You can implement middleware based on how you define your global and individual
 * callbacks, but nothing is done automatically otherwise.
 *
 * @see __global_ajax_example_usage
 *
 * Class Ajax_Config
 * @package JM
 */
Class Ajax_Config
{

    /**
     * The name of the hidden input field where we print the nonce/csrf token.
     *
     * @var string
     */
    public $nonce_name;

    /**
     * The name of the hidden input field where we store the key (the ID of the
     * registered handler).
     *
     * @var string
     */
    public $key_name;

    /**
     * The value of $_REQUEST['action'] when submitting a form to wp-admin/admin-ajax-config.php.
     *
     * @var string
     */
    public $wp_action;

    /**
     * The registered handlers, each of which is an array.
     *
     * @var array
     */
    private $handlers = [];

    /**
     * Ajax_Config constructor.
     * @param $wp_action
     * @param $key_name
     * @param $nonce_name
     */
    public function __construct($wp_action, $key_name, $nonce_name)
    {
        $this->wp_action = $wp_action;
        $this->key_name = $key_name;
        $this->nonce_name = $nonce_name;
    }

    /**
     * Register a single handler, specifying the callback which should run
     * on the request (if you setup your global handler properly).
     *
     * @param $key
     * @param $nonce_secret
     * @param callable $callback
     */
    public function register($key, $nonce_secret, Callable $callback, $extra = [])
    {
        $this->handlers[$key] = array_merge([
            'key' => $key,
            'nonce_secret' => $nonce_secret,
            'callback' => $callback,
        ], $extra);
    }

    /**
     * Pass in $this->get( "your_registered_key" )
     *
     * @param $handler
     */
    public function print_hidden_fields($handler)
    {
        ?>
        <input type="hidden" name="action" value="<?= esc_attr($this->wp_action); ?>">
        <input type="hidden" name="<?= esc_attr($this->key_name); ?>" value="<?= esc_attr(@$handler['key']); ?>">
        <input type="hidden" name="<?= esc_attr($this->nonce_name); ?>"
               value="<?= wp_create_nonce(@$handler['nonce_secret']); ?>">
        <?php
    }

    /**
     * Register your global handler, and attach it to the actions which
     * fire in admin-ajax.php.
     *
     * @param callable $global_handler - accepts $this as a parameter
     */
    public function commit(Callable $global_handler)
    {

        $invoke = function () use ($global_handler) {
            call_user_func_array($global_handler, [$this]);
        };

        // same action for logged in and non-logged in users.
        // its your job to authenticate users when required.
        add_action("wp_ajax_{$this->wp_action}", $invoke);
        add_action("wp_ajax_nopriv_{$this->wp_action}", $invoke);
    }

    /**
     * Gets the registered handler.
     *
     * @param $key
     * @return array|mixed
     */
    public function get($key)
    {
        return isset($this->handlers[$key]) ? $this->handlers[$key] : [];
    }

    /**
     * Gets the submitted key, ie. $_REQEUST[$this->key_name]
     *
     * @param array $request
     * @return mixed
     */
    public function get_request_key( array $request ) {
        return @$request[$this->key_name];
    }

    /**
     * Gets the submitted nonce value, ie. $_REQEUST[$this->nonce_name]
     *
     * @param array $request
     * @return mixed
     */
    public function get_request_nonce( array $request ) {
        return @$request[$this->nonce_name];
    }

    /**
     * ie. $request = [ $this->key_name => "some_key", $this->nonce_name => "asdhasd71656asd" ]
     *
     * Using the above array, and $this->handlers, (and some WP session stuff), can return true or false.
     *
     * @param array $request
     * @return bool|int
     */
    public function verify_nonce(array $request ){
        $handler = $this->get( $this->get_request_key( $request ));
        return wp_verify_nonce( $this->get_request_nonce( $request ), @$handler['nonce_secret'] );
    }
}

/**
 * Messy to have a function that should not be called, but,
 * easier to explain in code than words.
 */
function __global_ajax_example_usage(){

    // create your own way of accessing the instance globally
    global $ajax_config;
    $ajax_config = new Ajax_Config( "ajax_global", "_handler", "_nonce" );


    // register all of your handlers (or all of the handlers that you want to run
    // via the same global callback).
    $ajax_config->register( "contact_form", "nonce_seed_12345", function(){
        include "some-directory/some-file.php";
    });

    $ajax_config->register( "another_form", "asd9i67asd", function(){
        include "some-directory/some-file-2.php";
    });

    // printing the form ...
    if ( false ) {
        ?>
        <form action="<?= admin_url( 'admin-ajax-config.php' ); ?>">
            <?= ajax()->print_hidden_fields( ajax()->get( "contact_form" )); ?>
        </form>
        <?php
    }

    // commit and register global handler
    $ajax_config->commit( function( $ajax ){

        /** @var Ajax_Config $ajax */

        // check csrf if you want
        if ( ! $ajax->verify_nonce( $_REQUEST ) ) {
            // ...
        }

        // invoke the individual handler
        if ( $handler = $ajax->get( $ajax->get_request_key( $_REQUEST ) ) ) {
            call_user_func_array( $handler['callback'], [] );
        }
    });
}
