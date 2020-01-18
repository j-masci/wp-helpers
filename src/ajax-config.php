<?php

namespace JMasci\WP;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Holds ajax configuration for multiple forms which submit to /wp-admin/admin-ajax.php.
 *
 * You must register a global handler (callback) which invokes the callback for
 * the specific handler. In addition, you find it useful to check CSRF or implement
 * some middleware in the global handler, but its up to you.
 *
 * There are some specifics on how to use the class, which is explained easier
 * in code than in words, so, @see self::__example_usage().
 *
 * As an FYI, this class would be a lot simpler if it was just singleton,
 * but this would take away flexibility.
 *
 * Class Ajax_Config
 * @package JMasci\WP
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
     * The name of the hidden input field where we store the key (the unique ID of the
     * registered handler).
     *
     * @var string
     */
    public $key_name;

    /**
     * The value of $_REQUEST['action'] submitted to admin-ajax.php
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
     * The global handler which invokes a registered handler.
     *
     * @var null|callable
     */
    private $global_handler;

    /**
     * Ajax_Config constructor.
     * @param $global_handler
     * @param $wp_action
     * @param $key_name
     * @param $nonce_name
     */
    public function __construct( callable $global_handler, $wp_action, $key_name, $nonce_name )
    {
        $this->global_handler = $global_handler;
        $this->wp_action = $wp_action;
        $this->key_name = $key_name;
        $this->nonce_name = $nonce_name;

        add_action("wp_ajax_{$this->wp_action}", [ $this, 'admin_ajax_handler' ] );
        add_action("wp_ajax_nopriv_{$this->wp_action}", [ $this, 'admin_ajax_handler' ] );
    }

    /**
     * @hooked 'wp_ajax_{$this->wp_action]'
     * @hooked 'wp_ajax_nopriv_{$this->wp_action]'
     */
    public function admin_ajax_handler(){

        $handler = $this->get( @$_REQUEST[$this->key_name] );
        $handler = $handler ? $handler : [];

        // pass even an empty handler array to the global callback. The global
        // callback may want to fail in a certain way.
        call_user_func_array($this->global_handler, [$handler, $this]);
    }

    /**
     * You might put something like this in your config file or similar.
     */
    public static function __example_usage(){

        // store the instance(s) globally (and make your own way of retrieving that instance).
        global $ajax_config_example_instance;

        // pass the global handler into the constructor which is then hooked onto admin-ajax.php.
        // the global handler must invoke the single handlers callback.
        $ajax_config_example_instance = new Ajax_Config( function( $handler, $ajax ){

            /** @var $ajax Ajax_Config */
            /** @var $handler array */

            if ( ! $handler ) {
                // handle invalid $_REQUEST[$this->key_name]
            }

            if ( ! $ajax->verify_nonce( $handler ) ) {
                // handle invalid nonce/csrf if you want.
            }

            // invoke the callback that you specified in $this->set_handler().
            $ajax::invoke_handler( $handler, [] );

        }, "ajax_global", "_handler", "_nonce" );

        // register handlers:
        $ajax_config_example_instance->set_handler( "form_1", "nonce_seed_12351", function(){
            // handle the submission (include a file maybe)
        });

        $ajax_config_example_instance->set_handler( "form_2", "nonce_seed_134234", function(){
            // handle the submission (include a file maybe)
        });

        // how to print hidden input fields in your form
        $example_form_rendering_function = function() use( $ajax_config_example_instance ){
            /** @var Ajax_Config $ajax_config_example_instance */
            ?>
            <form action="<?= \admin_url( 'admin-ajax.php' ); ?>">
                <?= $ajax_config_example_instance->render_hidden_inputs( 'form_1' ); ?>
            </form>
            <?php
        };
    }

    /**
     * @param $key
     * @param $nonce_secret
     * @param callable $callback
     * @param array $extra
     */
    public function set_handler($key, $nonce_secret, Callable $callback, $extra = [])
    {
        $this->handlers[$key] = array_merge([
            'key' => $key,
            'nonce_secret' => $nonce_secret,
            'callback' => $callback,
        ], $extra);
    }

    /**
     * Upon form submission, you pass in $_REQUEST[$this->key_name].
     *
     * Upon form rendering, you'll pass in a hardcoded value or maybe some defined constant.
     *
     * @param $key
     * @return array|mixed
     */
    public function get_handler($key)
    {
        return isset($this->handlers[$key]) ? $this->handlers[$key] : [];
    }

    /**
     * Pass in the full $handler array, ie. the result of $this->get_handler()
     *
     * @param array $handler
     * @param array $func_args
     * @return mixed
     */
    public static function invoke_handler( array $handler, array $func_args = [] ){

        // check the array key exists because user input can result in an empty $handler array.
        if ( isset( $handler['callback'] ) ) {
            return call_user_func_array( $handler['callback'], $func_args );
        }
    }

    /**
     * Gets the array of data that needs to end up in $_GET, $_POST, or $_REQUEST
     * in admin-ajax.php.
     *
     * @param object $self - ie. $this
     * @param array $handler
     * @return array
     */
    public static function get_fields_arr( $self, array $handler ){
        return [
            'action' => [
                // this name is hardcoded into admin-ajax.php, and must always be "action"
                'name' => 'action',
                'value' => $self->wp_action,
            ],
            'handler' => [
                'name' => $self->key_name,
                'value' => @$handler['key']
            ],
            'nonce' => [
                'name' => $self->nonce_name,
                'value' => \wp_create_nonce( @$handler['nonce_secret'] )
            ]
        ];
    }
    /**
     * @param $handler_key
     * @return false|string
     */
    public function render_hidden_inputs( $handler_key )
    {
        ob_start();
        foreach ( self::get_fields_arr( $this, $this->get_handler( $handler_key ) ) as $arr ) {
            $n = esc_attr( @$arr['name'] );
            $v = esc_attr( @$arr['value'] );
            echo '<input type="hidden" name="' . $n . '" value="' . $v . '">';
        }
        return ob_get_clean();
    }

    /**
     * Pass in the full $handler array, ie $this->get_handler( 'key' ).
     *
     * @param array $handler
     * @return mixed
     */
    public function verify_nonce( array $handler ){
        return \wp_verify_nonce( @$_REQUEST[$this->nonce_name], @$handler['nonce_secret'] );
    }
}