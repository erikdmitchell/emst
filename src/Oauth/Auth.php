<?php

/**
 * Description
 *
 * @package EMST\Oauth
 */

namespace EMST\Oauth;

use EMST\Functions;

/**
 * Description
 *
 * @package EMST\Oauth
 */
class Auth {

    public function __construct() {
        $this->functions = new Functions();
        $this->client_id = $this->functions->get_client_id();
        $this->db = $this->functions->init_db();
    }

    public function init() {
        if ( $this->can_validate_app() ) {
            $message = $this->validate_app();

            return '<div class="validate-app ' . $message['action'] . '">' . $message['message'] . '</div>';

            if ( 'error' == $message['action'] ) {
                return '<div class="validate-app reload"><a href="http://' . $_SERVER['HTTP_HOST'] . '">Try Again</a></div>';
            }
        } else {
            return $this->get_authorize_url();
        }
    }

    private function get_authorize_url() {
        $url = 'https://www.strava.com/api/v3/oauth/authorize';
        $redirect_uri = 'http://stapi.test';
        $params =
            '?client_id=' . $this->client_id
            . '&redirect_uri=' . $redirect_uri
            . '&response_type=code'
            . '&approval_prompt=force'
            . '&scope=read';

        $authorization_url = $url . $params;

        return '<a href="' . $authorization_url . '">Authorize App</a>';
    }

    private function can_validate_app() {
        if ( isset( $_GET['state'] ) ) {
            return true;
        }

        // The state parameter will be always included in the response if it was initially provided by the application.
        return false;
    }

    protected function validate_app() {
        $return = array(
            'action' => 'error',
            'message' => 'There was an error.',
        );

        if ( isset( $_GET['error'] ) && 'access_denied' == $_GET['error'] ) {
            $return['action'] = 'error';
            $return['message'] = 'Access denied.';
        }

        if ( isset( $_GET['code'] ) && '' != $_GET['code'] ) {
            return $this->token_exchange( $_GET['code'] );
        }

        return $return;
    }

    private function token_exchange( $code = '' ) {
        $return = array();
        $token_url = 'https://www.strava.com/api/v3/oauth/token';
        $client_secret = get_client_secret();
        $params =
            'client_id=' . $this->client_id
            . '&client_secret=' . $client_secret
            . '&code=' . $code
            . '&grant_type=authorization_code';

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

        // store data | scope is always read.
        // $scope = 'read';

        // write to db - tokens_sl
        $this->db->insert(
            'tokens_sl',
            array(
                'athlete_id' => $response['athlete']['id'],
                // 'scope' => '',
                'expires_at' => $response['expires_at'],
                'access_token' => $response['access_token'],
            )
        );

        // write to db - tokens_refresh
        $this->db->insert(
            'tokens_refresh',
            array(
                'athlete_id' => $response['athlete']['id'],
                // 'scope' => '',
                'refresh_token' => $response['refresh_token'],
            )
        );

        $return['action'] = 'success';
        $return['message'] = 'User authorized!';

        return $return;
    }

}
