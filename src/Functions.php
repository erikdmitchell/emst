<?php

/**
 * Description
 *
 * @package EMST\Lib
 */

namespace EMST\Lib;

use EMST\Lib;

/**
 * Description
 *
 * @package EMST\Lib
 */
class Functions {

    public function __construct() {
        $this->db = new Lib\Database( 'stapi', 'wp', 'wp', 'localhost' );
    }

    function init_db() {
        return Database::instance();
    }

    public function get_client_id() {
        $db = Database::instance();

        return $client_id = $db->select( 'admin', array( 'key' => 'client_id' ), 1, '', '', 'value' )->result()[0]->value;
    }

    function get_client_secret() {
        $db = Database::instance();

        return $client_id = $db->select( 'admin', array( 'key' => 'client_secret' ), 1, '', '', 'value' )->result()[0]->value;
    }

}
