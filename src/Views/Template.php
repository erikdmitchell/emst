<?php

/**
 * Description
 *
 * @package EMST\Views
 */

namespace EMST\Views;

/**
 * Description
 *
 * @package EMST\Views
 */
class Template {

    protected $file;

    public $values;

    function __construct( $filename = '', $base = __DIR__ . '/templates/' ) {
        $file = $base . $filename;

        if ( ! file_exists( $file ) ) {
            die( 'unable to locate file' );
        }

        $this->file = $file;
    }

    // arg must be an array
    public function set( $values ) {
        foreach ( $values as $key => $value ) {
            $this->values[ $key ] = $value;
        }
    }

    // render's html with keys replaced
    public function renderHTML() {
        $contents = file_get_contents( $this->file );

        foreach ( $this->values as $key => $value ) {
            $contents = str_replace( '[@' . $key . ']', $value, $contents );
        }

        echo $contents;
    }

}

