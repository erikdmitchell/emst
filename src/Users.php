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
class Users {

    public function __construct() {
        $this->functions = new Functions();
        $this->client_id = $this->functions->get_client_id();
        $this->db = $this->functions->init_db();
    }

    public function init() {
        //$this->check_users_token();
    }

    protected function check_users_token() {
        $users = $this->db->select( 'tokens_sl' )->result();

        // check tokens.
        foreach ( $users as $user ) :
            $this->check_token( $user );
        endforeach;
    }

    private function check_token( $user = '' ) {
        if ( empty( $user ) ) {
            return 'error';
        }
        // until we fix the date issue, make this a link or something.
        //echo '<p>' . $user->expires_at . '|' . time() . '</p>';
        //if ( $user->expires_at < time() ) {
            //echo 'use existing short lived token<br>';
        //} else {
            echo 'update token?not true?<br>';
            $this->refresh_token( $user );
        //}
    }

    private function refresh_token( $user = '' ) {
        $refresh_token = $this->db->select( 'tokens_refresh', array( 'athlete_id' => $user->athlete_id ), 1, '', '', 'refresh_token' )->result()[0]->refresh_token;
        $return = array();
        $token_url = 'https://www.strava.com/api/v3/oauth/token';
        $client_secret = $this->functions->get_client_secret();
        $params =
            'client_id=' . $this->client_id
            . '&client_secret=' . $client_secret
            . '&grant_type=refresh_token'
            . '&refresh_token=' . $refresh_token;

        $curl = curl_init( $token_url );
        curl_setopt( $curl, CURLOPT_HEADER, false );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_POST, true );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $params );

        $json_response = curl_exec( $curl );

        $status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

        curl_close( $curl );

        $response = json_decode( $json_response, true );

        if ( 200 != $status ) {
            $resource = '';
            $code = '';
            $return['action'] = 'error';

            if ( isset( $response['errors']['resource'] ) ) {
                $resource = $response['errors']['resource'];
            }

            if ( isset( $response['errors']['code'] ) ) {
                $code = $response['errors']['code'];
            }

            $return['message'] = $response['message'] . ' for ' . $resource . ' code: ' . $code;

            return $return;
        }

        // update data | scope is always read.
        // $scope = 'read';

        // update tokens_sl
        $this->db->update(
            'tokens_sl',
            array(
                'access_token' => $response['access_token'],
                'expires_at' => $response['expires_at'],
            ),
            array( 'athlete_id' => $user->athlete_id )
        );

        // update tokens_refresh
        $this->db->update( 'tokens_refresh', array( 'refresh_token' => $response['refresh_token'] ), array( 'athlete_id' => $user->athlete_id ) );

        $return['action'] = 'success';
        $return['message'] = 'Token updated!';

        return $return;
    }
    
    public function get_users_data() {
        return $this->db->select( 'tokens_sl' )->result();
    }

}
