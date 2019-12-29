<?php

namespace JM\WP_Helpers\Tests;

use PHPUnit\Framework\TestCase;

use JM\Arr;
use JM\Str;
use JM\Types;
use JM\Admin_Columns;
use JM\Ajax_Config;
use JM\Post_Type_Superclass;
use JM\Theme_Settings;

Class GlobalTest extends TestCase{

    /**
     *
     */
    public function testStrClass(){
        \JM\Str::perform_tests( $this );
    }
}