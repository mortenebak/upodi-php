<?php

/**
 * Upodi-API-PHP : Simple PHP wrapper for the Upodi.com API
 *
 * PHP version:
 * @package Upodi-API-PHP
 * @author Morten Bak <meb@indexed.dk>
 */

class UpodiAPI
{

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';

    /**
     * @var string
     */
    private $access_key;

    /**
     * @var string
     */
    private $api_version;

    /**
     * The HTTP status code from the previous request
     *
     * @var int
     */
    protected $httpStatusCode;

    /**
     * @var string
     */
    protected $request_method;

    /**
     * @var string
     */
    private $endpoint;
    /**
     * @var array
     */
    private $post_data;
    /**
     * @var string
     */
    private $api_url = "https://api.upodi.io/";

    /**
     * UpodiAPIExchange constructor.
     *
     * Create API access object. Requires an array of settings: access_key
     * These are all available by creating an account on upodi.com
     *
     * Requires the cURL library
     *
     * @throws \RuntimeException When cURL isn't loaded
     * @throws \InvalidArgumentException When incomplete settings parameters are provided
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        // requires cURL
        if (!function_exists('curl_init')) {
            throw new RuntimeException('UpodiAPIExchange requires cURL extension to be loaded, see: http://curl.haxx.se/docs/install.html');
        }

        // Check for missing settings
        if (!isset($settings['access_key']) || !isset($settings['api_version'])) {
            throw new InvalidArgumentException('Incomplete settings passed to UpodiAPIExchange');
        }

        // Setup settings
        $this->access_key = $settings['access_key'];
        $this->api_version = $settings['api_version'];

    }

    /**
     * Private method to generate authorization header used by cURL
     *
     * @return string $return Header used by cURL for request
     */
    private function buildAuthHeader()
    {

        return "Authorization: bearer " . base64_encode($this->access_key);

    }

    /**
     * @param $endpoint
     * @return $this
     * @throws Exception
     */
    public function get($endpoint)
    {
        $this->request_method = self::METHOD_GET;
        $this->endpoint = $endpoint;
        $this->performRequest();
        return $this;
    }

    /**
     * @param $endpoint
     * @param $data
     * @return $this
     * @throws Exception
     */
    public function post($endpoint, $data)
    {
        $this->request_method = self::METHOD_POST;
        $this->endpoint = $endpoint;
        $this->post_data = $data;
        $this->performRequest();
        return $this;
    }

    /**
     * @param $endpoint
     * @return $this
     * @throws Exception
     */
    public function delete($endpoint)
    {
        $this->request_method = self::METHOD_DELETE;
        $this->endpoint = $endpoint;
        $this->performRequest();
        return $this;
    }

    /**
     * @param $endpoint string The endpoint as a string - omit first "/"
     * @param $data array
     * @return $this
     * @throws Exception
     */
    public function patch($endpoint, array $data)
    {
        $this->request_method = self::METHOD_PATCH;
        $this->endpoint = $endpoint;
        $this->post_data = $data;
        $this->performRequest();
        return $this;
    }

    /**
     * Get the HTTP status code for the previous request
     *
     * @return integer
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * @throws Exception
     */
    private function performRequest()
    {

        try {


            $curl = curl_init($this->endpoint);
            $method = trim(strtoupper($this->request_method));

            $headers = [
                'Content-Type: application/json',
                $this->buildAuthHeader(),
            ];

            switch ($method) {
                default:
                case self::METHOD_GET:
                    break;

                case self::METHOD_DELETE:
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                    break;

                case self::METHOD_POST:
                case self::METHOD_PUT:
                case self::METHOD_PATCH:

                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $this->post_data);
                    $headers[] = 'Content-Length: ' . strlen($this->post_data); // @fixme
                    break;

            }

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->api_url . $this->api_version . "/" . $this->endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            ));
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($curl);
            $httpcode = $this->httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($httpcode == 401) {
                throw new \Exception('401 Not authorized');
            }

            if (curl_errno($curl)) {
                throw new \Exception(curl_error($curl));
            }

            $data = json_decode($response);

        } catch (\Exception $e) {
            die($e->getMessage());
        }

        return $data;

    }

}