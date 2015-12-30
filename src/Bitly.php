<?php
/**
* Bitly Library for Lumen PHP
*
* @package    bitly
* @author     javier telio z <jtelio118@gmail.com>
* @version    1.1
*
*/
namespace Javiertelioz\BitlyLumen;

class Bitly
{

    /**
     * The URI of the standard bitly v3 API.
     */
    const BITLY_API = 'http://api.bit.ly/v3/';

    /**
     * The URI of the bitly OAuth endpoints.
     */
    const BITLY_OAUTH_API = 'https://api-ssl.bit.ly/v3/';

    /**
     * The URI for OAuth access token requests.
     */
    const BITLY_OAUTH_ACCESS_TOKEN = 'https://api-ssl.bit.ly/oauth/';

    /**
     * Method to get Access Token
     * @var string
     */
    protected $method_login = 'password';

    /**
     * Bitly Username
     * @var null
     */
    protected $username = null;

    /**
     * Bitly Password
     * @var null
     */
    protected $password = null;

    /**
     * Bitly Client Id
     * @var null
     */
    protected $client_id = null;

    /**
     * Bitly Client Secret
     * @var null
     */
    protected $client_secret = null;

    /**
     * Token Access
     * @var null
     */
    protected $token = null;

    /**
     * Set Default Data
     * @param string $username
     * @param string $password
     * @param string $client_id
     * @param string $client_secret
     * @param string $method
     */
    public function __construct($username, $password, $client_id, $client_secret, $method = null) {

        if ($this->method_login == 'password' || $method == null) {
            $this->username = !is_null($username) ? $username : env('BITLY_LOGIN');
            $this->password = !is_null($password) ? $password : env('BITLY_KEY');
            $this->client_id = !is_null($client_id) ? $client_id : env('BITLY_CLIENT_ID');
            $this->client_secret = !is_null($client_secret) ? $client_secret : env('BITLY_CLIENT_SECRET');
            $this->token = $this->bitly_oauth_access_token_via_password();
        }
    }

    /**
     * Returns an OAuth access token as well as API users for a given code.
     * @param  string $code          The OAuth verification code acquired via OAuth’s web authentication protocol.
     * @param  string $redirect      The page to which a user was redirected upon successfully authenticating.
     * @return string                An associative array containing:
     *                                  login: The corresponding bit.ly users username.
     *                                  	- api_key: The corresponding bit.ly users API key.
     *                                   	- access_token: The OAuth access token for specified user.
     */
    public function bitly_oauth_access_token($code, $redirect) {
        $results = array();
        $url = self::BITLY_OAUTH_ACCESS_TOKEN . "access_token";
        $params = array('client_id' => $this->client_id, 'client_secret' => $this->client_secret, 'code' => $code, 'redirect_uri' => $redirect);
        $output = $this->bitly_post_curl($url, $params);
        $parts = explode('&', $output);
        foreach ($parts as $part) {
            $bits = explode('=', $part);
            $results[$bits[0]] = $bits[1];
        }
        return $results;
    }

    /**
     * Returns an OAuth access token via the user's bit.ly login Username and Password
     * @return array An associative array containing:
     *                  - access_token: The OAuth access token for specified user.
     */
    public function bitly_oauth_access_token_via_password() {
        $results = array();
        $url = self::BITLY_OAUTH_ACCESS_TOKEN . "access_token";
        $headers = array();
        $headers[] = 'Authorization: Basic ' . base64_encode($this->client_id . ":" . $this->client_secret);

        $params = array('grant_type' => "password", 'username' => $this->username, 'password' => $this->password);

        $output = $this->bitly_post_curl($url, $params, $headers);
        $decoded_output = json_decode($output, 1);
        $results = array("access_token" => $decoded_output['access_token']);

        return $results;
    }

    /**
     * Format a GET call to the bit.ly API.
     * @see http://code.google.com/p/bitly-api/wiki/ApiDocumentation#/v3/validate
     *
     * @param  string  $endpoint bit.ly API endpoint to call.
     * @param  array  $params   associative array of params related to this call.
     * @param  boolean $complex  set to true if params includes associative arrays itself (or using <php5)
     * @return array            associative array of bit.ly response
     */
    public function get($endpoint, $params, $complex = false) {
        if (!isset($params['access_token'])) {
            $params['access_token'] = $this->token['access_token'];
        }
        $result = array();
        if ($complex) {
            $url_params = "";
            foreach ($params as $key => $val) {
                if (is_array($val)) {
                    // we need to flatten this into one proper command
                    $recs = array();
                    foreach ($val as $rec) {
                        $tmp = explode('/', $rec);
                        $tmp = array_reverse($tmp);
                        array_push($recs, $tmp[0]);
                    }
                    $val = implode('&' . $key . '=', $recs);
                }
                $url_params.= '&' . $key . "=" . $val;
            }
            $url = self::BITLY_OAUTH_API . $endpoint . "?" . substr($url_params, 1);
        }
        else {
            $url = self::BITLY_OAUTH_API . $endpoint . "?" . http_build_query($params);
        }

        $result = json_decode($this->bitly_get_curl($url), true);
        return $result;
    }

    /**
     * Format a POST call to the bit.ly API
     * @param  string $endpoint 	URI to call
     * @param  array $params 		Array of fields to send.
     * @return array 				Associative array of bit.ly response
     */
    public function post($endpoint, $params) {
        if (!isset($params['access_token'])) {
            $params['access_token'] = $this->token['access_token'];
        }
        $result = array();
        $url = self::BITLY_OAUTH_API . $api_endpoint;
        $output = json_decode($this->bitly_post_curl($url, $params), true);
        $result = $output['data'][str_replace('/', '_', $api_endpoint) ];
        $result['status_code'] = $output['status_code'];
        return $result;
    }

    /**
     * Make a GET call to the bit.ly API.
     * @param  string $uri 	URI to call.
     * @return array 		Associative array of bit.ly response
     */
    public function bitly_get_curl($uri) {
        $output = "";
        try {
            $ch = curl_init($uri);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $output = curl_exec($ch);
        }
        catch(Exception $e) {
        }
        return $output;
    }

    /**
     * Make a POST call to the bit.ly API.
     * @param  string $uri          URI to call.
     * @param  array  $fields       Array of fields to send.
     * @param  array  $header_array Array of headers to send.
     * @return array 		Associative array of bit.ly response
     */
    public function bitly_post_curl($uri, $fields, $header_array = array()) {
        $output = "";
        $fields_string = "";
        foreach ($fields as $key => $value) {
            $fields_string.= $key . '=' . urlencode($value) . '&';
        }
        rtrim($fields_string, '&');
        try {
            $ch = curl_init($uri);
            if (is_array($header_array) && !empty($header_array)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array);
            }

            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $output = curl_exec($ch);
        }
        catch(Exception $e) {
        }
        return $output;
    }
}
