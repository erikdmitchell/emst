<?php

/**
 * Description
 *
 * @package EMST
 */

namespace EMST;

/**
 * Description
 *
 * @package EMST
 */
class Functions {

    public function __construct() {
        $this->db = new Database( 'stapi', 'wp', 'wp', 'localhost' );
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
